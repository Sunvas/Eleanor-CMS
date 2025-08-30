<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;
use CMS\CMS;

/** Проверка решения hCaptcha
 * @return bool */
class hCaptcha extends \Eleanor\Basic
{
	static function Check(string$k='h-captcha-response'):bool
	{
		$resp=\is_string($_POST[$k] ?? 0) ? $_POST[$k] : '';

		if(\strlen($resp)<25)
			return false;

		$curl=\curl_init('https://api.hcaptcha.com/siteverify');

		\curl_setopt_array($curl,[
			\CURLOPT_TIMEOUT=>10,
			\CURLOPT_RETURNTRANSFER=>true,
			\CURLOPT_ENCODING=>'',//https://php.watch/articles/curl-php-accept-encoding-compression
			\CURLOPT_HEADER=>false,
			\CURLOPT_POST=>true,
			\CURLOPT_POSTFIELDS=>[
				'secret'=>CMS::$config['system']['hcaptcha_secret'],
				'response'=>$resp,
				'remoteip'=>\inet_ntop(CMS::$ip),
			],
		]);

		$json=\curl_exec($curl);
		$errn=\curl_errno($curl);

		\curl_close($curl);

		if($errn>0)
			return \strlen($resp)>100;

		$json=\json_decode($json,true);
		return $json && isset($json['success']) && $json['success'];
	}
}

return hCaptcha::class;