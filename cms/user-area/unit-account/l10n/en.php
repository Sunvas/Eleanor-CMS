<?php
return[
	'title'=>'User area',

	# Menu
	'overview'=>'Overview',
	'settings'=>'Settings',
	'sessions'=>'Sessions',
	'sign-in-history'=>'Sessions',

	# Sign-in form
	'user-sign-in'=>'Signing in for user',
	'use-widget'=>'To log in to your account, use the form on the left',
	'remember-me'=>'Remember me',
	'sign-in'=>'Sign in',

	# Overview
	'username'=>'Username',
	'groups'=>'Groups',
	'registered'=>'Registered',
	'last_login_attempt'=>'Last login attempt',
	'password_changed_at'=>'Password changed',
	'change-password'=>'Change password',
	'totp'=>'Onetime passcodes (TOTP)',
	'recovery-codes'=>'Recovery codes',
	'state'=>'Current state',

	'verification'=>'Verification',
	'verification_'=>'Current password / OTP / recovery code',
	'grace-period'=>'☝️ Pay attention',
	'grace-period_'=>'Changing your password, TOTP, and recovery codes is available without confirmation for <b v-text="recovery_grace_timer"></b>.',

	'totp-disabled'=>'Disabled',
	'totp-enabled'=>'Configured',
	'enable'=>'Enable',
	'disable'=>'Disable',
	'change'=>'Change',
	'totp-hint'=>'Can be changed for display convenience or for secrecy',
	'totp-issuer'=>'Issuer',
	'totp-digits'=>'Number of digits',
	'totp-digits-6'=>'6 - sufficient',
	'totp-digits-7'=>'7 - enhanced',
	'totp-digits-8'=>'8 - paranoid',
	'totp-qr'=>'Scan the QR code via <em>Aegis</em> or <em>Google Authenticator</em>',
	'totp-code'=>'Enter the code from the app',
	'totp-delete'=>'Disable onetime passcodes',

	'rc-empty'=>'Empty',
	'rc-generate'=>'Generate',
	'rc-created-at'=>'Generated at',
	'rc-last-used-at'=>'Last used at',
	'rc-codes'=>'Please keep these codes: they will be needed to restore access.<br><br>Each code (12 characters per line) is case-insensitive and can only be used once.',
	'rc-check'=>'Check recovery code',
	'check'=>'Check',

	'save'=>'Save',
	'loading'=>'Loading&hellip;',

	# Settings
	'avatar'=>'Avatar',
	'l10n'=>'Localization by default',
	'timezone'=>'Timezone',
	'info'=>'Information about yourself',
	'default'=>'By default',

	'browser'=>'Browser',
	'way'=>'Way',
	'created'=>'Created',
	'used'=>'Activity',
	'terminate'=>'Terminate',
	'sessions-info%'=>'For safety, you cannot terminate earlier sessions, except those that have not been used for more than %d months.',

	'signing-up'=>'User registration',
	'username_'=>'Is used for identification and sign in',
	'password'=>'Password',
	'password2'=>'Repeat password',
	'password_'=>'Must be minimum 10 characters long',
	'display_name'=>'Display name',
	'display_name_'=>'If left blank, the username will be used',
	'register'=>'Register',

	'signed-out'=>'You have signed out',
	'signed-out_'=>'Now you\'ll be transferred to main page&hellip;',

	'EXISTS'=>'Registration completed successfully. You have been authorized on the site.',
	'USERS_LIMIT'=>'Limit of users is reached',
];