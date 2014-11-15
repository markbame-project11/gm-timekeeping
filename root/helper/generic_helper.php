<?php
/**
 * Generates random string normally used for password generator.
 * 
 * @param     int      $length      The length of password.
 * @return    string                The generated password.
 * @author    http://stackoverflow.com/questions/4356289/php-random-string-generator(Stephen Watkins)
 */
function generate_random_string($length = 20, $hex = true)
{
	if ($hex)
	{
		$bytes = openssl_random_pseudo_bytes(floor($length / 2), $cstrong);
		return bin2hex($bytes);
	}
	else
	{
		$characters = '01GHIJKLM2abcdetuvwxyzNOPQRS56789TUVWXYZfghijklm34nopqrs_ABCDEF';
		$randomString = '';

		for ($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}

	    return $randomString;
	}
}

/**
 * Returns all the days.
 */
function get_all_days()
{
	return array(
		'sun' => 'Sunday',
		'mon' => 'Monday',
		'tue' => 'Tuesday',
		'wed' => 'Wednesday',
		'thu' => 'Thursday',
		'fri' => 'Friday',
		'sat' => 'Saturday'
	);
}

/**
 * Returns all the days.
 */
function get_days_with_numeric_value()
{
	return array(
		'sun' => 1,
		'mon' => 2,
		'tue' => 3,
		'wed' => 4,
		'thu' => 5,
		'fri' => 6,
		'sat' => 7
	);
}

/**
 * Returns all the hours of the day
 */
function get_all_hours()
{
	$hours = array();

	for ($i = 0; $i < 48; $i++)
	{
		$num = (int) ($i / 2);
		$hour_key = ($num < 10) ? '0'.$num  : $num;
		$hour_key .= (($i % 2) == 0) ? ':00' : ':30';

		$hour_val = (($num % 12) < 10) ? '0'.($num % 12) : ($num % 12);
		$hour_val .= (($i % 2) == 0) ? ':00' : ':30';
		$hour_val .= (((int)($num / 12)) == 0) ? ' AM' : ' PM';

		$hours[$hour_key] = $hour_val;	
	}

	return $hours;
}

/**
 * Returns the marital statuses
 */
function get_all_marital_statuses()
{
	return array(
		's'  => 'Single',
		'm1' => 'Married(1 child or none)',
		'm2' => 'Married(2 children)',
		'm3' => 'Married(3 children)',
		'm4' => 'Married(4 children)'
	);
}

/**
 * Converts an integer to excel column index
 */
function convert_int_to_excel_column($int)
{
	$excel_column = '';
	for ($i = $int; $i > -1; $i = (int)($i / 26) - 1)
	{
		$excel_column = chr(ord('A') + (int)($i % 26)).$excel_column;
	}

	return $excel_column;
}