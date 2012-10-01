<?php
return array(
	#For control uploading file /core/controls/uploadfile.php
	'path_to_save'=>'Path to save the file on the server',
	'path_to_save_'=>'Specify the path relative to the directory /'.Eleanor::$uploads,
	'no_upload_path'=>'The specified path to save the file does not exist or is not writable!',
	'file_types'=>'File types allowed for uploading',
	'file_types_'=>'Separated by commas. For example: jpg,png,bmp',
	'filename'=>'The code to create the file name',
	'filename_'=>'Incoming variables: $options,$Obj',
	'error_eval'=>'You made a mistake in the code.',
	'uploaded_file'=>'Uploaded file: %s',
	'writed_file'=>'Input file: %s',
	'allowed_types'=>'File types allowed for uploading: %s',
	'max_size'=>'Maximum file size: %s',
	'max_size_f'=>'Maximum file size',
	'max_size_fd'=>'In bytes. Also allowed recording: 30 kb, 5 mb, 1 gb',
	'error_ext'=>'You can upload only the following types: %s',
	'write'=>'Set the path to the file',
	'upload'=>'Upload file',
	'delete'=>'Delete',
);