<?php
return array(
	#For Classes/Settings.php
	'GROUP_EXISTS'=>'Group with the internal name already exists!',
	'OPTION_EXISTS'=>'In this group already exists setting with this internal name!',
	'make_o_def_c'=>'Do you really want to install the current group of &quot;%s&quot; settings by default?',
	'olist'=>'Settings list',
	'addg'=>'Add group',
	'addo'=>'Add the setting',
	'options'=>function($n){ return$n.($n>1 ? ' settings' : ' setting'); },
	'up'=>'Move up',
	'down'=>'Move down',
	'reset_def_gr'=>'Replace the current group settings by default',
	'make_def_gr'=>'Make current group settings by default',
	'ops_wo_g_d'=>'Typically, this &quot;group&quot; is empty, but if you place any bugs, look for the missing settings here.',
	'find'=>'Find',
	'ays_to_rg'=>'Are you sure you want to reset the settings for the group &quot;%s&quot;?',
	'group'=>'Group: ',
	'reset_opt'=>'Show the importance of default',
	'default_opt'=>'Make the current value of the value of default',
	'ex_with_ex'=>'On import:',
	'ex_ignore'=>'Ignore',
	'ex_update'=>'Update partially (all, without value)',
	'ex_full'=>'Update fully',
	'ex_delete'=>'Delete',
	'do_export'=>'Export',
	'select_file_im'=>'Select a file to the settings:',
	'do_import'=>'Import',
	'deleting_g'=>'Are you sure you want to delete a group setting &quot;%s&quot;?',
	'deleting_o'=>'Are you sure you want to delete a setting &quot;%s&quot;?',
	'pos'=>'Position',
	'pos_'=>'Leave blank to add it to the end',
	'keyw_g'=>'Keyword',
	'priv_name'=>'Internal name',
	'prot_g'=>'Secure group?',
	'beg_subg'=>'Start a subgroup of settings',
	'prot_o'=>'Protected setting?',
	'multilang'=>'Configuring multilanguage?',
	'multilang_'=>'When the option setting will be multilingual within the chosen language.',
	'eval_load'=>'Processing before editing',
	'inc_vars'=>'Incoming variables: %s',
	'op_example'=>'Example of use',
	'eval_save'=>'Processing while maintaining',
	'edit_control'=>'Control management',
	'evals'=>'Processing values',
	'error'=>'Error',
	'import_result'=>function($gd=0,$od=0,$ag=0,$ug=0,$ao=0,$uo=0)
	{
		$s=rtrim(($gd>0 ? $gd.($gd>1 ? ' groups' : ' group').' deleted, ' : '')
			.($od>0 ? $od.($od>1 ? ' options' : ' option').' deleted, ' : '')
			.($ag>0 ? $ag.($ag>1 ? ' groups' : ' group').' added, ' : '')
			.($ug>0 ? $ug.($ug>1 ? ' groups' : ' group').' updated, ' : '')
			.($ao>0 ? $ao.($ao>1 ? ' options' : ' option').' added, ' : '')
			.($uo>0 ? $uo.($uo>1 ? ' options' : ' option').' updated' : ''),', ');
		return $s ? $s : 'Nothing was imported';
	},
);