<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Traits;

use CMS\CMS,
	Eleanor\Classes\E;

/** Event-related methods extracted from Enums/Events to keep that enum concise. */
trait Events
{
	/** Trigger event
	 * @param ?array $data Extra data, should be convertible to JSON
	 * @throws \Throwable */
	function Trigger(?array$data=null):void
	{
		$json=$data===null ? null : \json_encode($data,\JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE);

		if($json===false)
		{
			$fl=\Eleanor\BugFileLine(self::class);
			throw new E("Data cannot be converted to JSON",E::PHP,...$fl,input:$data);
		}

		CMS::$Db->Insert('events',[
			'event'=>$this->value,
			'data'=>$json
		]);

		$amount=CMS::$Db->Update('cron',['run_at'=>fn()=>'NOW()'],"`status`='OK' AND FIND_IN_SET(?,`triggers`)>0",[$this->value]);

		if($amount>0)
			CMS::$Cache->Delete('cron');
	}
}

# Not necessary here, since trait name equals filename
return Events::class;