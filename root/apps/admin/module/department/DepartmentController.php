<?php

/**
 * The department controller class.
 */
class DepartmentController extends BaseAdminController
{
	public function executeCreate()
	{
		$deptModel = $this->loadModel('Department');
		$this->formErrors = array();

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			if (strlen(trim($_POST['deptname'])) > 0)
			{
				$deptid = $deptModel->create(array(
					'managerid'      => 0,
					'deptname'       => trim($_POST['deptname']),
					'deptdesc'       => trim($_POST['deptdesc'])
				));

				$this->redirect($this->getConfig()->get('base_url').'/department/view?deptid='.$deptid);
			}
			else
			{
				$this->formErrors['deptname'] = 'Department name is mandatory.';
				$this->departments = $deptModel->getAll();
			}
		}
		else
		{
			$this->departments = $deptModel->getAll();
		}
	}

	public function executeEdit()
	{
		$this->redirectUnless((array_key_exists('deptid', $_GET)), $this->getConfig()->get('base_url'));

		$deptModel = $this->loadModel('Department');
		$this->department = $deptModel->getByID($_GET['deptid']);
		
		$this->redirectUnless(($this->department != NULL), $this->getConfig()->get('base_url').'/department/list');

		$this->formErrors = array();
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			if (strlen(trim($_POST['deptname'])) > 0)
			{
				$deptModel->update($_GET['deptid'], array(
					'managerid'      => 0,
					'deptname'       => trim($_POST['deptname']),
					'deptdesc'       => trim($_POST['deptdesc'])
				));

				$this->redirect($this->getConfig()->get('base_url').'/department/view?deptid='.$_GET['deptid']);
			}
			else
			{
				$this->formErrors['deptname'] = 'Department name is mandatory.';
				$this->departments = $deptModel->getAll();
			}
		}
		else
		{
			$this->departments = $deptModel->getAll();
		}
	}

	public function executeDelete()
	{
		$this->redirectUnless((array_key_exists('deptid', $_GET)), $this->getConfig()->get('base_url'));

		$deptModel = $this->loadModel('Department');
		$this->department = $deptModel->getByID($_GET['deptid']);

		// show flash message in department list page
		if ($this->department != NULL)
		{
			$deptModel->delete($_GET['deptid']);
		}
		
		$this->redirect($this->getConfig()->get('base_url').'/department/list');
	}

	public function executeView()
	{
		$this->redirectUnless((array_key_exists('deptid', $_GET)), $this->getConfig()->get('base_url'));

		$deptModel = $this->loadModel('Department');
		$employeeModel = $this->loadModel('Employee');
		$this->department = $deptModel->getByID($_GET['deptid']);
		
		$this->redirectUnless(($this->department != NULL), $this->getConfig()->get('base_url').'/department/list');

		$this->employees = $employeeModel->getEmployeesInDepartment($_GET['deptid']);
	}

	public function executeList()
	{
		$deptModel = $this->loadModel('Department');
		$this->departments = $deptModel->getAll();	
	}
}