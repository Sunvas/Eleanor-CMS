<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class extends Abstracts\AdminPanel implements Interfaces\CLI {
	function __construct()
	{
		$this->name=\basename(__FILE__,'.php');
	}

	function CLI(array$argv):never
	{
		\var_dump($argv);
		die;
	}
};