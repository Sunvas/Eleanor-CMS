<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class TaskInformer extends BaseClass implements Task
{
	private
		$data=array();

	public function Run($data)
	{
		if(DEBUG)
			return;

		if(!is_array($data))
			$data=array();
		if(!isset($data['t']))
			$data['t']=time()-86400;

		$vars=Eleanor::LoadOptions(array('errors','site'),true);

		$f=Eleanor::$root.'addons/logs/errors.log.inc';
		if($vars['errors_code_users'] and is_file($f))
		{
			$vars['errors_code_users']=explode(',,',trim($vars['errors_code_users'],','));
			$users=array();
			$R=Eleanor::$Db->Query('SELECT `email`,`name`,`language` FROM `'.P.'users_site` WHERE `id`'.Eleanor::$Db->In($vars['errors_code_users']));
			while($a=$R->fetch_assoc())
				$users[]=$a;

			$repl=array('cnt'=>0,'errors'=>array());
			$f=file_get_contents($f);
			$f=$f ? (array)unserialize($f) : array();
			foreach($f as &$v)
				if(strtotime($v['d']['d'])>$data['t'])
				{
					$repl['cnt']++;
					$repl['errors'][]=($v['d']['n']>1 ? substr_replace($v['d']['e'],'('.$v['d']['n'].')',strpos($v['d']['e'],':'),0) : $v['d']['e']).PHP_EOL.'File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL.'URL: '.PROTOCOL.Eleanor::$punycode.Eleanor::$site_path.($v['d']['p'] ? $v['d']['p'] : '').PHP_EOL.'Date: '.$v['d']['d'];
				}

			if($repl['cnt']>0)
			{
				$repl['errors']=join('<br /><br />',$repl['errors']);
				$repl+=array(
					'site'=>$vars['site_name'],
					'link'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,
				);
				$vars['errors_code_text']=OwnBB::Parse($vars['errors_code_text']);
				foreach($users as &$v)
				{
					Language::$main=$v['language'] ? $v['language'] : LANGUAGE;
					$repl['name']=$v['name'];
					Email::Simple(
						$v['email'],
						Eleanor::ExecBBLogic($vars['errors_code_title'],$repl),
						Eleanor::ExecBBLogic($vars['errors_code_text'],$repl)
					);
				}
			}
		}

		$f=Eleanor::$root.'addons/logs/db_errors.log.inc';
		if($vars['errors_db_users'] and is_file($f))
		{
			$vars['errors_db_users']=explode(',,',trim($vars['errors_db_users'],','));
			$users=array();
			$R=Eleanor::$Db->Query('SELECT `email`,`name`,`language` FROM `'.P.'users_site` WHERE `id`'.Eleanor::$Db->In($vars['errors_db_users']));
			while($a=$R->fetch_assoc())
				$users[]=$a;

			$repl=array('cnt'=>0,'errors'=>array());
			$f=file_get_contents($f);
			$f=$f ? (array)unserialize($f) : array();
			foreach($f as &$v)
				if(strtotime($v['d']['d'])>$data['t'])
				{
					$repl['cnt']++;
					$log=$v['d']['e'].PHP_EOL;
					switch($v['d']['d'])
					{
						case'connect':
							$log.='DB: '.$v['d']['db'].PHP_EOL.'File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL.'Date: '.$v['d']['d'].PHP_EOL.'Happend: '.$v['d']['n'];
						break;
						case'query':
							$log.='Query: '.$v['d']['q'].PHP_EOL.'File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL.'Date: '.$v['d']['d'].PHP_EOL.'Happend: '.$v['d']['n'];
						break;
						default:
							$log.='File: '.$v['d']['f'].'['.$v['d']['l'].']'.PHP_EOL.'Date: '.$v['d']['d'].PHP_EOL.'Happend: '.$v['d']['n'];
					}
					$repl['errors'][]=$log;
				}

			if($repl['cnt']>0)
			{
				$repl['errors']=join('<br /><br />',$repl['errors']);
				$repl+=array(
					'site'=>$vars['site_name'],
					'link'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,
				);
				$vars['errors_db_text']=OwnBB::Parse($vars['errors_db_text']);
				foreach($users as &$v)
				{
					Language::$main=$v['language'] ? $v['language'] : LANGUAGE;
					$repl['name']=$v['name'];
					Email::Simple(
						$v['email'],
						Eleanor::ExecBBLogic($vars['errors_db_title'],$repl),
						Eleanor::ExecBBLogic($vars['errors_db_text'],$repl)
					);
				}
			}
		}

		$f=Eleanor::$root.'addons/logs/request_errors.log.inc';
		if($vars['errors_requests_users'] and is_file($f))
		{
			$vars['errors_requests_users']=explode(',,',trim($vars['errors_requests_users'],','));
			$users=array();
			$R=Eleanor::$Db->Query('SELECT `email`,`name`,`language` FROM `'.P.'users_site` WHERE `id`'.Eleanor::$Db->In($vars['errors_requests_users']));
			while($a=$R->fetch_assoc())
				$users[]=$a;

			$repl=array('cnt'=>0,'errors'=>array());
			$f=file_get_contents($f);
			$f=$f ? (array)unserialize($f) : array();
			foreach($f as &$v)
				if(strtotime($v['d']['d'])>$data['t'])
				{
					$repl['cnt']++;
					$repl['errors'][]=$v['d']['e'].'('.$v['d']['n'].'): '.($v['d']['p'] ? $v['d']['p'] : '/').PHP_EOL.'Date: '.$v['d']['d'].PHP_EOL.'IP: '.$v['d']['ip'].PHP_EOL.(isset($v['d']['u']) ? 'User: '.$v['d']['u'].PHP_EOL : '').'Browser: '.$v['d']['b'].PHP_EOL.'Referrers: '.join(', ',$v['d']['r']);
				}

			if($repl['cnt']>0)
			{
				$repl['errors']=join('<br /><br />',$repl['errors']);
				$repl+=array(
					'site'=>$vars['site_name'],
					'link'=>PROTOCOL.Eleanor::$punycode.Eleanor::$site_path,
				);
				$vars['errors_requests_text']=OwnBB::Parse($vars['errors_requests_text']);
				foreach($users as &$v)
				{
					Language::$main=$v['language'] ? $v['language'] : LANGUAGE;
					$repl['name']=$v['name'];
					Email::Simple(
						$v['email'],
						Eleanor::ExecBBLogic($vars['errors_requests_title'],$repl),
						Eleanor::ExecBBLogic($vars['errors_requests_text'],$repl)
					);
				}
			}
		}

		$data['t']=time();
		$this->data=$data;
	}

	public function GetNextRunInfo()
	{
		return$this->data;
	}
}