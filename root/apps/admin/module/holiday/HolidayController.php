<?php

/**
 * The holiday controller class.
 */
class HolidayController extends BaseAdminController
{	
	public function executeDelete()
	{
		$this->forward404Unless((array_key_exists('holiday_id', $_GET)));

		$holidayModel = $this->loadModel('Holiday');
		$holidayModel->delete($_GET['holiday_id']);

		$this->redirect($this->getConfig()->get('base_url').'/holiday');
	}

	public function executeAdd()
	{
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$holidayModel = $this->loadModel('Holiday');

			$holidayObj = $holidayModel->getHolidayOn($_POST['date']);

			if (is_array($holidayObj))
			{
				$holidayModel->update($holidayObj['id'], array(
					'date'         => $_POST['date'],
					'name'         => $_POST['name']
				));
			}
			else
			{
				$holidayModel->create(array(
					'date'         => $_POST['date'],
					'name'         => $_POST['name']
				));
			}

			$this->success_message = 'Successfully added a holiday.';
		}
		else
		{
			$this->fields = array(
				'name'    => '',
				'date'    => ''
			);
		}
	}

	public function executeAddHolidayPay()
	{
		$holidayModel = $this->loadModel('Holiday');
		$employeeModel = $this->loadModel('Employee');

		$this->holidays = $holidayModel->getHolidaysBetween(date('Y-m-d', strtotime('-30days')), date('Y-m-d', strtotime('+30days')));
		$this->employees = $employeeModel->getPayrollEmployees();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$holiday = $holidayModel->getByID($_POST['holiday_id']);
			$employee = $employeeModel->getByID($_POST['employee_id']);

			if ($holiday != NULL && $employee != NULL)
			{
				$holidayPayModel = $this->loadModel('HolidayPay');
				if ($holidayPayModel->addHolidayPayFor($_POST['holiday_id'], $_POST['employee_id']))
				{
					$this->success_message = 'Successfully added holiday pay for '.$employee['firstname'].' '.$employee['lastname'].'.';
				}
			}
		}
		else
		{
			$this->fields = array(
				'holiday_id'     => '',
				'employee_id'    => ''
			);
		}
	}

	public function executeIndex()
	{
		$holidayModel = $this->loadModel('Holiday');

		$this->holidays = $holidayModel->getHolidaysGreaterThanEqual(date('Y-m-d', strtotime('-120days')));
	}
}