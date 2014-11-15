<?php

/**
 * The overtime controller class.
 */
class OvertimeController extends BaseAdminController
{	
	public function executeAdd()
	{
		$employeeModel = $this->loadModel('Employee');
		$overtimeModel = $this->loadModel('Overtime');

		$this->employees = $employeeModel->getPayrollEmployees();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$employee = $employeeModel->getByID($_POST['employee_id']);
			if ($employee != NULL)
			{
				$overtimeID = $overtimeModel->create(array(
					'employee_id'   => $_POST['employee_id'],
					'date'          => $_POST['date'],
					'notes'         => $_POST['notes']
				));

				if ($overtimeID > 0)
				{
					$this->success_message = 'Successfully added overtime for employee '.$employee['firstname'].' '.$employee['lastname'];
				}
			}
		}
		else
		{
			$this->fields = array(
				'employee_id'    => 0,
				'date'           => '',
				'notes'          => ''
			);
		}
	}

	public function executeIndex()
	{
		$overtimeModel = $this->loadModel('Overtime');

		$start_date = date('Y-m-01');
		$end_date = date('Y-m-t');

		$this->overtimes = $overtimeModel->getOvertimeBetween($start_date, $end_date);
	}
}