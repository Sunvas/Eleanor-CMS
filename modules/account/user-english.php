<?php
return array(
	#For user/groups.php
	'groups'=>'Users groups',

	#For user/online.php
	'who_online'=>'Who online',

	#For user/guest/index.php
	'cabinet'=>'Personal cabinet',

	#For user/guest/lostpass.php
	'reminderpass'=>'Password recovery',
	'wait_pass1'=>'Check e-mail',
	'new_pass'=>'The new password for %s',
	'successful'=>'Successful',

	#For user/guest/login.php
	'TEMPORARILY_BLOCKED'=>'Due to frequent entering an incorrect password, your account blocked!<br />Please try again in %s minute (s).',

	#For user/guest/register.php
	'NAME_TOO_LONG'=>function($l,$e){ return'User name length should not exceed '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
	'PASS_TOO_SHORT'=>function($l,$e){ return'The password should be at least '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
	'form_reg'=>'Registration form',
	'reg_fin'=>'Registration complete!',
	'wait_act'=>'Waiting for activation',

	#For user/user/activate.php
	'reactivation'=>'Reactivating',
	'activate'=>'Activation',

	#For user/user/changepass.php
	'changing_email'=>'Changing e-mail address',

	#For user/user/changepass.php
	'changing_pass'=>'Change password',

	#For user/user/externals.php
	'externals'=>'External services',

	#For user/user/settings.php
	'site'=>'Website',
	'site_'=>'Enter a website address, beginning with http://',
	'lang'=>'Language',
	'theme'=>'Template',
	'timezone'=>'Timezone',
	'personal'=>'Personal',
	'siteopts'=>'Site options',
	'by_default'=>'Default',
	'full_name'=>'Full name',
	'editor'=>'Editor',
	'staticip'=>'Static IP',
	'staticip_'=>'Each entrance to the site, your session will be tied to IP.',
	'gender'=>'Gender',
	'male'=>'Male',
	'female'=>'Female',
	'nogender'=>'I would not say',
	'bio'=>'Biography',
	'interests'=>'Interests',
	'location'=>'Location',
	'location_'=>'City, country',
	'signature'=>'Signature',
	'connect'=>'Connect',
	'vk'=>'VK',
	'vk_'=>'Please enter only your id, or name',
	'twitter_'=>'Please enter your nickname only',
	'settings'=>'Profile setup',
);