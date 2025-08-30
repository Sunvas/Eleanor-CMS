<?php
namespace CMS;

/** Main unit
 * @var Classes\UriDashboard $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

/** Contents of main page of dashboard */
function Main(object$Unit):array|string
{
	if(CMS::$json)
	{
		if(!CMS::$post)
		{
			$is_lang=\is_string($_GET['lang'] ?? 0);

			#Loading contents of another l10n
			if($is_lang)
			{
				$file=$Unit->GetMainPageFile($_GET['lang']);
				$content=\is_file($file) ? \file_get_contents($file) : 'null';

				Output::SendHeaders(Output::JSON);
				die($content);
			}

			return[
				'ok'=>false
			];
		}

		$success=0;

		foreach($_FILES as $lang=>$F)
		{
			if(\strlen($lang)!=2)
				continue;

			$file=$Unit->GetMainPageFile($lang);

			if(!\is_file($file) or !\is_uploaded_file($F['tmp_name']))
				continue;

			$content=\file_get_contents($file);

			if(\json_validate($content) and \move_uploaded_file($F['tmp_name'],$file))
				$success++;
		}

		if($success>0 and $success===\count($_FILES))
			return[
				'ok'=>true
			];

		return[
			'ok'=>false,
			'error'=>\count($_FILES)>0 ? 'SOMETHING_WENT_WRONG' : 'MISSED'
		];
	}

	$file=$Unit->GetMainPageFile();
	$content=\is_file($file) ? \file_get_contents($file) : null;

	return(CMS::$T)('MainPage',content:$content);
}

/** Site config settings */
function SiteSettings():array|string
{
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		$mono=L10NS===null;
		$storage=[];

		#Multilingual values
		foreach(['name','title','description'] as $f)
			if($mono ? \is_string($_POST[$f] ?? 0) : \is_array($_POST[$f] ?? 0) && IsS(...$_POST[$f]))
				$storage[$f]=$_POST[$f];

		$ok=$storage && \file_put_contents(ROOT.'config/site.json',\json_encode($storage + CMS::$config['site'],JSON));

		if($ok)
			return[
				'ok'=>true
			];

		return[
			'ok'=>false,
			'error'=>$storage ? 'WRITE_ERROR' : 'NOTHING_TO_STORE'
		];
	}

	return(CMS::$T)('SiteSettings',config:CMS::$config['site']);
}

/** System config settings */
function SystemSettings():array|string
{
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		$storage=[];

		#String values
		foreach(['bot_name','bot_key','hcaptcha','hcaptcha_secret'] as $f)
			if(\is_string($_POST[$f] ?? 0))
				$storage[$f]=$_POST[$f];

		#Boolean values
		foreach(['maintenance','captcha'] as $f)
			if(\is_bool($_POST[$f] ?? 0))
				$storage[$f]=$_POST[$f];

		$ok=$storage && \file_put_contents(ROOT.'config/system.json',\json_encode($storage + CMS::$config['system'],JSON));

		if($ok)
			return[
				'ok'=>true
			];

		return[
			'ok'=>false,
			'error'=>$storage ? 'WRITE_ERROR' : 'NOTHING_TO_STORE'
		];
	}

	return(CMS::$T)('SystemSettings',config:CMS::$config['system']);
}

if(!CMS::$json)
{
	CMS::$T->queue[]=ROOT.'dashboard/'.$this->name;
	CMS::$T->default['links']+=[
		'settings'=>$Uri(zone:'settings'),
		'system-settings'=>$Uri(zone:'system-settings'),
	];
}

#Some zones are available for administrators only
$is_admin=\in_array('admin',CMS::$P->roles);

return match($_GET['zone'] ?? ''){
	'settings'=>$is_admin ? SiteSettings() : Halt(),
	'system-settings'=>$is_admin ? SystemSettings() : Halt(),
	''=>Main($this),
	default=>Halt()
};