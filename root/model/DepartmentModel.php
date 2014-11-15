<?php
/**
 * The model for an department.
 */
class DepartmentModel extends CcBaseModel
{
	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'deptid'         => array(
					'type'              => 'int',
					'is_autoincrement'  => true
				),
			'managerid'      => array(
					'type'              => 'int',
					'default_value'     => '0'
				),
			'deptparentid'   => array(
					'type'              => 'int',
					'default_value'     => '0'
				),
			'deptname'       => array(
					'type'              => 'string',
					'is_mandatory'      => true
				),
			'location'       => array(
					'type'              => 'string'
				),
			'deptdesc'       => array(
					'type'              => 'string'
				),
			'mandaworkdesc'  => array(
					'type'              => 'string',
					'default_value'     => 'y'
				), 
			'messaging'      => array(
					'type'              => 'string',
					'default_value'     => 'y'
				)
		);

		$this->setTableName('department');
		$this->setFields($fields, 'deptid');
	}
}