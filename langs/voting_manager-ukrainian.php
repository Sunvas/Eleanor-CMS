<?php
return array(
	#��� /core/others/voting_manager.php
	'errorva'=>'������� ������� ��� ��������� ����� ����������!',
	'EMPTY_VARIANT'=>function($l){return'���� � ������� ������ �� ����������'.($l ? ' (��� '.$l.')' : '');},
	'EMPTY_TITLE'=>function($l=''){return'�� ������ ����� �������'.($l ? ' (��� '.$l.')' : '');},
	'DATES'=>'���� ���������� ���������� �� ���� ���� ������ ���� ���� �������!',
	'addvoting'=>'������ ����������',
	'dbegin'=>'���� �������',
	'lblank'=>'������� ��������, ��� ������ ���������� �����',
	'dend'=>'���� ���������',
	'onlyusers'=>'�������� ����� ������������',
	'onlyusers_'=>'���� �� ������� ����������. ����������� ������� ���������� ����� 1 ���.',
	'againdays'=>'���� �� ������ ������',
	'againdays_'=>'ʳ������ ����, ���� ���� ���������� ����� ������������� �����',
	'votes'=>'�������',
	'question'=>'�������',
	'vv'=>'������� ��������',
	'multiple'=>'�������� ������',
	'maxa'=>'����������� ����� ��������',

	#��� �������
	'rvoters'=>function($n)
	{		return$n.Ukrainian::Plural($n,array(' ���������� ������� ������������',' ����������� ������� �������������',' ������������ ������� �������������'));
	},
	'norv'=>'�� ��� ������ ����� �� ���������.',
	'va'=>'������ ������',
	'questions'=>'�������',
	'addq'=>'������ �������',
	'delq'=>'�������� �������',
);