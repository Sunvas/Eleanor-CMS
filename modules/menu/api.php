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
class ApiMenu extends BaseClass
{
	private
		$config=array();

	public function __construct($config=array())
	{
		$this->config=$config;
	}

	public function GetOrderedList($lang=false,$status=1)
	{
		if(!$lang)
			$lang=Language::$main;

		if($db=Eleanor::$Cache->Get($this->config['n'].'_dump'.$status.'_'.$lang))
			return $db;

		$maxlen=0;
		$res=$to1sort=$to2sort=$db=array();
		$R=Eleanor::$Db->Query('SELECT `id`,`url`,`eval_url`,`title`,`pos`,`parents`,`status` FROM `'.$this->config['t'].'` LEFT JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$lang.'\')'.($status===false ? '' : ' AND `status`=1'));
		while($a=$R->fetch_assoc())
		{
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
			if($db[$k]['parents'] and preg_match('#(\d+),$#',$db[$k]['parents'],$p)>0)
				if(isset($to2sort[$p[1]]))
					$to2sort[$k]=$to2sort[$p[1]].','.$to2sort[$k];
				else
					unset($to1sort[$k],$db[$k],$to2sort[$k]);

		foreach($to2sort as $k=>&$v)
			$v.=str_repeat(',0',$maxlen-substr_count($db[$k]['parents'],','));

		natsort($to2sort);
		foreach($to2sort as $k=>&$v)
			$res[$k]=$db[$k];
		Eleanor::$Cache->Put($this->config['n'].'_dump'.$status.'_'.$lang,$res,86400);
		return $res;
	}

	public static function BuildMultilineMenu($a,$first='<ul>')
	{
		$parents=reset($a);
		$l=strlen($parents['parents']);
		$c=$first;
		$n=-1;
		$onp=false;
		foreach($a as &$v)
		{
			++$n;
			$nl=strlen($v['parents']);
			if($nl!=$l)
			{
				if($l>$nl)
					break;
				elseif(!$onp)
				{
					$c.=self::BuildMultilineMenu(array_slice($a,$n));
					$onp=true;
				}
				continue;
			}
			if($n>0)
				$c.='</li>';
			$c.='<li><a href="'.$v['url'].'"'.$v['params'].'>'.$v['title'].'</a>';
			$onp=false;
		}
		return$c.'</li></ul>';
	}
}