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

class Blocks
{
	public static
		$blocks;

	/**
	 * Получение блоков определенного места. Метод, выполняя все блоки, возвращает готовый HTML для непосредственной его вставке на странице.
	 *
	 * @param string|array $place Названия мест, например: left, right, top, bottom
	 */
	public static function Get($place)
	{
		if(!isset(self::$blocks))
		{
			$order=$blocks=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`code`,`blocks` FROM `'.P.'blocks_ids` INNER JOIN `'.P.'blocks_groups` USING(`id`) WHERE `service`=\''.Eleanor::$service.'\'');
			while($a=$R->fetch_assoc())
			{
				$order[$a['id']]=(float)self::QuietEval($a['code']);
				$blocks[$a['id']]=$a['blocks'];
			}
			if($order)
			{
				arsort($order,SORT_NUMERIC);
				$p=reset($order);
			}
			else
				$p=0;
			if($p>0)
			{
				$k=key($order);
				self::$blocks=(array)unserialize($blocks[$k]);
			}
			else
			{
				$b=(array)Eleanor::$Cache->Get('blocks-'.Eleanor::$service,true);
				self::$blocks=isset($b['blocks']) ? (array)$b['blocks'] : array();
			}
		}

		$dump=$ids=array();
		$isa=is_array($place);
		if(!$isa)
			$place=(array)$place;
		foreach($place as $pl)
			if(isset(self::$blocks[$pl]))
				$ids=array_merge($ids,self::$blocks[$pl]);

		if($ids)
		{
			$t=time();
			$R=Eleanor::$Db->Query('SELECT `id`,`ctype`,`file`,`user_groups`,`showfrom`,`showto`,`textfile`,`template`,`notemplate`,`vars`,`title`,`text`,`config` FROM `'.P.'blocks` INNER JOIN `'.P.'blocks_l` USING(`id`) WHERE `id`'.Eleanor::$Db->In($ids).' AND `language`IN(\'\',\''.Language::$main.'\') AND `status`IN(1,-3)');
			while($a=$R->fetch_assoc())
			{
				if($a['user_groups'] and !array_intersect(explode(',,',trim($a['user_groups'],',')),Eleanor::GetUserGroups()))
					continue;

				if((int)$a['showfrom'] and $t<strtotime($a['showfrom']))
					continue;

				if((int)$a['showto'] and $t>=strtotime($a['showto']))
				{
					Eleanor::$Db->Update(P.'blocks',array('status'=>-2),'`id`='.$a['id'].' LIMIT 1');
					continue;
				}

				if($a['ctype']=='file')
				{
					$f=Eleanor::$root.$a['file'];
					if(!$a['file'] or !is_file($f))
						continue;
					if($a['textfile'])
						$a['text']=file_get_contents($f);
					else
					{
						$vars=$a['vars'] ? (array)unserialize($a['vars']) : array();
						if($a['config'])
							$vars['CONFIG']=$a['config'] ? (array)unserialize($a['config']) : array();
						$a['text']=Eleanor::LoadFileTemplate(Eleanor::$root.$a['file'],$vars);
						if(is_object($a['text']) and $a['text'] instanceof Template and $a['text']->s=='')
							$a['text']=false;
					}
				}
				else
					$a['text']=OwnBB::Parse($a['text']);
				if($a['text'])
					$dump[$a['id']]=array(
						't'=>$a['title'],
						'c'=>$a['text'],
						'tpl'=>$a['template'] ? $a['template'] : !$a['notemplate'],
					);
			}
		}

		$r=array_combine($place,array_fill(0,count($place),''));
		foreach($place as $pl)
		{
			$s='';
			if(isset(self::$blocks[$pl]))
			{
				$Tpl=Eleanor::$Template;
				foreach(self::$blocks[$pl] as &$v)
				{
					if(!isset($dump[$v]))
						continue;

					$b=$dump[$v];
					if($b['tpl'])
						$s.=$Tpl($b['tpl']===true ? 'Blocks_'.$pl : $b['tpl'],array('title'=>$b['t'],'content'=>$b['c']));
					else
						$s.=$b['c'];
				}
			}
			$r[$pl]=$s;
		}
		return$isa ? $r : reset($r);
	}

	/**
	 * Оёбертка для eval, чтобы тот не испортил нам доступные переменные
	 *
	 * @param string Неявная переменная, код, который должен быть исполнен
	 */
	protected static function QuietEval()
	{
		return eval(func_get_arg(0));
	}
}