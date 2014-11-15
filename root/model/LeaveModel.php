<?php

/**
 * The model for leaves
 */
class LeaveModel extends CcBaseModel
{
	/**
	 * Returns the leaves of employee for the given dates. If the employee has no leave on that date the value will be NULL.
	 */
	public function getEmployeeLeavesForDates($employee_id, array $dates)
	{
		$results = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					),
					array(
						'field'      => 'date',
						'condition'  => 'IN',
						'value'      => "'".implode("','", $dates)."'"
					)
				)
			)
		));

		$ret_val = array();
		foreach ($dates as $date)
		{
			$ret_val[$date] = NULL;
		}

		foreach ($results as $result)
		{
			$ret_val[$result['date']] = $result;
		}

		return $ret_val;
	}

	/**
	 * Returns the consolidation of leaves.
	 * 
	 * @return        Return values as an associative array { 'paid' : { 'vacation' : [<leave data>, ...], 'sick' : [<leave data>, ...] }, 'unpaid' : { 'vacation' : [<leave data>, ...], 'sick' : [<leave data>, ...] } }
	 */
	public function getEmployeeApprovedLeavesConsolidationInYear($employee_id, $year)
	{
		$leaves = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'status',
						'condition'  => '=',
						'value'      => 'approved'
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				)
			)
		));

		$ret_val = array(
			'paid'   => array(
				'vacation'  => array(),
				'sick'      => array()
			),
			'unpaid'   => array(
				'vacation'  => array(),
				'sick'      => array()
			)
		);

		foreach ($leaves as $leave)
		{
			if ($leave['is_paid'] == '1')
			{
				if ($leave['leave_type'] == 'vacation' || $leave['leave_type'] == 'half_day')
				{
					$ret_val['paid']['vacation'][] = $leave; 
				}
				else
				{
					$ret_val['paid']['sick'][] = $leave; 
				}
			}
			else
			{
				if ($leave['leave_type'] == 'vacation' || $leave['leave_type'] == 'half_day')
				{
					$ret_val['unpaid']['vacation'][] = $leave; 
				}
				else
				{
					$ret_val['unpaid']['sick'][] = $leave;
				}
			}
		}

		return $ret_val;
	}

	/**
	 * Returns the pending leaves of employee.
	 */
	public function getEmployeePendingLeaves($employee_id)
	{
		return $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'status',
						'condition'  => '=',
						'value'      => 'pending'
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				)
			)
		));
	}

	/**
	 * Returns the pending leaves of employee.
	 */
	public function getEmployeeLeavesOfStatus($employee_id, $status)
	{
		return $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'status',
						'condition'  => '=',
						'value'      => $status
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				)
			)
		));
	}

	/**
	 * Returns all the approved leaves of employee before the given date but on the same year.
	 */
	public function getEmployeeApprovedLeavesBeforeOnSameYear($employee_id, $date)
	{
		return $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'status',
						'condition'  => '=',
						'value'      => 'approved'
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					),
					array(
						'field'             => 'YEAR(`date`)',
						'dont_quote_field'  => true,
						'condition'         => '=',
						'value'             => date('Y', strtotime($date))
					),
					array(
						'field'      => 'date',
						'condition'  => '<',
						'value'      => $date
					)
				)
			)
		));
	}

	/**
	 * Returns the approved leaves of employee after the given date.
	 */
	public function getEmployeeApprovedLeavesAfter($employee_id, $date)
	{
		return $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'status',
						'condition'  => '=',
						'value'      => 'approved'
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					),
					array(
						'field'      => 'date',
						'condition'  => '>=',
						'value'      => $date
					)
				)
			)
		));
	}

	/**
	 * Returns the leaves of employees on $month.
	 */
	public function getPendingLeaves()
	{
		$sql = 'SELECT `'.$this->getTableName().'`.*, employee.lastname, employee.firstname ';
		$sql .= 'FROM `'.$this->getTableName().'` INNER JOIN `employee` ON (`employee`.`empid` = `'.$this->getTableName().'`.`employee_id`) ';
		$sql .= 'WHERE `'.$this->getTableName().'`.`status` = ? ';

		$values = array('pending');

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute($values);

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

			return array();
		}
	}

	/**
	 * Returns the leaves of employees on $month.
	 */
	public function getLeavesOfEmployeesOnMonthJoinEmployee($date)
	{
		$sql = 'SELECT `'.$this->getTableName().'`.*, employee.lastname, employee.firstname ';
		$sql .= 'FROM `'.$this->getTableName().'` JOIN `employee` ON (`employee`.`empid` = `'.$this->getTableName().'`.`employee_id`) ';
		$sql .= 'WHERE MONTH(`date`) = ? AND YEAR(`date`) = ? ';

		$dTime = strtotime($date);
		$values = array(date('n', $dTime), date('Y', $dTime));

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute($values);

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

			return array();
		}
	}

	/**
	 * Returns the leaves with employee ID equal to the given $id and $date.
	 */
	public function getLeaveOfEmployeeOn($id, $date)
	{
		$leaves = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '=',
						'value'      => $date
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $id
					)
				)
			)
		));

		return (0 < count($leaves)) ? $leaves[0] : NULL;
	}

	/**
	 * Returns the leaves within the given range.
	 *
	 * {
	 *   <date> :
	 *   {
	 *     <employee id>: <leave type>, ...
	 *   }, ...
	 * }
	 */
	public function getEmployeeLeavesBetween($employee_id, $start_date, $end_date)
	{
		$ret_val = array();

		$leaves = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'date',
						'condition'  => '<=',
						'value'      => $end_date
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)			
				)
			)
		));

		foreach ($leaves as $leave)
		{
			if (!array_key_exists($leave['date'], $ret_val))
			{
				$ret_val[$leave['date']] = array();
			}

			$ret_val[$leave['date']] = array(
				'leave_type' => $leave['leave_type'],
				'is_paid'    => $leave['is_paid']
			);
		}

		return $ret_val;
	}

	/**
	 * Returns the leaves within the given range.
	 *
	 * {
	 *   <date> :
	 *   {
	 *     <employee id>: <leave type>, ...
	 *   }, ...
	 * }
	 */
	public function getApprovedLeavesBetween($start_date, $end_date)
	{
		$ret_val = array();

		$leaves = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'date',
						'condition'  => '<=',
						'value'      => $end_date
					),
					array(
						'field'      => 'status',
						'condition'  => '=',
						'value'      => 'approved'
					)
				)
			)
		));

		foreach ($leaves as $leave)
		{
			if (!array_key_exists($leave['date'], $ret_val))
			{
				$ret_val[$leave['date']] = array();
			}

			$ret_val[$leave['date']][$leave['employee_id']] = array(
				'leave_type' => $leave['leave_type'],
				'is_paid'    => $leave['is_paid']
			);
		}

		return $ret_val;
	}

	/**
	 * Returns the leaves within the given range joining with employee table
	 */
	public function getLeavesBetweenJoinEmployee($start_date, $end_date)
	{
		$sql = 'SELECT `'.$this->getTableName().'`.*, employee.lastname, employee.firstname ';
		$sql .= 'FROM `'.$this->getTableName().'` JOIN `employee` ON (`employee`.`empid` = `'.$this->getTableName().'`.`employee_id`) ';
		$sql .= 'WHERE `date` >= ? AND `date` <= ? ';

		$values = array($start_date, $end_date);

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute($values);

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

			return array();
		}
	}

	/**
	 * Returns the leaves on the given date.
	 */
	public function getLeavesOnJoinEmployee($date)
	{
		$sql = 'SELECT `'.$this->getTableName().'`.*, `employee`.`lastname`, `employee`.`firstname` ';
		$sql .= 'FROM `'.$this->getTableName().'` JOIN `employee` ON (`employee`.`empid` = `'.$this->getTableName().'`.`employee_id`) ';
		$sql .= 'WHERE `date` = ?';

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array($date));

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

			return array();
		}
	}

	/**
	 * Returns the leaves 
	 */

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'id'          => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'employee_id' => array(
				'type'              => 'int',
				'required'          => true
			),
			'date'        => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
			'start_time'        => array(
				'type'              => 'time',
				'default_value'     => '00:00:00'
			),
			'number_of_hours'   => array(
				'type'              => 'int',
				'default_value'     => 0
			),
			'leave_type'  => array(
				'type'              => 'enum',
				'type_values'       => array('sick', 'vacation'),
				'default_value'     => 'sick'
			),
			'reason'      => array(
				'type'              => 'string',
				'default_value'     => ''
			),
			'is_paid'     => array(
				'type'              => 'bool',
				'default_value'     => true
			),
			'status'      => array(
				'type'              => 'enum',
				'type_values'       => array('pending', 'approved', 'denied'),
				'default_value'     => 'approved'
			),
			'date_filed'      => array(
				'type'              => 'date',
				'default_value'     => '0000-00-00'
			),
		);

		$this->setTableName('leave');
		$this->setFields($fields, 'id');
	}
}