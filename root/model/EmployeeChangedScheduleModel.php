<?php
/**
 * The model for the changed schedule of employee.
 */
class EmployeeChangedScheduleModel extends CcBaseModel
{
	/**
	 * Returns the changed schedule of the given employee between start date and end date.
	 */
	function getEmployeeChangedSchedulesBetween($employee_id, $start_date, $end_date)
	{
		$ret_val = array(
			'removed_schedules' => array(),
			'added_schedules'   => array()
		);

		$results = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'for_date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'for_date',
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

		foreach ($results as $result)
		{
			$ret_val['removed_schedules'][$result['for_date']] = $result;
			$ret_val['added_schedules'][$result['new_date']] = $result;
		}

		return $ret_val;
	}

	/**
	 * Returns the schedule of employee between the start date and end date.
	 */
	function getEmployeesChangedSchedulesBetween($start_date, $end_date)
	{
		$ret_val = array(
			'removed_schedules' => array(),
			'added_schedules'   => array()
		);

		$changed_schedules = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'for_date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'for_date',
						'condition'  => '<=',
						'value'      => $end_date
					)
				) 
			)
		));

		foreach ($changed_schedules as $schedule)
		{
			if (!array_key_exists($schedule['employee_id'], $ret_val['removed_schedules']))
			{ 
				$ret_val['removed_schedules'][$schedule['employee_id']] = array();
				$ret_val['added_schedules'][$schedule['employee_id']] = array();
			}

			$ret_val['removed_schedules'][$schedule['employee_id']][$schedule['for_date']] = $schedule;
			$ret_val['added_schedules'][$schedule['employee_id']][$schedule['new_date']] = $schedule;
		}

		return $ret_val;
	}

	/**
	 * Returns the changed schedule of the given employee on a specific date
	 */
	public function getScheduleOfEmployeeOn($employeeID, $date)
	{
		$schedules = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'             => 'employee_id',
						'condition'         => '=',
						'value'             => $employeeID
					),
					array(
						'field'             => 'for_date',
						'condition'         => '=',
						'value'             => $date
					)
				) 
			)
		));

		return (0 < count($schedules)) ? $schedules[0] : NULL;
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
				'is_mandatory'      => true
			),
			'for_date'       => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'new_date'       => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'start_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'number_of_hours'   => array(
				'type'              => 'int',
				'default_value'     => 9				
			)
		);

		$this->setTableName('employee_changed_schedule');
		$this->setFields($fields, 'id');
	}
}