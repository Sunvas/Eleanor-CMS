<?php
return array(
	#��� user/groups.php
	'groups'=>'������ �������������',

	#��� user/online.php
	'who_online'=>'��� ������',

	#��� user/guest/index.php
	'cabinet'=>'������ �������',

	#��� user/guest/lostpass.php
	'reminderpass'=>'�������������� ������',
	'wait_pass1'=>'��������� e-mail',
	'new_pass'=>'����� ������ ��� %s',
	'successful'=>'�����',

	#��� user/guest/login.php
	'TEMPORARILY_BLOCKED'=>'� ����� � ������ ������ ������������� ������, ������� ������������!<br />��������� ������� ����� %s  �����(�).',

	#��� user/guest/register.php
	'NAME_TOO_LONG'=>'����� ����� ������������ �� ������ ��������� %s ��������. �� ����� %s ��������.',
	'PASS_TOO_SHORT'=>'����������� ����� ������ %s ��������. �� ����� ������ %s ��������.',
	'form_reg'=>'����� �����������',
	'reg_fin'=>'����������� ���������!',
	'wait_act'=>'�������� ���������',

	#��� user/user/activate.php
	'reactivation'=>'��������� ���������',
	'activate'=>'���������',

	#��� user/user/changeemail.php
	'changing_email'=>'��������� e-mail ������',

	#��� user/user/changepass.php
	'changing_pass'=>'��������� ������',

	#��� user/user/settings.php
	'site'=>'����',
	'site_'=>'������� ����� �����, ������� � http://',
	'lang'=>'����',
	'theme'=>'���� ����������',
	'timezone'=>'������� ����',
	'personal'=>'������',
	'siteopts'=>'��������� �����',
	'by_default'=>'�� ���������',
	'full_name'=>'������ ���',
	'editor'=>'��������',
	'staticip'=>'����������� IP',
	'staticip_'=>'��� ������ ����� �� ����, ���� ������ ����� ������������� � IP.',
	'gender'=>'���',
	'male'=>'�������',
	'female'=>'�������',
	'nogender'=>'�� �����',
	'bio'=>'���������',
	'interests'=>'��������',
	'location'=>'������',
	'location_'=>'��������������: ������, �����',
	'signature'=>'�������',
	'connect'=>'�����',
	'vk'=>'���������',
	'vk_'=>'����������, ������� ������ ���� id, ���� ���',
	'twitter_'=>'����������, ������� ������ ���� ���',
	'settings'=>'��������� �������',

	#��� �������
	'wait_new_act'=>function($h){return'��� ���� �������� ������� ������ � ������������ �� ��������� ������� ������. ����������: ��� ���������� ������������ ���� ������� ������, ���� �� ��������� '.$h.Russian::Plural($h,array(' ����',' �����',' �����')).', ��� ����� �������. ���� � ��� �������� ��������� - ����������, ��������� � ��������������.';},
	'please_activate'=>function($h,$l){return '�� �� ��� ��� �� ������������ ���� ������� ������! �������� ���, ������ ��� ����� '.$h.($h!=11 && $h%10==1  ? ' �����' : ' ����').' ����� ���� ������� ������ ����� ������������� �������. <a href="'.$l.'">�������� ������� ������ ���������.</a>';},
	'group'=>'������',
	'descr'=>'��������',
	'who'=>'���',
	'activity'=>'����������',
	'pl'=>'���',
	'snf'=>'������ �� �������',
	'guest'=>'�����',
	'main'=>'�������',
	'captcha'=>'�������� ���',
	'captcha_'=>'������� ������� � ��������',
	'ENTER_CAPTCHA'=>'����������, ������� �������� ���',
	'WRONG_CAPTCHA'=>'�������� ��� ������ � �������',
	'WRONG_PASSWORD'=>'������������ ������',
	'NOT_FOUND'=>'������������ �� ������',
	'PASSWORD_MISMATCH'=>'������ �� ���������!',
	'EMAIL_EXISTS'=>'��������� e-mail ��� ������������ ������ �������������',
	'NAME_BLOCKED'=>'������ ��� ������������',
	'EMAIL_ERROR'=>'������ ������������ e-mail!',
	'EMAIL_BLOCKED'=>'��������� e-mail ������������',
	'NAME_EXISTS'=>'���� ��� ��� ������������ ������ �������������!',
	'EMPTY_NAME'=>'������� ��� ������������',
	'EMPTY_EMAIL'=>'������� e-mail',
	'reg_off'=>'��������, ����������� ������������� ���������.',
	'external_reg'=>'%s, ��� ����� ������ ����������� �� �����. � ���������� �� ������� ������� �� ���� �����.',
	'name'=>'�����',
	'enter_g_name'=>'������� �������� �����',
	'name_'=>'������ �������� �� �������� A-z, 0-9, �-� ���������� � �����, ������������� ������ ��� ������, ��������� �� ����� 4 ��������',
	'enter_g_email'=>'������� ����� ����������� �����',
	'email_'=>'����������� ����� ����������� ���� � ��������� e-mail �������.',
	'check'=>'���������',
	'pass'=>'������',
	'pass_'=>'������ ����� �� ������� - ����� ������� ����������� ��� �������������. �� ���� ������ ������� - �� ����������� ������������ ������� ������',
	'rpass'=>'��������� ������',
	'do_reg'=>'������������������',
	'success_reg'=>'�� ������� ������������������.',
	'wait_act_text'=>function($h){return'�� ������� ������������������. ������ ��� ���������� �����������, ��� ���������� ������������ ���� ������� ������. ������ ��� ��������� �������� ���� ������� �� �������� ���� e-mail - ��� ����� ������ ������� �� ���. ������ ������������� � ������� '.$h.Russian::Plural($h,array(' ����',' �����',' �����')).' �����.';},
	'wait_act_admin'=>'�� ������� ������������������. ��� ������������� ������� ������ �������� ������ ��������� ���������������.',
	'wait_pass1_text'=>'���������� ��� ���������� ������� ��� �� e-mail.',
	'EMPTY_FIELDS'=>'�� ������ �� ���������!',
	'ACCOUNT_NOT_FOUND'=>'������� ������ � ������ ������� �� �������',
	'notnoem'=>'���� �� �� ������� �� ������ �� e-mail, � ���� �� �����, ��� �� ��� ����������������? - ����������������� ��� ���.',
	'enterna'=>'������� ��� �����',
	'enterem'=>'������� ��� e-mail',
	'fogotname'=>'������ �����?',
	'fogotemail'=>'��������� �����?',
	'ent_newp'=>'������� ����� ������',
	'rep_newp'=>'��������� ����� ������',
	'new_pass_sent'=>'����� ������ ������ ��� �� e-mail.',
	'pass_changed'=>'��� ������ ������� �������!',
	'ractletter'=>'�������� ������� ������ ���������',
	'activation_ok'=>'���� ������� ������ ������� ������������!',
	'activation_err'=>'������� ������ �� ������������. ��������, �� ������� �� ���������������� ������.',
	'EMAIL_BROKEN_LINK'=>'E-mail �� ����� ���� �������. ��������, �� ������� �� ���������������� ������',
	'EMAIL_YOURS'=>'�� ����� ���� ������� e-mail',
	'change_email'=>'������� e-mail',
	'curr_email'=>'��� ������� e-mail',
	'new_email'=>'����� e-mail',
	'continue'=>'����������',
	'email_changed'=>'��� �-mail ������� �������!',
	'wait_change1'=>'��� ��������� ������ e-mail, �� ������ e-mail ��� ���������� �������������. ����������, ��������� �����.',
	'wait_change2'=>'��� ������������� ��������� ������ e-mail, ��� ���������� ������������� � �� ����. ����������, ��������� �����.',
	'email_success'=>'��� e-mail ������� �������!',
	'change_pass'=>'������� ������',
	'WRONG_OLD_PASSWORD'=>'������ ������ ��������!',
	'your_curr_pass'=>'��� ������� ������',
	'en_ycp'=>'������� ��� ������� ������',
	'new_pass_me'=>'����� ������',
	'externals'=>'������� �������',
	'optssaved'=>'��������� ������� ���������',
	'SITE_ERROR'=>'����� ����� ������ �����������!',
	'AVATAR_NOT_EXISTS'=>'���������� ������� �� ����������',
	'SHORT_ICQ'=>'����� ICQ ������ ��������� ��� ������� 5 ����',
	'avatar'=>'������',
	'alocation'=>'����������',
	'apersonal'=>'���������',
	'agallery'=>'�� �������',
	'amanage'=>'����������',
	'gallery_select'=>'�������',
	'noavatar'=>'��� �������',
	'nickname'=>'���',
	'registered'=>'�����������������',
	'last_visit'=>'��������� �����',
	'maingroup'=>'�������� ������',
	'othgroups'=>'��������� ������',
	'no_avatars'=>'��������� �������� ���',
	'cancel_avatar'=>'��������',
	'togals'=>'� ��������',
	'aexternal'=>'������� ��������� ������ � %s.',
	'add'=>'��������',
	'datee'=>'���� ���������',
	'csnd'=>'������� ������ ������ �������',
	'sessions'=>'�������� ������',

	'twitter.com'=>'Twitter',
	'www.facebook.com'=>'Facebook',
	'openid.yandex.ru/server'=>'������',
	'vkontakte.ru'=>'VK',
);