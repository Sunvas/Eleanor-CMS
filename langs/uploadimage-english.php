<?php
if(!defined('CMS'))die;
return array(
	#For control uploading image /core/controls/uploadimage.php
	'path_to_save'=>'Directory to save the file on the server',
	'path_to_save_'=>'Specify the path relative to the directory /'.Eleanor::$uploads,
	'file_types'=>'File types allowed for uploading',
	'file_types_'=>'Separated by commas. For example: jpg,png,bmp',
	'max_size_f'=>'Maximum file size',
	'max_size_f_'=>'In bytes. Also allowed recording: 30 kb, 5 mb, 1 gb',
	'filename'=>'The code to create the file name',
	'filename_'=>'Incoming variables: $options,$Obj',
	'error_eval'=>'You made a mistake in the code.',
	'maximsize'=>'Maximum image size',
	'maximsize_'=>'Given in the format width[blank]height. For example: 300 200. For the removal of restrictions on any of the parties, specify 0.',
	'nosmaller'=>'Prohibit downloading of images of smaller',
	'nosmaller_'=>'Downloading images will be banned if you are loading the image height or width less than the specified',
	'onmaxupload'=>'When you upload an image larger in size',
	'onmaxupload_'=>'Choose the action that will be applied to uploaded images, if it is bigger than',
	'disable_upload'=>'Disable upload',
	'bybigger'=>'By bigger side',
	'bysmaller'=>'By smaller side (with trim bigger)',
	'bywidth'=>'By width',
	'byheight'=>'By height',
	'source'=>'Source',
	'must1t'=>'You must select at least one source!',
	'upload'=>'Upload file',
	'address'=>'Enter path',
	'cancel'=>'Cancel',
	'enter_address'=>'Enter an address ',
	'upload_image'=>'Upload your image',
	'delete'=>'Delete',
	'session_lost'=>'Session lost.',
	'no_upload_path'=>'The specified path to save the file does not exist or is not writable!',
	'noimage'=>'No image',
	'only_types'=>'Supported only images with following types: %s',
	'not_image'=>'File is not a valid image',
	'bigger_w'=>'Image width exceeds the permissible limit in %s pixels. Image width - %s pixels.',
	'bigger_h'=>'Image height exceeds the allowable limit of %s pixels. Image height - %s pixels.',
	'smaller'=>'Image size does not reach the prescribed standard. Image size - %s x %s. Standard - %s x %s',
);