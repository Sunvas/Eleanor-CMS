<?php
return[
	'version'=>'Version: ',
	'error'=>'Error',
	'progress'=>'Progress bar',

	'installation_impossible'=>'Installation is not possible',
	'problems'=>'The following problems were identified',
	'LOCKED'=>'Installer is blocked by file <code>install/install.lock</code>.<br>You should remove this file and reload the page.',
	'LOW_PHP_VERSION'=>sprintf('The system requires PHP version <span style="color:darkblue">%s</span> or higher. You have <span style="color:darkred">%s</span>.',\CMS\REQUIRED_PHP_VERSION,\PHP_VERSION),
	'MYSQLI_MISSED'=>'PHP MySQLi module in unavailable.',
	'NOT_WRITABLE'=>'The following folders and files should be available for writing:<br>',
	'NOT_EXIST'=>'The following folders and files do not exist:<br>',

	'license'=>'License agreement',
	'read_careful'=>'Read carefully',
	'i_agree'=>'👍 I agree',
	'back'=>'⬅️ Back',

	'config'=>'Configuration',
	'fill'=>'Please fill in all fields',
	'db'=>'MySQL DateBase',
	'db_host'=>'MySQL DB host',
	'db_host_'=>'If you prepend host by <code>p:</code>, a persistent connection will be used',
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
	'administrator'=>'Administrator account',
	'username'=>'👤 Username ',
	'p1'=>'Enter password',
	'p2'=>'Repeat password',
	'MYSQL_CONNECT'=>'Unable to connect to the MySQL server',
	'MYSQL_LOW'=>'👎 The version of MySQL server is required to be at least <b>8.0</b>',
	'install'=>'📦 Install',

	'installing'=>'Installing&hellip;',
	'creating'=>'Creating tables&hellip;',
	'inserting'=>'Writing rows&hellip;',
	'config_files'=>'Writing config files&hellip;',
	'update'=>'Refresh the page in case it hasn\'t happened automatically',
	'queries_error'=>'Errors occurred during the execution of the queries. It is not possible to continue the installation. Click on the errors for details.',

	'finish'=>'Finishing the installation',
	'finished'=>'Installation successfully completed',
	'finish_text'=><<<HTML
<p>Enable <a href="https://en.wikipedia.org/wiki/Clean_URL" target="_blank" rel="nofollow">Clean URLs</a> by routing non-existent requests to <code>index.php</code>. This can usually be configured in the hosting control panel or web server configuration. An nginx example is available in <code>cms/library/classes/uri.php</code>, lines 12&ndash;23.</p>
<p>The installer is now locked by <code>install/install.lock</code>. Delete this file only before reinstalling the system.</p>
<p>Delete the <code>install</code> directory together with all its contents. For additional protection, rename <code>admin.php</code> to a non-obvious filename.</p>
HTML,
	'user-area'=>'Go to the user area',
	'admin-panel'=>'Go to the admin panel',
];