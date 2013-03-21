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

class Voting extends BaseClass
{
	public
		$mid,#ID модуля
		$uid,#ID пользователя
		$tpl='Voting',#Класс шаблона с оформлением опроса
		$status;#Статус пользователя по отношению к голосованию: [false] - можно голосовать, voted - уже проголосовали, refused - голос не защитан, confirmed - голос защитан, guest - голосовать нельзя, потому что голосование только для пользователей, wait - ожидает открытия, finished - голосование завершено

	protected
		$TC,#Объект TimeCheck
		$table,#Таблица опроса
		$voting;#Дамп опроса

	/**
	 * Конструктор
	 *
	 * @param int $id идентификатор опроса
	 * @param string|FALSE $t имя основной таблицы с опросами, в случае FALSE берется таблица по умолчанию
	 */
	public function __construct($id,$t=false)
	{
		$this->table=$t ? $t : P.'voting';
		$this->uid=(int)Eleanor::$Login->GetUserValue('id');
		$R=Eleanor::$Db->Query('SELECT * FROM `'.$this->table.'` WHERE `id`='.(int)$id.' LIMIT 1');
		$this->voting=$R->fetch_assoc();
	}

	/**
	 * Отображение опроса
	 *
	 * @param array $request Массив параметров AJAX запроса
	 */
	public function Show(array$request=array())
	{
		if(!$this->voting)
			return'';

		$qs=array();
		$R=Eleanor::$Db->Query('SELECT `qid`,`multiple`,`maxans`,`answers`,`title`,`variants` FROM `'.$this->table.'_q` INNER JOIN `'.$this->table.'_q_l` USING(`id`,`qid`) WHERE `id`='.$this->voting['id'].' AND `language` IN (\'\',\''.Language::$main.'\')');
		while($a=$R->fetch_assoc())
		{
			$a['answers']=$a['answers'] ? (array)unserialize($a['answers']) : array();
			$a['variants']=$a['variants'] ? (array)unserialize($a['variants']) : array();
			$qs[$a['qid']]=array_slice($a,1);
		}
		if(!isset($this->status))
			$this->Status();
		if($this->tpl)
			Eleanor::$Template->queue[]=$this->tpl;
		return Eleanor::$Template->VotingCover($this->voting,$qs,$this->status,$request);
	}

	/**
	 * Получение статуса опроса (смотри выше описание всех возможных статусов)
	 */
	public function Status()
	{
		if((int)$this->voting['begin']>0 and time()<strtotime($this->voting['begin']))
			return$this->status='wait';

		if((int)$this->voting['end']>0 and time()>strtotime($this->voting['end']))
			return$this->status='finished';

		if($this->voting['onlyusers'] or $this->uid)
		{
			if($this->uid)
			{
				$R=Eleanor::$Db->Query('SELECT `id` FROM `'.$this->table.'_results` WHERE `id`='.$this->voting['id'].' AND `uid`='.$this->uid.' LIMIT 1');
				return$this->status=$R->num_rows==0 ? false : 'voted';
			}
			return$this->status='guest';
		}

		if(!isset($this->TC))
			$this->TC=new TimeCheck($this->mid,false,$this->uid);
		$this->status=$this->TC->Check('v'.$this->voting['id']) ? 'voted' : false;
	}
}