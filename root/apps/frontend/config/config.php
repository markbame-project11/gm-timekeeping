<?php
  /*
	if (!date_default_timezone_get('date.timezone'))
	{
  */
	    date_default_timezone_set('Asia/Singapore'); // put here default timezone
	//}
//echo date_default_timezone_get('date.timezone');
$base_url = 'www.payroll.com.local';

if (array_key_exists('PHP_ENVIRONMENT', $_SERVER))
{
	switch ($_SERVER['PHP_ENVIRONMENT'])
	{
		case 'production':
		{
			$base_url = '10.12.0.170';
			break;
		}

		case 'development':
		default:
		{
			$base_url = 'www.payroll.com.local';
			break;
		}
	}
}
return array(
	'base_url'           => 'http://'.$base_url.'/index.php',
	'assets_base_url'    => 'http://'.$base_url.'/',
	'admin_base_url'     => 'http://'.$base_url.'/admin.php',
	'system_pub_date'    => '2014-08-18'
);