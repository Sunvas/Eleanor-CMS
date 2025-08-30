<?php
namespace CMS;

/** Script of hcaptcha
 * @var ?string $nonce
 * Default:
 * @var string $hcaptcha hcaptcha key (if empty - off) */

if($hcaptcha)
{
	$hl=\Eleanor\Classes\L10n::$code;
	$nonce??=Nonce();

	echo<<<HTML
<script src="//js.hcaptcha.com/1/api.js?recaptchacompat=off&render=explicit&hl={$hl}" nonce="{$nonce}" defer></script>
HTML;
}