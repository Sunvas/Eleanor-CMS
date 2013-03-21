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

$event=isset($_POST['event']) ? (string)$_POST['event'] : '';
switch($event)
{
	case'fixed':
		$log=isset($_POST['log']) ? (string)$_POST['log'] : '';
		if(in_array($log,array('errors','db_errors','request_errors')))
		{
			$id=isset($_POST['id']) ? (string)$_POST['id'] : '';
			$log=Eleanor::$root.'addons/logs/'.$log.'.log';
			$hlog=$log.'.inc';

			if(is_file($log) and is_file($hlog))
			{
				$info=(array)unserialize(file_get_contents($hlog));
				if(isset($info[$id]))
				{
					if(count($info)==1)
					{
						Files::Delete($log);
						Files::Delete($hlog);
					}
					else
					{
						$fh=fopen($log,'rb+');
						if(flock($fh,LOCK_EX))
						{
							$diff=Files::FReplace($fh,'',$info[$id]['o'],$info[$id]['l']+strlen(PHP_EOL)*2);
							foreach($info as &$v)
								if($v['o']>$info[$id]['o'])
									$v['o']+=$diff;
							flock($fh,LOCK_UN);
							fclose($fh);
							unset($info[$id]);
							file_put_contents($hlog,serialize($info));
						}
						else
						{
							fclose($fh);
							Error();
							break;
						}
					}

				}
			}

			Result(true);
			break;
		}
	default:
		Error(Eleanor::$Language['main']['unknown_event']);
}