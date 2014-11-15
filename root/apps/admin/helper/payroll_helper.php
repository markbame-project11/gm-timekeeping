<?php
/**
 * Returns the SSS contribution for salary
 */
function get_sss_contribution_for_salary($salary)
{
	$sss_table = include(dirname(dirname(__FILE__)).'/config/sss_table.php');
	$len = count($sss_table);

	$salary = (float) $salary;
	$i = 1;
	for (; $i < $len; $i++)
	{
		if ($salary < $sss_table[$i]['start_range'])
		{
			break;
		}
	}

	return $sss_table[$i - 1]['employee_contribution'];
}

/**
 * Returns the Philhealth contribution for salary
 */
function get_philhealth_contribution_for_salary($salary)
{
	$table = include(dirname(dirname(__FILE__)).'/config/philhealth_table.php');
	$len = count($table);

	$salary = (float) $salary;
	$i = 1;
	for (; $i < $len; $i++)
	{
		if ($salary < $table[$i]['start_range'])
		{
			break;
		}
	}

	return $table[$i - 1]['employee_contribution'];
}

/**
 * Returns the half month tax object
 */
function get_tax_object_for_half_month_pay($half_month_pay, $tax_status)
{
	$tax_table_list = include(dirname(dirname(__FILE__)).'/config/tax_table.php');
	if (!array_key_exists($tax_status, $tax_table_list))
	{
		return NULL;
	}

	$tax_table = $tax_table_list[$tax_status];
	$len = count($tax_table);

	$half_month_pay = (float) $half_month_pay;
	$i = 1;
	for (; $i < $len; $i++)
	{
		if ($half_month_pay < $tax_table[$i]['start_range'])
		{
			break;
		}
	}

	$tax_object = $tax_table[$i - 1];
	$withholding_tax = $tax_object['base_tax'] + (($half_month_pay - $tax_object['start_range']) * $tax_object['add_percentage']);
	$pay_less_withholding_tax = $half_month_pay - $withholding_tax;

	return array(
		'exemption'                 => $tax_object['start_range'],
		'basic_tax'                 => $tax_object['base_tax'],
		'percentage'                => (int)($tax_object['add_percentage'] * 100),
		'withholding_tax'           => $withholding_tax,
		'pay_less_withholding_tax'  => $pay_less_withholding_tax
	);
}