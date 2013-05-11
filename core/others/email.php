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
class Email
{
	public
		$from,#Непосредственный автор письма
		$sender,#Отправитель, кто отправляет письмо, а не тот, кто его автор
		$subject,#Тема письма
		$pr,#Уровень важности от 1 (самый важный) до 5 (самый неважный)

		$reply,#Куда должен прийти ответ
		$notice_on=false,#Требовать подтверждение о прочтении
		$notice,#E-mail, куда будет отправляться подверждение

		$method='mail',#Метод отправки smtp|mail
		$smtp_port=25,
		$smtp_host,
		$smtp_user,
		$smtp_pass,

		$parts=array();#Части письма

	protected
		$lang;

	/**
	 * Упрощенная отправка письма, практически идентичена стандартной функции mail().
	 * Пример использования: Email::Simple('mail@example.com','Тема письма','Текст письма',array('files'=>array('имя файла'=>'Содержимое файла',0=>'path/to/files.txt')));
	 *
	 * @param string|array $to Получатель письма
	 * @param string $subj Тема письма
	 * @param string $mess Текст письма
	 */
	public static function Simple($to,$subj,$mess,array$a=array())
	{
		$a+=array(
			'type'=>'text/html',
			'files'=>array(),
			'copy'=>array(),
			'hidden'=>array(),
		);

		$Email=new self;
		$Email->parts=array(
			'multipart'=>'mixed',
			array(
				'content-type'=>$a['type'],
				'charset'=>DISPLAY_CHARSET,
				'content'=>str_replace('="go.php?','="',$mess),
			),
		);
		foreach($a['files'] as $k=>&$v)
		{
			if(is_int($k))
			{
				$name=basename($v);
				$c=file_get_contents($v);
			}
			else
			{
				$name=basename($k);
				$c=$v;
			}
			$Email->parts[]=array(
				'content-type'=>Types::MimeTypeByExt($name),
				'filename'=>$name,
				'content'=>$c,
			);
		}
		foreach($a as $k=>&$v)
			if(!in_array($k,array('type','files','copy','hidden')) and $v!==false)
				$Email->$k=$v;
		$Email->subject=$subj;
		$Email->Send(array('to'=>$to,'cc'=>$a['copy'],'bcc'=>$a['hidden']));
	}

	/**
	 * Конструктор класса, здесь задаются значения по умолчанию, которые читаются из настроек системы
	 */
	public function __construct()
	{
		$vars=Eleanor::LoadOptions('mailer',true);
		if($vars['mail_method']=='smtp')
		{
			$this->method='smtp';
			if(isset($vars['mail_smtp_port']))
				$this->smtp_port=(int)$vars['mail_smtp_port'];
			$this->smtp_host=$vars['mail_smtp_host'] ? $vars['mail_smtp_host'] : 'localhost';
			$this->smtp_user=$vars['mail_smtp_user'];
			$this->smtp_pass=$vars['mail_smtp_pass'];
		}
		$this->from=$vars['mail_from'];
		$this->pr=$vars['mail_priority'];
		$this->reply=$vars['mail_reply'];
		$this->notice=$vars['mail_notice'];
	}

	/**
	 * Непосредственная отправка письма
	 *
	 * @param array $a Параметры отправки письма
	 */
	public function Send(array$a=array())
	{
		if(empty($a['to']))
			return;
		$a+=array(
			'bcc'=>array(),#Копия (может быть строкой или массивом)
			'cc'=>array(),#Скрытая (может быть строкой или массивом)
		);
		foreach($a as &$av)
			$av=(array)$av;

		$d="\n";
		$subject='=?'.DISPLAY_CHARSET.'?B?'.base64_encode($this->subject).'?=';
		$headers='MIME-Version: 1.0'.$d
			.'Date: '.date('r').$d
			.'From: '.$this->from.$d
			.($this->sender ? 'Sender: '.$this->sender.$d : '')
			.($a['cc'] ? 'Cc: '.join(', ',$a['cc']).$d : '')
			.(($a['bcc'] and $this->method!='smtp') ? 'Bcc: '.join(', ',$a['bcc']).$d : '')
			.($this->method=='mail'
				? ''
				: ($a['to'] ? 'To: '.join(', ',$a['to']).$d : '')
					.'Subject: '.$subject.$d
			)
			.'Return-Path: '.$this->from.$d
			.($this->reply ? 'Reply-To: '.$this->reply.$d : '')
			.($this->from && $this->notice_on ? 'Return-Receipt-To: '.$this->from.$d : '')
			.($this->notice_on && $this->notice ? 'Disposition-Notification-To: '.$this->notice.$d : '')
			.'X-Priority: '.$this->pr.$d.self::DoHeaders($this->parts);

		switch($this->method)
		{
			case'mail':
				if(!mail(join(', ',$a['to']),$subject,null,$headers))
					throw new EE('MAIL',EE::UNIT);
			break;
			case'smtp':
				if(!$socket=fsockopen($this->smtp_host,$this->smtp_port,$errno,$errstr,30))
					throw new EE('SMTP error #'.$errno.': '.$errstr,EE::UNIT);
				$error=true;
				do
				{
					if(!self::Parse($socket,220))
						break;
					fputs($socket,($this->smtp_user ? 'EHLO ' : 'HELO ').$this->smtp_host.PHP_EOL);
					$error=false;
					if(!self::Parse($socket,250))
						break;
					if($this->smtp_user)
					{
						fputs($socket,"AUTH LOGIN\n");
						if(!self::Parse($socket,334))
							break;
						fputs($socket,base64_encode($this->smtp_user).PHP_EOL);
						if(!self::Parse($socket,334))
							break;
						if($this->smtp_pass)
						{
							fputs($socket,base64_encode($this->smtp_pass).PHP_EOL);
							if(!self::Parse($socket,235))
								break;
						}
					}
					fputs($socket,'MAIL FROM:'.$this->from.PHP_EOL);
					if(!self::Parse($socket,250))
						break;
					foreach(array_merge($a['to'],$a['cc'],$a['bcc']) as $v)
					{
						fputs($socket,'RCPT TO:'.$v.PHP_EOL);
						if(!self::Parse($socket,250))
							break;
					}
					fputs($socket,"DATA\n");
					if(!self::Parse($socket,354))
						break;
					fputs($socket,$headers."\n.\n");
					if(!self::Parse($socket,250))
						break;
					fputs($socket,"QUIT\n");
					fclose($socket);
				}while(false);
				if($error)
				{
					fclose($socket);
					throw new EE('SMTP',EE::UNIT);
				}
			break;
			default:
				throw new EE('NO_METHOD',EE::DEV);
		}
		return true;
	}

	/**
	 * Создание заголовков письма
	 *
	 * @param array $a Параметры письма
	 * @param array $def Параметры по умолчанию
	 */
	protected static function DoHeaders($a,array$def=array())
	{
		$r='';
		$d="\n";
		if(isset($a['multipart']))
		{#Multipart
			$b=empty($a['boundary']) ? uniqid() : $a['boundary'];
			$r.='Content-Type: multipart/'.$a['multipart'].'; boundary="'.$b.'"'.$d.$d.'--'.$b.$d;
			$e=(int)max(array_keys($a));
			foreach($a as $k=>&$v)
				if(is_int($k))
					$r.=self::DoHeaders($v,array('multipart'=>$a['multipart'])).'--'.$b.($k==$e ? '--' : '').$d;
		}
		elseif(isset($a['content']))
		{
			$encode=!isset($a['encoding']) || !in_array($a['encoding'],array('base64','quoted-printable'));
			if(isset($a['id']))
				$r.='Content-ID: <'.$a['id'].'>'.$d;
			$r.='Content-Type: '.(isset($a['content-type']) ? $a['content-type'].(isset($a['charset']) ? '; charset='.$a['charset'] : '') : 'text/plain; charset=windows-1251').$d;
			$dtype=false;
			if(isset($a['filename']))
				$a['disposition']='';
			if(isset($a['disposition']) or isset($def['multipart'],$a['content-type']) and $dtype=in_array($def['multipart'],array('mixed','related')) and strpos($a['content-type'],'text')!==0)
				$r.='Content-Disposition: '.($dtype && $def['multipart']=='related' ? 'inline' : 'attachment; filename="=?'.DISPLAY_CHARSET.'?B?'.base64_encode(isset($a['filename']) ? $a['filename'] : 'file').'?="').$d;
			$r.='Content-Transfer-Encoding: '.($encode ? 'base64' : $a['encoding']).$d.$d;
			if($encode)
				$r.=chunk_split(base64_encode((string)$a['content']),76,"\n");
			elseif($a['encoding']=='base64')
			{
				while(preg_match("#^.{76}[^\r\n].+$#m",$a['content'])>0)
					$a['content']=preg_replace("#^(.{76})([^\r\n].+)$#m","\\1\r\n\\2",$a['content']);
				$r.=$a['content'].$d;
			}
			else
			{
				while(preg_match("#^.{75}[^=\r\n].+$#m",$a['content'])>0)
					$a['content']=preg_replace("#^(.{75})([^=\r\n].+)$#m","\\1=\r\n\\2",$a['content']);
				$r.=$a['content'].$d;
			}
		}
		return$r;
	}

	/**
	 * Чение данных из сокета
	 */
	protected static function Parse($socket,$resp)
	{
		while($r=fgets($socket,128))
			if(strpos($r,(string)$resp)===0)
				return true;
		return false;
	}
}