<?php
return[
	'version'=>'Version: ',
	'error'=>'Error',
	'progress'=>'Progress bar',

	'installation_impossible'=>'Installation is not possible',
	'problems'=>'The following problems were identified',
	'LOCKED'=>'Installer is blocked by file <code>install/install.lock</code>.<br>You should remove this file and reload the page.',
	'LOW_PHP_VERSION'=>sprintf('The system requires PHP version 8.4 or higher. You have %s.',PHP_VERSION),
	'MYSQLI_MISSED'=>'PHP MySQLi module in unavailable.',
	'NOT_WRITABLE'=>'The following folders and files should be available for writing:<br>',
	'NOT_EXIST'=>'The following folders and files do not exist:<br>',

	'license'=>'License agreement',
	'read_careful'=>'Read carefully',
	'i_agree'=>'👍 I agree',
	'back'=>'⬅️ Back',

	'config'=>'Configuration',
	'fill'=>'Please fill in all fields',
	'db'=>'DateBase',
	'db_host'=>'DB host',
	'db_name'=>'DB name',
	'db_user'=>'DB User',
	'db_pass'=>'DB Password',
	'db-info'=>'Allocate a separate database (the tables will be truncated!).',
	'settings'=>'Settings of the site',
	'site-name'=>'Site name',
	'description'=>'Description',

	'multilang'=>'Multi-language support',
	'multi'=>'Enable <span style="color:darkred">(cannot be changed after installation)</span>',
	'add-l10n'=>'Add Russian language',
	'hcaptcha'=>'<a href="https://hCaptcha.com/?r=2b68096cb450" target="_blank" title="Obtain" rel="nofollow">hCaptcha 🔗</a> key',
	'hsecret'=>'hCaptcha secret',
	'bot_name'=>'Telegram BOT username',
	'bot_key'=>'Telegram BOT Api Token',
	'administrator'=>'Administrator account',
	'username'=>'👤 Username ',
	'p1'=>'Enter password',
	'p2'=>'Repeat password',
	'PASS_MISMATCH'=>'Passwords don\'t match',
	'MYSQL_CONNECT'=>'Unable to connect to the MySQL server',
	'MYSQL_LOW'=>'👎 The version of MySQL server is required to be at least <b>8.0</b>',
	'install'=>'📦 Install',

	'installing'=>'Installing&hellip;',
	'creating'=>'Creating tables&hellip;',
	'inserting'=>'Writing rows&hellip;',
	'update'=>'Refresh the page in case it hasn\'t happened automatically',
	'queries_error'=>'Errors occurred during the execution of the queries. It is not possible to continue the installation. Click on the errors for details.',

	'finish'=>'Finishing the installation',
	'finished'=>'Installation successfully completed',
	'finish_text'=><<<HTML
<p>Don't forget to enable <a href="https://en.wikipedia.org/wiki/Clean_URL" target="_blank" rel="nofollow">Clean URL</a> processing on the server (when non-existent URLs are passed to index.php for processing). This can be done from the hosting panel or in the nginx configuration files &ndash; an example configuration is available in the <code>cms/library/classes/uri.php [22-33]</code> file.</p>
<p>The installer is blocked by the <code>install/install.lock</code> file, so if you need to install the system again, you should remove it. It is recommended to delete the <code>install</code> directory with all its contents and rename the <code>dashboard.php</code> file.</p>
HTML,
	'index'=>'Go to the main page',
	'dashboard'=>'Go to the dashboard',
];