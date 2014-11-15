<?php
/**
 * The model for timesheets
 */
class TimesheetModel extends CcBaseModel
{
	/**
	 * Returns the list of timesheet of the given employee with 'checkin' date as the key
	 */
	public function getTimesheetOfEmployeeWithDateKey($employeeID, $start_date, $end_date)
	{
		$ret_val = array();

		$timesheets = $this->getTimesheetOfEmployeeBetween($employeeID, $start_date, $end_date);

		foreach ($timesheets as $timesheet)
		{
			$ret_val[date('Y-m-d', strtotime($timesheet['checkin']))] = $timesheet;
		}

		return $ret_val;
	}

	/**
	 * Returns the timesheet of employees in the given department.
	 * 
	 * @return      array        Associative array of employee timesheets with keys equal to date.
	 */
	function getDepartmentEmployeesTimesheetsBetween($department_id, $start_date, $end_date)
	{
		$timesheets = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '>=',
						'value'             => $start_date
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '<=',
						'value'             => $end_date
					),
					array(
						'field'             => 'empid',
						'condition'         => 'IN',
						'value'             => 'SELECT `empid` FROM employee WHERE `deptid` = '.((int)$department_id)
					)			
				)
			) 
		));

		$return_val = array();
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
		{
			$return_val[date('Y-m-d', $i)] = array();
		}

		foreach ($timesheets as $timesheet)
		{
			$return_val[date('Y-m-d', strtotime($timesheet['checkin']))][$timesheet['empid']] = $timesheet;
		}

		return $return_val;
	}

	/**
	 * Returns the timesheet of employee between $start_date and $end_date inclusively.
	 *
	 * @ereturn      array        Associative array of employee timesheets with keys equal to date.
	 */
	function getEmployeeTimesheetsBetween($start_date, $end_date)
	{
		$timesheets = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '>=',
						'value'             => $start_date
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '<=',
						'value'             => $end_date
					)
				)
			) 
		));

		$return_val = array();
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
		{
			$return_val[date('Y-m-d', $i)] = array();
		}

		foreach ($timesheets as $timesheet)
		{
			$return_val[date('Y-m-d', strtotime($timesheet['checkin']))][$timesheet['empid']] = $timesheet;
		}

		return $return_val;
	}	

	/**
	 * Employee can time-in / time out and must not matter on 
	 * changed schedule
	 */
    function getemployeeTimeInTimeOut($empid,$checkinORout){
    	/*
    	  to replace calculate_show_timein
    	  SELECT * FROM `timesheet` WHERE empid=74 and date(checkin)=CURDATE()
    	  UPDATE timesheet set checkin='00:00:00',checkout='00:00:00' where empid=74
    	*/
		$sql = 'SELECT '.$checkinORout.' from timesheet where date('.$checkinORout.')=CURDATE() and empid ='.$empid;
		//if there's time-in show the time-out button
			$sth = $this->getPDO()->query($sql);
            return $sth->rowCount(); //. count($existName);
    }

    //------------------------------------
    function getemployeeTimesheet_checkout($empid){
		$sql = 'SELECT checkout from timesheet where date(checkout)=CURDATE() and empid ='.$empid;
		/*
         SELECT date(checkout) FROM `timesheet` WHERE date(checkout)='2014/11/13'		
		*/
         $_SESSION['tmesheet_checkout'] = '';
         //echo $sql;
			$sth = $this->getPDO()->query($sql);
            $existName = $sth->fetch(PDO::FETCH_ASSOC);
            return $sth->rowCount(); //. count($existName);
            /*
            	return "1";
            if($existName=="") {	
            if($sth->rowCount()<1) {	            	
            	echo "wala";
            	return "0";
            }	
            */

    }

    //------------------------------------

	function getEmployeeTimesheetOfTheDayInTheDepartment($departmentID)
	{
				//echo $employeeID .' this dept';		

		$sql = 'SELECT `employee`.`empid`, `employee`.`firstname`, `employee`.`lastname`, `timesheet`.`checkin`, `timesheet`.`checkout` ';
		$sql .= 'FROM `employee` LEFT JOIN `timesheet` ON (`timesheet`.`empid` = `employee`.`empid` AND DATE(`timesheet`.`checkin`) = ?) ';
		$sql .= 'WHERE `employee`.`deptid` = ? ';
		$sql .= 'ORDER BY `employee`.`lastname` ASC';

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array(date('Y-m-d'), $departmentID));

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			return $results;
		}
		catch (Exception $ex)
		{
			if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
			{
				openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
				syslog(LOG_WARNING, $ex->getFile().':'.$ex->getLine().' -- '.$ex->getMessage());
				closelog();
			}

			return array(); // return empty array on error
		}
	}	

	/**
	 * Returns the timesheet of employee in the current day
	 */
	function getEmployeeTimesheetOfTheDay($employeeID)
	{
		$timesheets = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'empid',
						'condition'         => '=',
						'value'             => $employeeID
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '=',
						'value'             => date('Y-m-d')
					)				
				) 
			)
		));

		return (0 < count($timesheets)) ? $timesheets[0] : NULL;	
	}

	/**
	 * Returns the list of timesheet of the given employee.
	 */
	public function getTimesheetOfEmployeeBetween($employeeID, $fromDate, $toDate)
	{
		return $this->find(array(
			'order_by'    => array(
				'order'   => 'desc',
				'field'   => 'checkin'
			),
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'empid',
						'condition'         => '=',
						'value'             => $employeeID
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '>=',
						'value'             => $fromDate
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '<=',
						'value'             => $toDate
					)
				) 
			)
		));
	}

	/**
	 * Returns the timesheet of the given employee on a specific date
	 */
	public function getTimesheetOfEmployeeOn($employeeID, $date)
	{
		//echo $employeeID .' this one';		
		$timesheets = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'empid',
						'condition'         => '=',
						'value'             => $employeeID
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '=',
						'value'             => $date
					)
				) 
			)
		));

		return (0 < count($timesheets)) ? $timesheets[0] : NULL;
	}

	/**
	 * Returns the timesheet of the given employee on a specific date
	 */
	public function getLastEmployeeTimesheet($employeeID)
	{
		//echo $employeeID .' this one';
		$timesheets = $this->find(array(
			'order_by'    => array(
				'order'   => 'desc',
				'field'   => 'checkin'
			),
			'where'       => array(
				'conditions'  => array(
					array(
						'field'             => 'empid',
						'condition'         => '=',
						'value'             => $employeeID
					)
				) 
			),
			'limit'      => array(
				'numrecords'  => 1
			)
		));

		return (0 < count($timesheets)) ? $timesheets[0] : NULL;
	}

	/**
	 * Checkin to bundyclock
	 */
	public function checkin($employeeID, $ipCheckin)
	{
		$results = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'empid',
						'condition'         => '=',
						'value'             => $employeeID
					),
					array(
						'field'             => 'DATE(`checkin`)',
						'dont_quote_field'  => true,
						'condition'         => '=',
						'value'             => date('Y-m-d')
					)
				) 
			)
		));

		if (0 < count($results))
		{
			// update only if checkin isn't set yet
			if ($results[0]['checkin'] == '0000-00-00 00:00:00')
			{
				$this->update($results[0]['timeid'], array(
					'checkin'       => date('Y-m-d H:i:s'),
					'ipcheckin'     => $ipCheckin
				));
			}
		}
		else
		{
			$this->create(array(
				'checkin'       => date('Y-m-d H:i:s'),
				'ipcheckin'     => $ipCheckin,
				'empid'         => $employeeID,
			));			
		}
	}

	/**
	 * Checkout to bundyclock
	 */
	public function checkout($employeeID, $ipCheckout)
	{
		$results = $this->find(array(
			'order_by'    => array(
				'field'   => 'timeid',
				'order'   => 'desc'
			),
			'limit'    => array(
				'numrecords'   => 1
			),
			'where'       => array(
				'conditions'  => array(
					array(
						'field'      => 'empid',
						'condition'  => '=',
						'value'      => $employeeID
					)
				) 
			)
		));

		if (0 < count($results))
		{
			// allow checkout multiple times within the day
			return $this->update($results[0]['timeid'], array(
				'checkout'       => date('Y-m-d H:i:s'),
				'ipcheckout'     => $ipCheckout
			));
		}

		return false;
	}

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'timeid'         => array(
					'type'              => 'int',
					'is_autoincrement'  => true
				),
			'empid'          => array(
					'type'              => 'int',
					'default_value'     => '0'
				),
			'checkin'        => array(
					'type'              => 'string',
					'default_value'     => '0000-00-00 00:00:00'
				),
			'checkout'       => array(
					'type'              => 'string',
					'default_value'     => '0000-00-00 00:00:00'
				),
			'ipcheckin'      => array(
					'type'              => 'string'
				),
			'ipcheckout'     => array(
					'type'              => 'string'
				)
		);

		$this->setTableName('timesheet');
		$this->setFields($fields, 'timeid');
	}
}