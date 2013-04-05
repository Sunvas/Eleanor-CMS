<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/
if(!defined('CMS'))die;
class ApiAccount extends BaseClass
{
	public
		$module;

	public function LangUrl($q)
	{
		$El=Eleanor::getInstance();
		if(isset($this->module['section']) and in_array($this->module['section'],array('groups','online')))
			return$El->Url->Prefix();
		if(!is_array($q))
		{
			$str=$El->Url->GetEnding(array($El->Url->ending,$El->Url->delimiter),true);
			$q=$El->Url->Parse($str ? array('user','do') : array('do'),true);
		}
		$user=isset($q['user']) ? $q['user'] : 0;
		$id=isset($q['userid']) ? (int)$q['userid'] : 0;
		$do=isset($q['do']) ? preg_replace('#[^a-z0-9\-_]+#','',$q['do']) : false;
		if($user)
			return$El->Url->Construct(array('user'=>$user,'do'=>$do)+$q);
		if($id)
			return$El->Url->Construct(array(array('userid'=>$user),'do'=>$do)+$q);
		if($do)
			return$El->Url->Construct(array('do'=>$do)+$q,true,'');
		return$El->Url->Prefix();
	}
}