<?php
return array(
	'reg_t'=>'Register on site {site}',
	'reg_fin'=>'Hellow, {name}!<br /><br />
You have successfully registered on the site {site}.<br />
Login: <b>{login}</b><br />
Pass: <b>{pass}</b><br /><br />

Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',
	'reg_act'=>'Hellow, {name}!<br /><br />
You are registered on the site {site}.<br />
Login: <b>{login}</b><br />
[pass]Pass: <b>{pass}</b><br />[/pass]<br />

<b>To complete registration, you must click on the link <a href="{confirm}">{confirm}</a></b>.<br />
If you do not complete the registration, within {hours} hours, your account will be deleted.<br /><br />

Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',

	'reg_act_admin'=>'Hellow, {name}!<br /><br />
You are registered on the site {site}.<br />
Login: <b>{login}</b><br />
[pass]Pass: <b>{pass}</b><br />[/pass]<br />

Your account has not yet been activated. Account activation proivoditsya administrator manually. Expect.<br /><br />

Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',

	'act_t'=>'Account activation site {site}',
	'act_success'=>'Hellow, {name}!<br /><br />

Your account has been activated successfully. <a href="{link}">Go to site</a><br />
<br />
Best regards,<br />
{site} team.',
	'act_refused'=>'Hellow, {name}!<br /><br />

Your account has been activated. Moreover, it was removed.[reason]Reason:<br />
{reason}[/reason]
<a href="{link}">Go to site</a><br />
<br />
Best regards,<br />
{site} team.',

	'passrem_t'=>'Password recovery on site {site}',
	'passrem'=>'Hellow, {name}!<br /><br />
You asked for password recovery. To continue, please click on the link: <a href="{confirm}">{confirm}</a>.<br /><br />

Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',

	'passremfin_t'=>'Your new password to access the site {site}',
	'passremfin'=>'Hellow, {name}!<br /><br />
Your new password to access the site {site}:<br />
<b>{pass}</b><br />

Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',

	'newemail_t'=>'Change the contact e-mail site {site}',
	'newemail_old'=>'Hellow, {name}!<br /><br />
Your current e-mail ({oldemail}) will be replaced by the new: {newemail}. For confirmation, please go to <a href="{confirm}">{confirm}</a>.
Once confirmed, you will need to confirm this operation again with a new e-mail.<br /><br />

If you do not want to change your e-mail, just ignore this letter.<br /><br />

Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',
	'newemail_new'=>'Hellow, {name}!<br /><br />
Your old e-mail ({oldemail}) will be replaced by the current: {newemail}. For confirmation, please go to <a href="{confirm}">{confirm}</a>.<br /><br />

If you do not want to change your e-mail, just ignore this letter.<br />
<br />
Site adress: <b><a href="{link}">{link}</a></b><br />
<br />
Best regards,<br />
{site} team.',
);