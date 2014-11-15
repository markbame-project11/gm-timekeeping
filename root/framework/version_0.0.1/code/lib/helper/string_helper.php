<?php

/**
 * Converts a string with underscore to capitalize the next char after underscore.
 *
 * @param      string     $string
 */
function str_underscore_to_camelcase($string)
{
	$string_pcs = explode('_', $string);
	$new_string_arr = array();

	foreach ($string_pcs as $string_pc)
	{
		$new_string_arr[] = ucfirst($string_pc);
	}

	return implode("", $new_string_arr);
}