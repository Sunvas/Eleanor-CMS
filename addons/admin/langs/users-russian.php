<?php
return array(
	#��� /addons/admin/modules/users.php
	'personal'=>'������',
	'gender'=>'���',
	'nogender'=>'����������',
	'male'=>'�������',
	'female'=>'�������',
	'bio'=>'���������',
	'interests'=>'��������',
	'location'=>'��������������',
	'site'=>'����',
	'site_'=>'������� ����� �����, ������� � http://',
	'signature'=>'�������',
	'connect'=>'�����',
	'vk'=>'���������',
	'vk_'=>'���������� ������� ������ ���� id, ���� ���',
	'twitter_'=>'����������, ������� ������ ���',
	'theme'=>'������',
	'by_default'=>'�� ���������',
	'editor'=>'��������',
	'lettertitle'=>'���� ������',
	'letterdescr'=>'����� ������',
	'letter4new'=>'������ ��� �������� ������ ������������',
	'descr4new'=>'{site} - �������� �����<br />
{name} - ��� ������������<br />
{login} - ����� ������������<br />
{pass} - ������ ������������<br />
{link} - ������ �� ��� ����',
	'letter4name'=>'������ ��� ��������� ����� ������������',
	'descr4name'=>'{site} - �������� �����<br />
{name} - ��� ������������<br />
{oldlogin} - ������ ����� ������������<br />
{newlogin} - ����� ����� ������������<br />
{link} - ������ �� ��� ����',
	'letter4pass'=>'������ ��� ��������� ������ ������������',
	'letters'=>'������� �����',
	'whoonline'=>'��� ������',
	'delc'=>'������������� ��������',
	'adding'=>'�������� ������������',
	'editing'=>'�������������� ������������',
	'list'=>'������ �������������',

	#Errors
	'NAME_TOO_LONG'=>function($l,$e){ return'����� ����� ������������ �� ������ ��������� '.$l.Russian::Plural($l,array(' ������',' �������',' ��������')).' ��������. �� ����� '.$e.Russian::Plural($e,array(' ������',' �������',' ��������')).' ��������.'; },
	'PASS_TOO_SHORT'=>function($l,$e){ return'����������� ����� ������ '.$l.Russian::Plural($l,array(' ������',' �������',' ��������')).' ��������. �� ����� ������ '.$e.Russian::Plural($e,array(' ������',' �������',' ��������')).' ��������.'; },
);