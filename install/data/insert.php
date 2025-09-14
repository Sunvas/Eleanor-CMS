<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;
use Eleanor\Classes\L10n,
	Eleanor\Classes\MySQL;

/** @var MySQL $Db */

/** Преобразование языковых значений в пригодный для сайта формат
 * @param MySQL $Db
 * @param array $values Языковые значения
 * @return array */
function L10n2Db(MySQL$Db,array$values):array
{
	$l10n=L10n::$code;#Выбранный язык
	$l10ns=$_SESSION['l10ns'];#Доступные языки

	if($l10ns)
	{
		$l10ns[]=$l10n;

		foreach($values as &$v)
			$v=$Db->Escape(\json_encode(\array_filter($v,fn($k)=>\in_array($k,$l10ns),\ARRAY_FILTER_USE_KEY),JSON));
	}
	elseif($l10ns!==null)
		foreach($values as &$v)
			$v=$Db->Escape(\json_encode([''=>$v[$l10n],JSON]));

	else
		foreach($values as &$v)
			$v=$Db->Escape($v[$l10n]);

	return $values;
}

$insert=['SET FOREIGN_KEY_CHECKS=0;'];

$insert['cron']=<<<SQL
INSERT INTO `cron` (`unit`, `triggers`) VALUES ('account', 'user_signed_in'), ('daily-cleanup', NULL);
SQL;

$group=L10n2Db($Db,[
	1=>['en'=>'Administrators','ru'=>'Администраторы'],
	['en'=>'Site team','ru'=>'Команда сайта'],
	['en'=>'Users','ru'=>'Пользователи'],
	['en'=>'Customers','ru'=>'Клиенты'],
]);
$insert['groups']=<<<SQL
INSERT INTO `groups` (`id`, `title`, `roles`, `slow_mode`) VALUES
(1, {$group[1]}, 'admin', 0),
(2, {$group[2]}, 'team', 0),
(3, {$group[3]}, '', 25),
(4, {$group[4]}, '', 10);
SQL;


return$insert;