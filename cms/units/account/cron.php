<?php
namespace CMS;

/** Sending notifications to Telegram bot
 * @var ?array $remnant Remnant from previous run
 * @return array|int */

const CACHE_NAME='account-last-event';

/** Loading notification l10n file
 * @param string $l10n language code
 * @return ?array */
function Notification(string$l10n):?array
{static$objects=[];
	if(!$l10n)
		$l10n=\Eleanor\Classes\L10n::$code;

	if(!isset($objects[$l10n]))
	{
		$file=__DIR__."/notification-{$l10n}.php";

		if(!\is_file($file))
			$objects[$l10n]=[];
		else
		{
			$data=\Eleanor\AwareInclude($file);
			$objects[$l10n]=\is_array($data) ? $data : [];
		}
	}

	return $objects[$l10n];
}

$last_event=$remnant['last'] ?? CMS::$Cache->Get(CACHE_NAME);

if($last_event===null)
{
	$R=CMS::$Db->Query(<<<SQL
SELECT MIN(`happened`) `min` FROM `events` WHERE `happened`>NOW() - INTERVAL 1 DAY AND `event`='user_signed_in'
SQL );
	$last_event=$R->fetch_column();
}

if(!$last_event)
	return 86400;

$R=CMS::$Db->Execute(<<<SQL
SELECT `happened`, `data` FROM `events` WHERE `happened`>? AND `event`='user_signed_in' ORDER BY `happened` ASC LIMIT 10
SQL ,[$last_event]);
while($a=$R->fetch_assoc())
{
	$last_event=$a['happened'];
	$data=\json_decode($a['data'],true);

	if(!isset($data['id'],$data['ip'],$data['way'],$data['ua']) or isset($data['notified']))
		continue;

	CMS::$Db->Update('events',['data'=>fn()=>'JSON_SET(`data`,"$.notified",true)'],"`happened`='{$a['happened']}' AND `event`='user_signed_in'");

	$R2=CMS::$Db->Execute(<<<SQL
SELECT `name`, `l10n`, `telegram_id` FROM `users` WHERE `id`=? AND `telegram_id` IS NOT NULL LIMIT 1
SQL ,[$data['id']]);
	if($user=$R2->fetch_assoc())
	{
		$notification=Notification($user['l10n']);

		if(!\is_callable($notification['telegram'] ?? 0))
			continue;

		$name=htmlspecialchars($user['name'],ENT,\Eleanor\CHARSET,false);
		$message=$notification['telegram']($name,$data['way'],$data['ip'],$data['ua']);

		$T=new \Eleanor\Classes\Telegram(CMS::$config['system']['bot_key']);

		try{
			$T->SendMessage((int)$user['telegram_id'],$message,[
				'parse_mode'=>'HTML',
				'link_preview_options'=>['is_disabled'=>true]
			]);
		}catch(\Eleanor\Classes\E$E){
			$E->Log();
		}
	}
}

if($R->num_rows==10)
	return['last'=>$last_event];

CMS::$Cache->Put(CACHE_NAME,$last_event,86400*2);

return 86400;