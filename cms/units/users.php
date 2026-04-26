<?php
# Eleanor CMS © 2025 --> https://eleanor-cms.com
namespace CMS;

return new class extends Abstracts\AdminPanel {
	function __construct()
	{
		$this->name=\basename(__FILE__,'.php');
	}
};