<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
class ApiStatic extends BaseClass
{
	private
		$config=array(),
		$urls;

	public function __construct($config=array())
	{
		$this->config=$config ? $config : include dirname(__file__).'/config.php';
	}

	public function QuickMenu($type='admin',$module=array())
	{
		if(!isset(Eleanor::$Language[__class__]))
			Eleanor::$Language->Load(dirname(__file__).'/api-*.php',__class__);
		if(!is_array($module['sections']))
		{
			$module['sections']=unserialize($module['sections']);
			foreach($module['sections'] as &$v)
				if(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
					$v=reset($v[Language::$main]);
				else
					$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
		}
		return array(
			array(
				'title'=>Eleanor::$Language[__class__]['add'],
				'href'=>Eleanor::$services['admin']['file'].'?section=modules&amp;module='.urlencode($module['sections']['static']).'&amp;do=add',
			),
		);
	}

	public function GetOrderedList($lang=false,$parent=false)
	{
		if(!$lang)
			$lang=Language::$main;

		if($db=Eleanor::$Cache->Get($this->config['n'].'_dump_'.$parent.$lang))
			return $db;

		$maxlen=0;
		$res=$to1sort=$to2sort=$db=array();

		if($parent)
		{
			$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$this->config['t'].'` WHERE `id`='.(int)$parent.' LIMIT 1');
			if(!$a=$R->fetch_assoc())
				return array();
			$repl=$a['parents'].$parent.',';
			$where=' AND `parents` LIKE \''.$repl.'%\'';
		}
		else
			$where=$repl='';

		$R=Eleanor::$Db->Query('SELECT `id`,`uri`,`title`,`parents`,`pos` FROM `'.$this->config['t'].'` LEFT JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$lang.'\') AND `status`=1'.$where);
		while($a=$R->fetch_assoc())
		{
			if($repl)
				$a['parents']=substr($a['parents'],strlen($repl));
			if($a['parents'])
			{
				$cnt=substr_count($a['parents'],',');
				$to1sort[$a['id']]=$cnt;
				$maxlen=max($cnt,$maxlen);
			}
			$db[$a['id']]=array_slice($a,1);
			$to2sort[$a['id']]=$a['pos'];
		}
		asort($to1sort,SORT_NUMERIC);

		foreach($to1sort as $k=>&$v)
			if($db[$k]['parents'] and preg_match('#(\d+),$#',$db[$k]['parents'],$p)>0 and $parent!=$p[1])
				if(isset($to2sort[$p[1]]))
					$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
				else
					unset($to2sort[$k]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

		natsort($to2sort);
		foreach($to2sort as $k=>&$v)
			$res[$k]=$db[$k];
		Eleanor::$Cache->Put($this->config['n'].'_dump_'.$parent.$lang,$res,86400);
		return$res;
	}

	public function LangUrl($q,$lang)
	{
		$El=Eleanor::getInstance();
		if(is_array($q))
			$trace=false;
		else
		{
			$q=$El->Url->string ? $El->Url->Parse(array(),true) : array();
			$trace=isset($q['']) ? $q[''] : false;
		}

		if(Eleanor::$service!='user')
			return$El->Url->Construct($q);

		$id=isset($q['id']) ? (int)$q['id'] : false;
		$this->config=include(dirname(__file__).'/config.php');
		$parents='';
		if($id)
		{
			$R=Eleanor::$Db->Query('SELECT `parents` FROM `'.$this->config['t'].'` WHERE `status`=1 AND `id`='.$id.' LIMIT 1');
			if(!list($parents)=$R->fetch_row())
				return;
		}
		if($trace)
		{
			$requrl=reset($trace);
			$R=Eleanor::$Db->Query('SELECT `id`,`parents`,`uri` FROM `'.$this->config['t'].'` INNER JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `uri`'.Eleanor::$Db->In($trace).' AND `status`=1 ORDER BY `parents` ASC');
			if($R->num_rows>0)
			{
				$uri='';
				while($a=$R->fetch_assoc())
					if($parents==$a['parents'])
					{
						$id=$a['id'];
						$uri=$a['uri'];
						$parents.=$a['id'].',';
						if(mb_strtolower($uri)==mb_strtolower($a['uri']))
							$requrl=true;
					}
				if(mb_strtolower(end($trace))!=mb_strtolower($uri) or $requrl!==true)
					return;
			}
		}
		$u=$this->GetUrl($id,$lang);
		return $u ? $El->Url->Construct($u) : $El->Url->Prefix();
	}

	public function GetUrl($id,$lang=false)
	{
		if(!$lang)
			$lang=Language::$main;
		$this->urls=Eleanor::$Cache->Get($this->config['n'].'_urls_'.$lang,false);
		if($this->urls===false)
		{
			$tmp=$this->GetOrderedList($lang);
			$this->urls=array();
			foreach($tmp as $k=>&$v)
				$this->urls[$k]=array(
					'parents'=>$v['parents'],
					'uri'=>$v['uri'],
				);
			Eleanor::$Cache->Put($this->config['n'].'_urls_'.$lang,$this->urls,7200,false);
		}
		if(!isset($this->urls[$id]))
			return;

		$El=Eleanor::getInstance();
		if(!isset($this->urls[$id]))
			return $El->Url->Prefix();
		$params=array();
		$lastu=$this->urls[$id]['uri'];
		if($El->Url->furl and $this->urls[$id]['parents'] and $lastu)
		{
			foreach(explode(',',rtrim($this->urls[$id]['parents'],',')) as $v)
				if(isset($this->urls[$v]))
					if($this->urls[$v]['uri'])
						$params[]=array($this->urls[$v]['uri']);
					else
					{
						$lastu='';
						break;
					}
		}
		$params[]=array($lastu,'id'=>$id);
		return$params;
	}

	public function SitemapConfigure(&$post,$ti=13)
	{
		if(!isset(Eleanor::$Language[__class__]))
			Eleanor::$Language->Load(dirname(__file__).'/api-*.php',__class__);
		return array(
			'pp'=>array(
				'title'=>Eleanor::$Language[__class__]['pp'],
				'type'=>'input',
				'default'=>'0.7',
				'bypost'=>&$post,
				'options'=>array(
					'type'=>'number',
					'extra'=>array(
						'tabindex'=>$ti++,
						'min'=>0.1,
						'max'=>1,
						'step'=>0.1,
					),
				),
			),
			'ps'=>array(
				'title'=>Eleanor::$Language[__class__]['ps'],
				'type'=>'input',
				'default'=>'0.5',
				'bypost'=>&$post,
				'options'=>array(
					'type'=>'number',
					'extra'=>array(
						'tabindex'=>$ti++,
						'min'=>0.1,
						'max'=>1,
						'step'=>0.1,
					),
				),
			),
		);
	}

	/*
		$data - данные, полученные от этой функции на предыдущем этапе
		$conf - конфигурация от функции SitemapConfigure
		$opts - опции, где:
			per_time - количество ссылок за один раз.
			type (тип данных):
				number - получить полное число всех новых ссылок
				get - получить ссылки
			callback - функция, которую следует вызать для отправки результата
			sections - секции модуля
	*/
	public function SitemapGenerate($data,$conf,$opts)
	{
		$conf+=array(
			'pp'=>0.7,
			'ps'=>0.5,
		);
		$limit=$opts['per_time'];
		$vars=Eleanor::LoadOptions('site',true);
		$Url=new Url;
		$Url->furl=$vars['furl'];
		$Url->delimiter=$vars['url_static_delimiter'];
		$Url->defis=$vars['url_static_defis'];
		$Url->ending=$vars['url_static_ending'];

		$Url->special=$Url->furl ? '' : Eleanor::$filename.'?';
		foreach(Eleanor::$langs as $lang=>&$_)
		{
			if($limit<1)
				break;
			if(!isset($data[$lang]))
				$data[$lang]=array(
					'st'=>false,
					'pages'=>false,
				);
			if($data[$lang]['st'] and $data[$lang]['pages'])
				continue;

			$qlang=$lang==LANGUAGE ? 'IN (\'\',\''.$lang.'\')' : '=\''.$lang.'\'';
			if($opts['type']=='number')
			{
				if(!$data[$lang]['st'])
					call_user_func($opts['callback'],1);
				if(!$data[$lang]['pages'])
				{
					$R=Eleanor::$Db->Query('SELECT COUNT(`id`) `cnt` FROM `'.$this->config['t'].'` INNER JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language`'.$qlang.' AND `status`=1');
					list($cnt)=$R->fetch_row();
					call_user_func($opts['callback'],$cnt);
				}
			}
			else
			{
				$sect=array();
				foreach($opts['sections'] as $k=>$v)
				{
					if(Eleanor::$vars['multilang'] and isset($v[$lang]))
						$v=reset($v[$lang]);
					else
						$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
					$sect[$k]=$v;
				}
				$sect=reset($sect);
				$Url->SetPrefix(Eleanor::$vars['multilang'] && $lang!=LANGUAGE ? array('lang'=>$_['uri'],'module'=>$sect) : array('module'=>$sect));

				if(!$data[$lang]['st'])
				{
					$a=array(
						$Url->Construct(array()),
					);
					foreach($a as &$v)
						call_user_func(
							$opts['callback'],
							array(
								'loc'=>$v,
								'changefreq'=>'monthly',
								'priority'=>$conf['ps'],
							)
						);
					$data[$lang]['st']=true;
				}
				$r=$this->GetOrderedList($lang);
				foreach($r as $k=>&$v)
				{
					call_user_func(
						$opts['callback'],
						array(
							'loc'=>$Url->Construct($this->GetUrl($k,$lang)),
							'changefreq'=>'monthly',
							'priority'=>$conf['pp'],
						)
					);
					$limit--;
				}
				$data[$lang]['pages']=true;
			}
		}
		return$data;
	}
}