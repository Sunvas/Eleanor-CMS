<?php
return[
	/** Уведомление о новом входе через Telegram bot
	 * @param string $name Имя пользователя
	 * @param string Способ (username, telegram)
	 * @param string $ip
	 * @param string $ua User Agent (браузер)
	 * @return string */
	'telegram'=>function(string$name,string$way,string$ip,string$ua){
		$way=match($way){
			'username'=>'логин и пароль',
			'telegram'=>'телеграм',
			default=>$way
		};

		return<<<HTML
<b>{$name}, новый выход в вашу учётную запись</b>
Метод: {$way}.
Браузер: <code>{$ua}</code>.
IP: <a href="https://www.infobyip.com/?ip={$ip}" target="_blank">{$ip}</a>
HTML;
	}
];