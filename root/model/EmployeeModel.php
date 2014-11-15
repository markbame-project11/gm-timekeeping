<?php

/**
 * The model for an employee.
 */
class EmployeeModel extends CcBaseModel
{
	/**
	 * Returns the employee with gumi email equal to the given email.
	 */
	public function getByGumiEmail($email)
	{
		$employees = $this->find(array(
			'where'       => array(
				'conditions'  => array(
					array(
						'field'      => 'gumi_email',
						'condition'  => '=',
						'value'      => $email
					)
				) 
			)
		));

		return (0 < count($employees)) ? $employees[0] : NULL;
	}

    //-----------------------------------------------------------------------
      /*
         Gets individual GrossPay per employee for inclusion in
         the report generation
      */
	public function getgrosspaybyempidNov03FORTESTING($empid)
	{
		/*
		$sql = "SELECT `gross_pay`FROM `employee` ";
		$sql .= "WHERE `empid` = ? ";
		*/

		$sql = "SELECT gross_pay FROM employee WHERE empid =".$empid."";
        //echo $sql . '<br />';
		try
		{
			//!!$sth = $this->getPDO()->prepare($sql);
			//!!$sth->execute(array($empid));
			$sth = $this->getPDO()->query($sql); 

			//!!$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			$results = $sth->fetch(PDO::FETCH_ASSOC);

			return $results;
		}
		catch (Exception $ex)
		{
			return array();
		}
	}
	public function getgrosspaybyempid($empid)
	{
			$sql= "SELECT gross_pay FROM employee WHERE empid = :empid"; 
			$stmt = $this->getPDO()->prepare($sql);
			$stmt->bindParam(':empid', $empid, PDO::PARAM_INT); 
			$stmt->execute();
			$obj = $stmt->fetchObject();
			return $obj->gross_pay;

	}


    //-----------------------------------------------------------------------

	/**
	 * Returns payroll employees in the given department
	 */
	public function getEmployeeIdsToFScanIdMappings($start_date, $end_date)
	{
		$employees = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'is_employee',
						'condition'  => '=',
						'value'      => 1
					)
				) 
			)
		));

		$mappings = array();
		foreach ($employees as $employee)
		{
			$mappings[$employee['empid']] = $employee['fscan_id'];
		}

		return $mappings;
	}

	/**
	 * Override create to hash password.
	 */
	public function create(array $props)
	{
		if (array_key_exists('password', $props))
		{
			$props['password'] = hash('sha256', $props['password']);
		}

		return parent::create($props);
	}

	/**
	 * Override update to hash password.
	 */
	public function update($id, array $props)
	{
		if (array_key_exists('password', $props))
		{
			$props['password'] = hash('sha256', $props['password']);
		}

		return parent::update($id, $props);
	}

	/**
	 * Returns the user with login equal to the given login.
	 */
	public function getEmployeeByUsernameAndPassword($username, $password)
	{
		$results = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'login',
						'condition'  => '=',
						'value'      => $username
					),
					array(
						'field'      => 'password',
						'condition'  => '=',
						'value'      => hash('sha256', $password)
					)
				)
			)
		));

		return (0 < count($results)) ? $results[0] : NULL;
	}

	/**
	 * Returns the users that contains the word $keyword in their firstname, lastname or email. 
	 */
	public function searchUserByFirstNameOrLastName($firstname, $lastname)
	{
		$results = $this->find(array(
			'where'       => array(
				'conditions'  => array(
					array(
						'field'      => 'firstname',
						'condition'  => 'LIKE',
						'value'      => $firstname
					)
				) 
			)
		));

		if (0 == count($results))
		{
			return $this->find(array(
				'where'       => array(
					'conditions'  => array(
						array(
							'field'      => 'lastname',
							'condition'  => 'LIKE',
							'value'      => $lastname
						)
					) 
				)
			));
		}
		else
		{
			return $results;
		}
	}

	/**
	 * Returns the users that contains the word $keyword in their firstname, lastname or email. 
	 */
	public function searchUsers($keyword)
	{
		return $this->find(array(
			'where'       => array(
				'relation'    => '||',
				'conditions'  => array(
					array(
						'field'      => 'firstname',
						'condition'  => 'LIKE',
						'value'      => $keyword
					),
					array(
						'field'      => 'lastname',
						'condition'  => 'LIKE',
						'value'      => $keyword
					),
					array(
						'field'      => 'email',
						'condition'  => 'LIKE',
						'value'      => $keyword
					)
				) 
			)
		));
	}

	/**
	 * Returns the list of employees with is_employee set to 1.
	 */
	public function getPayrollEmployees()
	{
		return $this->find(array(
			'order_by'    => array(
				'order'    => 'asc',
				'field'    => 'lastname'
			),
			'where'       => array(
				'conditions'  => array(
					array(
						'field'      => 'is_employee',
						'condition'  => '=',
						'value'      => 1
					)
				) 
			)
		));
	}

	/**
	 * Returns employees in the given department
	 */
	public function getEmployeesInDepartment($deptid)
	{
		return $this->find(array(
			'order_by'    => array(
				'order'    => 'asc',
				'field'    => 'lastname'
			),
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'deptid',
						'condition'  => '=',
						'value'      => $deptid
					)
				) 
			)
		));
	}
    //-----------------------------------------------------------------------
	public function getByEmployeeIDgross_pay()
	{
		$sql = "SELECT * FROM employee ";
		//$sql .= "WHERE empid = ? ";
		try
		{
			$sth = $this->getPDO()->prepare($sql);
			//$sth->execute(array($employee_id));
			$sth->execute();

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			if (0 < count($results))
			{
				return $results[0];
			}
			else
			{
				return NULL;
			}
		}
		catch (Exception $ex)
		{
			// log exception
			return NULL;
		}

	}

    //-----------------------------------------------------------------------

	/**
	 * Returns the employee with joined department info
	 */
	public function getByEmployeeIDJoinDepartment($employee_id)
	{
		$sql = "SELECT `employee`.*, `department`.`deptname`, `department`.`managerid` FROM `employee` ";
		$sql .= "LEFT JOIN `department` ON `employee`.`deptid` = `department`.`deptid` ";
		$sql .= "WHERE `employee`.`empid` = ? ";

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array($employee_id));

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			if (0 < count($results))
			{
				return $results[0];
			}
			else
			{
				return NULL;
			}
		}
		catch (Exception $ex)
		{
			// log exception
			return NULL;
		}
	}

	public function configure()
	{
		$fields = array(
			'empid'                 => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			), 
			'deptid'                => array(
				'type'              => 'int',
				'is_mandatory'      => true
			), 
			'lastname'              => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'firstname'             => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'minit'                 => array(
				'type'              => 'string'
			),
			'fscan_id'              => array(
				'type'              => 'int',
				'default_value'     => 0,
			),
			'dob'                   => array(
				'type'              => 'string',
				'default_value'     => '0000-00-00',
				'is_mandatory'      => true
			),
			'gender'                => array(
				'type'              => 'string',
				'default_value'     => 'm'
			),
			'tax_status'            => array(
				'type'              => 'string',
				'default_value'     => 'single',
				'is_mandatory'      => true
			),
			'address1'              => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'email'                 => array(
				'type'              => 'string'
			),
			'nickname'              => array(
				'type'              => 'string'
			),
			'cellphone'             => array(
				'type'              => 'string'
			),
			'login'                 => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'password'              => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'admin'                 => array(
				'type'              => 'bool',
				'default_value'     => false
			),
			'superadmin'            => array(
				'type'              => 'bool',
				'default_value'     => false
			),
			'numlogins'             => array(
				'type'              => 'int',
				'default_value'     => 0
			),
			'datesignup'            => array(
				'type'              => 'string',
				'default_value'     => '0000-00-00'
			),
			'ipsignup'              => array(
				'type'              => 'string'
			),
			'lastlogindate'         => array(
				'type'              => 'datetime',
				'default_value'     => '0000-00-00 00:00:00'
			),
			'loginip'               => array(
				'type'              => 'string'
			),
			'dateupdated'           => array(
				'type'              => 'datetime',
				'default_value'     => '0000-00-00 00:00:00'
			),
			'ipupdated'             => array(
				'type'              => 'string'
			),
			'sss_no'                => array(
				'type'              => 'string'
			),
			'tin_no'                => array(
				'type'              => 'string'
			),
			'philhealth_no'         => array(
				'type'              => 'string'
			),
			'pagibig_no'            => array(
				'type'              => 'string'
			),
			'position'              => array(
				'type'              => 'string'
			),
			'skype_id'              => array(
				'type'              => 'string'
			),
			'em_contact_person'     => array(
				'type'              => 'string'
			),
			'em_contact_no'         => array(
				'type'              => 'string'
			),
			'em_contact_address'    => array(
				'type'              => 'string'
			),
			'employment_status'     => array(
				'type'              => 'enum',
				'type_values'       => array('regular', 'probationary', 'resigned'),
				'default_value'     => 'regular'
			),
			'regularization_date'   => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
			'resignation_date'      => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
			'date_hired'            => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
			'gumi_email'            => array(
				'type'              => 'string',
				'default_value'     => ''	
			),
			'active'                => array(
				'type'              => 'bool',
				'default_value'     => 0
			)
		);

		$this->setTableName('employee');
		$this->setFields($fields, 'empid');
	}
}