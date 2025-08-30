<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Assign,
	Eleanor\Classes\L10n,
	Eleanor\Classes\Output,

	CMS\Classes\Uri,
	CMS\Interfaces\UserSpace;

use const Eleanor\SITEDIR;

/** @const Время старта системы, используется для отображения служебной информации внизу страницы */
\define('CMS\STARTED',\hrtime(true));

#If system is not install - redirect to installation setup. You can remove this block after installation
if(!\file_exists(__DIR__.'/cms/config/system.json'))
{
	\header('Location: install/',false,302);
	die;
}

require __DIR__.'/cms/core.php';

#Killing /index.php requests
if(\str_starts_with(Uri::$current,'index.php'))
	Redirect(\substr(Uri::$current,0,9));

#Import Uri class to CMS namespace
class_alias(Uri::class,__NAMESPACE__.'\Uri');

/** Страница с ошибкой
 * @param int $code Код ошибки 4XX
 * @param string $allow405 Значение заголовка allow для ошибки 405 */
function Halt(int$code=404,string$allow405='DELETE'):never
{
	if(CMS::$json)
	{
		$output=\json_encode([
			'ok'=>false,
			'code'=>$code,
		],JSON);
		Output::SendHeaders(Output::JSON, $code);
	}
	else
	{
		$output=(CMS::$T)('error',code:$code);

		Output::SendHeaders(Output::HTML, $code);
	}

	if($code===405 and $allow405)
		header('Allow: '.$allow405);

	die($output);
}

/** Каноническая ссылка
 * @param Uri|string $Uri
 * @param string|array $slug
 * @return void */
function Canonical(Uri|string$Uri,string|array$slug='',...$a):void
{
	if(\is_string($Uri))
		$orig=Uri::$base.$Uri;
	else
		$orig=$slug==='' ? (string)$Uri : $Uri($slug,...$a);

	if($_SERVER['REQUEST_URI']!==\htmlspecialchars_decode($orig))
		CMS::$T->default['canonical']=$orig;
	else
		CMS::$T->default['canonical']=null;
}

/** Making URI for alternative l10n of page
 * @param \Closure $Gen Function which receives l10n code as first param and Uri object for the second one */
function Alternate(\Closure$Gen):void
{
	if(!L10NS)
		return;

	foreach(CMS::$T->default['hreflang'] as $lang=>&$l)
	{
		$Uri=new class($lang) extends Uri {
			static string $base='';

			function __construct(string$lang)
			{
				static::$base=$lang;
				parent::__construct('');
			}
		};
		$l=$Gen($lang,$Uri);
	}
}

/** Разбиение URI на SLUG и URI
 * @param ?string $uri
 * @return array [slug, uri] */
function SlugUri(?string$uri):array
{
	return $uri && \str_contains($uri,'/') ? \explode('/',$uri,2) : [$uri ?? '',null];
}

Assign::For(CMS::$A,fn()=>new Authorization('a11n_userspace',(int)($_GET['iam'] ?? 0),require ROOT.'external.php'));

$uri=Uri::GetURI();

#Redirecting user to the appropriate language version
if(L10NS)
{
	#Перечень доступных языков
	$stack=L10NS;
	$stack[]=L10N;

	#Запросы /ru и /en перенаправляем на /ru/ и /en/ соответственно
	if(\in_array($uri,$stack))
		Redirect(SITEDIR.$uri.'/'.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''),307);

	#Языковой префикс
	[$l10n,$uri]=SlugUri($uri);

	//Проверка префикса
	if(\in_array($l10n,$stack,true))
	{
		L10n::$code=$l10n;
		Uri::$base.=$l10n.'/';
	}
	else
	{
		if(CMS::$A->current)
		{
			$id=CMS::$A->current;
			$R=CMS::$Db->Query(<<<SQL
SELECT `l10n` FROM `users` WHERE `id`={$id} LIMIT 1
SQL );
			$preferred=$R->fetch_column();

			if(!$preferred)
				goto HAL;
		}
		else
		{
			HAL:
			//https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Accept-Language - HAL is already sorted
			$hal=isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? \explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) : [];
			$hal=\array_map(fn($item)=>\substr(\trim($item),0,2),$hal);

			$preferred=\array_find($hal,fn($item)=>\in_array($item,$stack,true));
		}

		$loc=[$l10n];
		\array_push($loc,...($uri!==null ? \explode('/',$uri) : []));

		#Если при включённой мультиязычности перешли по моноязычной ссылке, перенаправим пользователя по новому адресу с флагом from=autol10n. Далее будет разбираться юнит: либо оставит всё как есть (дубля страницы не возникнет из-за заголовка canonical) либо перенаправит по актуальному адресу
		Redirect(SITEDIR.$preferred.'/'.Uri::Make($loc).'?from=autol10n'.($_SERVER['QUERY_STRING'] ? '&'.$_SERVER['QUERY_STRING'] : ''),307);
	}

	#Links to other localizations
	foreach($stack as $k)
		if($k!=L10n::$code)
			CMS::$T->default['hreflang'][$k]=SITEDIR.$k.'/';
}
else
	L10n::$code=L10N;

#Template binding
if(!CMS::$json)
{
	CMS::$T->queue[]=ROOT.'userspace';
	CMS::$T->default+=[
		#Link to dashboard
		'dashboard'=>CMS::$a11n && CMS::$A->current && array_intersect(['admin','team'],CMS::$P->roles) ? CMS::$Cache->Get('dashboard',true) : null,

		#hCaptcha key
		'hcaptcha'=>CMS::$config['system']['captcha'] ? CMS::$config['system']['hcaptcha'] : ''
	];
}

#Site is closed (under maintenance), return https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/503
if(CMS::$config['system']['maintenance'] and !\in_array('maintainer',CMS::$P->roles))
{
	if(CMS::$json)
		JSON(['ok'=>false],503);

	$content=(CMS::$T)('maintenance');
	HTML($content,503);
}

#Global object to carry shared data inside cms
$Shared=new CMS;

[$slug,$uri]=SlugUri($uri);

#Nothing found... Let's look for unit
$units=\scandir(ROOT.'units');
$units=\array_filter($units,fn($item)=>\str_ends_with($item,'.php'));

foreach($units as $unit)
{
	$u=\strrchr($unit,'.',true);
	$U=require (ROOT.'units/'.$unit);

	if(!\is_object($U))
		continue;

	$Shared->$u=$U;

	if($U instanceof UserSpace and $U->slug===$slug)
		$U->UserSpace($uri);
}

#Try static page
if(isset($Shared->static))
	$Shared->static?->Try($slug,$uri);

#Still nothing found... Let's look for direct folder
if($slug and \preg_match('#[^a-z\d\-_.]#i',$slug)==0 and \is_file($f=ROOT."direct/{$slug}.php"))
	require$f;
elseif(CMS::$json)
	JSON(['ok'=>false],404);
else
	Halt();