<?php

/**
 * The timekeeping controller class.
 */
class TimekeepingController extends BaseAdminController
{	
	public function executeUploadFile()
	{
		$objPHPExcel = new PHPExcel();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			if ($_FILES['file']['type'] == 'application/vnd.ms-excel')
			{
				$objReader = new PHPExcel_Reader_Excel5();
				$objReader->setReadDataOnly(true);
				$objPHPExcel = $objReader->load($_FILES['file']['tmp_name']);

				$objWorksheet = $objPHPExcel->getActiveSheet();

				$data = array();
				foreach ($objWorksheet->getRowIterator() as $row)
				{
					$cellIterator = $row->getCellIterator();
					$cellIterator->setIterateOnlyExistingCells(false);

					$d = array();
					foreach ($cellIterator as $cell)
					{
						$d[] = $cell->getValue();
					}

					$data[] = $d;
				}

				$max_dt_to_time = NULL;
				$min_dt_to_time = NULL;
				$id_to_timesheets = array();
				foreach ($data as $d)
				{
					if ($d[3] != 'Enroll ID' && $d[3] != '56')
					{
						$dt_to_time = strtotime($d[4]);
						$dt = date('Y-m-d', $dt_to_time);

						if ($max_dt_to_time == NULL || $dt_to_time > $max_dt_to_time)
						{
							$max_dt_to_time = $dt_to_time;
						}

						if ($min_dt_to_time == NULL || $dt_to_time < $min_dt_to_time)
						{
							$min_dt_to_time = $dt_to_time;
						}

						if (!array_key_exists($d[3], $id_to_timesheets))
						{
							$id_to_timesheets[$d[3]] = array();
						}

						for ($i = 5; $i <= 10; $i++)
						{
							if ('' != trim($d[$i]))
							{
								$id_to_timesheets[$d[3]][] = $dt.' '.trim($d[$i]); 
							}
						}
					}
				}

				$scheduleModel = $this->loadModel('EmployeeSchedule');
				$changedScheduleModel = $this->loadModel('EmployeeChangedSchedule');
				$employeeModel = $this->loadModel('Employee');

				if (NULL != $max_dt_to_time && NULL != $min_dt_to_time)
				{
					$employeeSchedules = $scheduleModel->getEmployeesIDToScheduleMapping(date('Y-m-d', $min_dt_to_time), date('Y-m-d', $max_dt_to_time));
					$changedSchedules = $changedScheduleModel->getEmployeesChangedSchedulesBetween(date('Y-m-d', $min_dt_to_time), date('Y-m-d', $max_dt_to_time));
					$employeeIdToFScanIDMappings = $employeeModel->getEmployeeIdsToFScanIdMappings(date('Y-m-d', $min_dt_to_time), date('Y-m-d', $max_dt_to_time));

					foreach ($employeeIdToFScanIDMappings as $employee_id => $fscan_id)
					{
						if (array_key_exists($fscan_id, $id_to_timesheets))
						{
							$timeInFound = false;
							$lastEntry = NULL;
							foreach ($id_to_timesheets[$fscan_id] as $fscan_entry)
							{
								$fscan_date = substr($fscan_entry, 0, 10);
								$fscan_time = substr($fscan_entry, 11, 8);

								
							}
						}

						// $employeeSchedForTheDay = NULL;
						// if (array_key_exists($employee_id, $employeeSchedules) && array_key_exists($iDate, $employeeSchedules[$employee_id]['days_data']))
						// {
						// 	$employeeSchedForTheDay = $employeeSchedules[$employee_id]['days_data'][$iDate];
						// }

						// // check first in the changed schedules before checking the user schedule
						// if (array_key_exists($employee_id, $changedSchedules['removed_schedules']) && array_key_exists($iDate, $changedSchedules['removed_schedules'][$employee_id]))
						// {
						// 	if (array_key_exists($iDate, $changedSchedules['added_schedules'][$employee_id]))
						// 	{
						// 		$sched_start_time = $iDate.$changedSchedules['added_schedules'][$employee_id][$iDate]['start_time'];
						// 		$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$employee_id][date('Y-m-d', $i)]['number_of_hours'].'hours', strtotime($sched_start_time)));

						// 		// changing of schedule time happened
						// 		$employeeSchedForTheDay = array(
						// 			'start_time'       => $sched_start_time,
						// 			'end_time'         => $sched_end_time,
						// 			'number_of_hours'  => $changedSchedules['added_schedules'][$employee_id][$iDate]['number_of_hours'],
						// 		);
						// 	}
						// 	else
						// 	{
						// 		$employeeSchedForTheDay = NULL; // no sched for today
						// 	}
						// }
						// else if (array_key_exists($employee_id, $changedSchedules['added_schedules']) && array_key_exists($iDate, $changedSchedules['added_schedules'][$employee_id]))
						// {
						// 	$sched_start_time = $iDate.$changedSchedules['added_schedules'][$employee_id][$iDate]['start_time'];
						// 	$sched_end_time = date('Y-m-d H:i:s', strtotime('+'.$changedSchedules['added_schedules'][$employee_id][$iDate]['number_of_hours'].'hours', strtotime($sched_start_time)));

						// 	// changing of schedule time happened
						// 	$employeeSchedForTheDay = array(
						// 		'start_time'       => $sched_start_time,
						// 		'end_time'         => $sched_end_time,
						// 		'number_of_hours'  => $changedSchedules['added_schedules'][$employee_id][$iDate]['number_of_hours'],
						// 	);
						// }
						// // end of checking of changed schedules
					}

					echo '<textarea>'.json_encode($employeeSchedules).'</textarea>';
					echo '<textarea>'.json_encode($changedSchedules).'</textarea>';
				}

				echo '<textarea>'.json_encode($id_to_timesheets).'</textarea>';
				echo '<br /> <br />';
				exit;
			}
		}
		else
		{

		}
	}

	public function executeChangeTimesheet()
	{
		$employeeModel = $this->loadModel('Employee');
		$timesheetModel = $this->loadModel('Timesheet');

		$this->employees = $employeeModel->getPayrollEmployees();
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;


			//echo values
			/*
			echo 'IN: ' . $_POST['date'].' '.$_POST['checkin_time'].':00' . '<br/>' .
			     'OUT ' . $_POST['checkout_date'].' '.$_POST['checkout_time'].':00' . '<br/>' . 
			     'empid'. $_POST['employee_id'];
			exit;
			*/

			$employee = $employeeModel->getByID($_POST['employee_id']);
			if (NULL != $employee)
			{
				$employeeTimesheet = $timesheetModel->getTimesheetOfEmployeeOn($_POST['employee_id'], $_POST['date']);

				$checkin = $_POST['date'].' '.$_POST['checkin_time'].':00';
				$checkout = $_POST['checkout_date'].' '.$_POST['checkout_time'].':00';
				if (strtotime($checkout) <= strtotime($checkin))
				{
					$checkout = date('Y-m-d', strtotime('+1day', strtotime($_POST['date']))).' '.$_POST['checkout_time'].':00';
				}

				if (is_array($employeeTimesheet))
				{
					$timesheetModel->update($employeeTimesheet['timeid'], array(
						'checkin'    => $checkin,
						'checkout'   => $checkout
					));
				}
				else
				{
					$timesheetModel->create(array(
						'checkin'    => $checkin,
						'checkout'   => $checkout,
						'empid'      => $_POST['employee_id']
					));
				}

				$this->success_message = 'Successfully changed timesheet for '.$employee['firstname'].' '.$employee['lastname'];
			}
			else
			{
				$this->error_message = 'Choose an employee first.';
			}
		}
		else
		{
			$this->fields = array(
				'checkin_time'    => '',
				'checkout_time'   => '',
				'date'            => '',
				'checkout_date'   => '',
				'employee_id'     => '0'
			);
		}
	}

	public function executeGetTimesheetOf()
	{
		if (
			array_key_exists('employee_id', $_POST) &&
			array_key_exists('date', $_POST)
		)
		{
			$timesheetModel = $this->loadModel('Timesheet');
			$employeeTimesheet = $timesheetModel->getTimesheetOfEmployeeOn($_POST['employee_id'], $_POST['date']);

			if (is_array($employeeTimesheet))
			{
				echo json_encode(array(
					'success'      => true,
					'message'      => 'Record found',
					'record_found' => true,
					'record'       => array(
						'checkin_time'   => date('H:i', strtotime($employeeTimesheet['checkin'])),
						'checkout_time'  => date('H:i', strtotime($employeeTimesheet['checkout'])),
						'checkout_date'  => date('Y-m-d', strtotime($employeeTimesheet['checkout']))
					)
				));
			}
			else
			{
				echo json_encode(array(
					'success'      => true,
					'message'      => 'Record not found',
					'record_found' => false
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

	public function executeViewEmployeeTimesheet()
	{

		$this->loadHelpers(array('generic', 'attendance'));

		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$employeeModel = $this->loadModel('Employee');
		$timesheetModel = $this->loadModel('Timesheet');

		$this->employees = $employeeModel->getPayrollEmployees();

		$this->data = array();

        /*
          //copy from backup
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->employee = $employeeModel->getByID($_POST['employee_id']);
			$this->forward404Unless(($this->employee != NULL));

			$employeeTimesheets = $timesheetModel->getTimesheetOfEmployeeWithDateKey($_POST['employee_id'], $_POST['start_date'], $_POST['end_date']);
			$employeeSchedules = $scheduleModel->getEmployeeIDToScheduleMapping($_POST['employee_id']);

			$this->data = build_employee_attendance($this->employee, $employeeSchedules, $employeeTimesheets, $_POST['start_date'], $_POST['end_date']);

			$this->setTemplate('_viewEmployeeTimesheetResult');
		}

        */

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$leaves = ''; 
			$holidays = '';
			$changedSchedules = '';
			$holidayPays = '';
			$otPays = '';

			$this->employee = $employeeModel->getByID($_POST['employee_id']);
			$this->forward404Unless(($this->employee != NULL));

			$employeeTimesheets = $timesheetModel->getTimesheetOfEmployeeWithDateKey($_POST['employee_id'], $_POST['start_date'], $_POST['end_date']);
			$employeeSchedules = $scheduleModel->getEmployeeIDToScheduleMapping($_POST['employee_id'], $_POST['start_date'], $_POST['end_date']);


             /*
            function build_employee_attendance($employee, $employeeSchedules, $employeeTimesheets, $start_date, $end_date, $leaves, $holidays, $changedSchedules, $holidayPays, $otPays)             
            */


			//!!$this->data = build_employee_attendance($this->employee, $employeeSchedules, $employeeTimesheets, $_POST['start_date'], $_POST['end_date']);
			$this->data = build_employee_attendance($this->employee, $employeeSchedules, $employeeTimesheets, $_POST['start_date'], $_POST['end_date']);

			$this->setTemplate('_viewEmployeeTimesheetResult');
		}
		else
		{
			$this->fields = array(
				'start_date'      => date('Y-m-d'),
				'end_date'        => date('Y-m-d', strtotime('+1day'))
			);
		}
	}

	public function executeIndex()
	{
		$this->loadHelpers(array('generic', 'attendance'));

		$employeeModel = $this->loadModel('Employee');
		$timesheetModel = $this->loadModel('Timesheet');
		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$holidayModel = $this->loadModel('Holiday');
		$changedScheduleModel = $this->loadModel('EmployeeChangedSchedule');
		$leaveModel = $this->loadModel('Leave');

		$employees = $employeeModel->getPayrollEmployees();
		$employeeSchedules = $scheduleModel->getEmployeesIDToScheduleMapping(date('Y-m-d'), date('Y-m-d'));
		$employeeTimesheets = $timesheetModel->getEmployeeTimesheetsBetween(date('Y-m-d'), date('Y-m-d'));
		$holiday = $holidayModel->getHolidayOn(date('Y-m-d'));
		$changedSchedules = $changedScheduleModel->getEmployeesChangedSchedulesBetween(date('Y-m-d'), date('Y-m-d'));
		$leaves = $leaveModel->getApprovedLeavesBetween(date('Y-m-d'), date('Y-m-d'));

		$this->data = build_employees_attendance_for_the_day($employees, $employeeSchedules, $employeeTimesheets, $holiday, $changedSchedules, $leaves);
	}

	public function executeGenerate()
	{
		$this->fields = array(
			'start_date'     => date('Y-m-d', strtotime('-15days')),
			'end_date'       => date('Y-m-d', strtotime('-1day'))
		);
	}

	public function executeDownloadAttendanceExcel()
	{
		$this->redirectUnless((array_key_exists('start_date', $_POST) && array_key_exists('end_date', $_POST)), $this->getConfig()->get('base_url').'/timekeeping');

		$this->loadHelpers(array('generic', 'attendance'));

		$employeeModel = $this->loadModel('Employee');
		$timesheetModel = $this->loadModel('Timesheet');
		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$holidayModel = $this->loadModel('Holiday');
		$changedScheduleModel = $this->loadModel('EmployeeChangedSchedule');
		$leaveModel = $this->loadModel('Leave');
		$holidayPayModel = $this->loadModel('HolidayPay');
		$overtimeModel = $this->loadModel('Overtime');

		$employees = $employeeModel->getPayrollEmployees();
		$employeeSchedules = $scheduleModel->getEmployeesIDToScheduleMapping($_POST['start_date'], $_POST['end_date']);
		$employeeTimesheets = $timesheetModel->getEmployeeTimesheetsBetween($_POST['start_date'], $_POST['end_date']);
		$holidays = $holidayModel->getHolidaysBetweenAsAssoc($_POST['start_date'], $_POST['end_date']);
		$changedSchedules = $changedScheduleModel->getEmployeesChangedSchedulesBetween($_POST['start_date'], $_POST['end_date']);
		$leaves = $leaveModel->getApprovedLeavesBetween($_POST['start_date'], $_POST['end_date']);
		$holidayPays = $holidayPayModel->getHolidayPayForEmployeesBetween($_POST['start_date'], $_POST['end_date']);
		$otPays = $overtimeModel->getOvertimeBetweenGroupedByDate($_POST['start_date'], $_POST['end_date']);

		$employeeAttendance = build_employees_attendance($employees, $employeeSchedules, $employeeTimesheets, $_POST['start_date'], $_POST['end_date'], $holidays, 10, $changedSchedules, $leaves, $holidayPays, $otPays);

		$this->data = $employeeAttendance['data'];
		$this->data_days = $employeeAttendance['days'];

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set document properties
		$objPHPExcel->getProperties()
				->setCreator("Gumi Payroll System")
				->setLastModifiedBy("Gumi Payroll System")
				->setTitle("Office 2007 XLSX Test Document")
				->setSubject("Office 2007 XLSX Test Document")
				->setDescription("Generated By Gumi Payroll System")
				->setKeywords("office 2007 openxml php");

		// feed data
		$objPHPExcel->setActiveSheetIndex(0)
				->mergeCells('A1:E2')
				->setCellValue('A1', 'ATTENDANCE');

		$objPHPExcel->getActiveSheet()->setTitle('AllDetails');

		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A3', 'HIRE DATE')
				->setCellValue('B3', 'SURNAME')
				->setCellValue('C3', 'FIRST NAME')
				->setCellValue('D3', 'DAYS')
				->setCellValue('E3', 'SCHED');
				
		$i = 4;
		foreach ($this->data as $employee)
		{
			$objPHPExcel->getActiveSheet()
				->setCellValue('A'.$i, date('d-M-y', strtotime($employee['date_hired'])))
				->setCellValue('B'.$i, $employee['lastname'])
				->setCellValue('C'.$i, $employee['firstname'])
				->setCellValue('D'.$i, $employee['sched']['days'])
				->setCellValue('E'.$i, $employee['sched']['time']);

			foreach ($this->data_days as $j => $data_day)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(5 + ($j * 4)).$i, $employee['dates'][$data_day]['time_in'])
					->setCellValue(convert_int_to_excel_column(6 + ($j * 4)).$i, $employee['dates'][$data_day]['time_out'])
					->setCellValue(convert_int_to_excel_column(7 + ($j * 4)).$i, $employee['dates'][$data_day]['total'])
					->setCellValue(convert_int_to_excel_column(8 + ($j * 4)).$i, $employee['dates'][$data_day]['status']);

				if ($employee['dates'][$data_day]['status'] == 'A' || $employee['dates'][$data_day]['status'] == 'RD')
				{
					$objPHPExcel->getActiveSheet()
						->getStyle(convert_int_to_excel_column(5 + ($j * 4)).$i.':'.convert_int_to_excel_column(8 + ($j * 4)).$i)
						->getFont()
						->getColor()
						->setARGB('FF'.excel_color_for_attendance_status($employee['dates'][$data_day]['status']));
				}
				else if ($employee['dates'][$data_day]['status'] == 'U')
				{
					$objPHPExcel->getActiveSheet()
						->getStyle(convert_int_to_excel_column(7 + ($j * 4)).$i)
						->getFont()
						->getColor()
						->setARGB('FF'.excel_color_for_attendance_status('L'));

					$objPHPExcel->getActiveSheet()
						->getStyle(convert_int_to_excel_column(8 + ($j * 4)).$i)
						->getFont()
						->getColor()
						->setARGB('FF'.excel_color_for_attendance_status('U'));													
				}
				else if ($employee['dates'][$data_day]['status'] == 'L')
				{
					$objPHPExcel->getActiveSheet()
						->setCellValue(convert_int_to_excel_column(8 + ($j * 4)).$i, '-'.$employee['dates'][$data_day]['status_val'].'hr')
						->getStyle(convert_int_to_excel_column(7 + ($j * 4)).$i.':'.convert_int_to_excel_column(8 + ($j * 4)).$i)
						->getFont()
						->getColor()
						->setARGB('FF'.excel_color_for_attendance_status('L'));
				}
				else if ($employee['dates'][$data_day]['status'] == 'PR')
				{
					$objPHPExcel->getActiveSheet()
						->setCellValue(convert_int_to_excel_column(8 + ($j * 4)).$i, 'P');
				}
			}

			$i++;
		}

		$k = 5;
		foreach ($this->data_days as $data_day)
		{
			$objPHPExcel->getActiveSheet()
					->mergeCells(convert_int_to_excel_column($k).'1:'.convert_int_to_excel_column($k + 3).'2')
					->setCellValue(convert_int_to_excel_column($k).'1', date('d-M-y', strtotime($data_day)))
					->setCellValue(convert_int_to_excel_column($k).'3', 'IN')
					->setCellValue(convert_int_to_excel_column($k + 1).'3', 'OUT')
					->setCellValue(convert_int_to_excel_column($k + 2).'3', 'TOTAL')
					->setCellValue(convert_int_to_excel_column($k + 3).'3', 'Note');

			$k += 4;
		}
		// end of feed data

		// set styles

		// style for Attendance
		$styleArray = array(
			'font' => array(
				'bold'  => true,
				'color' => array(
					'argb' => 'FF474747'
				),
				'size'  => 26,
				'font'  => 'Calibri'
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				) 
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FF9DC3E7'
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('A1:E2')->applyFromArray($styleArray);	

		// style for 'Hire Date', 'Surname', 'Last Name'
		$styleArray['font']['color']['argb'] = 'FF000000';
		$styleArray['font']['size'] = 9;
		$styleArray['fill']['startcolor']['argb'] = 'FFFEE699';
		$objPHPExcel->getActiveSheet()->getStyle('A3:E3')->applyFromArray($styleArray);	

		// style for employee info 
		$styleArray['font']['bold'] = false;
		$styleArray['font']['color']['argb'] = 'FF000000';
		$styleArray['fill']['startcolor']['argb'] = 'FFE2F0D9';
		$objPHPExcel->getActiveSheet()->getStyle('A4:E'.($i - 1))->applyFromArray($styleArray);

		$styleArray2 = array(
			'borders' => array(
				'right' => array(
					'style' => PHPExcel_Style_Border::BORDER_MEDIUM
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('A1:E'.($i - 1))->applyFromArray($styleArray2);

		// freeze pane
		$objPHPExcel->getActiveSheet()->freezePane('F'.$i);

		// style for header dates
		$styleArray['font']['bold'] = true;
		$styleArray['font']['size'] = 26;
		$styleArray['font']['color']['argb'] = 'FF474747';
		$styleArray['fill']['startcolor']['argb'] = 'FF9DC3E7';
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(5).'1:'.convert_int_to_excel_column($k - 4).'2')->applyFromArray($styleArray);

		// style for 'IN', 'OUT', 'TOTAL', 'NOTES'
		$styleArray['font']['size'] = 9;
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(5).'3:'.convert_int_to_excel_column($k - 1).'3')->applyFromArray($styleArray);

		$styleArray['font']['bold'] = false;
		$styleArray['font']['size'] = 11;
		unset($styleArray['font']['color']);
		$styleArray['fill']['startcolor']['argb'] = 'FFDEEBF7';
		$objPHPExcel->getActiveSheet()->getStyle('F4:'.convert_int_to_excel_column($k - 1).($i - 1))->applyFromArray($styleArray);
		// end of styles

		// summary sheet -- SHEET 2
		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);

		$objPHPExcel->getActiveSheet()
					->setCellValue('B1', 'GUMI ASIA  PTE LTD (PHILIPPINES BRANCH)')
					->setCellValue('B2', 'Period Covered :')
					->setCellValue('C2', date('M d, Y', strtotime($_POST['start_date'])).' - '.date('M d, Y', strtotime($_POST['end_date'])))
					->setCellValue('B4', 'TIMEKEEPING SUMMARY')
					->setCellValue('B6', 'LNAME')
					->setCellValue('C6', 'FNAME')
					->setCellValue('D6', 'SCHED')
					->setCellValue('E6', 'TIME');

		foreach ($this->data_days as $j => $data_day)
		{
			$objPHPExcel->getActiveSheet()
				->setCellValue(convert_int_to_excel_column(5 + $j).'6', date('j', strtotime($data_day)))
				->setCellValue(convert_int_to_excel_column(5 + $j).'5', date('D', strtotime($data_day)));
		}

		$data_days_len = count($this->data_days);
		$objPHPExcel->getActiveSheet()
			->setCellValue(convert_int_to_excel_column(5 + $data_days_len).'6', 'NS')
			->setCellValue(convert_int_to_excel_column(6 + $data_days_len).'6', 'NS.OT')
			->setCellValue(convert_int_to_excel_column(7 + $data_days_len).'6', 'ADJ')
			->setCellValue(convert_int_to_excel_column(8 + $data_days_len).'6', 'NO DAYS')
			->setCellValue(convert_int_to_excel_column(9 + $data_days_len).'6', 'W/O PAY')
			->setCellValue(convert_int_to_excel_column(10 + $data_days_len).'6', 'W PAY')
			->setCellValue(convert_int_to_excel_column(9 + $data_days_len).'4', 'LEAVE/ABSENCE')
			->setCellValue(convert_int_to_excel_column(9 + $data_days_len).'5', 'NO. OF DAYS')
			->setCellValue(convert_int_to_excel_column(11 + $data_days_len).'6', 'Total HOURS')
			->setCellValue(convert_int_to_excel_column(11 + $data_days_len).'5', 'Tardy/UT')
			->setCellValue(convert_int_to_excel_column(12 + $data_days_len).'6', 'RD')
			->setCellValue(convert_int_to_excel_column(13 + $data_days_len).'6', 'REG')
			->setCellValue(convert_int_to_excel_column(14 + $data_days_len).'6', 'SPE')
			->setCellValue(convert_int_to_excel_column(15 + $data_days_len).'6', 'LEG')
			->setCellValue(convert_int_to_excel_column(12 + $data_days_len).'5', 'OT')
			->setCellValue(convert_int_to_excel_column(16 + $data_days_len).'6', 'REMARKS');

		$objPHPExcel->getActiveSheet()
			->mergeCells(convert_int_to_excel_column(9 + $data_days_len).'4:'.convert_int_to_excel_column(10 + $data_days_len).'4')
			->mergeCells(convert_int_to_excel_column(9 + $data_days_len).'5:'.convert_int_to_excel_column(10 + $data_days_len).'5')
			->mergeCells(convert_int_to_excel_column(12 + $data_days_len).'5:'.convert_int_to_excel_column(15 + $data_days_len).'5');		

		$i = 7;
		$ctr = 1;
		$toCountAsOneDayStatus = array('L', 'U', 'PR', 'HD');
		foreach ($this->data as $key => $employee)
		{
			$objPHPExcel->getActiveSheet()
				->setCellValue('A'.$i, $ctr)
				->setCellValue('B'.$i, strtoupper($employee['lastname']))
				->setCellValue('C'.$i, strtoupper($employee['firstname']))
				->setCellValue('D'.$i, $employee['sched']['days'])
				->setCellValue('E'.$i, $employee['sched']['time']);

			$total_hours = 0;
			$spent_hours = 0.0;
			$total_tardy_and_undertime_in_hours = 0;
			$total_nightshift_in_hours = 0.0;
			$leave_with_pay = 0.0;
			$leave_without_pay = 0.0;
			$regular_holiday_hours_spent = 0.0;
			$special_non_working_holiday_hours_spent = 0.0;
			$total_normal_ot_hours = 0.0;
			$total_nightshift_ot_hours = 0.0;
			foreach ($this->data_days as $j => $data_day)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(5 + $j).$i, $employee['dates'][$data_day]['status']);

				if (in_array($employee['dates'][$data_day]['status'], $toCountAsOneDayStatus))
				{
					$total_hours += (float) $employee['dates'][$data_day]['total'];
					$spent_hours += (float) $employee['dates'][$data_day]['spent_hours'];

					if ($employee['dates'][$data_day]['has_holiday_pay'])
					{
						if ($employee['dates'][$data_day]['holiday_type'] == 'regular_working')
						{
							$regular_holiday_hours_spent += (float) $employee['dates'][$data_day]['spent_hours'];
						}
						else if ($employee['dates'][$data_day]['holiday_type'] == 'special_non_working')
						{
							$special_non_working_holiday_hours_spent += (float) $employee['dates'][$data_day]['spent_hours'];
						}
					}

					if ($employee['dates'][$data_day]['has_ot_pay'])
					{
						$total_normal_ot_hours += (float) $employee['dates'][$data_day]['normal_ot_hours'];
						$total_nightshift_ot_hours += (float) $employee['dates'][$data_day]['night_differential_ot_hours'];
					}
				}

				$total_nightshift_in_hours += $employee['dates'][$data_day]['nightdiff_hours'];

				if ($employee['dates'][$data_day]['status'] == 'U')
				{
					$total_tardy_and_undertime_in_hours += ($employee['dates'][$data_day]['sched_total_hours'] - $employee['dates'][$data_day]['total_hours']); 
				}
				else if ($employee['dates'][$data_day]['status'] == 'L')
				{
					$total_tardy_and_undertime_in_hours += $employee['dates'][$data_day]['status_val'];
				}
				else if (
					$employee['dates'][$data_day]['status'] == 'SL' ||
					$employee['dates'][$data_day]['status'] == 'VL' ||
					$employee['dates'][$data_day]['status'] == 'EL'
				)
				{
					if ($employee['dates'][$data_day]['is_paid'])
					{
						$leave_with_pay += 1.0;
					}
					else
					{
						$leave_without_pay += 1.0;
					}
				}
				else if ($employee['dates'][$data_day]['status'] == 'HD')
				{
					if ($employee['dates'][$data_day]['is_paid'])
					{
						$leave_with_pay += 0.5;
					}
					else
					{
						$leave_without_pay += 0.5;
					}
				}

				if ($employee['dates'][$data_day]['status'] == 'PR')
				{
					$objPHPExcel->getActiveSheet()
						->setCellValue(convert_int_to_excel_column(5 + $j).$i, 'P');
				}

				if ($employee['dates'][$data_day]['status'] == 'PR')
				{
					$objPHPExcel->getActiveSheet()
						->setCellValue(convert_int_to_excel_column(5 + $j).$i, 'P');
				}
				else
				{
					$objPHPExcel->getActiveSheet()
						->getStyle(convert_int_to_excel_column(5 + $j).$i)
						->getFont()
						->getColor()
						->setARGB('FF'.excel_color_for_attendance_status($employee['dates'][$data_day]['status']));					
				}

				$objPHPExcel->getActiveSheet()
					->getCell(convert_int_to_excel_column(5 + $j).$i)
					->getHyperlink()
					->setUrl("sheet://'AllDetails'!".convert_int_to_excel_column(8 + ($j * 4)).($i - 3));	
			}

			$data_days_len = count($this->data_days);
			$objPHPExcel->getActiveSheet()
				->setCellValue(convert_int_to_excel_column(8 + $data_days_len).$i, round(($spent_hours / 8.0), 1));

			if (0 < $total_tardy_and_undertime_in_hours)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(11 + $data_days_len).$i, round($total_tardy_and_undertime_in_hours, 2));
			}

			if (0 < $total_nightshift_in_hours)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(5 + $data_days_len).$i, round($total_nightshift_in_hours));
			}

			if (0 < $leave_with_pay)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(10 + $data_days_len).$i, $leave_with_pay);		
			}

			if (0 < $leave_without_pay)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(9 + $data_days_len).$i, $leave_without_pay);	
			}

			if (0 < $regular_holiday_hours_spent)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(13 + $data_days_len).$i, $regular_holiday_hours_spent);	
			}

			if (0 < $special_non_working_holiday_hours_spent)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(14 + $data_days_len).$i, $special_non_working_holiday_hours_spent);	
			}

			if (0 < $total_normal_ot_hours)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(12 + $data_days_len).$i, $total_normal_ot_hours);
			}

			if (0 < $total_nightshift_ot_hours)
			{
				$objPHPExcel->getActiveSheet()
					->setCellValue(convert_int_to_excel_column(6 + $data_days_len).$i, $total_nightshift_ot_hours);
			}

			$i++;
			$ctr++;
		}

		$objPHPExcel->getActiveSheet()
			->setCellValue('C'.($i + 1), 'Legend:')
			->setCellValue('C'.($i + 3), 'If day is without pay')
			->setCellValue('D'.($i + 3), 'A')
			->setCellValue('C'.($i + 4), 'If day is VL or SL (with pay)')
			->setCellValue('D'.($i + 4), 'VL/SL')
			->setCellValue('C'.($i + 5), 'If day is plainly regular day (8 hrs-normal)')
			->setCellValue('D'.($i + 5), 'P')
			->setCellValue('C'.($i + 6), 'If regular day is with late/undertime')
			->setCellValue('D'.($i + 6), '-0.5hrs')
			->setCellValue('C'.($i + 7), 'If day is regular day with regular overtime')
			->setCellValue('D'.($i + 7), '5')
			->setCellValue('C'.($i + 8), 'If day is a special/rest day (total hours)')
			->setCellValue('D'.($i + 8), '5')
			->setCellValue('C'.($i + 9), 'If day is rest day and unworked')
			->setCellValue('D'.($i + 9), 'RD')
			->setCellValue('C'.($i + 10), 'Employee not yet started')
			->setCellValue('D'.($i + 10), 'NS')
			->setCellValue('C'.($i + 11), 'Separated')
			->setCellValue('D'.($i + 11), 'S')
			->setCellValue('C'.($i + 12), 'Holiday')
			->setCellValue('D'.($i + 12), 'H')
			->setCellValue('C'.($i + 13), 'Half-day')
			->setCellValue('D'.($i + 13), 'HD');


		// freeze pane
		$objPHPExcel->getActiveSheet()->freezePane('E7');

		// styles
		$styleArray = array(
			'font' => array(
				'bold'  => true,
				'color' => array(
					'argb' => 'FF000000'
				),
				'size'  => 12,
				'font'  => 'Arial'
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				) 
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FFFDD966'
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('B6:'.convert_int_to_excel_column(count($this->data_days) + 4).'6')->applyFromArray($styleArray);

		$styleArray['fill']['startcolor']['argb'] = 'FFFFFFFF';
		$objPHPExcel->getActiveSheet()->getStyle('F5:'.convert_int_to_excel_column(count($this->data_days) + 4).'5')->applyFromArray($styleArray);

		$styleArray['fill']['startcolor']['argb'] = 'FF548136';
		$styleArray['font']['color']['argb'] = 'FFFFFFFF';
		$styleArray['font']['size'] = 11;
		$styleArray['font']['bold'] = false;
		$objPHPExcel->getActiveSheet()->getStyle('D7:E'.($i - 1))->applyFromArray($styleArray);

		$styleArray['fill']['startcolor']['argb'] = 'FFE2F0D9';
		$styleArray['font']['color']['argb'] = 'FF000000';
		$styleArray['font']['size'] = 11;
		$styleArray['font']['bold'] = false;
		$styleArray['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
		$objPHPExcel->getActiveSheet()->getStyle('B7:C'.($i - 1))->applyFromArray($styleArray);

		$styleArray['fill']['startcolor']['argb'] = 'FFFFFFFF';
		$styleArray['font']['color']['argb'] = 'FF000000';
		$styleArray['font']['size'] = 12;
		$styleArray['font']['bold'] = true;
		$styleArray['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(5 + $data_days_len).'6:'.convert_int_to_excel_column(16 + $data_days_len).'6')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(9 + $data_days_len).'4:'.convert_int_to_excel_column(10 + $data_days_len).'5')->applyFromArray($styleArray);
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(11 + $data_days_len).'5:'.convert_int_to_excel_column(15 + $data_days_len).'5')->applyFromArray($styleArray);

		$styleArray = array(
			'font' => array(
				'bold'  => false,
				'size'  => 11,
				'font'  => 'Arial'
			),
			'alignment' => array(
				'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
			),
			'borders' => array(
				'allborders' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				) 
			),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array(
					'argb' => 'FFDEEBF7'
				)
			)
		);
		$objPHPExcel->getActiveSheet()->getStyle('F7:'.convert_int_to_excel_column(count($this->data_days) + 4).($i - 1))->applyFromArray($styleArray);

		$styleArray['fill']['startcolor']['argb'] = 'FFFFFFFF';
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(6 + $data_days_len).'6:'.'D'.($i + 13))->applyFromArray($styleArray);

		$styleArray['fill']['startcolor']['argb'] = 'FFFFFFFF';
		$objPHPExcel->getActiveSheet()->getStyle(convert_int_to_excel_column(5 + $data_days_len).'7:'.convert_int_to_excel_column(16 + $data_days_len).($i + 13))->applyFromArray($styleArray);

		$styleArray['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
		$objPHPExcel->getActiveSheet()->getStyle('C'.($i + 3).':D'.($i + 13))->applyFromArray($styleArray);

		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(26);
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(12);

		$objPHPExcel->getActiveSheet()->getColumnDimension(convert_int_to_excel_column(8 + $data_days_len))->setWidth(18);
		$objPHPExcel->getActiveSheet()->getColumnDimension(convert_int_to_excel_column(11 + $data_days_len))->setWidth(18);
		$objPHPExcel->getActiveSheet()->getColumnDimension(convert_int_to_excel_column(16 + $data_days_len))->setWidth(50);
		// end summary sheet

		// Redirect output to a client’s web browser (Excel5)
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="01simple.xls"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save('php://output');

		exit;
	}
}