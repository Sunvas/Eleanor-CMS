<?php
/*
	Copyright � Eleanor CMS
	URL: http://eleanor-cms.ru, http://eleanor-cms.com
	E-mail: support@eleanor-cms.ru
	Developing: Alexander Sunvas*
	Interface: Rumin Sergey
	=====
	*Pseudonym

	������ �� ��������� ��� ������� ������ ��������
	������������� ����������� ���� ���� � templates/[������ �������]/Classes/[��� ����� �����] � ��� ��� �������� �������.
	� ������ ���� ����� ���� ��� ���������� - ������� ���.
*/
class TPLAdminNews
{
	/*
		�������� ����������� ���� �����
		$items - ������ ����������� �������. ������: ID=>array(), ����� ����������� �������:
			language - ���� ����
			name - �������� ����
			news - ���������� �������� � ������� ����
			_aedit - ������ �� �������������� ����
			_adel - ������ �� �������� ����
		$cnt - ���������� ����� �����
		$pp - ���������� ����� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_name - ������ �� ���������� ������ $items ����� ���� (�����������/�������� � ����������� �� ������� ����������)
			sort_news - ������ �� ���������� ������ $items ���������� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� ����� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function TagsList($items,$cnt,$pp,$qs,$page,$links)
	{	}

	/*
		�������� ����������/�������������� ����
		$id - ������������� �������������� ����, ���� $id==0 ������ ��� �����������
		$controls - �������� ��������� � ������������ � ������� ���������. ���� �����-�� ������� ������� �� �������� ��������, ������ ��� ��������� ��������� ���������
		$values - �������������� HTML ��� ���������, ������� ���������� ������� �� ��������. ����� ������� ������� ��������� � ������� $controls
		$error - ������, ���� ������ ������ - ������ �� ����
		$back - URL ��������
		$hasdraft - ������� ������� ���������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
	*/
	public static function AddEditTag($id,$controls,$values,$error,$back,$hasdraft,$links)
	{
	}

	/*
		�������� ����������� ���� ��������
		$items - ������ ��������. ������: ID=>array(), ����� ����������� �������:
			cats - ������ ID ���������, � ������� ����������� ������ �������
			date - ���� ���������� �������
			enddate - ���� ���������� ������ �������
			author - ��� ������ �������, ��������� HTML
			author_id - ID ������ �������
			status - ������ ���������� �������: 0 - �� �������, 1 - �������, -1 - ������� ���������, -2 - ������� ����������� ���� ���������, 2 - ����������
			title - �������� �������
			_aedit - ������ �� �������������� �������
			_adel - ������ �� �������� �������
			_aswap - ������ �� ��������� ���������� �������, ���� ����� false - ������ ������ ���������� (������� ������)
		$categs - ������ ��������� �������. �����: ID=>array(), ����� ����������� �������:
			title - �������� ���������
		$cnt - ���������� ����� �����
		$pp - ���������� ����� �� ��������
		$qs - ������ ���������� �������� ������ ��� ������� �������
		$page - ����� ������� ��������, �� ������� �� ������ ���������
		$links - �������� ����������� ������, ������ � �������:
			sort_title - ������ �� ���������� ������ $items �� �������� (�����������/�������� � ����������� �� ������� ����������)
			sort_date - ������ �� ���������� ������ $items �� ���� (�����������/�������� � ����������� �� ������� ����������)
			sort_author - ������ �� ���������� ������ $items �� ������ (�����������/�������� � ����������� �� ������� ����������)
			sort_id - ������ �� ���������� ������ $items �� ID (�����������/�������� � ����������� �� ������� ����������)
			form_items - ������ ��� ��������� action �����, ������ ������� ���������� ����������� ������� $items
			pp - �������-��������� ������ �� ��������� ���������� �������� ������������ �� ��������
			first_page - ������ �� ������ �������� ����������
			pages - �������-��������� ������ �� ��������� ��������
	*/
	public static function ShowList($items,$categs,$cnt,$pp,$qs,$page,$links)
	{	}

	/*
		�������� ����������/�������������� �������
		$id - ������������� ������������� �������, ���� $id==0 ������ ������� �����������
		$values - ������ �������� �����
			����� �����:
			cats - ������ ���������
			date  - ���� ���������� �������
			pinned - ���� �� ����������� �������, ������� ����� ����������
			enddate - ���������� ������� �������
			author - ��� ������ �������
			author_id - ID ������ �������
			show_detail - ���� ��������� ������ ������ "���������" ��� ���������� ������������ �������
			show_sokr - ���� ��������� ����������� ������ ����������� ������� ��� ��������� ���������
			reads - ���������� ���������� �������
			status - ������ ���������� �������: 0 - �� �������, 1 - �������, -1 - ������� ���������

			�������� �����:
			title - ��������� �������
			announcement - ����� �������
			text - ����� �������
			uri - URI �������
			meta_title - ��������� ���� �������� ��� ��������� �������
			meta_descr - ���� �������� �������

			������ �������� �����:
			tags - ���� �������

			����������� �����:
			_onelang - ���� ����������� ������� ��� ���������� ���������������
			_maincat - ������������� �������� ��������� �������
		$errors - ������ ������
		$uploader - ��������� ����������
		$voting - ��������� ���������
		$bypost - ������� ����, ��� ������ ����� ����� POST �������
		$hasdraft - ������� ������� ���������
		$back - URL ��������
		$links - �������� ����������� ������, ������ � �������:
			delete - ������ �� �������� ��������� ��� false
			nodraft - ������ �� ������/���������� ��������� ��� ������������� ��������� ��� false
			draft - ������ �� ���������� ���������� (��� ������� ��������)
	*/
	public static function AddEdit($id,$values,$errors,$uploader,$voting,$bypost,$hasdraft,$back,$links)
	{	}

	/*
		�������� �������� �������
		$a - ������ ���������� ��������� ��������
			title - �������
		$back - URL ��������
	*/
	public static function Delete($a,$back)
	{	}

	/*
		�������� �������� ����
		$a - ������ ���������� ���������� ����
			name - ���
		$back - URL ��������
	*/
	public static function DeleteTag($a,$back)
	{

	}

	/*
		������� ��� ���������
		$c - ��������� ���������
	*/
	public static function Categories($c)
	{

	}

	/*
		������� ��� ��������
		$c - ��������� ��������
	*/
	public static function Options($c)
	{

	}
}