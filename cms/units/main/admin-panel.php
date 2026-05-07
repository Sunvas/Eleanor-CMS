<?php
namespace CMS;

/** Main unit
 * @var Classes\Uri4AdminPanel $Uri
 * @var object $this This unit
 * @var int &$code Response code
 * @var int|string &$cache Defines cache on client (int specifies the number of seconds for which the result should be cached, string means etag content) */

/** Contents of main page in admin panel */
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

			if(\is_uploaded_file($F['tmp_name']) and \move_uploaded_file($F['tmp_name'],$file))
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
function SettingsSite():array|string
{
	if(CMS::$json)
	{
		if(!CMS::$post)
			return[
				'ok'=>false
			];

		$mono=L10NS===null;
		$storage=[];

		#PHP 8.6
		#Multilingual values
		foreach(['name','title','description'] as $f)
			if($mono ? \is_string($_POST[$f] ?? 0) : \is_array($_POST[$f] ?? 0) && \array_all($_POST[$f],fn($t)=>\is_string($t)))
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

	return(CMS::$T)('SettingsSite',config:CMS::$config['site']);
}

/** System config settings */
function SettingsSystem():array|string
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

	return(CMS::$T)('SettingsSystem',config:CMS::$config['system']);
}

if(!CMS::$json)
{
	CMS::$T->queue[]=ROOT.'admin-panel/'.$this->name;
	CMS::$T->default['links']+=[
		'settings'=>$Uri(zone:'settings'),
		'settings-system'=>$Uri(zone:'settings-system'),
	];
}

#Some zones are available for administrators only
$is_root=\in_array('root',CMS::$P->roles);

return match($_GET['zone'] ?? ''){
	'settings'=>$is_root ? SettingsSite() : Halt(),
	'settings-system'=>$is_root ? SettingsSystem() : Halt(),
	''=>Main($this),
	default=>Halt()
};