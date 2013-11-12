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

class Modules
{
	/**
	 * Запуск модуля
	 * @param string $p Путь к каталогу модуля
	 * @param bool $us От "UseService", флаг указывающий на необходимость добавления к $p каталога с именем сервиса
	 * @param string $f Файл, который будет проинклужен
	 */
	public static function Load($p,$us=true,$f='index.php')
	{
		if(isset(Eleanor::$Template))
			Eleanor::$Template->paths[__class__]=$p.'Template/';
		if($us)
			$p.=Eleanor::$service.DIRECTORY_SEPARATOR;
		$p.=$f;
		if(is_file($p))
		{
			ob_start();
			register_shutdown_function(array(__class__,'FatalHandler'));
			Eleanor::getInstance()->e_g_l=false;
			$r=include$p;
			return$r===null ? true : $r;
		}
		else
			throw new EE('Missing file '.substr($p,strlen(Eleanor::$root)),EE::ENV);
	}

	/**
	 * Перехват фатальной ошибки модуля, например PARSE_ERROR
	 */
	public static function FatalHandler()
	{
		$e=error_get_last();
		if($e && $e!=Eleanor::getInstance()->e_g_l && ($e['type'] & (E_ERROR|E_PARSE|E_COMPILE_ERROR|E_CORE_ERROR)))
		{
			$c=ob_get_contents();
			if($c!==false)
				ob_end_clean();
			Eleanor::ErrorHandle($e['type'],$e['message'],$e['file'],$e['line']);
			Error($c ? $c : $e['message']);
			Eleanor::getInstance()->e_g_l=$e;
		}
		elseif(ob_get_contents()!==false)
			ob_end_flush();
	}

	/**
	 * Получение кэша имен модулей и секций
	 * @param string|FALSE $s Название сервиса системы
	 * @param string|FALSE $l Язык
	 * @param bool Флаг регенерации кэша
	 */
	public static function GetCache($s=false,$l=false,$force=false)
	{
		if(!$s)
			$s=Eleanor::$service;
		if(!$l)
			$l=Language::$main;
		if(Eleanor::$vars['multilang'])
			$s.='_'.$l;
		if($force or false===$m=Eleanor::$Cache->Get('modules_'.$s,false))
		{
			$na=$sa=array();
			$R=Eleanor::$Db->Query('SELECT `id`,`services`,`sections` FROM `'.P.'modules` WHERE `active`=1');
			while($a=$R->fetch_assoc())
			{
				$sections=$a['sections'] ? (array)unserialize($a['sections']) : false;
				if($a['services'] and strpos($a['services'],','.Eleanor::$service.',')===false or !$sections)
					continue;
				foreach($sections as $k=>&$v)
				{
					if(isset($v['']))
					{
						$na+=array_combine($v[''],array_fill(0,count($v['']),$a['id']));
						$sa+=array_combine($v[''],array_fill(0,count($v['']),$k));
					}
					if(isset($v[$l]))
					{
						$na+=array_combine($v[$l],array_fill(0,count($v[$l]),$a['id']));
						$sa+=array_combine($v[$l],array_fill(0,count($v[$l]),$k));
					}
				}
			}
			Eleanor::$Cache->Put('modules_'.$s,$m=array('ids'=>$na,'sections'=>$sa),false);
		}
		return$m;
	}
}