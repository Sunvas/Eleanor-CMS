<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use CMS\Interfaces\Cron,
	Eleanor\Classes\Output;

require __DIR__.'/cms/core.php';

#Flag shows that there is at least one task left to run
$immediately=false;

$R=CMS::$Db->Query(<<<SQL
SELECT `unit`, `remnant` FROM `cron` WHERE `status`='OK' ORDER BY `date` ASC LIMIT 1
SQL );
if($task=$R->fetch_assoc())
{
	$file=ROOT."units/{$task['unit']}.php";

	#If unit doesn't exist
	if(!\is_file($file))
		goto Fail;

	$U=require$file;

	#... or doesn't comply with Cron interface
	if(!($U instanceof Cron))
	{
		Fail:
		CMS::$Db->Delete('cron','unit=?',[$task['unit']]);
		goto Skip;
	}

	CMS::$Db->Update('cron',['status'=>'RUN','date'=>fn()=>'NOW()'],'unit=?',[$task['unit']]);

	$remnant=$U->Cron($task['remnant'] ? \json_decode($task['remnant'],true) : null);

	if(\is_array($remnant))
	{
		$immediately=true;
		CMS::$Db->Update('cron',['status'=>'OK','date'=>fn()=>'NOW()','remnant'=>\json_encode($remnant,JSON)],'unit=?',[$task['unit']]);
	}
	else
		CMS::$Db->Update('cron',['status'=>'OK','date'=>fn()=>"NOW() + INTERVAL {$remnant} SECOND",'remnant'=>null],'unit=?',[$task['unit']]);

	Skip:
}

if(!$immediately)
{
	$R=CMS::$Db->Query(<<<SQL
SELECT `status` FROM `cron` WHERE `status`='OK' AND `date`<=NOW() LIMIT 1
SQL );
	if($R->num_rows>0)
		$immediately=true;
}

#Next run should be performed immediately
if($immediately)
{
	CMS::$Cache->Delete('cron');
	OutPut::SendHeaders(Output::TEXT,200,0);
	die('1');
}

#Next run should be performed after this amount of seconds
$R=CMS::$Db->Query(<<<SQL
SELECT TIMESTAMPDIFF(SECOND,NOW(),`date`) `seconds` FROM `cron` WHERE `status`='OK' ORDER BY `date` ASC LIMIT 1
SQL );
if($R->num_rows>0)
{
	$seconds=(int)$R->fetch_column();
	CMS::$Cache->Put('cron',\time() + $seconds,$seconds);

	OutPut::SendHeaders(Output::TEXT,200,0);
	die( (string)$seconds );
}

#No content: no next run, rechecking tomorrow
CMS::$Cache->Put('cron',\time() + 86400,86400);
\header('Cache-Control: no-store',true,204);