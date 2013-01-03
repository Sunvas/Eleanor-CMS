<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class TaskSpecial_Sitemap extends BaseClass implements Task
{
	private
		$opts=array(),
		$data=array(),

		$mtotal=0,
		$total=array(),

		$fh=false,
		$smtotal=0;

	public function __construct($opts)
	{
		$this->opts=$opts;
	}

	public function Run($data)
	{
		$this->data=$data;
		$R=Eleanor::$Db->Query('SELECT `modules`,`file`,`compress`,`per_time`,`fulllink`,`sendservice` FROM `'.P.'sitemaps` WHERE `id`='.$this->opts['id'].' LIMIT 1');
		if(!$a=$R->fetch_assoc() or !$a['modules'])
			return true;
		$a['modules']=explode(',,',trim($a['modules'],','));
		$marr=$mapi=array();
		$opts=array(
			'per_time'=>$a['per_time'],
		);

		$R=Eleanor::$Db->Query('SELECT `id`,`sections`,`path`,`api` FROM `'.P.'modules` WHERE `api`!=\'\' AND `id`'.Eleanor::$Db->In($a['modules']));
		while($m=$R->fetch_assoc())
		{
			$api=Eleanor::FormatPath($m['api'],$m['path']);
			$class='Api'.basename(dirname($api));
			do
			{
				if(class_exists($class,false))
					break;
				if(is_file($api))
				{
					include$api;
					if(class_exists($class,false))
						break;
				}
				continue 2;
			}while(false);
			if(!method_exists($class,'SitemapGenerate'))
				continue;
			$mapi[$m['id']]=new$class;
			$marr[$m['id']]['sections']=unserialize($m['sections']);
		}
		if(!$mapi)
			return true;

		$f=Eleanor::FormatPath($a['file']);
		$fx=$f.'.xml';
		$fgz=$f.'.xml.gz';
		$fxex=is_file($fx);

		if(!$this->data and $fxex)
		{
			Files::Delete($fx);
			Files::Delete($fgz);
			$fxex=false;
		}

		if(!isset($this->data['total'],$this->data['already'],$this->data['modules']))
		{
			foreach($mapi as $k=>&$v)
			{
				$this->mtotal=$k;
				$v->SitemapGenerate(
					isset($data['m'][$k]) ? $data['m'][$k] : false,
					isset($this->opts['m'][$k]) ? $this->opts['m'][$k] : false,
					$opts+array('callback'=>array($this,'GetNumbers'),'type'=>'number')+$marr[$k]
				);
			}
			$total=array_sum($this->total);
			if($total>0)
			{
				if($a['compress'])
					$a['compress']=function_exists('gzopen') && is_file($fgz) && !$fxex;

				if($a['compress'] and $fh=gzopen($fgz,'r'))
				{
					$this->FOpen($fx,'w');
					while(!gzeof($fh))
						fwrite($this->fh,gzread($fh,1024*64));
					gzclose($fh);
				}

				$this->data['total']=$total;
				$this->data['already']=0;
				$this->data['modules']=array();
				foreach($this->total as $k=>&$v)
					if($v>0)
						$this->data['modules'][$k]=$v;
			}
			else
				$this->data['modules']=false;
			$this->data['changed']=false;
		}
		if(!$this->data['modules'])
		{
			if(isset($this->data['sent']))
			{
				switch(array_pop($this->data['sent']))
				{
					case'google':
						$s='http://google.com/webmasters/sitemaps/ping?sitemap=%s';
					break;
					case'yahoo!':
						$s='http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=SitemapWriter&url=%s';
					break;
					case'ask.com':
						$s='http://submissions.ask.com/ping?sitemap=%s';
					break;
					case'bing':
						$s='http://www.bing.com/webmaster/ping.aspx?siteMap=%s';
					break;
					default:
						$s=false;
				}
				if($s)
				{
					$cu=curl_init(sprintf($s,urlencode(PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$a['file'].($a['compress'] ? '.gz' : '.xml'))));
					curl_setopt_array($cu,array(
						CURLOPT_RETURNTRANSFER=>true,
						CURLOPT_TIMEOUT=>10,
						CURLOPT_HEADER=>false,
					));
					curl_exec($cu);
					curl_close($cu);
				}
			}
			elseif($this->data['changed'])
			{
				if($a['compress'] and function_exists('gzopen') and $hgz=gzopen($fgz,'w9'))
				{
					$this->FOpen($fx,'r');
					while(!feof($this->fh))
						gzwrite($hgz,fread($this->fh,1024*64));
					gzclose($hgz);
					fclose($this->fh);
					#Удаление оригинального файла
					#Files::Delete($fx);
				}
				$this->data['sent']=$a['sendservice'] ? explode(',,',trim($a['sendservice'],',')) : false;
			}
			else
				$this->data['sent']=false;
			if($this->data['sent'])
				return false;
			else
			{
				unset($this->data['total'],$this->data['already'],$this->data['modules'],$this->data['sent'],$this->data['changed']);
				return true;
			}
		}
		$this->FOpen($fx,$fxex ? 'r+' : 'w');
		if(!$fxex)
			fwrite($this->fh,'<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="'.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.'addons/sitemap.xsl"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');
		do
		{
			$m=key($this->data['modules']);
			if(isset($mapi[$m]))
			{
				$this->full=$a['fulllink'];
				$this->data['m'][$m]=$mapi[$m]->SitemapGenerate(
					isset($data['m'][$m]) ? $data['m'][$m] : array(),
					isset($this->opts['m'][$m]) ? $this->opts['m'][$m] : array(),
					$opts+array('callback'=>array($this,'Sitemap'),'type'=>'get')+$marr[$m]
				);
				if($this->smtotal>0)
				{
					$this->data['changed']=true;
					$this->data['already']+=$this->smtotal;
					$this->data['modules'][$m]-=$this->smtotal;
					if($this->data['modules'][$m]<0)
					{
						$this->data['total']-=$this->data['modules'][$m];
						$this->data['modules'][$m]=0;
					}
					break;
				}
				$this->data['total']-=$this->data['modules'][$m];
				$this->data['modules'][$m]=0;
			}
			unset($this->data['modules'][$m]);
		}while(false);
		fclose($this->fh);
		Eleanor::$Db->Update(P.'sitemaps',array('already'=>$this->data['already'],'total'=>$this->data['total']),'`id`='.$this->opts['id'].' LIMIT 1');
		return false;
	}

	public function GetNumbers($n)
	{
		if($n>0)
			$this->total[$this->mtotal]=(int)$n;
	}

	public function Sitemap(array$a=array(),$isa=false)
	{
		if($isa)
		{
			foreach($a as &$v)
				$this->Sitemap($v);
			return;
		}
		$a+=array(
			'loc'=>false,
			'lastmod'=>'',
			'changefreq'=>'',
			'priority'=>'',
		);
		if(!$a['loc'])
			return;

		if($this->full and preg_match('#^[a-z]{3,6}://#i',$a['loc'])==0)
			$a['loc']=PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.$a['loc'];
		$a['loc']='<loc>'.$a['loc'].'</loc>';

		if($a['lastmod'])
		{
			if(is_int($a['lastmod']))
			{
				$t=date_offset_get(date_create());
				$s=$t>0 ? '+' : '-';
				$t=abs($t);
				$m=floor($t/3600);
				$s.=(strlen($m)==1 ? '0' : '').$m.':';
				$m=$t%3600;
				$s.=(strlen($m)==1 ? '0' : '').$m;
				$a['lastmod']=date('Y-m-d\TH:i:s').$s;
			}
			$a['lastmod']='<lastmod>'.$a['lastmod'].'</lastmod>';
		}

		if($a['changefreq'])
			$a['changefreq']=in_array($a['changefreq'],array('always','hourly','daily','weekly','monthly','yearly','never')) ? '<changefreq>'.$a['changefreq'].'</changefreq>' : '';

		if($a['priority'])
		{
			$a['priority']=floatval($a['priority']);
			if($a['priority']<=0)
				$a['priority']='0.1';
			elseif($a['priority']>1)
				$a['priority']='1.0';
			$a['priority']='<priority>'.$a['priority'].'</priority>';
		}

		$this->smtotal++;
		fseek($this->fh,-9,SEEK_END);
		fwrite($this->fh,'<url>'.join($a).'</url></urlset>');
	}

	private function FOpen($fx,$m)
	{
		if(!$this->fh and !$this->fh=fopen($fx,$m))
		{
			new EE('Unable to access file '.$fx.'.xml',EE::ENV);
			return true;
		}
	}

	public function GetNextRunInfo()
	{
		return$this->data;
	}
}