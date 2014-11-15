<?php
/**
 * The timekeeping controller class.
 */
class PayrollTask extends CcTask
{	
	public function executeGenerateSpreadsheet($params)
	{
		echo "Starting task.\n";

		$this->loadModel(array(
			'Payroll', 'EmployeePayroll',
			'Employee', 'Timesheet',
			'EmployeeSchedule', 'Holiday',
			'EmployeeChangedSchedule', 'Leave',
			'HolidayPay', 'Overtime'
		));

		$this->loadHelpers(array('generic', 'attendance', 'payroll'));

		if (!array_key_exists('id', $params))
		{
			echo "id does not exists in params.\n";
			return false;
		}

		$payroll = $this->Payroll->getById($params['id']);
		if ($payroll == NULL)
		{
			echo "Payroll does not exists with id [".$params['id']."].\n";
			return false;
		}

		echo "Starting payroll update.\n";

		$this->Payroll->update($payroll['id'], array(
			'script_is_running' => true,
			'script_run_start'  => date('Y-m-d H:i:s')
		));

		$payroll_date_to_time = strtotime($payroll['payroll_date']);
		$start_date_to_time = strtotime($payroll['start_date']);
		$end_date_to_time = strtotime($payroll['end_date']);

		$employeePayrolls = $this->EmployeePayroll->getByPayrollIDJoinEmployees($params['id']);
		$employeeSchedules = $this->EmployeeSchedule->getEmployeesIDToScheduleMapping($payroll['start_date'], $payroll['end_date']);
		$employeeTimesheets = $this->Timesheet->getEmployeeTimesheetsBetween($payroll['start_date'], $payroll['end_date']);
		$holidays = $this->Holiday->getHolidaysBetweenAsAssoc($payroll['start_date'], $payroll['end_date']);
		$changedSchedules = $this->EmployeeChangedSchedule->getEmployeesChangedSchedulesBetween($payroll['start_date'], $payroll['end_date']);
		$leaves = $this->Leave->getApprovedLeavesBetween($payroll['start_date'], $payroll['end_date']);
		$holidayPays = $this->HolidayPay->getHolidayPayForEmployeesBetween($payroll['start_date'], $payroll['end_date']);
		$otPays = $this->Overtime->getOvertimeBetweenGroupedByDate($payroll['start_date'], $payroll['end_date']);

		$employeeAttendance = build_employees_attendance($employeePayrolls, $employeeSchedules, $employeeTimesheets, $payroll['start_date'], $payroll['end_date'], $holidays, 10, $changedSchedules, $leaves, $holidayPays, $otPays);

		$this->data = $employeeAttendance['data'];
		$this->data_days = $employeeAttendance['days'];

		$tax_due = 'Tax due ';
		$tax_due .= (((int)date('j', $payroll_date_to_time)) <= 15) ? '1' : '2';
		$tax_due .= 'Q'.date('M Y', $payroll_date_to_time);

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// use template
		$objReader = new PHPExcel_Reader_Excel5();
		$objPHPExcel = $objReader->load($this->getConfig()->get('__APP_DIR__').'/data/payroll_template.xls');

		// feed data
		$objPHPExcel->setActiveSheetIndex(3);
				
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
				'name'  => 'Calibri'
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
		// $objPHPExcel->getActiveSheet()->getStyle('A1:E2')->applyFromArray($styleArray);	

		// style for employee info 
		$styleArray['font']['size'] = 9;
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
		$objPHPExcel->setActiveSheetIndex(2);
		$objPHPExcel->getActiveSheet()
					->setCellValue('C2', date('M d, Y', $start_date_to_time).' - '.date('M d, Y', $end_date_to_time));

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

		$total_data = array();
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
			$total_absent_days = 0;
			$total_absent_hours = 0;
			$total_work_hours = 0.0;
			$total_work_days = 0;
			foreach ($this->data_days as $j => $data_day)
			{
				$total_work_hours += $employee['dates'][$data_day]['work_hours'];

				if ($employee['dates'][$data_day]['status'] != 'RD' || $employee['dates'][$data_day]['status'] != 'HL')
				{
					$total_work_days++;
				}

				if ($employee['dates'][$data_day]['status'] == 'A')
				{
					$total_absent_days++;
					$total_absent_hours += $employee['dates'][$data_day]['work_hours'];
				}

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
					->setUrl("sheet://'Details'!".convert_int_to_excel_column(8 + ($j * 4)).($i - 3));	
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

			$total_data[$key] = array(
				'total_work_hours'       => $total_work_hours,
				'total_work_days'        => $total_work_days,
				'total_absent_days'      => $total_absent_days,
				'total_absent_hours'     => $total_absent_hours,
				'total_late_or_ut'       => $total_tardy_and_undertime_in_hours,
				'total_nightshift_hours' => $total_nightshift_in_hours
			);

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

		// headers info
		$objPHPExcel->setActiveSheetIndex(1)
				->setCellValue('A2', 'PAYROLL_'.date('F j, Y', $payroll_date_to_time))
				->setCellValue('A3', 'DTR FROM ( '.date('M j', $start_date_to_time).' - '.date('M j, Y', $end_date_to_time).' FOR LATES, ABSENCES, ND (+15%), HOLIDAY PAY)')
				->setCellValue('Y8', 'Lates ('.date('M j', $start_date_to_time).' - '.date('M j', $end_date_to_time).')')
				->setCellValue('AK6', $tax_due);

		$objPHPExcel->getActiveSheet()->setTitle(date('M j, Y', $payroll_date_to_time).'('.date('j', $start_date_to_time).'-'.date('j', $end_date_to_time).')');
		// end headers info

		// pay slip values
		$objPHPExcel->setActiveSheetIndex(0);
		$payslipValues = $objPHPExcel->getActiveSheet()->rangeToArray('B1:E40');
		$payslipStyleArray = array(
			'font' => array(
				'bold'  => true,
				'size'  => 14,
				'name'  => 'Tahoma'
			)
		);

		$rowHeights = array();
		for ($i = 1; $i <= 38; $i++)
		{
			$rowHeights[$i] = $objPHPExcel->getActiveSheet()->getRowDimension($i)->getRowHeight();
		}

		// data
		$key = -1;
		foreach ($employeePayrolls as $employeePayroll)
		{
			$sss_contrib = ($employeePayroll['has_sss_deduction'] == 1) ? get_sss_contribution_for_salary($employeePayroll['gross_pay']) : 0;
			$philhealth_contrib = ($employeePayroll['has_philhealth_deduction'] == 1) ? get_philhealth_contribution_for_salary($employeePayroll['gross_pay']) : 0;
			$pagibig_contrib = ($employeePayroll['has_pagibig_deduction'] == 1) ? 100 : 0;
			$total_nightshift_hours = (int) $total_data[$employeePayroll['employee_id']]['total_nightshift_hours'];
			$total_work_days = (int) $total_data[$employeePayroll['employee_id']]['total_work_days'];
			$total_absent_days = (int) $total_data[$employeePayroll['employee_id']]['total_absent_days'];
			$total_late_or_ut = (int) $total_data[$employeePayroll['employee_id']]['total_late_or_ut'];
			$total_absent_hours = (int) $total_data[$employeePayroll['employee_id']]['total_absent_hours'];

			$gross_pay = (int) $employeePayroll['gross_pay'];
			$daily_rate = ($gross_pay / 22);
			$hourly_rate = ($daily_rate / 8);

			$absent_deduction = $daily_rate * $total_absent_days;
			$late_or_ut_deduction = $hourly_rate * $total_late_or_ut;
			$nightdiff_pay = $hourly_rate * $total_nightshift_hours * 0.15;
			$total_overtime = $nightdiff_pay; // ND, overtime and calculation for holidays -- for now night diff
			$taxable_income = ((ceil($gross_pay / 22) * $total_work_days) + $total_overtime); 
			$taxable_income -= ($absent_deduction + $late_or_ut_deduction);
			$taxable_income -= ($sss_contrib + $philhealth_contrib + $pagibig_contrib);
			// add phone, core value, referral fee
			// subtract rice allowance and de minimis benefit

			$tax_object = get_tax_object_for_half_month_pay($taxable_income, $employeePayroll['tax_status']);
			$net_pay = $tax_object['pay_less_withholding_tax']; // add rice allowance and others

			$key++;
			$row = (9 + $key);
			$objPHPExcel->setActiveSheetIndex(1);
			$objPHPExcel->getActiveSheet()
				->setCellValue('A'.$row, ($key + 1))
				->setCellValue('B'.$row, $employeePayroll['lastname'])
				->setCellValue('C'.$row, $employeePayroll['firstname'])
				->setCellValue('D'.$row, date('d-M-Y', strtotime($employeePayroll['date_hired'])))
				->setCellValue('E'.$row, $total_work_days)
				->setCellValue('F'.$row, $gross_pay)
				->setCellValue('G'.$row, '=F'.$row.'/22')
				->setCellValue('H'.$row, '=G'.$row.'/8')
				->setCellValue('I'.$row, $total_absent_days)
				->setCellValue('K'.$row, $total_late_or_ut)
				->setCellValue('M'.$row, $total_nightshift_hours)
				->setCellValue('Q'.$row, '=H'.$row.'*M'.$row.'*0.15')
				->setCellValue('R'.$row, '=H'.$row.'*1.25*L'.$row)
				->setCellValue('S'.$row, '=H'.$row.'*1.3*O'.$row)
				->setCellValue('T'.$row, '=H'.$row.'*N'.$row.'*0.45')
				->setCellValue('U'.$row, '=H'.$row.'*P'.$row)
				->setCellValue('V'.$row, '=SUM(Q'.$row.':U'.$row.')')
				->setCellValue('W'.$row, '=F'.$row.'/22*E'.$row.'+V'.$row)
				->setCellValue('X'.$row, '=W'.$row)
				->setCellValue('Y'.$row, '=AT'.$row.'+AU'.$row)
				->setCellValue('Z'.$row, '=X'.$row.'-Y'.$row)
				->setCellValue('AA'.$row, $sss_contrib) // SSS
				->setCellValue('AB'.$row, $philhealth_contrib) // philhealth
				->setCellValue('AC'.$row, $pagibig_contrib) // pagibig
				->setCellValue('AD'.$row, '0') // phone allowance
				->setCellValue('AE'.$row, '0') // core value
				->setCellValue('AF'.$row, '0') // referral
				->setCellValue('AG'.$row, '=Z'.$row.'-AA'.$row.'-AB'.$row.'-AC'.$row.'+AD'.$row.'+AE'.$row.'+AF'.$row) // taxable income
				->setCellValue('AH'.$row, '0') // rice allowance
				->setCellValue('AI'.$row, '0') // de minimis benefit
				->setCellValue('AJ'.$row, '=AG'.$row.'-AH'.$row.'-AI'.$row) // net taxable income
				->setCellValue('AK'.$row, '=BL'.$row) // tax due 1H
				->setCellValue('AL'.$row, '') // tax adjustment
				->setCellValue('AM'.$row, '=AG'.$row.'-AK'.$row.'-AL'.$row) // salary net of tax
				->setCellValue('AN'.$row, '') // less SSS loan
				->setCellValue('AO'.$row, '') // less HDMF loan
				->setCellValue('AP'.$row, '') // adjustment
				->setCellValue('AQ'.$row, '=AG'.$row.'+AH'.$row.'-AK'.$row.'-AL'.$row) // net pay
				->setCellValue('AR'.$row, '') // tax status
				->setCellValue('AT'.$row, '=G'.$row.'*I'.$row)
				->setCellValue('AU'.$row, '=H'.$row.'*K'.$row)
				->setCellValue('AU'.$row, '=H'.$row.'*K'.$row)
				->setCellValue('AV'.$row, $sss_contrib) // SSS
				->setCellValue('AW'.$row, $philhealth_contrib) // Philhealth
				->setCellValue('AX'.$row, $pagibig_contrib) // Pagibig
				->setCellValue('BC'.$row, '=AJ'.$row)
				->setCellValue('BE'.$row, '=BC'.$row.'+BD'.$row)
				->setCellValue('BI'.$row, '=BE'.$row.'-BG'.$row)
				->setCellValue('BK'.$row, '=BI'.$row.'*BJ'.$row)
				->setCellValue('BG'.$row, $tax_object['exemption'])
				->setCellValue('BH'.$row, $tax_object['basic_tax'])
				->setCellValue('BI'.$row, '=BE'.$row.'-BG'.$row)
				->setCellValue('BJ'.$row, $tax_object['percentage'].'%')
				->setCellValue('BK'.$row, '=BI'.$row.'*BJ'.$row)
				->setCellValue('BL'.$row, '=BH'.$row.'+BK'.$row)
				;

			// pay slip
			$objPHPExcel->setActiveSheetIndex(0);
			$start_row = ((floor($key / 2) * 41) + 1);
			$start_col = (($key % 2) == 0) ? 'B' : 'G';
			$mid_col1  = (($key % 2) == 0) ? 'C' : 'H';
			$mid_col2  = (($key % 2) == 0) ? 'D' : 'I';
			$end_col   = (($key % 2) == 0) ? 'E' : 'J';

			$absent_deduction = $daily_rate * $total_absent_days;
			$late_or_ut_deduction = $hourly_rate * $total_late_or_ut;

			$deduction_hours = $total_absent_hours + $total_late_or_ut;

			$objPHPExcel->getActiveSheet()->fromArray($payslipValues, '', $start_col.$start_row);
			$objPHPExcel->getActiveSheet()
				->mergeCells($start_col.$start_row.':'.$end_col.$start_row)
				->mergeCells($start_col.($start_row + 1).':'.$end_col.($start_row + 1))
				->mergeCells($start_col.($start_row + 7).':'.$mid_col2.($start_row + 7))
				->mergeCells($start_col.($start_row + 21).':'.$mid_col2.($start_row + 21))
				->mergeCells($start_col.($start_row + 27).':'.$end_col.($start_row + 27))
				->mergeCells($start_col.($start_row + 36).':'.$end_col.($start_row + 36))
				->setCellValue($start_col.($start_row + 2), "Covered Period: ".date('M j', $start_date_to_time)." - ".date('M j, Y', $end_date_to_time))
				->setCellValue($mid_col1.($start_row + 2), "Payroll Period: ".date('M j, Y', $payroll_date_to_time))
				->setCellValue($start_col.($start_row + 4), "EMPLOYEE : ".$employeePayroll['lastname'].", ".$employeePayroll['firstname'])
				->setCellValue($end_col.($start_row + 5), $gross_pay)
				->setCellValue($start_col.($start_row + 6), "DATE HIRED: ".date('M d, Y', strtotime($employeePayroll['date_hired'])))
				->setCellValue($mid_col1.($start_row + 9), $total_work_days)
				->setCellValue($end_col.($start_row + 9), ceil($total_work_days * $daily_rate))
				->setCellValue($mid_col1.($start_row + 12), ($nightdiff_pay > 0) ? $total_nightshift_hours : '')
				->setCellValue($end_col.($start_row + 12), ($nightdiff_pay > 0) ? $nightdiff_pay : '')
				->setCellValue($end_col.($start_row + 19), ceil($total_work_days * $daily_rate) + $total_overtime)
				->setCellValue($mid_col1.($start_row + 20), ($deduction_hours > 0) ? $deduction_hours : '')
				->setCellValue($end_col.($start_row + 20), ($deduction_hours > 0) ? ($deduction_hours * $hourly_rate) : '')
				->setCellValue($end_col.($start_row + 21), (ceil($total_work_days * $daily_rate) + $total_overtime) - ($deduction_hours * $hourly_rate))
				->setCellValue($mid_col1.($start_row + 24), ($sss_contrib > 0) ? $sss_contrib : '')
				->setCellValue($mid_col1.($start_row + 25), ($philhealth_contrib > 0) ? $philhealth_contrib : '')
				->setCellValue($mid_col1.($start_row + 26), ($pagibig_contrib > 0) ? $pagibig_contrib : '')
				->setCellValue($end_col.($start_row + 29), $taxable_income)
				->setCellValue($end_col.($start_row + 31), $tax_object['withholding_tax'])
				->setCellValue($end_col.($start_row + 32), $tax_object['pay_less_withholding_tax'])
				->setCellValue($end_col.($start_row + 33), '') // rice subsidy 
				->setCellValue($end_col.($start_row + 34), '') // others
				->setCellValue($end_col.($start_row + 35), $net_pay)
				;


			// title style
			$payslipStyleArray['font']['bold'] = true;
			$payslipStyleArray['font']['size'] = 14;
			$payslipStyleArray['borders'] = array(
				'outline' => array(
					'style' => PHPExcel_Style_Border::BORDER_THIN,
				)
			);
			$objPHPExcel->getActiveSheet()->getStyle($start_col.$start_row.':'.$end_col.$start_row)->applyFromArray($payslipStyleArray);

			// upper content style
			$payslipStyleArray['font']['bold'] = false;
			$payslipStyleArray['font']['size'] = 13;
			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 1).':'.$end_col.($start_row + 6))->applyFromArray($payslipStyleArray);
			
			// table header style
			$payslipStyleArray['font']['bold'] = true;
			$payslipStyleArray['font']['size'] = 12;
			$payslipStyleArray['borders']['inline'] = array(
				'style' => PHPExcel_Style_Border::BORDER_THIN,
			);
			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 7).':'.$end_col.($start_row + 7))->applyFromArray($payslipStyleArray);

			// table data style
			$payslipStyleArray['font']['bold'] = false;
			$payslipStyleArray['alignment']['horizontal'] = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
			$payslipStyleArray['borders']['inline'] = array(
				'style' => PHPExcel_Style_Border::BORDER_NONE
			);
			$payslipStyleArray['borders']['outline'] = array(
				'style' => PHPExcel_Style_Border::BORDER_THIN
			);
			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 8).':'.$mid_col2.($start_row + 17))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($end_col.($start_row + 8).':'.$end_col.($start_row + 17))->applyFromArray($payslipStyleArray);

			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 18).':'.$mid_col2.($start_row + 20))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($end_col.($start_row + 18).':'.$end_col.($start_row + 20))->applyFromArray($payslipStyleArray);

			$objPHPExcel->getActiveSheet()->getStyle($end_col.($start_row + 21).':'.$end_col.($start_row + 21))->applyFromArray($payslipStyleArray);

			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 22).':'.$start_col.($start_row + 26))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($mid_col1.($start_row + 22).':'.$mid_col1.($start_row + 26))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($mid_col2.($start_row + 22).':'.$mid_col2.($start_row + 26))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($end_col.($start_row + 22).':'.$end_col.($start_row + 26))->applyFromArray($payslipStyleArray);

			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 28).':'.$mid_col2.($start_row + 31))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($end_col.($start_row + 28).':'.$end_col.($start_row + 31))->applyFromArray($payslipStyleArray);

			$objPHPExcel->getActiveSheet()->getStyle($start_col.($start_row + 32).':'.$mid_col2.($start_row + 35))->applyFromArray($payslipStyleArray);
			$objPHPExcel->getActiveSheet()->getStyle($end_col.($start_row + 32).':'.$end_col.($start_row + 35))->applyFromArray($payslipStyleArray);
			// table data style
			$payslipStyleArray = array(
				'borders' => array(
					'outline' => array(
						'style' => PHPExcel_Style_Border::BORDER_DOUBLE
					)
				)
			);
			$objPHPExcel->getActiveSheet()
						->getStyle($start_col.$start_row.':'.$end_col.($start_row + 36))
						->applyFromArray($payslipStyleArray);

			$objPHPExcel->getActiveSheet()
						->getStyle($start_col.$start_row.':'.$start_col.($start_row + 1))
						->getAlignment()
						->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			// end pay slip

			// row heights
			$wsheet = $objPHPExcel->getActiveSheet();
			
			for ($i = 0; $i < 38; $i++)
			{
				$wsheet->getRowDimension($start_row + $i - 1)->setRowHeight($rowHeights[$i]);
				$wsheet->getStyle('C'.($start_row + $i - 1))->getNumberFormat()->setFormatCode('0.00');
				$wsheet->getStyle('E'.($start_row + $i - 1))->getNumberFormat()->setFormatCode('0.00');
				$wsheet->getStyle('H'.($start_row + $i - 1))->getNumberFormat()->setFormatCode('0.00');
				$wsheet->getStyle('J'.($start_row + $i - 1))->getNumberFormat()->setFormatCode('0.00');
			}

			$wsheet->getColumnDimension('F')->setWidth($wsheet->getColumnDimension('A')->getWidth());
			$wsheet->getColumnDimension('G')->setWidth($wsheet->getColumnDimension('B')->getWidth());
			$wsheet->getColumnDimension('H')->setWidth($wsheet->getColumnDimension('C')->getWidth());
			$wsheet->getColumnDimension('I')->setWidth($wsheet->getColumnDimension('D')->getWidth());
			$wsheet->getColumnDimension('J')->setWidth($wsheet->getColumnDimension('E')->getWidth());
		}

		$objPHPExcel->setActiveSheetIndex(1);
		$style = $objPHPExcel->getActiveSheet()->getStyle('A9');
		$objPHPExcel->getActiveSheet()->duplicateStyle($style, 'A9:AR'.(9 + $key));
		$objPHPExcel->getActiveSheet()->duplicateStyle($style, 'AT9:AX'.(9 + $key));
		$objPHPExcel->getActiveSheet()->duplicateStyle($style, 'BC9:BL'.(9 + $key));

		for ($i = 0; $i <= $key; $i++)
		{
			$row = (9 + $i);
			$objPHPExcel->getActiveSheet()->getStyle("G".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("H".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("W".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("X".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("Y".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("Z".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("AG".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("AJ".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("AK".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("AQ".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("AT".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("AU".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("BC".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("BI".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("BK".$row)->getNumberFormat()->setFormatCode('0.00');
			$objPHPExcel->getActiveSheet()->getStyle("BL".$row)->getNumberFormat()->setFormatCode('0.00');
		}
		// end data

		$config = $this->getConfig();
		$new_file_name = date('Y-m-d', $payroll_date_to_time).'_'.date('His').'_payroll.xls';

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save($config->get('__WEB_DIR__').$config->get('upload_directory').'/'.$new_file_name);

		$this->Payroll->update($payroll['id'], array(
			'script_is_running' => false,
			'file_url'          => $config->get('upload_directory').'/'.$new_file_name
		));

		echo "Payroll generation done.";

		return true;
	}
}