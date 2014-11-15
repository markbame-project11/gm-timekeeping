<?php
/**
 * The default controller class.
 */
class DefaultController extends BaseFrontendLoggedInController
{	
	public function executeIndex()
	{
		$this->loadHelpers(array('generic', 'attendance'));

		$this->is_admin = false;
		if (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == 1)
		{
			$this->is_admin = true;
		}

		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$employeeModel = $this->loadModel('Employee');
		$timesheetModel = $this->loadModel('Timesheet');
		$holidayModel = $this->loadModel('Holiday');
		$changedScheduleModel = $this->loadModel('EmployeeChangedSchedule');
		$leaveModel = $this->loadModel('Leave');
		$holidayPayModel = $this->loadModel('HolidayPay');
		$overtimeModel = $this->loadModel('Overtime');

		$this->employee = $employeeModel->getByID($_SESSION['empid']);
		$this->forward404Unless(($this->employee != NULL));

		$config_pub_conv_datetime = strtotime($this->getConfig()->get('system_pub_date')); 
		$start_date = ($config_pub_conv_datetime > strtotime('-10 days')) ? $this->getConfig()->get('system_pub_date') : date('Y-m-d', strtotime('-15 days'));
		$end_date = date('Y-m-d');

        //echo $_SESSION['empid'];
        /*
        $_SESSION['tmesheet_checkout'] = $timesheetModel->getemployeeTimesheet_checkout($_SESSION['empid']);
        $_SESSION['empcheckin'] = $timesheetModel->getemployeeTimeInTimeOut($_SESSION['empid'],"checkin")
        $_SESSION['empcheckout'] = $timesheetModel->getemployeeTimeInTimeOut($_SESSION['empid'],"checkout")
        */


		$employeeTimesheets = $timesheetModel->getTimesheetOfEmployeeWithDateKey($_SESSION['empid'], $start_date, $end_date);
		$employeeSchedules = $scheduleModel->getEmployeeIDToScheduleMapping($_SESSION['empid'], $start_date, $end_date);

		$employeeTimesheet =  $timesheetModel->getLastEmployeeTimesheet($_SESSION['empid']);

		$holidays = $holidayModel->getHolidaysBetweenAsAssoc($start_date, $end_date);
		$changedSchedules = $changedScheduleModel->getEmployeeChangedSchedulesBetween($_SESSION['empid'], $start_date, $end_date);
		$leaves = $leaveModel->getEmployeeLeavesBetween($_SESSION['empid'], $start_date, $end_date);
		$holidayPays = $holidayPayModel->getHolidayPayForEmployeeBetween($_SESSION['empid'], $start_date, $end_date);
		$otPays = $overtimeModel->getEmployeeOvertimeBetween($_SESSION['empid'], $start_date, $end_date);

		$this->data = build_employee_attendance($this->employee, $employeeSchedules, $employeeTimesheets, $start_date, $end_date, $leaves, $holidays, $changedSchedules, $holidayPays, $otPays);

		$this->showCheckin = calculate_show_timein($employeeTimesheet, $employeeSchedules, $changedSchedules);

		$departmentEmployees = $employeeModel->getEmployeesInDepartment($this->employee['deptid']);
		$departmentEmployeeSchedules = $scheduleModel->getEmployeesInDepartmentIDToScheduleMapping($this->employee['deptid'], date('Y-m-d'), date('Y-m-d'));
		$departmentEmployeeTimesheets = $timesheetModel->getDepartmentEmployeesTimesheetsBetween($this->employee['deptid'], date('Y-m-d'), date('Y-m-d', strtotime('+1 days')));
		$holiday = $holidayModel->getHolidayOn(date('Y-m-d'));
		$changedSchedules = $changedScheduleModel->getEmployeesChangedSchedulesBetween(date('Y-m-d'), date('Y-m-d'));
		$leaves = $leaveModel->getApprovedLeavesBetween(date('Y-m-d'), date('Y-m-d'));

		$this->department_data = build_employees_attendance_for_the_day($departmentEmployees, $departmentEmployeeSchedules, $departmentEmployeeTimesheets, $holiday, $changedSchedules, $leaves);
	}
}