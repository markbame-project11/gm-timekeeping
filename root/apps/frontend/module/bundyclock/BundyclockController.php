<?php
/**
 * The bundyclock controller class.
 */
class BundyclockController extends BaseFrontendLoggedInController
{
	public function executeCheckIn()
	{
		$timesheetModel = $this->loadModel('Timesheet');
		$timesheetModel->checkin($_SESSION['empid'], $_SERVER['REMOTE_ADDR']);

		echo json_encode(array(
			'is_successful'  => true,
			'message'        => 'User have successfully checked in'  
		));
		exit;
	}

	public function executeCheckout()
	{
		$timesheetModel = $this->loadModel('Timesheet');
		$timesheetModel->checkout($_SESSION['empid'], $_SERVER['REMOTE_ADDR']);

		echo json_encode(array(
			'is_successful'  => true,
			'message'        => 'User have successfully checked out'  
		));
		exit;
	}
}