<?php

/**
 * The model for an employee payroll.
 */
class EmployeePayrollModel extends CcBaseModel
{
	/**
	 * Returns the employee payrolls of the given payroll ID
	 */
	public function getByPayrollID($payroll_id)
	{
		return $this->find(array(
			'where'       => array(
				'conditions'  => array(
					array(
						'field'             => 'payroll_id',
						'condition'         => '=',
						'value'             => $payroll_id
					)
				)
			) 
		));
	}

	/**
	 * Returns the employee payrolls of the given payroll ID joined to employee info
	 */
	public function getByPayrollIDJoinEmployees($payroll_id)
	{
		$sql = "SELECT `employee_payrolls`.*, `employee`.`empid`, `employee`.`lastname`, `employee`.`firstname`, `employee`.`date_hired`, `employee`.`tax_status` FROM `employee_payrolls` ";
		$sql .= "INNER JOIN `employee` ON (`employee_payrolls`.`employee_id` = `employee`.`empid`) ";
		$sql .= "WHERE `employee_payrolls`.`payroll_id` = ? ";
		$sql .= "ORDER BY `employee`.`lastname` ";

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array($payroll_id));

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);

			return $results;
		}
		catch (Exception $ex)
		{
			return array();
		}
	}

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'id'         => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'employee_id'      => array(
				'type'              => 'int',
				'is_mandatory'      => true
			),
			'payroll_id'   => array(
				'type'              => 'int',
				'is_mandatory'      => true
			),
			'gross_pay' => array(
				'type'              => 'int'
			),
			'has_sss_deduction' => array(
				'type'              => 'int',
				'default_value'     => 0
			),
			'has_philhealth_deduction' => array(
				'type'              => 'int',
				'default_value'     => 0
			),
			'has_pagibig_deduction' => array(
				'type'              => 'int',
				'default_value'     => 0
			)
		);

		$this->setTableName('employee_payrolls');
		$this->setFields($fields, 'id');
	}
}