<?php
return array(
	#For /addons/admin/modules/tasks.php
	'handler'=>'Task',
	'name'=>'Name',
	'runyear'=>'Years',
	'runyear_'=>'Specify the years in which to run the task. Enter * to run each year. For example: '.date('Y').','.date('Y',strtotime('+1year')),
	'runmonth'=>'Months',
	'runmonth_'=>'Specify the months in which to start the task. Enter * to run each month. For example: '.date('m').','.date('m',strtotime('+1month')),
	'runday'=>'Days',
	'runday_'=>'Specify the days of the month in which to start the task. Enter * to run every day. For example: '.date('d').','.date('d',strtotime('+7day')),
	'runhour'=>'Hours',
	'runhour_'=>'Specify the hours that you need to run the task. Enter * to run every hour. For example: '.date('H').','.date('H',strtotime('+2hour')),
	'runminute'=>'Minutes',
	'runminute_'=>'Specify the minutes when you need to run the task. Enter * to run every minute. For example: '.date('i').','.date('i',strtotime('+1min')),
	'runsecond'=>'Seconds',
	'runsecond_'=>'Specify the seconds, that need to run the task. Enter * to run every second. For example: 14,12',
	'maxrun'=>'The maximum number of run of the problem',
	'alreadyrun'=>'Current number of runs',
	'ondone'=>'After reaching the limit',
	'ondone_'=>'What to do with the task after reaching the limit of run.',
	'deactivate'=>'Deactivate',
	'delete'=>'Delete',
	'status'=>'Activate',
	'delc'=>'Confirm deleting',
	'list'=>'Task list',
	'adding'=>'Adding task',
	'editing'=>'Editing task',
);