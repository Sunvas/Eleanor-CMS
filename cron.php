<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

use CMS\Interfaces\Cron,
	Eleanor\Classes\Output;

# Reject unsupported browser request contexts
if(!\in_array($_SERVER['HTTP_SEC_FETCH_DEST'] ?? '',['','empty','document'],true))
	return \http_response_code(403);

require __DIR__.'/cms/core.php';

# Flag shows that there is at least one task left to run
$immediately=false;

$R=CMS::$Db->Query(<<<SQL
SELECT `unit`, `data` FROM `cron` WHERE `status`='OK' AND `run_at`<=NOW() ORDER BY `run_at` ASC LIMIT 1
SQL );

if($task=SingleFetch($R))
{
	$file=CMS."units/{$task['unit']}.php";

	# If unit doesn't exist
	if(!\is_file($file))
		goto Fail;

	$U=require$file;

	# ... or doesn't comply with Cron interface
	if(!($U instanceof Cron))
	{
		Fail:
		CMS::$Db->Delete('cron','`unit`=?',[$task['unit']]);
		goto Skip;
	}

	if(CMS::$Db->Update('cron',['status'=>'RUN','run_at'=>fn()=>'NOW()'],"`unit`=? AND `status`='OK'",[$task['unit']])<1)
		goto Skip;

	try {
		$data=$task['data']===null ? null : \json_decode($task['data'],true);
		$seconds=$U->Cron($data);
		$data=$data===null ? null : \json_encode($data,\JSON_THROW_ON_ERROR);
	}catch(\Throwable$E){
		CMS::$Db->Update('cron',[
			'status'=>'FAIL',
			'error'=>$E->getMessage(),
			'run_at'=>fn()=>'NOW()'
		],'`unit`=?',[$task['unit']]);
		goto Skip;
	}

	CMS::$Db->Update('cron',['status'=>'OK','run_at'=>fn()=>"NOW() + INTERVAL GREATEST($seconds,0) SECOND",'data'=>$data],'`unit`=?',[$task['unit']]);

	Skip:
}

if(!$immediately)
{
	$R=CMS::$Db->Query(<<<SQL
SELECT `status` FROM `cron` WHERE `status`='OK' AND `run_at`<=NOW() LIMIT 1
SQL );
	if(SingleFetch($R))
		$immediately=true;
}

# Reset tasks that have been running for more than an hour
if(!$immediately and CMS::$Db->Update('cron',['status'=>'OK','run_at'=>fn()=>'NOW()'],"`status`='RUN' AND `run_at`<NOW() - INTERVAL 1 HOUR")>0)
	$immediately=true;

# Next run should be performed immediately
if($immediately)
{
	CMS::$Cache->Delete('cron');
	Output::SendHeaders(Output::TEXT,200,0);
	die('1');
}

# Next run should be performed after this number of seconds
$R=CMS::$Db->Query(<<<SQL
SELECT TIMESTAMPDIFF(SECOND,NOW(),`run_at`) `seconds` FROM `cron` WHERE `status`='OK' ORDER BY `run_at` ASC LIMIT 1
SQL );
$seconds=SingleFetch($R,true);

if($seconds!==null)
{
	CMS::$Cache->Put('cron',\time() + $seconds,(int)$seconds);

	Output::SendHeaders(Output::TEXT,200,0);
	die( (string)$seconds );
}

# No content: no next run, rechecking tomorrow
CMS::$Cache->Put('cron',\time() + 86400,86400);
\header('Cache-Control: no-store',true,204);