<?php
/*
	Copyright © Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.su, http://eleanor-cms.com, http://eleanor-cms.net, http://eleanor.su
	E-mail: support@eleanor-cms.ru, support@eleanor.su
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym
*/

class Rating extends BaseClass
{	/**
	 * Вычисление нового среднего значения при добавлении оценки
	 *
	 * @param int $total Количество проголосовавших
	 * @param float $average Средняя оценка
	 * @param int $mark Добавляемая оценка
	 */	public static function AddMark($total,$average,$mark)
	{		return round((ceil($average*$total)+$mark)/++$total,2);	}

	/**
	 * Вычисление нового среднего значения при удалении оценки
	 *
	 * @param int $total Количество проголосовавших
	 * @param float $average Средняя оценка
	 * @param int $mark Удаляемая оценка
	 */
	public static function SubMark($total,$average,$mark)
	{
		return round((ceil($average*$total)-$mark)/--$total,2);
	}

	/**
	 * Вычисление нового среднего значения при изменении оценки
	 *
	 * @param int $total Количество проголосовавших
	 * @param float $average Средняя оценка
	 * @param int $oldmark Старая оценка
	 * @param int $newmark Новая оценка
	 */
	public static function ChangeMark($total,$average,$oldmark,$newmark)
	{
		return round((ceil($average*$total)-$oldmark+$newmark)/$total,2);
	}
}