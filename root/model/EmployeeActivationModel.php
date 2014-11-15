<?php
/**
 * The model for an employee activation.
 */
class EmployeeActivationModel extends CcBaseModel
{
	/**
	 * Returns the entry with key equal to $code
	 */
	public function getByCode($code)
	{
		$results = $this->find(array(
			'where'       => array(
				'conditions'  => array(
					array(
						'field'      => 'code',
						'condition'  => '=',
						'value'      => $code
					)
				) 
			)
		));

		return (0 < count($results)) ? $results[0] : NULL;
	}

	/**
	 * Returns the entry with employee id equal to $employee_id
	 */
	public function getByEmployeeID($employee_id)
	{
		$results = $this->find(array(
			'where'       => array(
				'conditions'  => array(
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				) 
			)
		));

		return (0 < count($results)) ? $results[0] : NULL;
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
			'create_date'      => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
			'code'      => array(
				'type'              => 'string',
				'default_value'     => ''
			),
			'email'      => array(
				'type'              => 'string',
				'default_value'     => ''
			)
		);

		$this->setTableName('employee_activation');
		$this->setFields($fields, 'id');
	}
}