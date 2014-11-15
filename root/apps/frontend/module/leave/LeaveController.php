<?php
/**
 * The leave controller class.
 */
class LeaveController extends BaseFrontendLoggedInController
{	
	/**
	 * 
	 */
	public function executeDelete()
	{
		if (array_key_exists('leave_id', $_POST))
		{
			$leaveModel = $this->loadModel('Leave');

			$leave = $leaveModel->getById($_POST['leave_id']);
			if (is_array($leave) && $leave['employee_id'] == $_SESSION['empid'])
			{
				$leaveModel->delete($_POST['leave_id']);

				echo json_encode(array(
					'success'    => true,
					'message'    => 'Leave successfully deleted.'
				));
			}
			else
			{
				echo json_encode(array(
					'success'    => false,
					'message'    => 'Leave doesn\'t exists.'
				));
			}
		}
		else
		{
			echo json_encode(array(
				'success'    => false,
				'message'    => 'Invalid request'
			));
		}

		exit;
	}

	/**
	 *
	 */
	public function executeIndex()
	{
		$this->loadHelpers(array('attendance'));

		$leaveModel = $this->loadModel('Leave');
		$employeeModel = $this->loadModel('Employee');
		$scheduleModel = $this->loadModel('EmployeeSchedule');
		
		$this->pendingLeaves = $leaveModel->getEmployeePendingLeaves($_SESSION['empid']);
		$this->upcomingApprovedLeaves = $leaveModel->getEmployeeApprovedLeavesAfter($_SESSION['empid'], date('Y-m-d'));
		$this->currentYearApprovedLeaves = $leaveModel->getEmployeeApprovedLeavesBeforeOnSameYear($_SESSION['empid'], date('Y-m-d'));

		// calculate remaining leaves
		$employee = $employeeModel->getById($_SESSION['empid']);
		$consolidatedLeaves = $leaveModel->getEmployeeApprovedLeavesConsolidationInYear($_SESSION['empid'], date('Y'));
		$lowestDate = find_lowest_date_from_consolidated_paid_leaves($consolidatedLeaves);
		$employeeSchedules = $scheduleModel->getEmployeeSchedulesAfter($_SESSION['empid'], $lowestDate);

		$remainingLeaves = calculate_remaining_paid_leaves($employee, $consolidatedLeaves, $employeeSchedules);
	}

	/**
	 *
	 */
	public function executeApply()
	{
		$this->loadHelpers(array('attendance'));

		$leaveModel = $this->loadModel('Leave');
		$employeeModel = $this->loadModel('Employee');
		$scheduleModel = $this->loadModel('EmployeeSchedule');

		$this->error_message = NULL;
		$this->info_message = NULL;
		$this->leave_types = array(
			'sick'      => 'Sick',
			'vacation'  => 'Vacation'
		);

		$this->employee = $employeeModel->getById($_SESSION['empid']);
		if ($this->employee['employment_status'] == 'probationary')
		{
			$this->info_message = 'You are not yet regular. This leave will be unpaid.';
			$this->show_false_only_for_is_paid = false;
		}
		else
		{
			// calculate remaining leaves
			$consolidatedLeaves = $leaveModel->getEmployeeApprovedLeavesConsolidationInYear($_SESSION['empid'], date('Y'));
			$lowestDate = find_lowest_date_from_consolidated_paid_leaves($consolidatedLeaves);
			$employeeSchedules = $scheduleModel->getEmployeeSchedulesAfter($_SESSION['empid'], $lowestDate);

			$remainingLeaves = calculate_remaining_paid_leaves($this->employee, $consolidatedLeaves, $employeeSchedules);
			$this->remainingVLs = $remainingLeaves['vacation'];
			$this->remainingSLs = $remainingLeaves['sick'];
			$this->info_message = 'You have '.$remainingLeaves['vacation'].' vacation leaves remaining and '.$remainingLeaves['sick'].' sick leaves remaining.';
			$this->show_false_only_for_is_paid = ($this->remainingVLs >= 1 || $this->remainingSLs >= 1) ? true : false;
		}

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$scheduleModel = $this->loadModel('EmployeeSchedule');

			$schedule = $scheduleModel->getEmployeeScheduleOn($_SESSION['empid'], $_POST['start_date']);
			if (NULL != $schedule && is_array($schedule))
			{
				$num_days = (int)$_POST['num_days'];
				if (1 == $num_days)
				{
					$scheduled_dates[] = $_POST['start_date'];
					$start_time = date('H:i:s', strtotime($schedule['start_time']));
					$number_of_hours = (int)$schedule['number_of_hours'];
				}
				else
				{
					$schedules = $scheduleModel->getEmployeeSchedulesAfter($_SESSION['empid'], $_POST['start_date']);
					$scheduled_dates = find_working_dates($_POST['start_date'], $num_days, $schedules, 'Y-m-d');

					$start_time = $_POST['start_time'];
					$number_of_hours = (int)$_POST['number_of_hours'];
					$number_of_hours = ($number_of_hours > 0 && $number_of_hours <= (int)$schedule['number_of_hours']) ? $number_of_hours : (int)$schedule['number_of_hours'];
				}

				$datesWithLeaves = $leaveModel->getEmployeeLeavesForDates($_SESSION['empid'], $scheduled_dates);
				$isSuccessful = true;
				foreach ($scheduled_dates as $scheduled_date)
				{
					if ($datesWithLeaves[$scheduled_date] != NULL)
					{
						$this->error_message = 'You already applied for a leave on '.date('M d, Y', strtotime($scheduled_date));
						$isSuccessful = false;
					}
				}

				if ($isSuccessful)
				{
					if ($this->employee['employment_status'] == 'regular')
					{
						$isPaidFieldVal = (array_key_exists('is_paid', $_POST) && $_POST['is_paid'] == '1') ? true : false;
						if ($_POST['leave_type'] == 'vacation')
						{
							$isPaid = ($this->remainingVLs >= 1) ? $isPaidFieldVal : false;
						}
						else
						{
							$isPaid = ($this->remainingSLs >= 1) ? $isPaidFieldVal : false;
						}
					}
					else
					{
						$isPaid = false;
					}

					foreach ($scheduled_dates as $scheduled_date)
					{
						$leaveModel->create(array(
							'employee_id'      => $_SESSION['empid'],
							'date'             => $scheduled_date,
							'start_time'       => $start_time,
							'number_of_hours'  => $number_of_hours,
							'leave_type'       => $_POST['leave_type'],
							'reason'           => $_POST['reason'],
							'status'           => 'pending',
							'date_filed'       => date('Y-m-d'),
							'is_paid'          => $isPaid
						));
					}

					$this->redirect($this->getConfig()->get('base_url').'/leave');
				}
			}
			else
			{
				$this->error_message = 'It\'s either that you don\'t have schedule yet or the start date is your rest day. Please check your schedule.';
			}
		}
		else
		{
			if ($this->employee['employment_status'] == 'regular')
			{
				$isPaid = ($this->remainingVLs > 0) ? true : false;
			}
			else
			{
				$isPaid = false;
			}

			$this->fields = array(
				'reason'          => '',
				'start_date'      => '',
				'num_days'        => '1',
				'start_time'      => '',
				'number_of_hours' => 0,
				'leave_type'      => '',
				'is_paid'         => ($isPaid) ? '1' : '0'
			);
		}
	}

	/**
	 * 
	 */
	public function executeValidateForm()
	{
		$this->loadHelpers(array('attendance'));

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$leaveModel = $this->loadModel('Leave');
			$scheduleModel = $this->loadModel('EmployeeSchedule');
			$employeeModel = $this->loadModel('Employee');

			$schedule = $scheduleModel->getEmployeeScheduleOn($_SESSION['empid'], $_POST['start_date']);
			if (NULL != $schedule && is_array($schedule))
			{
				if (1 == (int)$_POST['num_days'])
				{
					echo json_encode(array(
						'is_successful'      => true,
						'show_message'       => false,     
						'message'            => '' 
					));	
				}
				else
				{
					$num_days = (int)$_POST['num_days'];

					$employee = $employeeModel->getById($_SESSION['empid']);
					if ($employee['employment_status'] == 'regular' && array_key_exists('is_paid', $_POST) && $_POST['is_paid'] == '1')
					{
						// calculate remaining leaves
						$consolidatedLeaves = $leaveModel->getEmployeeApprovedLeavesConsolidationInYear($_SESSION['empid'], date('Y'));
						$lowestDate = find_lowest_date_from_consolidated_paid_leaves($consolidatedLeaves);
						$employeeSchedules = $scheduleModel->getEmployeeSchedulesAfter($_SESSION['empid'], $lowestDate);

						$remainingLeaves = calculate_remaining_paid_leaves($employee, $consolidatedLeaves, $employeeSchedules);
						$remainingVLs = $remainingLeaves['vacation'];
						$remainingSLs = $remainingLeaves['sick'];

						if (
							($_POST['leave_type'] == 'vacation' && $remainingVLs < $num_days) ||
							($_POST['leave_type'] == 'sick' && $remainingSLs < $num_days)
						)
						{
							echo json_encode(array(
								'is_successful'      => false,
								'show_message'       => true,         
								'message'            => 'You don\'t have enough credits to apply for a '.$_POST['leave_type'].' leave for '.$num_days.' days.'
							));
							exit;
						}
					}


					$schedules = $scheduleModel->getEmployeeSchedulesAfter($_SESSION['empid'], $_POST['start_date']);
					$scheduled_dates = find_working_dates($_POST['start_date'], (int) $_POST['num_days'], $schedules);

					echo json_encode(array(
						'is_successful'      => true,
						'show_message'       => true,         
						'message'            => 'You are applying for leave on the following dates: '.implode('; ', $scheduled_dates).'. Press OK to confirm.'
					));
				}
			}
			else
			{
				echo json_encode(array(
					'is_successful'                 => false,
					'message'                       => 'It\'s either that you don\'t have schedule yet or the start date is your rest day. Please check your schedule.' 
				));	
			}
		}
		else
		{
			echo json_encode(array(
				'is_successful'                 => false,
				'message'                       => 'Invalid request'
			));
		}

		exit;
	}

	/**
	 *
	 */
	public function executeCalculateStartTimeAndNumHours()
	{
		if (array_key_exists('start_date', $_GET) && array_key_exists('num_days', $_GET))
		{
			$leaveModel = $this->loadModel('Leave');
			$scheduleModel = $this->loadModel('EmployeeSchedule');

			$schedule = $scheduleModel->getEmployeeScheduleOn($_SESSION['empid'], $_GET['start_date']);

			if (NULL != $schedule && is_array($schedule))
			{
				echo json_encode(array(
					'start_time_and_hours_editable' => (1 == (int)($_GET['num_days'])) ? true : false,
					'start_time'                    => date('H:i', strtotime($schedule['start_time'])),
					'number_of_hours'               => (int) $schedule['number_of_hours'],
					'is_successful'                 => true
				));
			}
			else
			{
				echo json_encode(array(
					'is_successful'                 => false,
					'message'                       => 'It\'s either that you don\'t have schedule yet or the start date is your rest day. Please contact administrator to check this date.' 
				));	
			}		
		}
		else
		{
			echo json_encode(array(
				'is_successful'                 => false,
				'message'                       => 'Invalid request'
			));	
		}

		exit;
	}
}