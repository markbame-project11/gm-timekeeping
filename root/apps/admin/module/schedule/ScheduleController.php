<?php

/**
 * The schedule controller class.
 */
class ScheduleController extends BaseAdminController
{
	public function executeAddToFlexiSched()
	{
		$flexiSchedModel = $this->loadModel('FlexiSchedule');

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && array_key_exists('employee_id', $_POST))
		{
			$flexiSchedEntry = $flexiSchedModel->getFlexiScheduleOn($_POST['employee_id'], date('Y-m-d'));
			if (NULL == $flexiSchedEntry)
			{
				$flexiSchedModel->create(array(
					'start_date'   => date('Y-m-d'),
					'employee_id'  => $_POST['employee_id'],
					'is_flexi'     => true
				));

				echo json_encode(array(
					'is_successful'  => true,
					'message'        => 'Employee successfully added to list of employees with flexible schedules',
					'test'           => 'Created here...'
				));
				exit;
			}
			else
			{
				$flexiSchedModel->update($flexiSchedEntry['id'], array(
					'is_flexi'     => true
				));
			}

			echo json_encode(array(
				'is_successful'  => true,
				'message'        => 'Employee successfully added to list of employees with flexible schedules'
			));
		}
		else
		{
			echo json_encode(array(
				'is_successful'  => false,
				'message'        => 'Invalid request'
			));
		}

		exit;
	}

	public function executeRemoveFromFlexiSched()
	{
		$flexiSchedModel = $this->loadModel('FlexiSchedule');

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post' && array_key_exists('employee_id', $_POST))
		{
			$flexiSchedEntry = $flexiSchedModel->getFlexiScheduleOn($_POST['employee_id'], date('Y-m-d'));
			if (NULL == $flexiSchedEntry)
			{
				$flexiSchedModel->create(array(
					'start_date'   => date('Y-m-d'),
					'employee_id'  => $_POST['employee_id'],
					'is_flexi'     => false
				));
			}
			else
			{
				$flexiSchedModel->update($flexiSchedEntry['id'], array(
					'is_flexi'     => false
				));
			}

			echo json_encode(array(
				'is_successful'  => true,
				'message'        => 'Employee successfully removed from list of employees with flexible schedules'
			));
		}
		else
		{
			echo json_encode(array(
				'is_successful'  => false,
				'message'        => 'Invalid request'
			));
		}

		exit;
	}

	public function executeChangeScheduleForSpecificDate()
	{
		$employeeModel = $this->loadModel('Employee');
		$changeScheduleModel = $this->loadModel('EmployeeChangedSchedule');

		$this->employees = $employeeModel->getPayrollEmployees();
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$employee = $employeeModel->getByID($_POST['employee_id']);
			if (NULL != $employee)
			{
				$schedule = $changeScheduleModel->getScheduleOfEmployeeOn($_POST['employee_id'], $_POST['for_date']);

				if (is_array($schedule))
				{
					$changeScheduleModel->update($schedule['id'], array(
						'for_date'         => $_POST['for_date'],
						'new_date'         => $_POST['new_date'],
						'start_time'       => $_POST['start_time'].':00',
						'number_of_hours'  => $_POST['number_of_hours']
					));
				}
				else
				{
					$changeScheduleModel->create(array(
						'for_date'         => $_POST['for_date'],
						'new_date'         => $_POST['new_date'],
						'start_time'       => $_POST['start_time'].':00',
						'number_of_hours'  => $_POST['number_of_hours'],
						'employee_id'      => $_POST['employee_id']
					));
				}

				$this->success_message = 'Successfully changed schedule for '.$employee['firstname'].' '.$employee['lastname'].' on '.date('M d, Y', strtotime($_POST['for_date']));
			}
			else
			{
				$this->error_message = 'Choose an employee first.';
			}
		}
		else
		{
			$this->fields = array(
				'new_date'        => '',
				'for_date'        => '',
				'start_time'      => '',
				'number_of_hours' => '9',
				'employee_id'     => '0'
			);
		}
	}

	public function executeChange()
	{
		$this->redirectUnless((array_key_exists('employeeid', $_GET)), $this->getConfig()->get('base_url').'/timekeeping');

		$employeeModel = $this->loadModel('Employee');
		$schedModel = $this->loadModel('EmployeeSchedule');

		$this->employee = $employeeModel->getByID($_GET['employeeid']);
		$this->forward404Unless(($this->employee != NULL));

		$this->loadHelpers(array('generic'));

		$this->days = get_all_days();
		$this->hours = get_all_hours();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->schedule = array(
				'employee_id'    => $_GET['employeeid'],
				'start_date'     => $_POST['start_date']
			);

			$this->schedules = array();
			foreach ($this->days as $key => $day)
			{
				if (array_key_exists($key.'_checked', $_POST))
				{
					$this->schedule[$key.'_time']      = $_POST[$key.'_start_time'];
					$this->schedule[$key.'_num_hours'] = $_POST[$key.'_number_of_hours'];
				}
				else
				{
					$this->schedule[$key.'_time']      = '00:00:00';
					$this->schedule[$key.'_num_hours'] = 0;					
				}
			}

			$schedule_obj = $schedModel->getEmployeeScheduleWithStartDate($this->employee['empid'], $_POST['start_date']);

			if (NULL == $schedule_obj)
			{
				$schedModel->create($this->schedule);
			}
			else
			{
				$schedModel->update($schedule_obj['schedule_id'], $this->schedule);
			}

			$this->success_message = 'Schedule for '.$this->employee['firstname'].' '.$this->employee['lastname'].' successfully changed.';
		}
		else
		{
			$schedule = $schedModel->getLastEmployeeSchedule($this->employee['empid']);

			if ($schedule != NULL)
			{
				$this->schedule = $schedule;
			}
			else
			{
				$this->schedule = array(
					'employee_id'    => $_GET['employeeid'],
					'start_date'     => ($this->employee['date_hired'] == '0000-00-00') ? '2000-01-01' : $this->employee['date_hired'],
					'sun_time'       => '00:00:00',
					'mon_time'       => '09:00:00',
					'tue_time'       => '09:00:00',
					'wed_time'       => '09:00:00',
					'thu_time'       => '09:00:00',
					'fri_time'       => '09:00:00',
					'sat_time'       => '00:00:00',
					'sun_num_hours'  => 9,
					'mon_num_hours'  => 9,
					'tue_num_hours'  => 9,
					'wed_num_hours'  => 9,
					'thu_num_hours'  => 9,
					'fri_num_hours'  => 9,
					'sat_num_hours'  => 9
				);
			}
		}
	}
}