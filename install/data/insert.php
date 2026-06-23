<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;
use Eleanor\Classes\{L10n,MySQL};

/** @var MySQL $Db */

/** Converting l10n values to JSON db fields
 * @param MySQL $Db
 * @param array $values Языковые значения
 * @return array */
function L10n2JSONDbFields(MySQL$Db,array$values):array
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
			$v=$Db->Escape(\json_encode([$l10n=>$v[$l10n]],JSON));

	else
		foreach($values as &$v)
			$v=$Db->Escape($v[$l10n]);

	return $values;
}

$l10n=$_SESSION['l10n'];
$l10ns=$_SESSION['l10ns'];
$insert=['SET FOREIGN_KEY_CHECKS=0;'];

if($l10ns!==null)
	$l10ns[]=$l10n;

$insert['cron']=<<<SQL
INSERT INTO `cron` (`unit`, `triggers`) VALUES ('account', 'user_signed_in'), ('daily-cleanup', '');
SQL;

$group=L10n2JSONDbFields($Db,[
	1=>['en'=>'Administrators','ru'=>'Администраторы'],
	['en'=>'Site team','ru'=>'Команда сайта'],
	['en'=>'Users','ru'=>'Пользователи'],
	['en'=>'Customers','ru'=>'Клиенты'],
]);
$insert['groups']=<<<SQL
INSERT INTO `groups` (`id`, `title`, `roles`, `slow_mode`) VALUES
(1, {$group[1]}, 'root', 0),
(2, {$group[2]}, 'team', 0),
(3, {$group[3]}, '', 25),
(4, {$group[4]}, '', 10);
SQL;

$data=[
	'ru'=>[
		'slug'=>'демо-страница',
		'title'=>'Демо статической страницы',
		'description'=>'Описание статической страницы',
		'content_source'=>'{"time": 0, "blocks": [{"id": "uYNwSXLDfa", "data": {"text": "Содержимое статической страницы. Его можно редактировать в панели администратора."}, "type": "paragraph"}], "version": "2.31.6"}',
	],
	'en'=>[
		'slug'=>'demo-static',
		'title'=>'Demo static page',
		'description'=>'Meta description of static page',
		'content_source'=>'{"time": 0, "blocks": [{"id": "1zMHBVUVsb", "data": {"text": "Content of the static page. It can be edited id admin panel."}, "type": "paragraph"}], "version": "2.31.6"}',
	]
];
$values=['status'=>'ACTIVE'];

if($l10ns===null)
	$values+=$data[$l10n] ?? $data['en'];
else
	foreach($l10ns as $lk)
		if($data[$lk])
			foreach($data[$lk] as $k=>$v)
				$values[$k.'_'.$lk]=$v;

$insert['static']=fn()=>$Db->Insert('static',$values);

return$insert;