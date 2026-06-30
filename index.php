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

# If the system is not installed, redirect to installation setup. You can remove this block after installation
if(!\file_exists(__DIR__.'/cms/config/system.json'))
{
	\header('Location: install/',false,302);
	die;
}

require __DIR__.'/cms/core.php';

# Redirect /index.php requests to canonical URI
if(\str_starts_with(Uri::$raw,'index.php'))
	Redirect(\substr(Uri::$raw,9));

# Expose Uri class to CMS namespace
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

/** Set canonical link
 * @param Uri|string $Uri
 * @param string|string[] $slug
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

/** Generate alternative localization links
 * @param \Closure $Gen Function receiving l10n code and Uri object */
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

/** Split URI into slug and URI tail
 * @param ?string $uri
 * @return array [slug, URI tail] */
function SlugTail(?string $uri):array
{
	return $uri && \str_contains($uri,'/') ? \explode('/',$uri,2) : [$uri ?? '',null];
}

Assign::Bind(CMS::$A,fn()=>new Authorization('a11n_userarea',(int)($_GET['iam'] ?? 0),require CMS.'external.php'));

$uri=Uri::Clean();

# Redirecting user to the appropriate language version
if(L10NS!==null)
{
	$stack=[L10N,...L10NS];

	# Requests like /ru or /en are redirected to /ru/ or /en/ with a trailing slash
	if(\in_array($uri,$stack))
		Redirect(SITEDIR.$uri.'/'.($_SERVER['QUERY_STRING'] ? '?'.$_SERVER['QUERY_STRING'] : ''),307);

	# Localization prefix
	[$l10n,$uri]=SlugTail($uri);

	# Prefix verification
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
SELECT `l10n` FROM `users` WHERE `id`=$id LIMIT 1
SQL );
			$preferred=$R->fetch_column();

			if(!$preferred)
				goto HAL;
		}
		else
		{
			HAL:
			# https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Accept-Language - HAL is already sorted
			$hal=isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? \explode(',',$_SERVER['HTTP_ACCEPT_LANGUAGE']) : [];
			$hal=\array_map(fn($item)=>\substr(\trim($item),0,2),$hal);

			$preferred=\array_find($hal,fn($item)=>\in_array($item,$stack,true));
		}

		$loc=[$l10n];
		\array_push($loc,...($uri!==null ? \explode('/',$uri) : []));

		# If a monolingual link is followed while multilingual mode is enabled, redirect the user to a new address with the from=autol10n flag.
		# Then the unit decides what to do: keep the page as is (using a canonical link) or redirect to the correct address.
		Redirect(SITEDIR.$preferred.'/'.Uri::Make($loc).'?from=autol10n'.($_SERVER['QUERY_STRING'] ? '&'.$_SERVER['QUERY_STRING'] : ''),307);
	}
}
else
	L10n::$code=L10N;

# Arguments for the Template constructor (template source and default variables)
if(!CMS::$json and CMS::$T instanceof Assign)
	CMS::$T->args=[
		CMS.'user-area',
		[
			# Link to admin panel
			'adminpanel'=>CMS::$a11n && CMS::$A->current && array_intersect(['root','team'],CMS::$P->roles) ? CMS::$Cache->Get('admin-panel',0) : null,

			# hCaptcha key
			'hcaptcha'=>CMS::$config['system']['captcha'] ? CMS::$config['system']['hcaptcha'] : ''
		]
	];

# Site is closed (under maintenance), return https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Status/503
if(CMS::$config['system']['maintenance'] and !\in_array('maintainer',CMS::$P->roles))
{
	if(CMS::$json)
		JSON(['ok'=>false],503);

	$content=(CMS::$T)('maintenance');
	HTML($content,503);
}

# Global object to carry shared data inside cms
$CMS=new CMS;

[$slug,$uri]=SlugTail($uri);

# Nothing found... Let's look for a unit
$units=\scandir(CMS.'units');# PHP 8.6 - pipe operator
$units=\array_filter($units,fn($item)=>\str_ends_with($item,'.php'));

foreach($units as $unit)
{
	$u=\strrchr($unit,'.',true);
	$U=require (CMS.'units/'.$unit);

	if(!\is_object($U))
		continue;

	$CMS->$u=$U;

	if($U instanceof UserArea and $U->slug===$slug)
		$U->UserArea($uri);
}

# Try static page
if(isset($CMS->static))
	$CMS->static?->Try($slug,$uri);

# Still nothing found, try direct endpoint
if($slug and \preg_match('#[^a-z\d\-_.]#i',$slug)==0 and \is_file($f=CMS."direct/$slug.php"))
	require$f;
elseif(CMS::$json)
	JSON(['ok'=>false],404);
else
	Halt();