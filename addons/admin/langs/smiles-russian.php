<?php
return array(
	#��� /addons/admin/modules/smiles.php
	'emotion'=>'������',
	'emotion_'=>'������ � ������. ��������: :-) :-* ;-) . ����� ������� ��������� ����� �������.',
	'path'=>'���� � ������',
	'preview'=>'������������',
	'pos'=>'�������',
	'pos_'=>'�������� ������ ��� ���������� � �����',
	'status'=>'�������',
	'show'=>'���������� � ������',
	'gadd'=>'��������� ����������',
	'fdne'=>'��������� ������� �� ����������.',
	'emoexists'=>function($em){return count($em)>1 ? '������ '.join(', ',$em).' ��� ����������.' : '������ '.join($em).' ��� ����������.';},
	'smnots'=>'�� �� ������� �� ������ ������ ��� ����������.',
	'smnf'=>'� ������ �������� ����� ������ �� ����������.',
	'delc'=>'������������� ��������',
	'list'=>'������ �������',
	'adding'=>'���������� ������',
	'editing'=>'�������������� ������',

	#��� �������
	'NOFILE'=>'����� ������ �� ����������',
	'NOEMO'=>'�� ������ ������',
	'add'=>'�������� �����',
	'smile'=>'�����',
	'no_smiles'=>'������ �� �������',
	'deleting'=>'�� ������������� ������ ������� ����� %s?',
	'selcat'=>'�������� �������',
	'smadded'=>'������ ������� ���������.',
	'smpp'=>'������� �� ��������: %s',
	'addsels'=>'�������� ��������� ������.',
);