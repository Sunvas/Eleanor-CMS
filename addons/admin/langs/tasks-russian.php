<?php
return array(
	#��� /addons/admin/modules/tasks.php
	'handler'=>'������',
	'name'=>'��������',
	'runyear'=>'����',
	'runyear_'=>'������� ����, � ������� ���������� ��������� ������. ������� * ��� ������� ������ ���. ��������: '.date('Y').','.date('Y',strtotime('+1year')),
	'runmonth'=>'������',
	'runmonth_'=>'������� ������, � ������� ���������� ��������� ������. ������� * ��� ������� ������ �����. ��������: '.date('m').','.date('m',strtotime('+1month')),
	'runday'=>'���',
	'runday_'=>'������� ��� ������, � ������� ���������� ��������� ������. ������� * ��� ������� ������ ����. ��������: '.date('d').','.date('d',strtotime('+7day')),
	'runhour'=>'����',
	'runhour_'=>'������� ����, � ������� ���������� ��������� ������. ������� * ��� ������� ������ ���. ��������: '.date('H').','.date('H',strtotime('+2hour')),
	'runminute'=>'������',
	'runminute_'=>'������� ������, � ������� ���������� ��������� ������. ������� * ��� ������� ������ ������. ��������: '.date('i').','.date('i',strtotime('+1min')),
	'runsecond'=>'�������',
	'runsecond_'=>'������� �������, � ������� ���������� ��������� ������. ������� * ��� ������� ������ �������. ��������: 14,12',
	'maxrun'=>'������������ ����� �������� ������',
	'alreadyrun'=>'������� ����� ��������',
	'ondone'=>'����� ���������� �������',
	'ondone_'=>'��� ������� � ������� ����� ���������� ������� �������.',
	'deactivate'=>'��������������',
	'delete'=>'�������',
	'status'=>'������������',
	'delc'=>'������������� ��������',
	'list'=>'������ �����',
	'adding'=>'���������� ������',
	'editing'=>'�������������� ������',

	#��� �������
	'NO_NEXT_RUN'=>'��� �������� ������� �������, ������ ������� �� ����������!',
	'add'=>'�������� ������',
	'nextrun'=>'��������� ������',
	'now'=>'������',
	'deleting'=>'�� ������������� ������ ������� ������ &quot;%s&quot;',
	'notasks'=>'������ �� �������',
	'tpp'=>'����� �� ��������: %s',
);