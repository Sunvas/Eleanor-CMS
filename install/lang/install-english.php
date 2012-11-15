<?php
return array(
	'error'=>'Error',
	'low_php'=>'For the system correct system you must have at least PHP version 5.2.0, you have %s.',
	'low_mysql'=>'For the system correct work you must have at least MySQL version 5.0.18.',
	'install.lock'=>'Set locked file install.lock. Delete this file and try again.',
	'GD'=>'GD2 is required for correct working!',
	'MB'=>'Multibyte String is required for correct working!',
	'no_db_driver'=>'Database management systems are not detected!',
	'must_writeable'=>'The following folders and files should be available to record:<br />',
	'must_ex'=>'The following folders and files are not available:<br />',
	'err_email'=>'E-mail entered with an error!',
	'welcome'=>'Welcome! Preparing to install Eleanor CMS',
	'lang_select'=>'Selecting language',
	'install'=>'Install',
	'update'=>'Update',
	'license'=>'License agreement',
	'read_careful'=>'Read carefully!',
	'get_data'=>'Data collection',
	'already_to_install'=>'Everything is prepared for installation',
	'installing'=>'Installing...',
	'create_admin'=>'Administrator account creation',
	'do_create_admin'=>'Create',
	'finish'=>'Finishing of the installation',
	'i_am_agree_lic'=>'I accept the license agreement',
	'next'=>'Next &raquo;',
	'you_must_lagree'=>'You must accept the license agreement!',
	'sanctions'=>'Sanctions',
	'i_am_agree_sanc'=>'I accept the terms of the sanctions',
	'you_must_sagree'=>'You must accept the terms of the sanctions!',
	'print'=>'Printable version',
	'db_host'=>'Database server',
	'db_name'=>'Database name',
	'db_user'=>'User',
	'db_pass'=>'Password',
	'db_pref'=>'Table prefix',
	'db_prefinfo'=>'Attention! Use the unique table prefix, since all the tables will be overwritten during a new installation!',
	'sitename'=>'Site name',
	'email'=>'Basic e-mail',
	'install_me'=>'Install',
	'back'=>'&laquo; Back',
	'error_cont'=>'Content error!',
	'press_here'=>'Click here if you haven\'t been moved automatically',
	'creating_tables'=>'Creating of tables...',
	'inserting_v'=>'Record of values...',
	'a_name'=>'Login',
	'a_rpass'=>'Retype password',
	'a_email'=>'E-mail',
	'pass_mismatch'=>'Passwords do not match!',
	'install_finished'=>'Setup completed successfully!',
	'inst_fin_text'=>'Your copy of Eleanor CMS has been successfully installed and prepared to work! The installation script is blocked by file install/install.lock, so if you want to install the system again - you must manually delete the file. We strongly recommend you to remove the install folder and all its contents for security reasons.',
	'links'=>'<a href="%s">Back to the main page of your site</a><br /><a href="%s">Go to admin panel</a>',
	'srequirements'=>'System requirements',
	'skip'=>'Skipping...',
	'parametr'=>'Parameter',
	'value'=>'Value',
	'status'=>'Status',
	'unknown'=>'Unknown',
	'mysqlver'=>'For system to work correctly, MySQL is required and it should be no lower than 5.0.18.<br />Unfortunately, without connection, it is impossible to check MySQL version.<br />Please address to your hoster for additional information regarding this.',
	'php_version'=>'<b>PHP version:</b><br /><span class="small">PHP version must be not lower than 5.2.0</span>',
	'en_magic_q'=>'<b>Magic quotes</b><br /><span class="small">For the system correct work magic quotes (magic_quotes) should be disabled!</span>',
	'php_mbstring'=>'<b>Availability of the PHP MBstring library</b><br /><span style="font-size:10px">Multibyte String is required for correct working system</span>',
	'php_gd'=>'<b>Availability of the library GD</b><br /><span class="small">Image Processing is required for correct working</span>',
	'php_safemode'=>'<b>PHP safe mode</b><br /><span class="small">For the correct system work it is recommended to disable the PHP safe mode.</span>',
	'db_drivers'=>'<b>Database drivers</b><br /><span class="small">Database driver is required for correct working</span>',
	'php_ioncube'=>'<b>ionCube Loader</b><br /><span class="small">For working, Eleanor CMS need extension <a href="http://www.ioncube.com/loaders.php" target="_blank">ionCube Loader</a>. Without it, settings in the admin panel will work in a restricted mode.</span>',
	'php_dom'=>'<b>DOM Functions</b><br /><span class="small">To import and export settings Eleanor CMS, requires DOM Functions. Without them, these steps are <b>impossible</b>.</span>',
	'mod_rewrite'=>'<b>Apache mod_rewrite</b><br /><span class="small">For working <abbr title="Friendly URL">FURL</abbr> mod_rewrite is needed. Without it working <abbr title="Friendly URL">FURL</abbr> impossible!</span>',
	'not_find'=>'Not detected',
	'inst_err_text'=>'It was not possible to rename automatically config_general.bak in config_general.php. Please, make it manually. Only after that you can pass under references more low.',
	'sysever'=>'System version: ',
	'furl'=>'Enable SEF URL?',
	'yes'=>'Yes',
	'no'=>'No',
	'addl'=>'Additional languages',
	'addl_'=>'Choose languages, which will be used IN ADDITION at your site',
	'db'=>'Datebase',
	'gen_data'=>'Main site data',
	'timezone'=>'Time zone',
	'sethomepage'=>'Make a site Eleanor CMS home page',
	'addfavourite'=>'Add site Eleanor CMS to favourites',
	'EMPTY_NAME'=>'Login field blank!',
	'EMPTY_PASSWORD'=>'Password field blank!',
	'EMAIL_ERROR'=>'E-mail address entered incorrectly!',
	'NAME_EXISTS'=>'This user name already exists!',
	'NAME_TOO_LONG'=>function($l,$e){ return'User name length should not exceed '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
	'PASS_TOO_SHORT'=>function($l,$e){ return'The password should be at least '.$l.' character'.($l>1 ? 's' : '').'. Your - '.$e.' character'.($e>1 ? 's' : '').'.'; },
);