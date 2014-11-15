<?php

/**
 * The model for flexible schedules.
 */
class FlexiScheduleModel extends CcBaseModel
{
	/**
	 * Returns true if the user is currently on flexi schedule
	 * 
	 * @return     bool
	 */
	public function employeeIsOnFlexiSchedule($employee_id)
	{
		$results = $this->find(array(
			'order_by'    => array(
				'order'   => 'desc',
				'field'   => 'start_date'
			),
			'limit'    => array(
				'numrecords'   => 1
			),
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				)
			)
		));

		if (0 < count($results))
		{
			$result = $results[0];

			return (strtotime($result['start_date']) <= time() && $result['is_flexi'] == 1);
		}
		else
		{
			return false;
		}

		// if the latest is schedule is set as 
		return (0 < count($results)) ? $results[0] : NULL;
	}

	/**
	 * Returns the flexi schedule entry on the given date.
	 * 
	 * @return     flexi_entry
	 */
	public function getFlexiScheduleOn($employee_id, $date)
	{
		$results = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'start_date',
						'condition'  => '=',
						'value'      => $date
					),
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
	 * Returns an associative array of whether the employee has flexible schedule at a specific date or not.
	 * If the employee isn't found in the list it only means that he has no flexi schedule for all time.
	 * 
	 * @return     associative array         Structure for return value { <employee id> : { <date> : true|false, ... }, ... ], ... }
	 */
	public function getEmployeeFlexibleScheduleStatus($employee_id, $start_date, $end_date)
	{
		$results = $this->find(array(
			'order_by'    => array(
				'order'   => 'desc',
				'field'   => 'start_date'
			),
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'start_date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'start_date',
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

		$ret_val = array();
		$end_date_strt = strtotime($end_date);
		for ($i = strtotime($start_date); $i < $end_date_strt; $i = strtotime('+1day', $i))
		{
			$dt = date('Y-m-d', $i);

			// find if the employee is flexi on this date
			// the employee is flexi if we can find a $flexibleSchedule entry that is greater than the current date which is $i
			$lastFlexibleSchedule = NULL;
			foreach ($results as $result)
			{
				if ($i >= strtotime($result['start_date']))
				{
					$lastFlexibleSchedule = $result;
				}
				else if ($i < strtotime($result['start_date']))
				{
					break;
				}
			}

			if ($lastFlexibleSchedule != NULL)
			{
				$ret_val[$dt] = ($flexibleSchedule['is_flexi'] == 1);
			}
			else
			{
				$ret_val[$dt] = false;
			}
		}

		return $ret_val;
	}

	/**
	 * Returns an associative array of whether the employee has flexible schedule at a specific date or not.
	 * If the employee isn't found in the list it only means that he has no flexi schedule for all time.
	 * 
	 * @return     associative array         Structure for return value { <employee id> : { <date> : true|false, ... }, ... ], ... }
	 */
	public function getEmployeesFlexibleScheduleStatus($start_date, $end_date)
	{
		$results = $this->find(array(
			'order_by'    => array(
				'order'   => 'desc',
				'field'   => 'start_date'
			),
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'start_date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'start_date',
						'condition'  => '<=',
						'value'      => $end_date
					)
				)
			)
		));

		$employeeWithFlexiSchedules = array();
		$flexibleSchedules = array();
		foreach ($results as $result)
		{
			if (!array_key_exists($result['employee_id'], $flexibleSchedules))
			{
				$flexibleSchedules[$result['employee_id']] = array();
			}

			$flexibleSchedules[$result['employee_id']][] = $result;
			$employeeWithFlexiSchedules[] = $result['employee_id'];
		}

		$ret_val = array();
		$end_date_strt = strtotime($end_date);
		for ($i = strtotime($start_date); $i < $end_date_strt; $i = strtotime('+1day', $i))
		{
			$dt = date('Y-m-d', $i);

			foreach ($employeeWithFlexiSchedules as $employee_id)
			{
				if (!array_key_exists($employee_id, $ret_val))
				{
					$ret_val[$employee_id] = array();
				}

				// find if the employee is flexi on this date
				// the employee is flexi if we can find a $flexibleSchedule entry that is greater than the current date which is $i
				$lastFlexibleSchedule = NULL;
				foreach ($flexibleSchedules[$employee_id] as $flexibleSchedule)
				{
					if ($i >= strtotime($flexibleSchedule['start_date']))
					{
						$lastFlexibleSchedule = $flexibleSchedule;
					}
					else if ($i < strtotime($flexibleSchedule['start_date']))
					{
						break;
					}
				}

				if ($lastFlexibleSchedule != NULL)
				{
					$ret_val[$employee_id][$dt] = ($flexibleSchedule['is_flexi'] == 1);
				}
				else
				{
					$ret_val[$employee_id][$dt] = false;
				}
			}
		}

		return $ret_val;
	}

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'id'             => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'employee_id'    => array(
				'type'              => 'int',
				'default_value'     => '0'
			),
			'start_date'     => array(
				'type'              => 'string',
				'default_value'     => '0000-00-00'
			),
			'is_flexi'      => array(
				'type'              => 'bool',
				'default_value'     => true
			)
		);

		$this->setTableName('flexi_schedules');
		$this->setFields($fields, 'id');
	}
}