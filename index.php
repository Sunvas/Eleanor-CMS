<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use Eleanor\Assign,
	Eleanor\Classes\Output,

	CMS\Classes\Uri,
	CMS\Interfaces\UserArea;

use const Eleanor\SITEDIR;

/** @const Script start time, used to display service information at the bottom of the page. */
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

/** Error page
 * @param int $code Error code 4XX
 * @param string $allow405 'Allow' header value for 405 error */
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

/** Canonical link
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
		CMS::$T['canonical']=$orig;
	else
		CMS::$T['canonical']=null;
}

/** Making URI for alternative l10n of page
 * @param \Closure $Gen Function which receives l10n code as first param and Uri object for the second one */
function Alternate(\Closure$Gen):void
{
	if(!L10NS)
		return;

	foreach([L10N,...L10NS] as $lang)
	{
		if(L10n::$code===$lang)
			continue;

		$Uri=new class($lang) extends Uri {
			static string $base='';

			function __construct(string$lang)
			{
				static::$base=$lang;
				parent::__construct('');
			}
		};

		$link=$Gen($lang,$Uri);

		if($link!==null)
			CMS::$T['hreflang'][$lang]=$link;
	}
}

/** Split URI to SLUG and URI
 * @param ?string $uri
 * @return array [slug, uri] */
function SlugUri(?string$uri):array
{
	return $uri && \str_contains($uri,'/') ? \explode('/',$uri,2) : [$uri ?? '',null];
}

Assign::For(CMS::$A,fn()=>new Authorization('a11n_userarea',(int)($_GET['iam'] ?? 0),require ROOT.'external.php'));

$uri=Uri::GetURI();

#Redirecting user to the appropriate language version
if(L10NS!==null)
{
	$stack=[L10N,...L10NS];

	#Requests like /ru or /en are redirected to /ru/ or /en/ (added slash at the end)
	if(\in_array($uri,$stack))
		Redirect(SITEDIR.$uri.'/'.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''),307);

	#Localization prefix
	[$l10n,$uri]=SlugUri($uri);

	#Prefix verification
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

		#If a monolingual link is followed while multilingualism is enabled, we redirect the user to a new address with the from=autol10n flag.
		#Next, the unit will decide what to do: to leave everything as is (no duplicate page due to the canonical header) or redirect to the correct address.
		Redirect(SITEDIR.$preferred.'/'.Uri::Make($loc).'?from=autol10n'.($_SERVER['QUERY_STRING'] ? '&'.$_SERVER['QUERY_STRING'] : ''),307);
	}
}
else
	L10n::$code=L10N;

#Arguments for the Template constructor (template source and default variables)
if(!CMS::$json and CMS::$T instanceof Assign)
	CMS::$T->args=[
		ROOT.'user-area',
		[
			#Link to admin panel
			'adminpanel'=>CMS::$a11n && CMS::$A->current && array_intersect(['root','team'],CMS::$P->roles) ? CMS::$Cache->Get('admin-panel',true) : null,

			#hCaptcha key
			'hcaptcha'=>CMS::$config['system']['captcha'] ? CMS::$config['system']['hcaptcha'] : ''
		]
	];

#Site is closed (under maintenance), return https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/503
if(CMS::$config['system']['maintenance'] and !\in_array('maintainer',CMS::$P->roles))
{
	if(CMS::$json)
		JSON(['ok'=>false],503);

	$content=(CMS::$T)('maintenance');
	HTML($content,503);
}

#Global object to carry shared data inside cms
$CMS=new CMS;

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

	$CMS->$u=$U;

	if($U instanceof UserArea and $U->slug===$slug)
		$U->UserArea($uri);
}

#Try static page
if(isset($CMS->static))
	$CMS->static?->Try($slug,$uri);

#Still nothing found... Let's look for direct folder
if($slug and \preg_match('#[^a-z\d\-_.]#i',$slug)==0 and \is_file($f=ROOT."direct/{$slug}.php"))
	require$f;
elseif(CMS::$json)
	JSON(['ok'=>false],404);
else
	Halt();