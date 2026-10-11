<?php # Eleanor CMS © 2026 · https://eleanor-cms.com
namespace CMS;

/** Incrementally update sitemap files.
 * @var mixed $data Persistent Cron task data, passed by reference
 * @return int Seconds until the next execution */

use CMS\Interfaces\Sitemap;
use Eleanor\Classes\{E, Files};
use const Eleanor\{CHARSET, DOMAIN, PROTOCOL, SITEDIR};

const
	ENT=\ENT_XML1 | \ENT_QUOTES,
	SITEMAP=<<<'XML'
<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>
XML,
	LOCK_ERROR='Unable to lock file',
	WRITE_ERROR='Unable to write file';

/** Open or initialize a sitemap XML file.
 * @param string $file Absolute file path
 * @return resource Locked file handle
 * @throws E */
function SitemapFile(string$file):mixed
{
	$exists=\is_file($file);
	$fh=Files::LockFile($file,$exists ? 'r+' : 'c+');

	if($fh===false)
		throw new E(LOCK_ERROR,E::SYSTEM,input:$file);

	if(!$exists)
	{
		$len=strlen(SITEMAP);

		if(\fwrite($fh,SITEMAP)!==$len)
			throw new E(WRITE_ERROR,E::SYSTEM,input:$file);
	}

	return $fh;
}

/** Append a sitemap URL to the sitemap index.
 * @param string $file Absolute path to the sitemap index file
 * @param string $link Absolute URL of the sitemap file
 * @param string $firstlink Absolute URL of the first sitemap file
 * @return bool Whether the file was closed successfully
 * @throws E */
function IndexFile(string$file,string$link,string$firstlink):bool
{
	$exists=\is_file($file);
	$fh=Files::LockFile($file,$exists ? 'r+' : 'c+');

	if($fh===false)
		throw new E(LOCK_ERROR,E::SYSTEM,input:$file);

	if(!$exists)
	{
		$firstlink=\htmlspecialchars($firstlink,ENT,CHARSET,false);
		$data=<<<XML
<?xml version="1.0" encoding="UTF-8"?><sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"><sitemap><loc>$firstlink</loc></sitemap></sitemapindex>
XML;
		$len=strlen($data);

		if(\fwrite($fh,$data)!==$len)
		{
			Write:
			\fclose($fh);
			throw new E(WRITE_ERROR,E::SYSTEM,input:$file);
		}
	}

	\fseek($fh,-15,\SEEK_END);# Overwrite the closing </sitemapindex> tag

	$link=\htmlspecialchars($link,ENT,CHARSET,false);
	$xml="<sitemap><loc>$link</loc></sitemap></sitemapindex>";

	if(\fwrite($fh,$xml)!==\strlen($xml))
		goto Write;

	return \fclose($fh);
}

if(!isset($data['previous']))
{
	$R=CMS::$Db->Query(<<<SQL
SELECT MIN(`happened`) - INTERVAL 1 SECOND `min` FROM `events` WHERE `happened`>NOW() - INTERVAL 1 DAY AND `event`='uri_created'
SQL );
	$data['previous']=SingleFetch($R,true);
}

$immediately=false;

# Schedule sitemaps affected by new URI events
if($data['previous'])
{
	$limit=100;
	$schedule=[];
	$R=CMS::$Db->Execute(<<<SQL
SELECT `happened`, `data`
FROM `events`
WHERE `happened`>? AND `event`='uri_created'
ORDER BY `happened` ASC
LIMIT $limit
SQL ,[$data['previous']]);

	if($R->num_rows==$limit)
		$immediately=true;

	foreach($R as $a)
	{
		$unit=\json_decode($a['data'],true);
		$data['previous']=$a['happened'];

		if(!\is_string($unit))
			continue;

		if(!\in_array($unit,$schedule))
			$schedule[]=$unit;
	}
	$R->free();

	if($schedule)
	{
		$schedule=\json_encode($schedule);

		CMS::$Db->Update('sitemaps',[
			'run_at'=>fn()=>'TIMESTAMPADD(SECOND,TIME_TO_SEC(`debounce`),NOW())',
			'status'=>'SCHEDULED'
		],"`status`='OK' AND JSON_OVERLAPS(`units`,?)",[$schedule]);
	}
}

# Incremental sitemap writer
$dir=\rtrim(\getenv('DOCUMENT_ROOT') ?: \getcwd(),\DIRECTORY_SEPARATOR).\DIRECTORY_SEPARATOR;
$site=PROTOCOL.DOMAIN.SITEDIR;

/** Append new URIs from a unit to sitemap files.
 * @return bool Whether generation was interrupted due to the execution time limit */
$Sitemap=function(string$name,Sitemap$Unit,mixed&$data,int&$number,int&$n)use($dir,$site):bool{
	$amount=0;
	$file=$dir.$name.($n>0 ? '.'.$n : '').'.xml';
	$fh=SitemapFile($file);

	try{
		$Sitemap=$Unit->Sitemap($data);

		foreach($Sitemap as $link)
		{
			$number++;
			$amount++;

			if($number>50000)
			{
				\fclose($fh);

				$n++;
				$number=1;
				$filename=$name.".$n.xml";
				$file=$dir.$filename;

				$fh=SitemapFile($file);
				IndexFile($dir.$name.".index.xml",$site.$filename,$site.$name.'.xml');
			}

			\fseek($fh,-9,\SEEK_END);# Overwrite the closing </urlset> tag

			$loc=\htmlspecialchars($site.$link,ENT,CHARSET,false);
			$xml="<url><loc>$loc</loc></url></urlset>";

			if(\fwrite($fh,$xml)!==\strlen($xml))
				throw new E(WRITE_ERROR,E::SYSTEM,input:$file);

			if(($amount % 1000===0) and GetRemainingTime()<5)
			{
				$Sitemap->send(true);

				return true;
			}
		}
	}finally{
		if(\is_resource($fh))
			\fclose($fh);
	}

	return false;
};

$limit=5;
$R=CMS::$Db->Query(<<<SQL
SELECT `name`, `units`, `data`, `number`, `n`
FROM `sitemaps`
WHERE `run_at`<=NOW() AND `status`='SCHEDULED'
ORDER BY `run_at` ASC
LIMIT $limit
SQL);

if($R->num_rows==$limit)
	$immediately=true;

if($R->num_rows>0)
{
	$CMS=new CMS;

	foreach($R as $a)
	{
		CMS::$Db->Update('sitemaps',['status'=>'RUN','run_at'=>fn()=>'NOW()'],"`status`='SCHEDULED' AND `name`=?",[$a['name']]);

		$u_data=$a['data'] ? \json_decode($a['data'],true) : [];
		$units=\json_decode($a['units'],true);

		foreach($units as $unit)
		{
			try{
				$Unit=$CMS->{$unit};
			}catch(E){
				continue;
			}

			if($Unit instanceof Sitemap)
			{
				try{
					if($Sitemap($a['name'],$Unit,$u_data[$unit],$a['number'],$a['n']))
					{
						CMS::$Db->Update('sitemaps',[
							'status'=>'SCHEDULED',
							'run_at'=>fn()=>'NOW()',
							'data'=>\json_encode($u_data,\JSON_THROW_ON_ERROR),
							'number'=>$a['number'],
							'n'=>$a['n']
						],"`status`='RUN' AND `name`=?",[$a['name']]);

						$immediately=true;
						break 2;
					}
				}catch(\Throwable$E){
					$R->free();

					CMS::$Db->Update('sitemaps',[
						'status'=>'FAIL',
						'data'=>\json_encode($u_data,\JSON_THROW_ON_ERROR),
						'number'=>$a['number'],
						'n'=>$a['n']
					],"`status`='RUN' AND `name`=?",[$a['name']]);

					throw $E;
				}
			}
		}

		CMS::$Db->Update('sitemaps',[
			'status'=>'OK',
			'data'=>\json_encode($u_data,\JSON_THROW_ON_ERROR),
			'number'=>$a['number'],
			'n'=>$a['n']
		],"`status`='RUN' AND `name`=?",[$a['name']]);
	}
}
$R->free();

if($immediately)
	return 0;

# Reset sitemaps that have been running for more than 2 hours
CMS::$Db->Update('sitemaps',[
	'status'=>'SCHEDULED',
	'run_at'=>fn()=>'NOW()'
],"`status`='RUN' AND `run_at`<NOW() - INTERVAL 2 HOUR");

# Calculate the time until the next scheduled sitemap
$R=CMS::$Db->Query(<<<SQL
SELECT TIMESTAMPDIFF(SECOND,NOW(),MIN(`run_at`)) FROM `sitemaps` WHERE `status`='SCHEDULED'
SQL);

return SingleFetch($R,true) ?? 86400*2;

