<?php # Eleanor CMS © 2025 · https://eleanor-cms.com
namespace CMS\Traits;

use CMS\CMS;

/** Event-related methods extracted from Enums/Events to keep that enum concise. */
trait Events
{
	/** Trigger event
	 * @param mixed $data Extra data, must be JSON-serializable
	 * @throws \Throwable */
	function Trigger(mixed$data=null):void
	{
		if($data!==null)
			$data=\json_encode($data,\JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);

		CMS::$Db->Insert('events',[
			'event'=>$this->value,
			'data'=>$data
		]);

		$amount=CMS::$Db->Update('cron',[
			'run_at'=>fn()=>"IF(`status`='OK',NOW(),`run_at`)",
			'status'=>fn()=>"IF(`status`='RUN','RERUN',`status`)"
		],"`status` IN ('OK','RUN') AND FIND_IN_SET(?,`triggers`)>0",[$this->value]);

		if($amount>0)
			CMS::$Cache->Delete('cron');
	}
}

# Not required here because trait name matches filename
return Events::class;