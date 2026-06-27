<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS\Classes;

use CMS\CMS;

class hCaptcha extends \Eleanor\Basic
{
	/** If hCaptcha is unavailable, accept only non-trivial tokens to avoid blocking all users */
	const bool SOFT_FAIL=true;

	/** Verify hCaptcha challenge response
	 * @param string $k POST field containing hCaptcha response
	 * @return bool */
	static function Check(string$k='h-captcha-response'):bool
	{
		$resp=\is_string($_POST[$k] ?? 0) ? $_POST[$k] : '';

		if(\strlen($resp)<25)
			return false;

		$curl=\curl_init('https://api.hcaptcha.com/siteverify');

		\curl_setopt_array($curl,[
			\CURLOPT_TIMEOUT=>10,
			\CURLOPT_RETURNTRANSFER=>true,
			\CURLOPT_ENCODING=>'',# https://php.watch/articles/curl-php-accept-encoding-compression
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

		if($errn>0)
			return static::SOFT_FAIL && \strlen($resp)>100;

		$json=\json_decode($json,true);
		return $json['success'] ?? false;
	}
}

# Not required here because class name matches filename
return hCaptcha::class;