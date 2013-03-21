<?php
return array(
	'callback'=>function($co)
	{
		return Types::TimeZonesOptions($co['value']);
	},
);