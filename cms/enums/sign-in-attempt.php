<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS\Enums;

use CMS\CMS;

/** Result of a user sign-in attempt. */
enum SignInAttempt:string
{
	/** User signed in successfully. */
	case Ok='OK';

	/** Sign-in failed because the password was incorrect. */
	case WrongPassword='WRONG_PASSWORD';

	/** Sign-in failed because the TOTP code was incorrect. */
	case WrongTOTP='WRONG_TOTP';

	/** Sign-in failed because the recovery code was incorrect. */
	case WrongRecoveryCode='WRONG_RECOVERY_CODE';

	/** Log a user sign-in attempt.
	 * @param int $user_id User ID */
	function Log(int$user_id):void
	{
		CMS::$Db->Insert('users_signin_log',[
			'user_id'=>$user_id,
			'result'=>$this->value,
			'ip'=>CMS::$ip,
			'ua'=>$_SERVER['HTTP_USER_AGENT'] ?? ''
		]);
	}
}

# Not required here because the enum name matches filename
return SignInAttempt::class;