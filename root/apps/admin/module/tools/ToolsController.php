<?php

/**
 * The tools controller class.
 */
class ToolsController extends CcController
{	
	public function executeFillEmployees()
	{
		// generate admin account
		$employeeModel = $this->loadModel('Employee');

		$filename = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/employee_master_list.csv';
		
		$this->data = array();
		$handle = fopen($filename, "r");
		if ($handle)
		{
			while (($buffer = fgets($handle, 4096)) !== false)
			{
				$buffer_len = strlen($buffer);
				$read_string = "";
				$open_quote_found = false;

				$data_entry = array();
				for ($i = 0, $j = 0; $i < $buffer_len; $i++)
				{
					if ($buffer[$i] == '"')
					{
						if ($open_quote_found)
						{
							$open_quote_found = false;
						}
						else
						{
							$open_quote_found = true;
							$read_string = "";
						}
					}
					else if ($buffer[$i] == ',' && $open_quote_found == false)
					{
						if ($j == 1)
						{
							$this->loadHelpers(array('generic'));

							$read_string_pcs = explode(',', $read_string);

							$data_entry[] = $read_string_pcs[0];
							$data_entry[] = $read_string_pcs[1];
							$data_entry[] = 'G0m1Pa$$';
							$read_string = "";
							$first_entry_found = true;
						}
						else
						{
							$data_entry[] = $read_string;
							$read_string = "";
						}

						$j++;
					}
					else
					{
						$read_string .= $buffer[$i];
					}
				}

				$data_entry[] = $read_string;
				$this->data[] = $data_entry;
			}

			if (!feof($handle))
			{
				echo "Error: unexpected fgets() fail\n";
			}
			fclose($handle);
		}

		$departments = array();
		$departmentModel = $this->loadModel('Department');
		$departmentModel->truncateTable();

		$employeeModel = $this->loadModel('Employee');
		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$employeeModel->truncateTable();
		$scheduleModel->truncateTable();

		foreach ($this->data as $dd)
		{
			$dd_len = count($dd);

			if (!array_key_exists(trim($dd[7]), $departments))
			{
				$department_id = $departmentModel->create(array(
					'managerid'      => 0,
					'deptname'       => trim($dd[7]),
					'deptdesc'       => trim($dd[7])
				));

				$departments[trim($dd[7])] = (int)$department_id;
			}

			$employee_id = $employeeModel->create(
				array(
					'login'                 => trim($dd[0]),
					'lastname'              => trim($dd[1]),
					'firstname'             => trim($dd[2]),
					'password'              => trim($dd[3]),
					'minit'                 => trim($dd[4]),
					'nickname'              => trim($dd[5]),
					'gender'                => trim(strtolower($dd[6])),
					'deptid'                => $departments[trim($dd[7])],
					'tax_status'            => trim(strtolower($dd[8])),
					'cellphone'             => trim($dd[9]),
					'address1'              => trim($dd[10]),
					'email'                 => trim($dd[11]),
					'dob'                   => date('Y-m-d', strtotime(trim($dd[12]))),
					'sss_no'                => trim($dd[13]),
					'tin_no'                => trim($dd[14]),
					'philhealth_no'         => trim($dd[15]),
					'pagibig_no'            => trim($dd[16]),
					'position'              => trim($dd[17]),
					'date_hired'            => date('Y-m-d', strtotime($dd[18])),
					'skype_id'              => trim($dd[19]),
					'em_contact_person'     => trim($dd[20]),
					'em_contact_no'         => trim($dd[21]),
					'em_contact_address'    => trim($dd[22]),
					'is_employee'           => true,
					'admin'                 => (strtolower(trim($dd[1])) == 'cruda') ? '1' : '0'
				)
			);

			if ($employee_id > 0)
			{
				$scheduleModel->create(array(
					'employee_id'      => $employee_id,
					'start_date'       => date('Y-m-d', strtotime($dd[18])),
					'sun_time'         => '00:00:00',
					'mon_time'         => '09:30:00',
					'tue_time'         => '09:30:00',
					'wed_time'         => '09:30:00',
					'thu_time'         => '09:30:00',
					'fri_time'         => '09:30:00',
					'sat_time'         => '00:00:00',
					'sun_num_hours'    => 0,
					'mon_num_hours'    => 9,
					'tue_num_hours'    => 9,
					'wed_num_hours'    => 9,
					'thu_num_hours'    => 9,
					'fri_num_hours'    => 9,
					'sat_num_hours'    => 0
				));
			}
			else
			{
				echo 'Fail creation of employee '.$dd[2].' '.$dd[1].' <br />';
			}
		}

		// var_dump($employeeModel->getAll());

		echo 'Employees added...';
		exit;
	}

	public function executeFillTimesheetInfo()
	{
		$employeeModel = $this->loadModel('Employee');
		$timesheetModel = $this->loadModel('Timesheet');
		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$holidayModel = $this->loadModel('Holiday');

		$timesheetModel->truncateTable();

		$employees = $employeeModel->getPayrollEmployees();
		$employeeSchedules = $scheduleModel->getEmployeesIDToScheduleMapping(date('Y-m-d', strtotime("-15days")), date('Y-m-d'));
		$holidays = $holidayModel->getHolidaysBetweenAsAssoc(date('Y-m-d', strtotime("-15days")), date('Y-m-d'));

		foreach ($employees as $employee)
		{
			$start_time = strtotime("-15days");
			$end_time = time();

			for($i = $start_time; $i <= $end_time; $i = strtotime("+1day", $i))
			{
				$i_date = date('Y-m-d', $i);		
				if (array_key_exists($i_date, $holidays) || !array_key_exists($employee['empid'], $employeeSchedules))
				{
					continue;
				}

				if (array_key_exists($i_date, $employeeSchedules[$employee['empid']]['days_data']))
				{
					$sched = $employeeSchedules[$employee['empid']]['days_data'][$i_date];
					$sched_date_start_time = strtotime($sched['start_time']);
					$sched_date_end_time = strtotime($sched['end_time']);
				}
				else
				{
					continue;
				}

				$rand_num = rand(1, 7);

				if ($rand_num <= 5)
				{
					$timesheetModel->create(array(
						'empid'         => $employee['empid'],
						'checkin'       => date('Y-m-d H:i:s', $sched_date_start_time),
						'checkout'      => date('Y-m-d H:i:s', $sched_date_end_time)
					));
				}
				else if ($rand_num == 6) // late 5
				{
					$mins = rand (0, 120);
					$mins2 = rand (0, 120);

					$timesheetModel->create(array(
						'empid'         => $employee['empid'],
						'checkin'       => date('Y-m-d H:i:s', strtotime(($mins - 60).'minutes', $sched_date_start_time)),
						'checkout'      => date('Y-m-d H:i:s', strtotime(($mins2 - 40).'minutes', $sched_date_end_time))
					));
				}
				else // absent
				{

				}
			}
		}

		exit;
	}
}