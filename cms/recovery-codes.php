<?php
# Eleanor CMS © 2026 --> https://eleanor-cms.com
namespace CMS;

/** Generate a 60-bit recovery code.
 * @throws \Random\RandomException */
function RecoveryCode():string
{
	$alphabet='23456789ABCDEFGHJKLMNPQRSTUVWXYZ';
	$code='';

	for($i=0;$i<12;$i++)
		$code.=$alphabet[\random_int(0,31)];

	return $code;
}

/** Verify and consume a recovery code.
 * @param string $code Recovery code to verify.
 * @param string $json_hashes JSON array of hashed recovery codes.
 * @param ?int $id User ID. If null, the current user ID is used.
 * @return bool True if the code was valid and consumed successfully.
 * @throws \Throwable */
function VerifyRecoveryCode(string$code,string$json_hashes,?int$id=null):bool
{
	$hashes=\json_decode($json_hashes,true);
	$code=\preg_replace('#[^\da-z]+#i','',$code)
			|> \strtoupper(...);

	if(\is_array($hashes))
		foreach($hashes as $hash)
			if(\password_verify($code,$hash))
				return CMS::$Db->Update('users',[
						'recovery_codes'=>fn()=>'JSON_REMOVE(`recovery_codes`,JSON_UNQUOTE(JSON_SEARCH(`recovery_codes`, "one", ?)))',
					],'`id`=? AND ? MEMBER OF(`recovery_codes`)',[$hash,$id ?? CMS::$A->current,$hash])>0;

	return false;
}