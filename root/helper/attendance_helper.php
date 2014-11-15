<?php
/**
 * Find the lowest dates from the consolidated paid leaves.
 */
function find_lowest_date_from_consolidated_paid_leaves($consolidatedLeaves)
{
	$lowestDateToTime = NULL;

	foreach ($consolidatedLeaves['paid']['vacation'] as $leave)
	{
		if (NULL == $lowestDateToTime)
		{
			$lowestDateToTime = strtotime($leave['date']);
		}
		else if (strtotime($leave['date']) < $lowestDateToTime)
		{
			$lowestDateToTime = strtotime($leave['date']);
		}
	}

	foreach ($consolidatedLeaves['paid']['sick'] as $leave)
	{
		if (strtotime($leave['date']) < $lowestDateToTime)
		{
			$lowestDateToTime = strtotime($leave['date']);
		}
	}

	return (NULL == $lowestDateToTime) ? NULL : date('Y-m-d', $lowestDateToTime);
}

/**
 * Calculates the remaining paid leaves of the user.
 */
function calculate_remaining_paid_leaves($employee, $consolidatedLeaves, $employeeSchedules)
{
	$dateHiredToTime = strtotime($employee['date_hired']);

	$dateHiredDT = new DateTime($employee['date_hired'].' 00:00:00');
	$curYearDT = new DateTime(date('Y-').'01-01 00:00:00');
	$curDateDT = new DateTime(date('Y-m-d H:i:s'));

	$d2 = (date('Y') == date('Y', $dateHiredToTime)) ? $dateHiredDT : $curYearDT;
	$tDInterval = date_diff($curDateDT, $d2);

	$earnedLeaves = ($tDInterval->m * (7.5 / 12.0));
	$VLLeavesTaken = count($consolidatedLeaves['paid']['vacation']);
	$SLLeavesTaken = count($consolidatedLeaves['paid']['sick']);

	return array(
		'vacation'      => $earnedLeaves - $VLLeavesTaken,
		'sick'          => $earnedLeaves - $SLLeavesTaken
	);
}

/**
 * Calculate using the schedules, last timeout and changed schedules whether to show timein or not.
 */
function calculate_show_timein($employeeTimesheet, $employeeSchedules, $changedSchedules)
{
	if (
		($employeeTimesheet == NULL) ||
		(!is_array($employeeTimesheet)) ||
		($employeeTimesheet['checkout'] != '0000-00-00 00:00:00' && substr($employeeTimesheet['checkin'], 0, 10) != date('Y-m-d'))
	)
	{
		return true;
	}
	else if ($employeeTimesheet['checkout'] == '0000-00-00 00:00:00')
	{
		if (substr($employeeTimesheet['checkin'], 0, 10) == date('Y-m-d'))
		{
			return false;
		}
		else if (array_key_exists(date('Y-m-d'), $employeeSchedules['days_data']))
		{
			$iDate = date('Y-m-d');
			$schedule =  $employeeSchedules['days_data'][$iDate];

			// check first in the changed schedules before checking the user schedule
			if (array_key_exists($iDate, $changedSchedules['removed_schedules']))
			{
				if (array_key_exists($iDate, $changedSchedules['added_schedules']))
				{
					$sched_start_time = $iDate.$changedSchedules['added_schedules'][$iDate]['start_time'];
					$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$iDate]['number_of_hours'].'hours', strtotime($sched_start_time)));

					// changing of schedule time happened
					$schedule = array(
						'start_time'       => $sched_start_time,
						'end_time'         => $sched_end_time,
						'number_of_hours'  => $changedSchedules['added_schedules'][$iDate]['number_of_hours'],
					);
				}
				else
				{
					$schedule = NULL; // no sched for today
				}
			}
			else if (array_key_exists($iDate, $changedSchedules['added_schedules']))
			{
				$sched_start_time = $iDate.$changedSchedules['added_schedules'][$iDate]['start_time'];
				$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$iDate]['number_of_hours'].'hours', strtotime($sched_start_time)));

				// changing of schedule time happened
				$schedule = array(
					'start_time'       => $sched_start_time,
					'end_time'         => $sched_end_time,
					'number_of_hours'  => $changedSchedules['added_schedules'][$iDate]['number_of_hours'],
				);
			}
			// end of checking of changed schedules

			if ($schedule == NULL)
			{
				return false;
			}
			else
			{
				$schedStartDT = new DateTime($schedule['start_time']);
				$curDateDT = new DateTime(date('Y-m-d H:i:s'));

				if ($curDateDT >= $schedStartDT)
				{
					return true;
				}
				else
				{
					$tDInterval = date_diff($schedStartDT, $curDateDT);
					$hours_diff = (float)$tDInterval->h + (float)((float)$tDInterval->i / 60.0);

					return ($hours_diff <= 2.0) ? true : false;
				}
			}
		}
		else
		{
			// This is a rest day or something
			// Let the user checkout whenever the user wants
			return false;
		}
	}
	else
	{
		return false;	
	}
}

/**
 * Find $num_dates dates that are working date starting from $start_date using the given schedules.
 */
function find_working_dates($start_date, $num_days, $schedules, $date_format = 'M d, Y')
{
	$dates = array();

	for ($i = 0, $j = strtotime($start_date); $i < $num_days; $j = strtotime('+1day', $j))
	{
		$day = strtolower(date('D', $j));

		// find in the schedule the correct sched for the current time
		$sched = NULL;
		foreach ($schedules as $schedule)
		{
			if (
				($j > strtotime($schedule['start_date'])) ||
				($j == strtotime($schedule['start_date']))
			)
			{
				$sched = $schedule;
				break;
			}
		}

		// NULL should be impossible because of previous check
		if ($sched[$day.'_time'] != '00:00:00')
		{
			$dates[] = date($date_format, $j);
			$i++;
		}
		else
		{
			// no schedule for this date
		}
	}

	return $dates;
}

/**
 * Builds the list of attendance of the given employee and their corresponding notes
 *
 * Note: this needs generic helper to work
 */

function build_employee_attendance($employee, $employeeSchedules, $employeeTimesheets, $start_date, $end_date, $leaves, $holidays, $changedSchedules, $holidayPays, $otPays)
//function build_employee_attendance($employee , $employeeSchedules, $employeeTimesheets, $start_date, $end_date, $leaves = '', $holidays = '', $changedSchedules = '', $holidayPays = '', $otPays = '')

{
	$ret_val = array(
		'sched'           => array(
			'days'    => NULL,
			'time'    => NULL
		),
		'timesheets'      => array()
	);

	$ret_val['sched']['days'] = $employeeSchedules['sched_info']['days_string'];
	$ret_val['sched']['time'] = $employeeSchedules['sched_info']['time_string'];

	$start_time = strtotime($start_date);
	$end_time = strtotime($end_date);
	for ($i = $end_time; $i >= $start_time; $i = strtotime('-1day', $i))
	{
		$iDateTime = date('Y-m-d H:i:s', $i);
		$iDate = date('Y-m-d', $i);

		$holidayPay = NULL;
		if (array_key_exists($iDate, $holidayPays))
		{
			$holidayPay = $holidayPays[$iDate];
		}

		$hasOTPay = false;
		if (array_key_exists($iDate, $otPays))
		{
			$hasOTPay = true;
		}

		$employeeSchedForTheDay = NULL;
		if (array_key_exists($iDate, $employeeSchedules['days_data']))
		{
			$employeeSchedForTheDay = $employeeSchedules['days_data'][$iDate];
		}

		// if the employee is in leave
		if ($employeeSchedForTheDay != NULL && array_key_exists($iDate, $leaves) && array_key_exists($employee['empid'], $leaves[$iDate]))
		{
			$leave_types = array(
				'vacation'  => 'VL',
				'sick'      => 'SL',
				'emergency' => 'EL'
			);

			if (in_array($leaves[$iDate][$employee['empid']]['leave_type'], $leave_types))
			{
				$leave_type = $leave_types[$leaves[$iDate][$employee['empid']]['leave_type']];

				$ret_val['timesheets'][$iDate] = array(
					'time_in'         => $leave_type,
					'time_out'        => $leave_type,
					'total'           => $leave_type,
					'status'          => $leave_type,
					'spent_hours'     => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1) ? (float)$employeeSchedForTheDay['number_of_hours'] : 0.0,
					'nightdiff_hours' => 0.0,
					'is_paid'         => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1)
				);

				continue;
			}
		}

		// check first in the changed schedules before checking the user schedule
		if (array_key_exists($iDate, $changedSchedules['removed_schedules']))
		{
			if (array_key_exists($iDate, $changedSchedules['added_schedules']))
			{
				$sched_start_time = $iDate.$changedSchedules['added_schedules'][$iDate]['start_time'];
				$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][date('Y-m-d', $i)]['number_of_hours'].'hours', strtotime($sched_start_time)));

				// changing of schedule time happened
				$employeeSchedForTheDay = array(
					'start_time'       => $sched_start_time,
					'end_time'         => $sched_end_time,
					'number_of_hours'  => $changedSchedules['added_schedules'][$iDate]['number_of_hours'],
				);
			}
			else
			{
				$employeeSchedForTheDay = NULL; // no sched for today
			}
		}
		else if (array_key_exists($iDate, $changedSchedules['added_schedules']))
		{
			$sched_start_time = $iDate.$changedSchedules['added_schedules'][$iDate]['start_time'];
			$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$iDate]['number_of_hours'].'hours', strtotime($sched_start_time)));

			// changing of schedule time happened
			$employeeSchedForTheDay = array(
				'start_time'       => $sched_start_time,
				'end_time'         => $sched_end_time,
				'number_of_hours'  => $changedSchedules['added_schedules'][$iDate]['number_of_hours'],
			);
		}
		// end of checking of changed schedules

		if (array_key_exists($iDate, $employeeTimesheets))
		{
			$tsheet = $employeeTimesheets[$iDate];

			$d1 = new DateTime($tsheet['checkout']); 
			$d2 = new DateTime($tsheet['checkin']);

			if ($employeeSchedForTheDay != NULL)
			{
				$sched_start_time = $employeeSchedForTheDay['start_time'];
				$sched_end_time = $employeeSchedForTheDay['end_time'];

				$late_in_mins = (int)((int)(strtotime($tsheet['checkin']) - strtotime($sched_start_time)) / 60);
				// if ($late_in_mins > 0 && $number_of_applied_grace_period <= 3)
				// {
				// 	$late_in_mins = ($late_in_mins > 5) ? $late_in_mins - $grace_period_in_mins : 0;
				// 	$number_of_applied_grace_period++;
				// }
				
				$schedStartDT = new DateTime($sched_start_time);
				$schedEndDT = new DateTime($sched_end_time);
				$tDT1 = ($d2 > $schedStartDT) ? $d2 : $schedStartDT;
				$tDT2 = ($d1 < $schedEndDT) ? $d1 : $schedEndDT;

                //echo $sched_start_time . ' ' . $sched_end_time . '<br/>';

				$tDInterval = date_diff($tDT2, $tDT1);

				$total_hours = (float)$tDInterval->h + (float)((float)$tDInterval->i / 60.0);

				// calculate night diff
				$d3 = new DateTime(date('Y-m-d', strtotime($tsheet['checkin'])).' 22:00:00'); // 10pm
				$d4 = new DateTime(date('Y-m-d', strtotime($tsheet['checkout'])).' 06:00:00'); // 6am

				$nightDiffHours = 0.0;
				$nighDiffDTStart = ($tDT1 > $d3) ? $tDT1 : $d3;
				$nighDiffDTEnd = ($tDT2 < $d4) ? $tDT2 : $d4;
				if ($nighDiffDTStart < $nighDiffDTEnd)
				{
					$nightDiffDTInterval = date_diff($nighDiffDTEnd, $nighDiffDTStart);
					$nightDiffHours = (float)$nightDiffDTInterval->h + (float)((float)$nightDiffDTInterval->i / 60.0);
				}

				// if the employee haven't checked out.
				if ($tsheet['checkin'] != '0000-00-00 00:00:00' && $tsheet['checkout'] == '0000-00-00 00:00:00')
				{
					if (date('Y-m-d') == $iDate)
					{
						$ret_val['timesheets'][$iDate] = array(
							'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
							'time_out'        => ' --- ',
							'total'           => ' --- ',
							'status'          => ' --- ',
							'spent_hours'     => 0.0,
							'nightdiff_hours' => 0.0
						);
					}
					else
					{
						$ret_val['timesheets'][$iDate] = array(
							'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
							'time_out'        => 'No Checkout',
							'total'           => 'No Checkout',
							'status'          => 'NC',
							'spent_hours'     => 0.0,
							'nightdiff_hours' => 0.0
						);
					}
				}

				// if the employee is half day leave
				else if (array_key_exists($iDate, $leaves) && array_key_exists($employee['empid'], $leaves[$iDate]) && $leaves[$iDate][$employee['empid']]['leave_type'] = 'half_day')
				{
					$ret_val['timesheets'][$iDate] = array(
						'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'        => date('H:i:s', strtotime($tsheet['checkout'])),
						'total'           => $tDInterval->format('%hh %im %ss'),
						'status'          => 'HD',
						'spent_hours'     => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1) ? ((float)$employeeSchedForTheDay['number_of_hours']) / 2.0: 0.0,
						'nightdiff_hours' => 0.0,
						'is_paid'         => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1)
					);
				}

				// late
				else if ($late_in_mins > 0)
				{
					// todo: a certain length of time in late will be half day

					$late_in_hour = CalculateLateInHours(round($late_in_mins));
					$late_in_hour = ($late_in_hour == 'HD') ? (((float)$employeeSchedForTheDay['number_of_hours']) / 0.5) : $late_in_hour;

					$ret_val['timesheets'][$iDate] = array(
						'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'        => date('H:i:s', strtotime($tsheet['checkout'])),
						'total'           => $tDInterval->format('%hh %im %ss'),
						'status'          => 'L',
						'status_val'      => $late_in_hour,
						'spent_hours'     => ($total_hours - 1.0),
						'nightdiff_hours' => $nightDiffHours
					);			
				}

				// undertime
				else if ($total_hours < (float) $employeeSchedForTheDay['number_of_hours'])
				{
					$ret_val['timesheets'][$iDate] = array(
						'time_in'            => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'           => date('H:i:s', strtotime($tsheet['checkout'])),
						'total'              => $tDInterval->format('%hh %im %ss'),
						'status'             => 'U',
						'total_hours'        => $total_hours,
						'sched_total_hours'  => (float) $employeeSchedForTheDay['number_of_hours'],
						'spent_hours'        => ($total_hours - 1.0),
						'nightdiff_hours'    => $nightDiffHours
					);
				}

				// present
				// this appears in index				
				else
				{
                    //$tDInterval = date_diff(date('H:i:s', strtotime($tsheet['checkout'])), date('H:i:s', strtotime($tsheet['checkin'])));					
					$d1 = new DateTime($tsheet['checkout']); 
					$d2 = new DateTime($tsheet['checkin']);
				    $tDInterval = date_diff($d2, $d1);

					$ret_val['timesheets'][$iDate] = array(
						'time_in'            => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'           => date('H:i:s', strtotime($tsheet['checkout'])),
						'total'              => $tDInterval->format('%hh %im %ss'),
						'status'             => 'PR',
						'spent_hours'        => ($total_hours - 1.0),
						'nightdiff_hours'    => $nightDiffHours
					);
				}

				if ($holidayPay != NULL)
				{
					$ret_val['timesheets'][$iDate]['has_holiday_pay'] = true;
					$ret_val['timesheets'][$iDate]['holiday_type'] = $holidayPay['type'];	
				}
				else
				{
					$ret_val['timesheets'][$iDate]['has_holiday_pay'] = false;
				}

				if ($hasOTPay)
				{
					$checkin_str_to_time = strtotime($tsheet['checkin']);
					$checkout_str_to_time = strtotime($tsheet['checkout']);
					$start_ot_str_to_time = strtotime('+'.$employeeSchedForTheDay['number_of_hours'].'hours', $checkin_str_to_time);
					$night_shift_start_str_to_time = strtotime(date('Y-m-d', strtotime($tsheet['checkin'])).' 22:00:00');

					// no OT
					if ($start_ot_str_to_time >= $checkout_str_to_time)
					{
						$ret_val['timesheets'][$iDate]['has_ot_pay'] = false;
					}
					// no night shift OT
					else if ($checkout_str_to_time <= $night_shift_start_str_to_time)
					{
						$ret_val['timesheets'][$iDate]['has_ot_pay'] = true;

						$startOTDT = new DateTime(date('Y-m-d H:i:s', $start_ot_str_to_time));
						$endOTDT = new DateTime(date('Y-m-d H:i:s', $checkout_str_to_time));
						$otInterval = date_diff($endOTDT, $startOTDT);

						$total_hours_ot = (float)$otInterval->h + (float)((float)$otInterval->i / 60.0);

						$ret_val['timesheets'][$iDate]['normal_ot_hours'] = $total_hours_ot;
						$ret_val['timesheets'][$iDate]['night_differential_ot_hours'] = 0.0;
					}
					// night shift OT found
					else
					{
						$ret_val['timesheets'][$iDate]['has_ot_pay'] = true;

						$startNormalOTDT = new DateTime(date('Y-m-d H:i:s', $start_ot_str_to_time));
						$endNormalOTDT = new DateTime(date('Y-m-d H:i:s', $night_shift_start_str_to_time));
						$otNormalInterval = date_diff($endNormalOTDT, $startNormalOTDT);
						$total_normal_hours_ot = (float)$otNormalInterval->h + (float)((float)$otNormalInterval->i / 60.0);
						$ret_val['timesheets'][$iDate]['normal_ot_hours'] = $total_normal_hours_ot;

						$startNightshiftOTDT = new DateTime(date('Y-m-d H:i:s', $night_shift_start_str_to_time));
						$endNightshiftOTDT = new DateTime(date('Y-m-d H:i:s', $checkout_str_to_time));
						$otNightshiftInterval = date_diff($endNightshiftOTDT, $startNightshiftOTDT);
						$total_nightshift_hours_ot = (float)$otNightshiftInterval->h + (float)((float)$otNightshiftInterval->i / 60.0);
						$ret_val['timesheets'][$iDate]['night_differential_ot_hours'] = $total_nightshift_hours_ot;
					}
				}
				else
				{
					$ret_val['timesheets'][$iDate]['has_ot_pay'] = false;
				}
			}
			else
			{
				// if the user is REST DAY but still go to work, it wont be counted as work but REST DAY
				$ret_val['timesheets'][$iDate] = array(
					'time_in'         => 'RD',
					'time_out'        => 'RD',
					'total'           => 'RD',
					'status'          => 'RD',
					'has_holiday_pay' => false,
					'spent_hours'     => 0.0,
					'nightdiff_hours' => 0.0
				);
			}
		}
		else
		{
			if ($employeeSchedForTheDay != NULL)
			{
				// holiday for employee that isn't rest day on this day
				if (array_key_exists($iDate, $holidays))
				{
					$ret_val['timesheets'][$iDate] = array(
						'time_in'         => 'HL',
						'time_out'        => 'HL',
						'total'           => 'HL',
						'status'          => 'HL',
						'spent_hours'     => ($employeeSchedForTheDay != NULL) ? 8.0 : 0.0,
						'nightdiff_hours' => 0.0,
						'has_holiday_pay' => false
					);
				}
				// absent
				else
				{
					if ($iDate == date('Y-m-d'))
					{
						// no generalization for current date
						$ret_val['timesheets'][$iDate] = array(
							'time_in'         => ' --- ',
							'time_out'        => ' --- ',
							'total'           => ' --- ',
							'status'          => ' --- ',
							'spent_hours'     => 0.0,
							'nightdiff_hours' => 0.0,
							'has_holiday_pay' => false
						);
					}
					else
					{
						$ret_val['timesheets'][$iDate] = array(
							'time_in'         => 'A',
							'time_out'        => 'A',
							'total'           => 'A',
							'status'          => 'A',
							'spent_hours'     => 0.0,
							'nightdiff_hours' => 0.0,
							'has_holiday_pay' => false
						);
					}
				}
			}
			else
			{
				if ($employeeSchedules['sched_info']['days_string'] == 'No schedule')
				{
					// employee is in rest day
					$ret_val['timesheets'][$iDate] = array(
						'time_in'         => 'No Schedule',
						'time_out'        => 'No Schedule',
						'total'           => 'No Schedule',
						'status'          => 'No Schedule',
						'spent_hours'     => 0.0,
						'nightdiff_hours' => 0.0,
						'has_holiday_pay' => false
					);
				}
				else
				{
					// employee is in rest day
					$ret_val['timesheets'][$iDate] = array(
						'time_in'         => 'RD',
						'time_out'        => 'RD',
						'total'           => 'RD',
						'status'          => 'RD',
						'spent_hours'     => 0.0,
						'nightdiff_hours' => 0.0,
						'has_holiday_pay' => false
					);
				}
			}
		}
	}

	return $ret_val;	
}

/**
 * Builds employees attendance for the day. 
 *
 * Note: this needs generic helper to work
 * 
 * Data construction:
 *   $leaves      -> { <date> : { <employee id> : { 'leave_type' : <leave type>, 'is_paid' : <is_paid> }, ... }, ... }
 *   $holidayPays -> { <date> : { <employee id> : { 'type' : <holiday type>, 'name' : <holiday name> }, ... }, ... }
 */
function build_employees_attendance_for_the_day($employees, $employeeSchedules, $employeeTimesheets, $holiday, $changedSchedules, $leaves)
{
	$ret_val = array();

	foreach ($employees as $employee)
	{
		$ret_val[$employee['empid']] = array(
			'lastname'        => $employee['lastname'],
			'firstname'       => $employee['firstname'],
			'deptid'          => $employee['deptid'],
			'date_hired'      => $employee['date_hired'],
			'sched'           => array(
				'days'    => NULL,
				'time'    => NULL
			),
			'timesheet'       => array()
		);

		if (array_key_exists($employee['empid'], $employeeSchedules))
		{
			$ret_val[$employee['empid']]['sched']['days'] = $employeeSchedules[$employee['empid']]['sched_info']['days_string'];
			$ret_val[$employee['empid']]['sched']['time'] = $employeeSchedules[$employee['empid']]['sched_info']['time_string'];
		}
		else
		{
			$ret_val[$employee['empid']]['sched']['days'] = NULL;
			$ret_val[$employee['empid']]['sched']['time'] = NULL;
		}

		$number_of_applied_grace_period = 0;
		$iDateTime = date('Y-m-d H:i:s');
		$iDate = date('Y-m-d');

		$employeeSchedForTheDay = NULL;
		if (array_key_exists($employee['empid'], $employeeSchedules) && array_key_exists($iDate, $employeeSchedules[$employee['empid']]['days_data']))
		{
			$employeeSchedForTheDay = $employeeSchedules[$employee['empid']]['days_data'][$iDate];
		}

		// if the employee is in leave
		if ($employeeSchedForTheDay != NULL && array_key_exists($iDate, $leaves) && array_key_exists($employee['empid'], $leaves[$iDate]))
		{
			$leave_types = array(
				'vacation'  => 'VL',
				'sick'      => 'SL',
				'emergency' => 'EL'
			);

			if (in_array($leaves[$iDate][$employee['empid']]['leave_type'], $leave_types))
			{
				$leave_type = $leave_types[$leaves[$iDate][$employee['empid']]['leave_type']];

				$ret_val[$employee['empid']]['timesheet'] = array(
					'time_in'         => $leave_type,
					'time_out'        => $leave_type,
					'total'           => $leave_type,
					'status'          => $leave_type
				);

				continue;
			}
		}

		// check first in the changed schedules before checking the user schedule
		if (
			array_key_exists($employee['empid'], $changedSchedules['removed_schedules']) && 
			array_key_exists($iDate, $changedSchedules['removed_schedules'][$employee['empid']])
		)
		{
			if (
				array_key_exists($employee['empid'], $changedSchedules['added_schedules']) && 
				array_key_exists($iDate, $changedSchedules['added_schedules'][$employee['empid']])
			)
			{
				$sched_start_time = $iDate.$changedSchedules['added_schedules'][$employee['empid']][$iDate]['start_time'];
				$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$employee['empid']][date('Y-m-d', $i)]['number_of_hours'].'hours', strtotime($sched_start_time)));

				// changing of schedule time happened
				$employeeSchedForTheDay = array(
					'start_time'       => $sched_start_time,
					'end_time'         => $sched_end_time,
					'number_of_hours'  => $changedSchedules['added_schedules'][$employee['empid']][$iDate]['number_of_hours'],
				);
			}
			else
			{
				$employeeSchedForTheDay = NULL; // no sched for today
			}
		}
		else if (array_key_exists($employee['empid'], $changedSchedules['added_schedules']) && array_key_exists($iDate, $changedSchedules['added_schedules'][$employee['empid']]))
		{
			$sched_start_time = $iDate.$changedSchedules['added_schedules'][$employee['empid']][$iDate]['start_time'];
			$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$employee['empid']][$iDate]['number_of_hours'].'hours', strtotime($sched_start_time)));

			// changing of schedule time happened
			$employeeSchedForTheDay = array(
				'start_time'       => $sched_start_time,
				'end_time'         => $sched_end_time,
				'number_of_hours'  => $changedSchedules['added_schedules'][$employee['empid']][$iDate]['number_of_hours'],
			);
		}
		// end of checking of changed schedules

		if (array_key_exists($employee['empid'], $employeeTimesheets[$iDate]))
		{
			$tsheet = $employeeTimesheets[$iDate][$employee['empid']];

			if ($employeeSchedForTheDay != NULL)
			{
				$sched_start_time = $employeeSchedForTheDay['start_time'];
				$sched_end_time = $employeeSchedForTheDay['end_time'];

				$late_in_mins = (int)((int)(strtotime($tsheet['checkin']) - strtotime($sched_start_time)) / 60);
				// if ($late_in_mins > 0 && $number_of_applied_grace_period <= 3)
				// {
				// 	$late_in_mins = ($late_in_mins > 5) ? $late_in_mins - $grace_period_in_mins : 0;
				// 	$number_of_applied_grace_period++;
				// }

				// if the employee is half day leave
				if (array_key_exists($iDate, $leaves) && array_key_exists($employee['empid'], $leaves[$iDate]) && $leaves[$iDate][$employee['empid']]['leave_type'] = 'half_day')
				{
					$ret_val[$employee['empid']]['timesheet'] = array(
						'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'        => ($tsheet['checkout'] == '0000-00-00 00:00:00') ? '---' : date('H:i:s', strtotime($tsheet['checkout'])),
						'status'          => 'HD'
					);
				}

				// late
				else if ($late_in_mins > 0)
				{
					// todo: a certain length of time in late will be half day

					$ret_val[$employee['empid']]['timesheet'] = array(
						'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'        => ($tsheet['checkout'] == '0000-00-00 00:00:00') ? '---' : date('H:i:s', strtotime($tsheet['checkout'])),
						'status'          => 'L'
					);			
				}

				// present
				else
				{
					$ret_val[$employee['empid']]['timesheet'] = array(
						'time_in'            => date('H:i:s', strtotime($tsheet['checkin'])),
						'time_out'           => ($tsheet['checkout'] == '0000-00-00 00:00:00') ? '---' : date('H:i:s', strtotime($tsheet['checkout'])),
						'status'             => 'PR'
					);
				}
			}
			else
			{
				// if the user is REST DAY but still go to work, it wont be counted as work but REST DAY
				$ret_val[$employee['empid']]['timesheet'] = array(
					'time_in'         => 'RD',
					'time_out'        => 'RD',
					'status'          => 'RD'
				);
			}
		}
		else
		{
			if ($employeeSchedForTheDay != NULL)
			{
				// holiday for employee that isn't rest day on this day
				if ($holiday != NULL)
				{
					$ret_val[$employee['empid']]['timesheet'] = array(
						'time_in'         => 'HL',
						'time_out'        => 'HL',
						'status'          => 'HL'
					);
				}
				// absent
				else
				{
					$ret_val[$employee['empid']]['timesheet'] = array(
						'time_in'         => '---',
						'time_out'        => '---',
						'status'          => 'A'
					);
				}
			}
			else
			{
				// employee is in rest day
				$ret_val[$employee['empid']]['timesheet']= array(
					'time_in'         => 'RD',
					'time_out'        => 'RD',
					'status'          => 'RD'
				);
			}
		}
	}

	return $ret_val;
}

/**
 * Builds the timesheet status of each $employees for each date between $start_date and $end_date inclusive.
 *
 * Note: this needs generic helper to work
 * 
 * Data construction:
 *   $employees           -> [ <employee data>, ... ]
 *   $employeeSchedules   -> 
 *   $employeeTimesheets  -> 
 *   $holidays            -> 
 *   $changedSchedules    -> 
 *   $leaves              -> { <date> : { <employee id> : { 'leave_type' : <leave type>, 'is_paid' : <is_paid> }, ... }, ... }
 *   $holidayPays         -> { <date> : { <employee id> : { 'type' : <holiday type>, 'name' : <holiday name> }, ... }, ... }
 *   $otPays              -> { <date> : [ <employee id>, ... ], ... }
 */
function build_employees_attendance($employees, $employeeSchedules, $employeeTimesheets, $start_date, $end_date, $holidays, $grace_period_in_mins, $changedSchedules, $leaves, $holidayPays, $otPays)
{
	$ret_val = array(
		'data'  => array(),
		'days'  => array()
	);

	$start_time = strtotime($start_date);
	$end_time = strtotime($end_date);
	for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
	{
		$ret_val['days'][] = date('Y-m-d', $i);
	}

	foreach ($employees as $employee)
	{
		$ret_val['data'][$employee['empid']] = array(
			'lastname'        => $employee['lastname'],
			'firstname'       => $employee['firstname'],
			'date_hired'      => $employee['date_hired'],
			'sched'           => array(
				'days'    => NULL,
				'time'    => NULL
			),
			'dates'           => array()
		);

		if (array_key_exists($employee['empid'], $employeeSchedules))
		{
			$ret_val['data'][$employee['empid']]['sched']['days'] = $employeeSchedules[$employee['empid']]['sched_info']['days_string'];
			$ret_val['data'][$employee['empid']]['sched']['time'] = $employeeSchedules[$employee['empid']]['sched_info']['time_string'];
		}
		else
		{
			$ret_val['data'][$employee['empid']]['sched']['days'] = NULL;
			$ret_val['data'][$employee['empid']]['sched']['time'] = NULL;
		}

		$number_of_applied_grace_period = 0;
		for ($i = $start_time; $i <= $end_time; $i = strtotime('+1day', $i))
		{
			$iDateTime = date('Y-m-d H:i:s', $i);
			$iDate = date('Y-m-d', $i);

			$holidayPay = NULL;
			if (array_key_exists($iDate, $holidayPays) && array_key_exists($employee['empid'], $holidayPays[$iDate]))
			{
				$holidayPay = $holidayPays[$iDate][$employee['empid']];
			}

			$hasOTPay = false;
			if (array_key_exists($iDate, $otPays) && in_array($employee['empid'], $otPays[$iDate]))
			{
				$hasOTPay = true;
			}

			$employeeSchedForTheDay = NULL;
			if (array_key_exists($employee['empid'], $employeeSchedules) && array_key_exists($iDate, $employeeSchedules[$employee['empid']]['days_data']))
			{
				$employeeSchedForTheDay = $employeeSchedules[$employee['empid']]['days_data'][$iDate];
			}

			// if the employee is in leave
			if ($employeeSchedForTheDay != NULL && array_key_exists($iDate, $leaves) && array_key_exists($employee['empid'], $leaves[$iDate]))
			{
				$leave_types = array(
					'vacation'  => 'VL',
					'sick'      => 'SL',
					'emergency' => 'EL'
				);

				if (in_array($leaves[$iDate][$employee['empid']]['leave_type'], $leave_types))
				{
					$leave_type = $leave_types[$leaves[$iDate][$employee['empid']]['leave_type']];

					$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
						'time_in'         => $leave_type,
						'time_out'        => $leave_type,
						'total'           => $leave_type,
						'status'          => $leave_type,
						'spent_hours'     => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1) ? (float)$employeeSchedForTheDay['number_of_hours'] : 0.0,
						'nightdiff_hours' => 0.0,
						'is_paid'         => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1)
					);

					continue;
				}
			}

			// check first in the changed schedules before checking the user schedule
			if (
				array_key_exists($employee['empid'], $changedSchedules['removed_schedules']) && 
				array_key_exists($iDate, $changedSchedules['removed_schedules'][$employee['empid']])
			)
			{
				if (
					array_key_exists($employee['empid'], $changedSchedules['added_schedules']) && 
					array_key_exists($iDate, $changedSchedules['added_schedules'][$employee['empid']])
				)
				{
					$sched_start_time = $iDate.$changedSchedules['added_schedules'][$employee['empid']][$iDate]['start_time'];
					$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$employee['empid']][date('Y-m-d', $i)]['number_of_hours'].'hours', strtotime($sched_start_time)));

					// changing of schedule time happened
					$employeeSchedForTheDay = array(
						'start_time'       => $sched_start_time,
						'end_time'         => $sched_end_time,
						'number_of_hours'  => $changedSchedules['added_schedules'][$employee['empid']][$iDate]['number_of_hours'],
					);
				}
				else
				{
					$employeeSchedForTheDay = NULL; // no sched for today
				}
			}
			else if (array_key_exists($employee['empid'], $changedSchedules['added_schedules']) && array_key_exists($iDate, $changedSchedules['added_schedules'][$employee['empid']]))
			{
				$sched_start_time = $iDate.$changedSchedules['added_schedules'][$employee['empid']][$iDate]['start_time'];
				$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$employee['empid']][$iDate]['number_of_hours'].'hours', strtotime($sched_start_time)));

				// changing of schedule time happened
				$employeeSchedForTheDay = array(
					'start_time'       => $sched_start_time,
					'end_time'         => $sched_end_time,
					'number_of_hours'  => $changedSchedules['added_schedules'][$employee['empid']][$iDate]['number_of_hours'],
				);
			}
			// end of checking of changed schedules

			if (array_key_exists($employee['empid'], $employeeTimesheets[$iDate]))
			{
				$tsheet = $employeeTimesheets[$iDate][$employee['empid']];

				$d1 = new DateTime($tsheet['checkout']); 
				$d2 = new DateTime($tsheet['checkin']);

				if ($employeeSchedForTheDay != NULL)
				{
					$sched_start_time = $employeeSchedForTheDay['start_time'];
					$sched_end_time = $employeeSchedForTheDay['end_time'];

					$late_in_mins = (int)((int)(strtotime($tsheet['checkin']) - strtotime($sched_start_time)) / 60);
					// if ($late_in_mins > 0 && $number_of_applied_grace_period <= 3)
					// {
					// 	$late_in_mins = ($late_in_mins > 5) ? $late_in_mins - $grace_period_in_mins : 0;
					// 	$number_of_applied_grace_period++;
					// }
					
					$schedStartDT = new DateTime($sched_start_time);
					$schedEndDT = new DateTime($sched_end_time);
					$tDT1 = ($d2 > $schedStartDT) ? $d2 : $schedStartDT;
					$tDT2 = ($d1 < $schedEndDT) ? $d1 : $schedEndDT;
					$tDInterval = date_diff($tDT2, $tDT1);

					$total_hours = (float)$tDInterval->h + (float)((float)$tDInterval->i / 60.0);

					// calculate night diff
					$d3 = new DateTime(date('Y-m-d', strtotime($tsheet['checkin'])).' 22:00:00'); // 10pm
					$d4 = new DateTime(date('Y-m-d', strtotime($tsheet['checkout'])).' 06:00:00'); // 6am

					$nightDiffHours = 0.0;
					$nighDiffDTStart = ($tDT1 > $d3) ? $tDT1 : $d3;
					$nighDiffDTEnd = ($tDT2 < $d4) ? $tDT2 : $d4;
					if ($nighDiffDTStart < $nighDiffDTEnd)
					{
						$nightDiffDTInterval = date_diff($nighDiffDTEnd, $nighDiffDTStart);
						$nightDiffHours = (float)$nightDiffDTInterval->h + (float)((float)$nightDiffDTInterval->i / 60.0);
					}

					// if the employee is half day leave
					if (array_key_exists($iDate, $leaves) && array_key_exists($employee['empid'], $leaves[$iDate]) && $leaves[$iDate][$employee['empid']]['leave_type'] = 'half_day')
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
							'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
							'time_out'        => date('H:i:s', strtotime($tsheet['checkout'])),
							'total'           => $tDInterval->format('%hh %im %ss'),
							'status'          => 'HD',
							'spent_hours'     => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1) ? ((float)$employeeSchedForTheDay['number_of_hours']) / 2.0: 0.0,
							'nightdiff_hours' => 0.0,
							'is_paid'         => ($leaves[$iDate][$employee['empid']]['is_paid'] == 1)
						);
					}

					// late
					else if ($late_in_mins > 0)
					{
						// todo: a certain length of time in late will be half day

						$late_in_hour = CalculateLateInHours(round($late_in_mins));
						$late_in_hour = ($late_in_hour == 'HD') ? (((float)$employeeSchedForTheDay['number_of_hours']) / 0.5) : $late_in_hour;

						$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
							'time_in'         => date('H:i:s', strtotime($tsheet['checkin'])),
							'time_out'        => date('H:i:s', strtotime($tsheet['checkout'])),
							'total'           => $tDInterval->format('%hh %im %ss'),
							'status'          => 'L',
							'status_val'      => $late_in_hour,
							'spent_hours'     => ($total_hours - 1.0),
							'nightdiff_hours' => $nightDiffHours
						);			
					}

					// undertime
					else if ($total_hours < (float) $employeeSchedForTheDay['number_of_hours'])
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
							'time_in'            => date('H:i:s', strtotime($tsheet['checkin'])),
							'time_out'           => date('H:i:s', strtotime($tsheet['checkout'])),
							'total'              => $tDInterval->format('%hh %im %ss'),
							'status'             => 'U',
							'total_hours'        => $total_hours,
							'sched_total_hours'  => (float) $employeeSchedForTheDay['number_of_hours'],
							'spent_hours'        => ($total_hours - 1.0),
							'nightdiff_hours'    => $nightDiffHours
						);
					}

					// present
					else
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
							'time_in'            => date('H:i:s', strtotime($tsheet['checkin'])),
							'time_out'           => date('H:i:s', strtotime($tsheet['checkout'])),
							'total'              => $tDInterval->format('%hh %im %ss'),
							'status'             => 'PR',
							'spent_hours'        => ($total_hours - 1.0),
							'nightdiff_hours'    => $nightDiffHours,
						);
					}

					$ret_val['data'][$employee['empid']]['dates'][$iDate]['work_hours'] = ((float) $employeeSchedForTheDay['number_of_hours']) - 1.0; // subtract the 1 hour mandatory break

					if ($holidayPay != NULL)
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate]['has_holiday_pay'] = true;
						$ret_val['data'][$employee['empid']]['dates'][$iDate]['holiday_type'] = $holidayPay['type'];	
					}
					else
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate]['has_holiday_pay'] = false;
					}

					if ($hasOTPay)
					{
						$checkin_str_to_time = strtotime($tsheet['checkin']);
						$checkout_str_to_time = strtotime($tsheet['checkout']);
						$start_ot_str_to_time = strtotime('+'.$employeeSchedForTheDay['number_of_hours'].'hours', $checkin_str_to_time);
						$night_shift_start_str_to_time = strtotime(date('Y-m-d', strtotime($tsheet['checkin'])).' 22:00:00');

						// no OT
						if ($start_ot_str_to_time >= $checkout_str_to_time)
						{
							$ret_val['data'][$employee['empid']]['dates'][$iDate]['has_ot_pay'] = false;
						}
						// no night shift OT
						else if ($checkout_str_to_time <= $night_shift_start_str_to_time)
						{
							$ret_val['data'][$employee['empid']]['dates'][$iDate]['has_ot_pay'] = true;

							$startOTDT = new DateTime(date('Y-m-d H:i:s', $start_ot_str_to_time));
							$endOTDT = new DateTime(date('Y-m-d H:i:s', $checkout_str_to_time));
							$otInterval = date_diff($endOTDT, $startOTDT);

							$total_hours_ot = (float)$otInterval->h + (float)((float)$otInterval->i / 60.0);

							$ret_val['data'][$employee['empid']]['dates'][$iDate]['normal_ot_hours'] = $total_hours_ot;
							$ret_val['data'][$employee['empid']]['dates'][$iDate]['night_differential_ot_hours'] = 0.0;
						}
						// night shift OT found
						else
						{
							$ret_val['data'][$employee['empid']]['dates'][$iDate]['has_ot_pay'] = true;

							$startNormalOTDT = new DateTime(date('Y-m-d H:i:s', $start_ot_str_to_time));
							$endNormalOTDT = new DateTime(date('Y-m-d H:i:s', $night_shift_start_str_to_time));
							$otNormalInterval = date_diff($endNormalOTDT, $startNormalOTDT);
							$total_normal_hours_ot = (float)$otNormalInterval->h + (float)((float)$otNormalInterval->i / 60.0);
							$ret_val['data'][$employee['empid']]['dates'][$iDate]['normal_ot_hours'] = $total_normal_hours_ot;

							$startNightshiftOTDT = new DateTime(date('Y-m-d H:i:s', $night_shift_start_str_to_time));
							$endNightshiftOTDT = new DateTime(date('Y-m-d H:i:s', $checkout_str_to_time));
							$otNightshiftInterval = date_diff($endNightshiftOTDT, $startNightshiftOTDT);
							$total_nightshift_hours_ot = (float)$otNightshiftInterval->h + (float)((float)$otNightshiftInterval->i / 60.0);
							$ret_val['data'][$employee['empid']]['dates'][$iDate]['night_differential_ot_hours'] = $total_nightshift_hours_ot;
						}
					}
					else
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate]['has_ot_pay'] = false;
					}
				}
				else
				{
					// if the user is REST DAY but still go to work, it wont be counted as work but REST DAY
					$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
						'time_in'         => 'RD',
						'time_out'        => 'RD',
						'total'           => 'RD',
						'status'          => 'RD',
						'has_holiday_pay' => false,
						'spent_hours'     => 0.0,
						'nightdiff_hours' => 0.0,
						'work_hours'      => 0.0
					);
				}
			}
			else
			{
				if ($employeeSchedForTheDay != NULL)
				{
					// holiday for employee that isn't rest day on this day
					if (array_key_exists($iDate, $holidays))
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
							'time_in'         => 'HL',
							'time_out'        => 'HL',
							'total'           => 'HL',
							'status'          => 'HL',
							'spent_hours'     => (float) $employeeSchedForTheDay['number_of_hours'],
							'nightdiff_hours' => 0.0,
							'has_holiday_pay' => false,
							'work_hours'      => ((float) $employeeSchedForTheDay['number_of_hours']) - 1.0 // subtract the 1 hour mandatory break
						);
					}
					// absent
					else
					{
						$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
							'time_in'         => 'A',
							'time_out'        => 'A',
							'total'           => 'A',
							'status'          => 'A',
							'spent_hours'     => 0.0,
							'nightdiff_hours' => 0.0,
							'has_holiday_pay' => false,
							'work_hours'      => ((float) $employeeSchedForTheDay['number_of_hours']) - 1.0 // subtract the 1 hour mandatory break
						);
					}
				}
				else
				{
					// employee is in rest day
					$ret_val['data'][$employee['empid']]['dates'][$iDate] = array(
						'time_in'         => 'RD',
						'time_out'        => 'RD',
						'total'           => 'RD',
						'status'          => 'RD',
						'spent_hours'     => 0.0,
						'nightdiff_hours' => 0.0,
						'has_holiday_pay' => false,
						'work_hours'      => 0.0
					);
				}
			}
		}
	}

	return $ret_val;
}

/**
 * Returns the number of hours the user will be late given the number of minutes the user is actually late.
 */
function CalculateLateInHours($lateInMins)
{
	$lateRulesTable = GetLateRulesTable();
	$lateInMins = round($lateInMins);
	$lateInHours = 0.0;

	foreach ($lateRulesTable as $lateRuleTable)
	{
		if ($lateInMins < $lateRuleTable['start'])
		{
			break;
		}
		else
		{
			$lateInHours = $lateRuleTable['deduction'];
		}
	}

	return $lateInHours;
}

/**
 * Returns the table of rules for lates.
 */
function GetLateRulesTable()
{
	return array(
		array(
			'start'      => 6,  // in minutes
			'deduction'  => 0.5 // in hours
		),
		array(
			'start'      => 16,  // in minutes
			'deduction'  => 1.0 // in hours
		),
		array(
			'start'      => 36,  // in minutes
			'deduction'  => 2.0 // in hours
		),
		array(
			'start'      => 61,  // in minutes
			'deduction'  => 3.0 // in hours
		),
		array(
			'start'      => 121,  // in minutes
			'deduction'  => 4 // in hours
		),
		array(
			'start'      => 181,  // in minutes
			'deduction'  => 'HD' // HD means half day in hours
		)
	);
}

/**
 * Returns the corresponding color of the given status
 */
function excel_color_for_attendance_status($status)
{
	switch ($status)
	{
		case 'L':
			return 'FF0000';

		case 'U':
			return 'A14A1D';

		case 'A':
			return 'F40054';

		case 'RD':
			return '6C369D';

		case 'PR':
		default:
			return '000000';
	}
}

/**
 * Returns the corresponding color of the given status
 */
function html_color_for_attendance_status($status)
{
	switch ($status)
	{
		case 'L':
			return 'rgb(256, 0, 0)';

		case 'U':
			return 'rgb(161, 74, 29)';

		case 'A':
			return 'rgb(244, 0, 84)';

		case 'RD':
			return 'rgb(108, 54, 157)';

		case 'PR':
		default:
			return 'rgb(0, 0, 0)';
	}
}