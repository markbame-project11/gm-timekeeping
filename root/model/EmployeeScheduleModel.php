<?php

/**
 * The model for the schedule of employee.
 * Possible problem with the implementation here is when there are so many information for employee schedule.
 */
class EmployeeScheduleModel extends CcBaseModel
{
	/**
	 * Returns the schedules of employee starting from the given date.
	 */
	public function getEmployeeSchedulesAfter($employee_id, $date)
	{
		return $this->find(array(
			'order_by'    => array(
				array(
					'order'  => 'desc',
					'field'  => 'start_date'
				)
			),
			'where'      => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'start_date',
						'condition'  => '<=',
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
	}

	/**
	 * Returns the schedule of employee on the given date.
	 *
	 * @return            Returns NULL if no schedule is set on $date or the object { 'start_time' : <start time in datetime format>, 'end_time' : <end time in datetime format>, 'number_of_hours' : <number of hours> }
	 */
	public function getEmployeeScheduleOn($employee_id, $date)
	{
		$results = $this->find(array(
			'order_by'    => array(
				array(
					'order'  => 'desc',
					'field'  => 'start_date'
				)
			),
			'limit'      => array(
				'numrecords'  => 1
			),
			'where'      => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'start_date',
						'condition'  => '<=',
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

		if (0 < count($results))
		{
			$sched = $results[0];
			$day = strtolower(date('D', strtotime($date)));

			if ($sched[$day.'_time'] != '00:00:00')
			{
				$start_dt = $date.' '.$sched[$day.'_time'];
				$start_dt_str_to_time = strtotime($start_dt);
				$end_dt = date('Y-m-d H:i:s', strtotime('+'.$sched[$day.'_num_hours'].'hours', $start_dt_str_to_time));

				return array(
					'start_time'       => $start_dt,
					'end_time'         => $end_dt,
					'number_of_hours'  => $sched[$day.'_num_hours']
				);
			}
		}

		return NULL;
	}

	/**
	 * Returns the schedules for the given employee wherein the key are the start dates.
	 */
	public function getEmployeeIDToScheduleMapping($employee_id, $start_date, $end_date)
	{
		$results1 = $this->find(array(
			'order_by'    => array(
				array(
					'order'  => 'desc',
					'field'  => 'start_date'
				)
			),
			'where'      => array(
				'relation'    => 'AND',
				'conditions'  => array(
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

		$schedules = array();
		if (0 < count($results1))
		{
			foreach ($results1 as $result)
			{
				$schedules[$result['start_date']] = $result;
			}
		}
		else
		{
			// get the latest sched for each employee
			// this is needed because normally the employee's schedule is only set when it is hired
			$sql =  'SELECT * FROM `'.$this->getTableName().'` ';
			$sql .= 'INNER JOIN ';
			$sql .= ' ( ';
			$sql .= '   SELECT MAX(start_date) start_date, employee_id ';
			$sql .= '   FROM `'.$this->getTableName().'` ';
			$sql .= '   GROUP BY employee_id ';
			$sql .= ' ) as max ';
			$sql .= 'ON max.employee_id = `'.$this->getTableName().'`.employee_id ';
			$sql .= 'AND max.start_date = `'.$this->getTableName().'`.start_date ';
			$sql .= 'WHERE `employee_schedule`.`employee_id` = ';
			$sql .= ' \''.$employee_id.'\' ';

			$results2 = array();
			try
			{
				$sth = $this->getPDO()->prepare($sql);
				$sth->execute();

				$results2 = $sth->fetchAll(PDO::FETCH_ASSOC);

				foreach ($results2 as $result)
				{
					$schedules[$result['start_date']] = $result;
				}
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


		// we will make it easier for the user of the function on how to 
		// index the result of this function call
		// {
		//   'sched_info': {
		//     'days_string': <days_string>,
		//     'time_string': <time string>
		//   },
		//   'days_data': {
		//     <date>: {
		//       'start_time': <start time>,
		//       'end_time': <end time>,
		//       'number_of_hours': <number of hours>
		//     }, ...
		//   }
		// }
		if (0 < count($schedules))
		{
			$days = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
			$start_time = strtotime($start_date);
			$end_time = strtotime($end_date);
			$ret_val = array();

			$latest_schedule = array_values($schedules)[0];
			$start_day = NULL;
			$inf_days_string = '';
			$inf_time_string = '';
			$numdays_per_week = 0;
			foreach ($days as $key => $day)
			{
				if ($latest_schedule[$day.'_time'] != '00:00:00')
				{
					$numdays_per_week++;

					if ($start_day == NULL)
					{
						$start_day = array(
							'day'  => $day,
							'time' => $latest_schedule[$day.'_time']
						);
					}
					else
					{
						if ($start_day['time'] != $latest_schedule[$day.'_time'])
						{
							$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
							$inf_days_string .= $start_day['day'].'-'.$day;

							$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
							$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule[$day.'_num_hours'].'hours', $sched_start_time)));
							
							$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
							$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);	

							$start_day = NULL;
						}
					}
				}
				else
				{
					if ($start_day != NULL)
					{
						$day = $days[$key - 1];
						$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
						$inf_days_string .= ($day != $start_day['day']) ? $start_day['day'].'-'.$day : $day;

						$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
						$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule[$day.'_num_hours'].'hours', $sched_start_time)));
						
						$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
						$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);	

						$start_day = NULL;
					}
				}
			}

			if ($start_day != NULL)
			{
				$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
				$inf_days_string .= ($day != $start_day['day']) ? $start_day['day'].'-sat': 'sat';

				$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
				$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule['sat_num_hours'].'hours', $sched_start_time)));
				
				$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
				$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);
			}			

			$ret_val['sched_info'] = array(
				'days_string'       => $inf_days_string,
				'time_string'       => $inf_time_string,
				'numdays_per_week'  => $numdays_per_week
			);
			$ret_val['days_data'] = array();

			for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
			{
				// check if the user has schedule on this day
				// 1. Find the nearest schedule 
				$employeeSched = NULL;
				foreach ($schedules as $sched_date => $employeeSchedule)
				{
					if (NULL == $employeeSched)
					{
						$employeeSched = $employeeSchedule;
					}
					else
					{
						if ($i >= strtotime($sched_date))
						{
							$employeeSched = $employeeSchedule;
						}
						else
						{
							break;
						}
					}
				}

				$dayToday = strtolower(date('D', $i));
				if ($employeeSched != NULL && $employeeSched[$dayToday.'_time'] != '00:00:00')
				{
					$sched_start_time = date('Y-m-d ', $i).$employeeSched[$dayToday.'_time'];
					$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$employeeSched[$dayToday.'_num_hours'].'hours', strtotime($sched_start_time)));

					$ret_val['days_data'][date('Y-m-d', $i)] = array(
						'start_time'       => $sched_start_time,
						'end_time'         => $sched_end_time,
						'number_of_hours'  => $employeeSched[$dayToday.'_num_hours']
					);
				}
			}
		}
		else
		{
			$ret_val['sched_info'] = array(
				'days_string'       => 'No schedule',
				'time_string'       => 'No schedule',
				'numdays_per_week'  => 0
			);
			$ret_val['days_data'] = array();	
		}

		return $ret_val;
	}

	/**
	 * Returns the schedules for employees wherein the department ID is equal to $department_id
	 */
	function getEmployeesInDepartmentIDToScheduleMapping($department_id, $start_date, $end_date)
	{
		$results1 = $this->find(array(
			'order_by'    => array(
				array(
					'order'  => 'desc',
					'field'  => 'start_date'
				)
			),
			'where'      => array(
				'relation'    => 'AND',
				'conditions'  => array(
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
						'condition'  => 'IN',
						'value'      => 'SELECT `empid` FROM employee WHERE `deptid` = '.((int)$department_id)
					)
				)
			)
		));

		// get the latest sched for each employee
		// this is needed because normally the employee's schedule is only set when it is hired
		$sql =  'SELECT * FROM `'.$this->getTableName().'` ';
		$sql .= 'INNER JOIN ';
		$sql .= ' ( ';
		$sql .= '   SELECT MAX(start_date) start_date, employee_id ';
		$sql .= '   FROM `'.$this->getTableName().'` ';
		$sql .= '   GROUP BY employee_id ';
		$sql .= ' ) as max ';
		$sql .= 'ON max.employee_id = `'.$this->getTableName().'`.employee_id ';
		$sql .= 'AND max.start_date = `'.$this->getTableName().'`.start_date ';
		$sql .= 'WHERE `employee_schedule`.`employee_id` IN ';
		$sql .= ' (';
		$sql .= '   SELECT `empid` FROM employee WHERE `deptid` = '.((int)$department_id);
		$sql .= ' )';

		$results2 = array();
		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute();

			$results2 = $sth->fetchAll(PDO::FETCH_ASSOC);
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

		$schedules = array();
		foreach ($results1 as $result)
		{
			if (!array_key_exists($result['employee_id'], $schedules))
			{
				$schedules[$result['employee_id']] = array();
			}
			
			$schedules[$result['employee_id']][$result['start_date']] = $result;
		}

		// merge results2 to results1 data
		foreach ($results2 as $result)
		{
			if (!array_key_exists($result['employee_id'], $schedules))
			{
				$schedules[$result['employee_id']][$result['start_date']] = $result;
			}
		}

		// we will make it easier for the user of the function on how to 
		// index the result of this function call
		// {
		//   <employee_id> : {
		//     'sched_info': {
		//       'days_string': <days_string>,
		//       'time_string': <time string>
		//     },
		//     'days_data': {
		//       <date>: {
		//         'start_time': <start time>,
		//         'end_time': <end time>,
		//         'number_of_hours': <number of hours>
		//       }, ...
		//     }
		//   }
		//   , ...
		// }
		$days = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		$ret_val = array();
		$employee_ids = array_keys($schedules);
		foreach ($employee_ids as $employee_id)
		{
			$ret_val[$employee_id] = array();

			$latest_schedule = array_values($schedules[$employee_id])[0];
			$start_day = NULL;
			$inf_days_string = '';
			$inf_time_string = '';
			$numdays_per_week = 0;
			foreach ($days as $key => $day)
			{
				if ($latest_schedule[$day.'_time'] != '00:00:00')
				{
					$numdays_per_week++;

					if ($start_day == NULL)
					{
						$start_day = array(
							'day'  => $day,
							'time' => $latest_schedule[$day.'_time']
						);
					}
					else
					{
						if ($start_day['time'] != $latest_schedule[$day.'_time'])
						{
							$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
							$inf_days_string .= $start_day['day'].'-'.$day;

							$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
							$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule[$day.'_num_hours'].'hours', $sched_start_time)));
							
							$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
							$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);	

							$start_day = NULL;
						}
					}
				}
				else
				{
					if ($start_day != NULL)
					{
						$day = $days[$key - 1];
						$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
						$inf_days_string .= ($day != $start_day['day']) ? $start_day['day'].'-'.$day : $day;

						$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
						$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule[$day.'_num_hours'].'hours', $sched_start_time)));
						
						$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
						$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);	

						$start_day = NULL;
					}
				}
			}

			if ($start_day != NULL)
			{
				$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
				$inf_days_string .= ($day != $start_day['day']) ? $start_day['day'].'-sat': 'sat';

				$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
				$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule['sat_num_hours'].'hours', $sched_start_time)));
				
				$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
				$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);
			}			

			$ret_val[$employee_id]['sched_info'] = array(
				'days_string'       => $inf_days_string,
				'time_string'       => $inf_time_string,
				'numdays_per_week'  => $numdays_per_week
			);
			$ret_val[$employee_id]['days_data'] = array();

			for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
			{
				// check if the user has schedule on this day
				// 1. Find the nearest schedule 
				$employeeSched = NULL;
				foreach ($schedules[$employee_id] as $sched_date => $employeeSchedule)
				{
					if (NULL == $employeeSched)
					{
						$employeeSched = $employeeSchedule;
					}
					else
					{
						if ($i >= strtotime($sched_date))
						{
							$employeeSched = $employeeSchedule;
						}
						else
						{
							break;
						}
					}
				}

				$dayToday = strtolower(date('D', $i));
				if ($employeeSched != NULL && $employeeSched[$dayToday.'_time'] != '00:00:00')
				{
					$sched_start_time = date('Y-m-d ', $i).$employeeSched[$dayToday.'_time'];
					$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$employeeSched[$dayToday.'_num_hours'].'hours', strtotime($sched_start_time)));

					$ret_val[$employee_id]['days_data'][date('Y-m-d', $i)] = array(
						'start_time'       => $sched_start_time,
						'end_time'         => $sched_end_time,
						'number_of_hours'  => $employeeSched[$dayToday.'_num_hours']
					);
				}
			}
		}

		return $ret_val;
	}

	/**
	 * Returns the schedules for employees wherein the key is the ID of the employee.
	 */
	function getEmployeesIDToScheduleMapping($start_date, $end_date)
	{
		$results1 = $this->find(array(
			'order_by'    => array(
				array(
					'order'  => 'desc',
					'field'  => 'start_date'
				)
			),
			'where'      => array(
				'relation'    => 'AND',
				'conditions'  => array(
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

		// get the latest sched for each employee
		// this is needed because normally the employee's schedule is only set when it is hired
		$sql =  'SELECT * FROM `'.$this->getTableName().'` ';
		$sql .= 'INNER JOIN ';
		$sql .= '( ';
		$sql .= '   SELECT MAX(start_date) start_date, employee_id ';
		$sql .= '   FROM `'.$this->getTableName().'` ';
		$sql .= '   GROUP BY employee_id ';
		$sql .= ') as max ';
		$sql .= 'ON max.employee_id = `'.$this->getTableName().'`.employee_id ';
		$sql .= 'AND max.start_date = `'.$this->getTableName().'`.start_date ';

		$results2 = array();
		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute();

			$results2 = $sth->fetchAll(PDO::FETCH_ASSOC);
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

		$schedules = array();
		foreach ($results1 as $result)
		{
			if (!array_key_exists($result['employee_id'], $schedules))
			{
				$schedules[$result['employee_id']] = array();
			}
			
			$schedules[$result['employee_id']][$result['start_date']] = $result;
		}

		// merge results2 to results1 data
		foreach ($results2 as $result)
		{
			if (!array_key_exists($result['employee_id'], $schedules))
			{
				$schedules[$result['employee_id']][$result['start_date']] = $result;
			}
		}

		// we will make it easier for the user of the function on how to 
		// index the result of this function call
		// {
		//   <employee_id> : {
		//     'sched_info': {
		//       'days_string': <days_string>,
		//       'time_string': <time string>
		//     },
		//     'days_data': {
		//       <date>: {
		//         'start_time': <start time>,
		//         'end_time': <end time>,
		//         'number_of_hours': <number of hours>
		//       }, ...
		//     }
		//   }
		//   , ...
		// }
		$days = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
		$start_time = strtotime($start_date);
		$end_time = strtotime($end_date);
		$ret_val = array();
		$employee_ids = array_keys($schedules);
		foreach ($employee_ids as $employee_id)
		{
			$ret_val[$employee_id] = array();

			$latest_schedule = array_values($schedules[$employee_id])[0];
			$start_day = NULL;
			$inf_days_string = '';
			$inf_time_string = '';
			$numdays_per_week = 0;
			foreach ($days as $key => $day)
			{
				if ($latest_schedule[$day.'_time'] != '00:00:00')
				{
					$numdays_per_week++;

					if ($start_day == NULL)
					{
						$start_day = array(
							'day'  => $day,
							'time' => $latest_schedule[$day.'_time']
						);
					}
					else
					{
						if ($start_day['time'] != $latest_schedule[$day.'_time'])
						{
							$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
							$inf_days_string .= $start_day['day'].'-'.$day;

							$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
							$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule[$day.'_num_hours'].'hours', $sched_start_time)));
							
							$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
							$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);	

							$start_day = NULL;
						}
					}
				}
				else
				{
					if ($start_day != NULL)
					{
						$day = $days[$key - 1];
						$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
						$inf_days_string .= ($day != $start_day['day']) ? $start_day['day'].'-'.$day : $day;

						$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
						$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule[$day.'_num_hours'].'hours', $sched_start_time)));
						
						$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
						$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);	

						$start_day = NULL;
					}
				}
			}

			if ($start_day != NULL)
			{
				$inf_days_string .= ($inf_days_string != '') ? ', ' : '';
				$inf_days_string .= ($day != $start_day['day']) ? $start_day['day'].'-sat': 'sat';

				$sched_start_time = strtotime(date('Y-m-d ').$latest_schedule[$day.'_time']);
				$sched_end_time = strtotime(date('Y-m-d H:i:s', strtotime('+'.$latest_schedule['sat_num_hours'].'hours', $sched_start_time)));
				
				$inf_time_string .= ($inf_time_string != '') ? ', ' : '';
				$inf_time_string .= date('H:i', $sched_start_time).'-'.date('H:i', $sched_end_time);
			}			

			$ret_val[$employee_id]['sched_info'] = array(
				'days_string'       => $inf_days_string,
				'time_string'       => $inf_time_string,
				'numdays_per_week'  => $numdays_per_week
			);
			$ret_val[$employee_id]['days_data'] = array();

			for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
			{
				// check if the user has schedule on this day
				// 1. Find the nearest schedule 
				$employeeSched = NULL;
				foreach ($schedules[$employee_id] as $sched_date => $employeeSchedule)
				{
					if (NULL == $employeeSched)
					{
						$employeeSched = $employeeSchedule;
					}
					else
					{
						if ($i >= strtotime($sched_date))
						{
							$employeeSched = $employeeSchedule;
						}
						else
						{
							break;
						}
					}
				}

				$dayToday = strtolower(date('D', $i));
				if ($employeeSched != NULL && $employeeSched[$dayToday.'_time'] != '00:00:00')
				{
					$sched_start_time = date('Y-m-d ', $i).$employeeSched[$dayToday.'_time'];
					$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$employeeSched[$dayToday.'_num_hours'].'hours', strtotime($sched_start_time)));

					$ret_val[$employee_id]['days_data'][date('Y-m-d', $i)] = array(
						'start_time'       => $sched_start_time,
						'end_time'         => $sched_end_time,
						'number_of_hours'  => $employeeSched[$dayToday.'_num_hours']
					);
				}
			}
		}

		return $ret_val;
	}

	/**
	 * Returns the current schedule for employee
	 */
	function getLastEmployeeSchedule($employee_id)
	{
		$schedules = $this->find(array(
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					),
					array(
						'field'        => 'start_date',
						'condition'    => 'IN',
						'value'        => 'SELECT MAX(start_date) FROM `'.$this->getTableName().'` WHERE `employee_id` = '.$employee_id.' '
					)			
				) 
			)
		));

		return (0 < count($schedules)) ? $schedules[0] : NULL;
	}

	/**
	 * Returns the schedule of employee in the given date.
	 */
	function getEmployeeScheduleWithStartDate($employee_id, $start_date)
	{
		$schedules = $this->find(array(
			'limit'    => array(
				'numrecords'   => 1
			),
			'where'       => array(
				'relation'    => 'AND',
				'conditions'  => array(
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					),
					array(
						'field'      => 'start_date',
						'condition'  => '=',
						'value'      => $start_date
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
			'schedule_id'     => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'employee_id'    => array(
				'type'              => 'int',
				'is_mandatory'      => true
			),
			'start_date'     => array(
				'type'              => 'string',
				'is_mandatory'      => true
			),
			'sun_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'mon_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'tue_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'wed_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'thu_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'fri_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'sat_time'   => array(
				'type'              => 'string',
				'default_value'     => '00:00:00'					
			),
			'sun_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			),
			'mon_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			),
			'tue_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			),
			'wed_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			),
			'thu_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			),
			'fri_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			),
			'sat_num_hours'   => array(
				'type'              => 'int',
				'default_value'     => '0'					
			)
		);

		$this->setTableName('employee_schedule');
		$this->setFields($fields, 'schedule_id');
	}
}