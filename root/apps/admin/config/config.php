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
	'base_url'           => 'http://'.$base_url.'/admin.php',
	'assets_base_url'    => 'http://'.$base_url.'/',
	'frontend_base_url'  => 'http://'.$base_url.'/index.php',
	'upload_directory'   => '/uploads/payroll'
);


/*
define("pd_DSN", "mysql:dbname=payrolldb;host=127.0.0.1");
define("pd_USER", "payroll");
define("pd_PWD", "5ZnrVBuU2VZuLd2q");
*/