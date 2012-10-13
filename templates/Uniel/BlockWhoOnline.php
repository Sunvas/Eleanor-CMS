<?php
/*
	Оформление содержимого блока пользователей онлайн

	@var массив пользователей онлайн. Формат id=>array(), ключи:
		p - HTML-префикс группы
		e - HTML-окончание группы
		n - имя пользователя, безопасный HTML
		t - время входа
	@var массив поисковых ботов онлайн. Формат имя бота=>array(), ключи:
		cnt - количество "щупалец" (сессий) у этого бота
		t - время входа
	@var количество пользователей онлайн
	@var количество ботов онлайн
*/
if(!defined('CMS'))die;

$ltpl=Eleanor::$Language['tpl'];
$mo=array_keys($GLOBALS['Eleanor']->modules['sections'],'online');
$mo=reset($mo);

$users=$bots='';
$t=time();

foreach($v_0 as $k=>&$v)
{
	$et=floor(($t-strtotime($v['t']))/60);
	$users.='<a href="'.Eleanor::$Login->UserLink($v['n'],$k).'" title="'.$ltpl['minutes_ago']($et).'">'.$v['p'].htmlspecialchars($v['n'],ELENT,CHARSET).$v['e'].'</a>, ';
}

foreach($v_1 as $k=>&$v)
{
	$et=floor(($t-strtotime($v['t']))/60);
	$bots.='<span title="'.$ltpl['minutes_ago']($et).'">'.$k.($v['cnt']>1 ? ' ('.$v['cnt'].')' : '').'</span>, ';
}

return($users ? '<h5>'.$ltpl['users']($v_2).'</h5><p>'.rtrim($users,', ').'</p>' : '')
	.($bots ? '<h5>'.$ltpl['bots']($v_3).'</h5><p>'.rtrim($bots,', ').'</p>' : '')
	.($v_4>0 ? '<h5>'.$ltpl['guests']($v_4).'</h5><br />' : '')
	.'<a href="'.$GLOBALS['Eleanor']->Url->special.$GLOBALS['Eleanor']->Url->Construct(array('module'=>$mo),false).'">'.$ltpl['alls'].'</a>';
