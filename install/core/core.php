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

if(!defined('INSTALL'))die;

function Start($percent=0,$navi='')
{
	Eleanor::HookOutPut('Finish',200,array($percent,$navi));
}

function Finish($s,$a)
{global$title,$head;
	return (string)Eleanor::$Template->index(array('content'=>$s,'title'=>$title,'head'=>$head,'percent'=>$a[0],'navi'=>$a[1]));
}

function GoAway($info=false)
{global$Eleanor;
	if(!$ref=getenv('HTTP_REFERER') or $info)
	{
		if(is_bool($info))
			$info=PROTOCOL.Eleanor::$domain.Eleanor::$site_path.$Eleanor->Url->Prefix();
		elseif(is_array($info))
			$info=PROTOCOL.Eleanor::$domain.Eleanor::$site_path.html_entity_decode($Eleanor->Url->Construct($info));
		$ref=$info;
	}
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$ref);
	die;
}

function Error($e='')
{global$Eleanor;
	if($m=isset($Eleanor))
		Start();
	if(!headers_sent())
		header('Retry-After: 7200',true,503);
	die($m ? '<code>'.$e.'</code>' : $e);
}

function Result($s)
{
	Start();
	die($s);
}

class Install
{
	/*
		Функция проверки возможности установить Eleanor CMS. Проверяется:
			-Прав записи в папки
				/uploads
				/addons/logs
				/cache
		Возвращает либо TRUE, либо массив с ошибками.
	*/
	static function CheckErrors($wr_conf=true)
	{
		$result=array();
		if(!defined('UPDATE') and file_exists(Eleanor::$root.'install/install.lock'))
			return array(Eleanor::$Language['install']['install.lock']);
		if(version_compare(PHP_VERSION,'5.2.0','<'))
			$result[]=sprintf(Eleanor::$Language['install']['low_php'],PHP_VERSION);
		if(!function_exists('imagefttext'))
			$result[]=Eleanor::$Language['install']['GD'];
		if(!function_exists('mb_detect_encoding'))
			$result[]=Eleanor::$Language['install']['MB'];
		if(!function_exists('mysql_connect') and !function_exists('mysqli_connect'))
			$result[]=Eleanor::$Language['install']['no_db_driver'];

		$towrite=$toex=array();
		if(!is_dir(Eleanor::$root.'addons/logs'))
			$toex[]='<span style="color:red">/addons/logs</span>';
		elseif(!is_writeable(Eleanor::$root.'addons/logs'))
			$towrite[]='<span style="color:red">/addons/logs</span>';

		if(!is_dir(Eleanor::$root.'cache'))
			$toex[]='<span style="color:red">/cache</span>';
		elseif(!is_writeable(Eleanor::$root.'cache'))
			$towrite[]='<span style="color:red">/cache</span>';

		if(!is_writeable(Eleanor::$root.'.htaccess'))
			$towrite[]='<span style="color:red">.htaccess</span>';

		if(!defined('UPDATE'))
			if(!is_dir(Eleanor::$root.'install'))
				$toex[]='<span style="color:red">/install</span>';
			elseif(!is_writeable(Eleanor::$root.'install'))
				$towrite[]='<span style="color:red">/install</span>';
		if(!is_dir(Eleanor::$root.'uploads'))
			$toex[]='<span style="color:red">/uploads</span>';
		elseif(!is_writeable(Eleanor::$root.'uploads'))
			$towrite[]='<span style="color:red">/uploads</span>';
		if($wr_conf)
			if(!is_file(Eleanor::$root.'config_general.bak'))
				$toex[]='<span style="color:red">/config_general.bak</span>';
			elseif(!is_writeable(Eleanor::$root.'config_general.bak'))
				$towrite[]='<span style="color:red">/config_general.bak</span>';
		if(!is_writeable(Eleanor::$root.'robots.txt'))
			$towrite[]='<span style="color:red">/robots.txt</span>';

		if($toex)
			$result[]=Eleanor::$Language['install']['must_ex'].join('<br />',$toex);
		if($towrite)
			$result[]=Eleanor::$Language['install']['must_writeable'].join('<br />',$towrite);
		return$result;
	}

	/*
		Функция проверяет версию MySQLa. (не ниже 5.0.18)
	*/
	static function CheckMySQLVersion()
	{
		return version_compare(str_replace('-nt-max','',Eleanor::$Db->Driver->server_info),'5.0.18','>=');
	}

	static function IncludeDb()
	{		Eleanor::$nolog=true;		$c=include Eleanor::$root.'config_general.php';
		Eleanor::$nolog=false;
		Eleanor::$Db=new Db(array(
			'host'=>$c['db_host'],
			'user'=>$c['db_user'],
			'pass'=>$c['db_pass'],
			'db'=>$c['db'],
		));
		if(isset($c['users']))
			Eleanor::$UsersDb=new Db(array(
				'host'=>$c['users']['db_host'],
				'user'=>$c['users']['db_user'],
				'pass'=>$c['users']['db_pass'],
				'db'=>$c['users']['db'],
			));
		else
			Eleanor::$UsersDb=&Eleanor::$Db;
	}
}

abstract class UpdateClass
{
	public static function Run($data){}
	public static function GetText(){}
	public static function GetNextRunInfo(){}
}
/*
class Update_1 extends UpdateClass
{	public static function Run($data)
	{
		#Тут - события.
		return true - обновление завершено, false - нужно продолжать
	}

	public static function GetText()
	{
		return$this->data;
	}

	public static function GetNextRunInfo()
	{
		return$this->data;
	}
}
*/