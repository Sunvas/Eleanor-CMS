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
if(!defined('CMS') or !function_exists('BeAs'))die;
$orig=Eleanor::$service;
if(Eleanor::$service!='upload')
{
	BeAs('admin');
	if(!Eleanor::$Login->IsUser() or !Eleanor::$Permissions->IsAdmin() or !isset($_REQUEST['file']))
		return Error(false,array('httpcode'=>403));
	$Eleanor->Url->file=Eleanor::$services['admin']['file'];
}
require Eleanor::$root.'addons/admin/info.php';
if(isset($info[$_REQUEST['file']],$info[$_REQUEST['file']]['services'][$orig]))
{
	$a=$info[$_REQUEST['file']];
	$Eleanor->module=array(
		'name'=>$_REQUEST['file'],
		'title'=>$a['title'],
		'image'=>$a['image'],
		'path'=>Eleanor::$root.'addons/admin/modules',
	);
	include Eleanor::$root.'addons/admin/modules/'.$info[$_REQUEST['file']]['services'][$orig];
}
else
	Error(false,array('httpcode'=>403));