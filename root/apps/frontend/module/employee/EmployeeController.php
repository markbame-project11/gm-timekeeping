<?php
/**
 * The employee controller class.
 */
class EmployeeController extends BaseFrontendLoggedInController
{
	public function executeChangePassword()
	{
		$this->error_message = NULL;
		$this->success_message = NULL;
		$employeeModel = $this->loadModel('Employee');

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$employee = $employeeModel->getEmployeeByUsernameAndPassword($_SESSION['login'], $_POST['password']);
			if ($employee != NULL)
			{
				$res = $employeeModel->update($employee['empid'], array(
					'password'   => $_POST['new_password']
				));

				if ($res)
				{
					$this->success_message = 'Password successfully updated.';
				}
				else
				{
					$this->error_message = 'An unexpected error occurs.';
				}
			}
			else
			{
				$this->error_message = 'Wrong current password set.';
			}
		}
	}

	public function executeUpdate()
	{
		$employeeModel = $this->loadModel('Employee');
		$this->fieldsWithErrors = array();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$fieldsToCheck = array(
				'firstname'    => 'First Name', 
				'lastname'     => 'Last Name'
			);
			$fieldsWithErrors = $this->checkMandatoryFields(array_keys($fieldsToCheck), $_POST);

			if (0 == count($fieldsWithErrors))
			{	
				$data = array(
					'lastname'             => $_POST['lastname'],
					'firstname'            => $_POST['firstname'],
					'minit'                => $_POST['minit'],
					'dob'                  => $_POST['dob'],
					'address1'             => $_POST['address1'],
					'email'                => $_POST['email'],
					'cellphone'            => $_POST['cellphone'],
					'em_contact_person'    => $_POST['em_contact_person'],
					'em_contact_number'    => $_POST['em_contact_number'],
					'em_contact_address'   => $_POST['em_contact_address'],
					'skype_id'             => $_POST['skype_id'],
					'pagibig_no'           => $_POST['pagibig_no'],
					'philhealth_no'        => $_POST['philhealth_no'],
					'tin_no'               => $_POST['tin_no'],
					'sss_no'               => $_POST['sss_no'],
					'nickname'             => $_POST['nickname'],
					'dateupdated'          => date('Y-m-d H:i:s')
				);

				$employeeModel->update($_SESSION['empid'], $data);

				$this->redirect($this->getConfig()->get('base_url').'/employee/view');
			}
			else
			{
				foreach ($fieldsWithErrors as $fieldWithError)
				{
					$this->fieldsWithErrors[] = $fieldsToCheck[$fieldWithError];
				}
			}
		}
		else
		{
			$this->fields = $employeeModel->getByID($_SESSION['empid']);
		}
	}

	public function executeView()
	{
		$employeeModel = $this->loadModel('Employee');
		$this->employee = $employeeModel->getByEmployeeIDJoinDepartment($_SESSION['empid']);

		$this->forward404Unless(($this->employee != NULL));

		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$this->schedule = $scheduleModel->getLastEmployeeSchedule($_SESSION['empid']);

		$this->loadHelpers(array('generic'));

		$this->days = get_all_days();
		$this->hours = get_all_hours();
	}

	private function checkMandatoryFields(array $fieldsToCheck, array $fieldValues)
	{
		$fieldsWithErrors = array();

		foreach ($fieldsToCheck as $key)
		{
			if (
				!array_key_exists($key, $fieldValues) ||
				trim($fieldValues[$key]) == ''
			)
			{
				$fieldsWithErrors[] = $key;
			}
		}

		return $fieldsWithErrors;
	}
}