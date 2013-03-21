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
global$Eleanor;
Eleanor::$Language->Load('addons/admin/langs/s_general-*.php','sg');
Eleanor::$Template->queue[]='General';
$event=isset($_POST['event']) ? (string)$_POST['event'] : '';
switch($event)
{
	case'mynotesload':
		$text=Eleanor::$Cache->Get('notes_'.Eleanor::$Login->GetUserValue('id'),true);
		Result(Eleanor::$Template->Notes($Eleanor->Editor->Area('emynotes',$text),true));
	break;
	case'conotesload':
		$text=Eleanor::$Cache->Get('notes',true);
		Result(Eleanor::$Template->Notes($Eleanor->Editor->Area('econotes',$text),true));
	break;
	case'mynotes':
		$text=$Eleanor->Editor_result->GetHtml('text');
		Eleanor::$Cache->Put('notes_'.Eleanor::$Login->GetUserValue('id'),$text,0,true);
		Result(Eleanor::$Template->Notes(OwnBB::Parse($text)));
	break;
	case'conotes':
		$text=$Eleanor->Editor_result->GetHtml('text');
		Eleanor::$Cache->Put('notes',$text,0,true);
		Result(Eleanor::$Template->Notes(OwnBB::Parse($text)));
	break;
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}