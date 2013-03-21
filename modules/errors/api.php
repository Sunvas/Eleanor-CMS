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
class ApiErrors extends BaseClass
{
	private
		$config=array();

	public function __construct($config=array())
	{
		$this->config=$config;
	}

	public function QuickMenu($type='admin',$module=array())
	{
		if(!isset(Eleanor::$Language[__class__]))
			Eleanor::$Language->Load(__dir__.'/api-*.php',__class__);
		if(!is_array($module['sections']))
		{
			$module['sections']=unserialize($module['sections']);
			foreach($module['sections'] as &$v)
				if(Eleanor::$vars['multilang'] and isset($v[Language::$main]))
					$v=reset($v[Language::$main]);
				else
					$v=isset($v[LANGUAGE]) ? reset($v[LANGUAGE]) : reset($v['']);
		}
		return array(
			array(
				'title'=>Eleanor::$Language[__class__]['add'],
				'href'=>Eleanor::$services['admin']['file'].'?section=modules&amp;module='.urlencode($module['sections']['errors']).'&amp;do=add',
			),
		);
	}

	public function LangUrl($q,$lang)
	{
		$El=Eleanor::getInstance();
		if(!is_array($q))
		{
			$El->Url->GetEnding($El->Url->ending,true);
			$q=$El->Url->Parse(array('no'),true);
		}
		$id=isset($q['id']) ? (int)$q['id'] : false;
		$no=isset($q['no']) ? (int)$q['no'] : false;
		if(!$id and !$no)
			return;
		$this->config=include(__dir__.'/config.php');

		if($no)
		{
			$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->config['t'].'` INNER JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.Language::$main.'\') AND `uri`='.Eleanor::$Db->Escape($no).' LIMIT 1');
			if(!list($id)=$R->fetch_row())
				return;
		}
		$R2=Eleanor::$Db->Query('SELECT `uri` FROM `'.$this->config['t'].'` INNER JOIN `'.$this->config['tl'].'` USING(`id`) WHERE `language` IN (\'\',\''.$lang.'\') AND `id`='.$id.' LIMIT 1');
		if(list($no)=$R2->fetch_row())
			return$El->Url->Construct(array('no'=>$no));
	}
}