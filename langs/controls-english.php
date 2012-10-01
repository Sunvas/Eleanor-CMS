<?php
if(!defined('CMS'))die;
$m=Eleanor::$vars['multilang'] ? ' (%s)' : '';
return array(
	#For /core/others/controls_manager.php
	'edit'=>'Single-line text field',
	'text'=>'Multiline textbox',
	'select'=>'One-line single select',
	'item'=>'Multiline single select',
	'items'=>'Multi-line multiple select',
	'check'=>'Flag',
	'user'=>'Users',
	'editor'=>'Text editor',
	'uploadfile'=>'Upload file',
	'uploadimage'=>'Upload image',
	'checks'=>'Multiple select (flags)',
	'input'=>'Form element',
	'date'=>'Date select',

	'type_not_found'=>'This type of control is not supported!',
	'incoming_vars'=>'Incoming variables: %s',
	'editor_default'=>'By default',
	'addon_tag_params'=>'Additional parameters in the tag',
	'addon_tag_params_'=>'For examlpe: onclick="alert(\'Click!\')"',
	'user_load_eval'=>'The code responsible for displaying the control',
	'user_save_eval'=>'Code responsible for maintaining control',
	'select_source'=>'Source values',
	'editor_type'=>'Editor type',
	'time_select'=>'Timing',
	't_color'=>'Color selection',
	't_date'=>'Calendar date',
	't_datetime'=>'Date and time',
	't_datetime-local'=>'Local date and time',
	't_email'=>'E-mail address',
	't_number'=>'Numbers',
	't_range'=>'Slider to select the numbers in the specified range',
	't_tel'=>'Phone numbers',
	't_time'=>'Timing',
	't_url'=>'Web addresses',
	't_month'=>'Selecting month',
	't_week'=>'Selecting week',
	'no_load_eval'=>'You have not entered the code responsible for displaying the control.'.$m,
	'error_load_eval'=>'You made a mistake in the code responsible for displaying the control.'.$m,
	'no_save_eval'=>'You have not entered the code responsible for maintaining control.'.$m,
	'error_save_eval'=>'You made a mistake in the code responsible for maintaining control.'.$m,

	#For template EditControlTable
	'control_type'=>'Type of control',
	'preview'=>'Preview',
	'preview_'=>'State control will be retained as its default state',

	#For template SettingsSelectLoad
	'select_source_code'=>'Code',
	'select_source_input'=>'Specified list',
	'select_value1'=>'Displaing',
	'select_value'=>'Value',
);