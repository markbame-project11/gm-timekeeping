<?php

/**
 * The model for a payroll.
 */
class PayrollModel extends CcBaseModel
{
	/**
	 * Returns the payroll
	 */
	public function getPayrollsOnYear($year)
	{
		return $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'YEAR(`payroll_date`)',
						'dont_quote_field'  => true,
						'condition'         => '=',
						'value'             => $year
					)		
				)
			),
			'order_by'    => array(
				'order'    => 'desc',
				'field'    => 'payroll_date'
			)
		));
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
			'start_date'      => array(
				'type'              => 'date'
			),
			'end_date'   => array(
				'type'              => 'date'
			),
			'payroll_date' => array(
				'type'              => 'date'
			),
			'script_is_running' => array(
				'type'              => 'bool',
				'default_value'     => 0
			),
			'file_url' => array(
				'type'              => 'string',
				'default_value'     => ''
			),
			'script_run_start' => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00 00:00:00'    
			)
		);

		$this->setTableName('payrolls');
		$this->setFields($fields, 'id');
	}
}