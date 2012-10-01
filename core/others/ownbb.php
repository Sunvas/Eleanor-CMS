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
abstract class OwnBbCode extends BaseClass
{	const
		SINGLE=false;

	public static function RestrictDisplay()
	{
		return Eleanor::$Template->RestrictedSection(Eleanor::$Language['ownbb']['restrict']);
	}
#Если случается ошибка типа Error: Class 'Eleanor' not found Line: 21 in file ownbb.php - закомментируйте следующую строку
	abstract public static function PreDisplay($t,$p,$c,$cu);

	public static function PreEdit($t,$p,$ct,$cu,$se=self::SINGLE)
	{
		return self::PreSave($t,$p,$ct,true,$se);
	}

	public static function PreSave($t,$p,$ct,$cu,$se=self::SINGLE)
	{
		if(!is_array($p))
			$p=Strings::ParseParams($p,$t);
		$tp=isset($p[$t]) ? '' : ' ';
		if(!$cu or isset($p['noparse']))
		{
			unset($p['noparse']);
			$cu=false;
		}
		foreach($p as $k=>$v)
		{
			if($v==$k)
			{
				$tp.=$k.' ';
				continue;
			}

			if(strpos($v,' ')===false)
				$q=$v;
			elseif(strpos($v,'\'')===false)
				$q='"'.$v.'"';
			elseif(strpos($v,'"')===false)
				$q='\''.$v.'\'';
			else
				$q='"'.str_replace('"','&quot;',$v).'"';

			if($k==$t)
				$tp='='.$q.' '.$tp;
			else
				$tp.=$k.'='.$q;
			$tp.=' ';
		}
		return'['.$t.rtrim($tp).($cu ? '' : ' noparse').']'.$ct.($se ? '' : '[/'.$t.']');
	}
	/*
		Функции, которым для обработки необходимо передать весь массив текста
		public static function TotalPreSave($s,$ts,$cu,$ps,$ct,$cp,$l,$se){return $s;}
		public static function TotalPreEdit($s,$ts,$cu,$ps,$ct,$cp,$l,$se){return $s;}
		public static function TotalPreDisplay($s,$ts,$cu,$ps,$ct,$cp,$l,$se){return $s;}
	*/
}

class OwnBB extends BaseClass
{
	const
		DISPLAY=1,
		SHOW=2,#Это когда мы результат обработки должны сразу показать на экран. Отличие от DISPLAY состсавляет в том, что мы используем разрешение не gr_see, а gr_use!
		EDIT=3,
		SAVE=4;

	public static
		$replace=array(),#Массив замен классов обработчиков BB кодов.
		$bbs=array(),
		$opts=array(),
		$visual=false,#Флаг определяющий, редактор визуальный или нет
		$np;

	/*
		Функция парсит свои ББ коды.
		$s - текст с ББ кодами.
		$type - тип (см. выше константы)
		$codes - только эти ББ коды нужно парсить. Задаются через запятую. Неверный формат недопустим! Например b,i,u
	*/
	public static function Parse($s,$t=self::DISPLAY,$c=array())
	{		$s=self::StoreNotParsed($s,$t);
		$s=self::ParseBBCodes($s,$t,$c);
		return self::ParseNotParsed($s,$t);
	}

	public static function ParseBBCodes($s,$type,$codes=array())
	{		switch($type)
		{
			case self::EDIT:
				$mth='PreEdit';
			break;
			case self::SAVE:
				$mth='PreSave';
			break;
			default:
				$mth='PreDisplay';
		}
		if(!is_array($codes))
			$codes=$codes ? explode(',',$codes) : array();
		$groups=Eleanor::GetUserGroups();
		foreach(self::$bbs as &$bb)
		{
			$ts=explode(',',$bb['tags']);
			if($codes and count(array_intersect($codes,$ts))==0 or !$codes and $bb['special'])
				continue;
			$cu=true;
			if($type==self::DISPLAY and $grs=$bb['gr_see'] or $type==self::SHOW and $grs=$bb['gr_use'] or  $type>self::SHOW and $grs=$bb['gr_use'])
			{				$grs=explode(',',$grs);
				$cu=count(array_intersect($grs,$groups))>0;
			}
			if($type==self::SHOW and !$cu)
				continue;
			$h=(false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p);
			if(isset(self::$replace[$h]))
			{
				$c=self::$replace[$h];
				$cch=false;#Class Check
			}
			else
			{				$c='OwnBbCode_'.$h;
				$cch=true;			}
			foreach($ts as &$t)
			{
				$ocp=-1;
				$cp=0;
				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{
						++$cp;
						continue;
					}
					$tl=strlen($t);
					#Если мы нашли нужный нам тег т.е. i != img (отшибем все следующие знаки после найденного тега - )
					if(trim(substr($s,$cp+$tl+1,1),'=] ')!='')
					{
						++$cp;
						continue;
					}
					$l=strpos($s,']',$cp);
					if($l===false)
					{
						++$cp;
						continue;
					}
					$ps=trim(substr($s,$cp+$tl+1,$l-$cp-$tl-1));
					if($cch and !class_exists($c,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue 3;
					$se=constant($c.'::SINGLE');
					if($se or false===$clpos=stripos($s,'[/'.$t.']',$l+1))
					{
						$l-=$cp-1;#]
						$ct='';
					}
					else
					{
						$ct=substr($s,$l+1,$clpos-$l-1);
						$l=$clpos-$cp+$tl+3;#[/]
					}
					if(method_exists($c,'Total'.$mth))
					{						$s=call_user_func(array($c,'Total'.$mth),$s,$ts,$cu,$ps,$ct,$cp,$l,$se);
						continue 3;					}
					$r=call_user_func(array($c,$mth),$t,$ps,$ct,$cu,$se);
					$s=substr_replace($s,$r,$cp,$l);
					$ocp=$cp;
				}
			}
		}
		return $s;
	}

	public static function StoreNotParsed($s,$type)
	{
		$s=str_replace('<!-- NP ','<!-- ',$s);
		$n=0;
		self::$np=array();
		$groups=Eleanor::GetUserGroups();
		foreach(self::$bbs as &$bb)
		{
			if(!$bb['no_parse'])
				continue;
			if($type==self::DISPLAY and $grs=$bb['gr_see'] or $type==self::SHOW and $grs=$bb['gr_use'] or $type>self::SHOW and $grs=$bb['gr_use'])
			{
				$grs=explode(',',$grs);
				if(count(array_intersect($grs,$groups))==0)
					continue;
			}
			if($grs=$bb['gr_use'])
			{
				$grs=explode(',',$grs);
				if(count(array_intersect($grs,$groups))==0)
					continue;
			}
			$h=(false===$p=strrpos($bb['handler'],'.')) ? $bb['handler'] : substr($bb['handler'],0,$p);
			if(isset(self::$replace[$h]))
			{
				$c=self::$replace[$h];
				$cch=false;#Class Check
			}
			else
			{
				$c='OwnBbCode_'.$h;
				$cch=true;
			}
			$ts=explode(',',$bb['tags']);
			foreach($ts as &$t)
			{
				$ocp=-1;
				$cp=0;
				while(false!==$cp=stripos($s,'['.$t,$cp))
				{
					if($cp==$ocp)
					{						++$cp;
						continue;
					}
					$tl=strlen($t);
					#Если мы нашли нужный нам тег т.е. i != img (отшибем все следующие знаки после найденного тега - )
					if(trim(substr($s,$cp+$tl+1,1),'=] ')!='')
					{
						++$cp;
						continue;
					}
					if($cch and !class_exists($c,false) and !include(Eleanor::$root.'core/ownbb/'.$bb['handler']))
						continue 3;

					if(false!==$nop=strpos($s,'noparse]',$cp) and $nop<strpos($s,']',$cp))
					{
						++$cp;
						continue;
					}

					$se=constant($c.'::SINGLE');
					if($se or false===$l=strpos($s,'[/'.$t.']',$cp))
					{
						$l=strpos($s,']',$cp);
						if($l===false)
						{							++$cp;
							continue;
						}
						$l-=$cp-1;#]
					}
					else
						$l-=$cp-$tl-3;#[/]
					$r='<!-- NP '.$n++.' -->';
					$ct=substr($s,$cp,$l);
					$s=substr_replace($s,$r,$cp,$l);
					self::$np[]=array(
						'f'=>$r,
						't'=>$ct,
						'code'=>$bb['sp_tags'] ? $t.','.$bb['sp_tags'] : $t,
					);
					$ocp=$cp;
				}
			}
		}
		return$s;
	}

	/*
		Если $type===false - ничего не парсим.
	*/
	public static function ParseNotParsed($s,$type)
	{
		if(self::$np)
			if($type)
				foreach(self::$np as &$v)
					$s=str_replace($v['f'],self::ParseBBCodes($v['t'],$type,$v['code']),$s);
			else
				foreach(self::$np as &$v)
					$s=str_replace($v['f'],$v['t'],$s);
		self::$np=array();
		return$s;
	}

	public static function RecacheBB()
	{
		$bbs=array();
		$R=Eleanor::$Db->Query('SELECT `handler`,`tags`,`no_parse`,`special`,`sp_tags`,`gr_use`,`gr_see`,`sb` FROM `'.P.'ownbb` WHERE `active`=1 ORDER BY `pos` ASC');
		while($a=$R->fetch_assoc())
			$bbs[]=$a;
		Eleanor::$Cache->Put('ownbb',$bbs,0);
		return$bbs;
	}
}

OwnBB::$bbs=Eleanor::$Cache->Get('ownbb');
if(OwnBB::$bbs===false)
	OwnBB::$bbs=OwnBB::RecacheBB();