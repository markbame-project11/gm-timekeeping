<?php

/**
 * The leave controller class.
 */
class LeaveController extends BaseAdminController
{	
	/**
	 * 
	 */
	public function executeViewEmployeeLeaves()
	{
		$employeeModel = $this->loadModel('Employee');

		$this->employees = $employeeModel->getPayrollEmployees();
	}

	/**
	 * 
	 */
	public function executeEmployeeLeavesOf()
	{
		$this->redirectUnless((array_key_exists('employee_id', $_POST)), $this->getConfig()->get('base_url').'/leave');
		$this->setLayout(NULL);

		$leaveModel = $this->loadModel('Leave');
		$this->pending_leaves = $leaveModel->getEmployeePendingLeaves($_POST['employee_id']);
		$this->approved_leaves = $leaveModel->getEmployeeLeavesOfStatus($_POST['employee_id'], 'approved');
		$this->denied_leaves = $leaveModel->getEmployeeLeavesOfStatus($_POST['employee_id'], 'denied');
	}

	/**
	 *
	 */
	public function executeDeniedLeaves()
	{
		$leaveModel = $this->loadModel('Leave');
		$this->pending_leaves = $leaveModel->getPendingLeaves();

		$this->incoming_leaves = $leaveModel->getLeavesOfEmployeesOnMonthJoinEmployee(date('Y-m-d'));
		$this->date = date('Y-m-15');
	}

	/**
	 *
	 */
	public function executeIndex()
	{
		$leaveModel = $this->loadModel('Leave');
		$this->pending_leaves = $leaveModel->getPendingLeaves();

		$this->incoming_leaves = $leaveModel->getLeavesOfEmployeesOnMonthJoinEmployee(date('Y-m-d'));
		$this->date = date('Y-m-15');
	}

	/**
	 *
	 */
	public function executeApprove()
	{
		if (array_key_exists('leave_id', $_GET))
		{
			$leaveModel = $this->loadModel('Leave');

			$leave = $leaveModel->getById($_GET['leave_id']);
			if (is_array($leave))
			{
				if ($leave['status'] == 'pending')
				{
					$ret_val = $leaveModel->update($leave['id'], array(
						'status'   => 'approved'
					));

					if ($ret_val)
					{
						echo json_encode(array(
							'success'    => true,
							'message'    => 'Leave successfully approved.'
						));
					}
					else
					{
						echo json_encode(array(
							'success'    => false,
							'message'    => 'An unexpected error occurs.'
						));
					}
				}
				else
				{
					echo json_encode(array(
						'success'    => false,
						'message'    => 'Only pending leaves can be approved.'
					));
				}
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
	public function executeDeny()
	{
		if (array_key_exists('leave_id', $_GET))
		{
			$leaveModel = $this->loadModel('Leave');

			$leave = $leaveModel->getById($_GET['leave_id']);
			if (is_array($leave))
			{
				if ($leave['status'] == 'pending')
				{
					$ret_val = $leaveModel->update($leave['id'], array(
						'status'    => 'denied'
					));

					if ($ret_val)
					{
						echo json_encode(array(
							'success'    => true,
							'message'    => 'Leave successfully denied.'
						));
					}
					else
					{
						echo json_encode(array(
							'success'    => false,
							'message'    => 'An unexpected error occurs.'
						));
					}
				}
				else
				{
					echo json_encode(array(
						'success'    => false,
						'message'    => 'Only pending leaves can be denied.'
					));
				}
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
	public function executeAdd()
	{
		$employeeModel = $this->loadModel('Employee');
		$this->employees = $employeeModel->getPayrollEmployees();

		$this->leave_types = array(
			'sick'      => 'Sick',
			'vacation'  => 'Vacation',
			'half_day'  => 'Half Day'
		);

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$leaveModel = $this->loadModel('Leave');
			$leaveObj = $leaveModel->getLeaveOfEmployeeOn($_POST['employee_id'], $_POST['date']);

			if (is_array($leaveObj))
			{
				$leaveModel->update($leaveObj['id'], array(
					'employee_id'  => $_POST['employee_id'],
					'date'         => $_POST['date'],
					'leave_type'   => $_POST['leave_type'],
					'reason'       => $_POST['reason'],
					'is_paid'      => ($_POST['is_paid'] == 'yes') ? true : false
				));
			}
			else
			{
				$leaveModel->create(array(
					'employee_id'  => $_POST['employee_id'],
					'date'         => $_POST['date'],
					'leave_type'   => $_POST['leave_type'],
					'reason'       => $_POST['reason'],
					'is_paid'      => ($_POST['is_paid'] == 'yes') ? true : false
				));
			}

			$this->success_message = 'Successfully added a leave.';
		}
		else
		{
			$this->fields = array(
				'employee_id'  => '',
				'date'         => '',
				'leave_type'   => '',
				'reason'       => '',
				'is_paid'      => 'yes'
			);
		}	
	}

	public function executeGetLeavesInMonth()
	{
		$this->forward404Unless((array_key_exists('date', $_POST)));
		$this->setLayout(NULL);
		$this->setTemplate('_leaves_in_month');

		$leaveModel = $this->loadModel('Leave');
		$this->leaves = $leaveModel->getLeavesOfEmployeesOnMonthJoinEmployee($_POST['date']);
		$this->date = $_POST['date'];
		$this->dTime = strtotime($_POST['date']);
	}
}