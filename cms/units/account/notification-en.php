<?php
return[
	/** Sign in notification via Telegram bot
	 * @param string $name Username
	 * @param string way (username, telegram)
	 * @param string $ip
	 * @param string $ua User Agent (browser)
	 * @return string */
	'telegram'=>function(string$name,string$way,string$ip,string$ua) {
		return<<<HTML
<b>{$name}, new sign in to your account</b>
Method: {$way}.
Browser: <code>{$ua}</code>.
IP: <a href="https://www.infobyip.com/?ip={$ip}" target="_blank">{$ip}</a>
HTML;
	}
];