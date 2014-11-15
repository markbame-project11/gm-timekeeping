<?php

/**
 * The model for the password reset.
 */
class PasswordResetModel extends CcBaseModel
{
	/**
	 * Returns the password reset object with code equal to the given code.
	 */
	public function getByCode($code)
	{
		$results = $this->find(array(
			'where' => array(
				'conditions'   => array(
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
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'id'               => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'employee_id'      => array(
				'type'              => 'int',
				'default_value'     => 0
			),
			'date'             => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
			'code'             => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'activation_code'  => array(
				'type'              => 'string'
			)
		);

		$this->setTableName('password_reset_keys');
		$this->setFields($fields, 'id');
	}
}