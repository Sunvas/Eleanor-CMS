<?php
if(isset($_GET['eleanorid']) and preg_match('#^[a-z0-9]+$#i',(string)$_GET['eleanorid'])>0)
{
	session_id((string)$_GET['eleanorid']);
	session_start();
	if(!isset($_SESSION['EleanorCMS4sypex']))
		return;
	define('CMS',true);
	$c=include $_SESSION['EleanorCMS4sypex']['c'];
	if($this->connect($c['db_host'],'',$c['db_user'],$c['db_pass']))
	{
		$this->CFG['exitURL']=$_SESSION['EleanorCMS4sypex']['e'];
		$this->CFG['backup_path']=$_SESSION['EleanorCMS4sypex']['b'];
		$this->CFG['backup_url']=$_SESSION['EleanorCMS4sypex']['bu'];
		$this->CFG['lang']=$_SESSION['EleanorCMS4sypex']['l'];
		$auth=true;
	}
}