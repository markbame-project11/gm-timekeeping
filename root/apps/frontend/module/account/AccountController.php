<?php
/**
 * The login controller class.
 */
class AccountController extends CcController
{
	/**
	 *
	 */
	public function executeIndex()
	{
		$this->executeLogin();
	}

	/**
	 * 
	 */
	public function executeAccountActivation()
	{
		$this->redirectUnless(array_key_exists('to_change_gumi_email_empid', $_SESSION), $this->getConfig()->get('base_url').'/account/login');
		
		$employeeModel = $this->loadModel('Employee');
		$employeeActivationModel = $this->loadModel('EmployeeActivation');

		$employee = $employeeModel->getById($_SESSION['to_change_gumi_email_empid']);
		$this->redirectUnless((NULL != $employee && ($employee['gumi_email'] == NULL || trim($employee['gumi_email']) == '')), $this->getConfig()->get('base_url').'/account/login');

		$this->error_message = NULL;

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->loadHelpers(array('mailer', 'generic'));

			$activationEntry = $employeeActivationModel->getByEmployeeID($employee['empid']);
			if ($activationEntry != NULL)
			{
				$employeeActivationModel->delete($activationEntry['id']);
			}

			$code = generate_random_string(100);
			$employeeActivationModel->create(array(
				'employee_id'   => $employee['empid'],
				'create_date'   => date('Y-m-d'),
				'code'          => $code,
				'email'         => $_POST['email']
			));

			$email_body = include_template(
				'account/accountActivation.email', 
				array(
					'employee'    => $employee,
					'code'        => $code
				), 
				true
			);
			
			$mailer = new PHPMailer();
			if (send_email($mailer, 'Gumi Payroll Account Activation', $email_body, $_POST['email']))
			{
				unset($_SESSION['to_change_gumi_email_empid']);
				$this->email = $_POST['email'];
				$this->setTemplate('account/accountActivation.success');
			}
			else
			{
				$this->error_message = "Email not sent. Please retry forgot password.";
			}	
		}
		else
		{
			$this->fields = array(
				'email'   => ''
			);
		}
	}

	/**
	 * 
	 */
	public function executeActivateAccount()
	{
		$this->forward404Unless(array_key_exists('code', $_GET));

		$employeeActivationModel = $this->loadModel('EmployeeActivation');
		$employeeModel = $this->loadModel('Employee');

		$this->error_message = NULL;
		$this->success_message = NULL;

		$activationEntry = $employeeActivationModel->getByCode($_GET['code']);
		if (NULL == $activationEntry)
		{
			$this->error_message = "Invalid code or this code had already been used.";
		}
		else
		{
			$employeeActivationModel->delete($activationEntry['id']);
			$employee = $employeeModel->getById($activationEntry['employee_id']);

			if (NULL == $employee)
			{
				$this->error_message = "Invalid code or this code had already been used.";
			}
			else
			{
				$ret = $employeeModel->update($employee['empid'], array(
					'gumi_email'   => $activationEntry['email'],
					'active'       => true,
					'dateupdated'  => date('Y-m-d')
				));

				if ($ret)
				{
					$this->success_message = "You have successfully activated your account.";
				}
				else
				{
					$this->error_message = "An unexpected error occurs.";
				}
			}
		}
	}

	/**
	 *
	 */
	public function executeLogin()
	{
		$this->setLayout('login_layout');

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$model = $this->loadModel('Employee');
			$employee = $model->getEmployeeByUsernameAndPassword($_POST['username'], $_POST['password']);

			if ($employee != NULL)
			{
				if ($employee['gumi_email'] == NULL || trim($employee['gumi_email']) == '')
				{
					$_SESSION['to_change_gumi_email_empid'] = $employee['empid'];
					$_SERVER['REQUEST_METHOD'] = 'get';
					$this->forward('account', 'accountActivation');
				}
				else
				{
					if (array_key_exists('to_change_gumi_email_empid', $_SESSION))
					{
						unset($_SESSION['to_change_gumi_email_empid']);
					}

					$_SESSION['auth'] = 1; 
					$_SESSION['login'] = $employee['login'];
					$_SESSION['starttime'] = date("Y-m-d H:i:s");
					$_SESSION['empid'] = $employee['empid'];
					$_SESSION['deptid'] = $employee['deptid'];
					$_SESSION['lastname'] = $employee['lastname'];
					$_SESSION['firstname'] = $employee['firstname'];             
					$_SESSION['email'] = $employee['email'];
					$_SESSION['superadmin'] = ((int)$employee['superadmin'] == 1) ? 1 : 0;
					$_SESSION['admin'] = ((int)$employee['superadmin'] == 1 || (int)$employee['admin'] == 1) ? 1 : 0;

					$this->redirect($_POST['referpage']);
				}
			}
			else
			{
				$this->errorMessage = "The username or password is incorrect.";
			}
		}
		else
		{
			if (array_key_exists('auth', $_SESSION) && $_SESSION['auth'] == 1)
			{
				$this->redirectUnless(!(array_key_exists('auth', $_SESSION) && $_SESSION['auth'] == 1), $this->getConfig()->get('base_url'));
			}

			$this->referpage = (array_key_exists('referrer', $_GET)) ? $_GET['referrer'] : $this->getConfig()->get('base_url');
		}
	}

	/**
	 *
	 */
	public function executeLogout()
	{
		$sessionKeys = array('auth', 'login', 'starttime', 'empid', 'deptid', 'parentid', 'lastname', 'firstname', 'email', 'superadmin', 'admin');

		foreach ($sessionKeys as $sessionKey)
		{
			if (array_key_exists($sessionKey, $_SESSION))
			{
				unset($_SESSION[$sessionKey]);
			}
		}

		$this->redirect($this->getConfig()->get('base_url').'/account/login');
	}

	/**
	 *
	 */
	public function executeForgotPassword()
	{
		$this->loadHelpers(array('mailer', 'generic'));

		$employeeModel = $this->loadModel('Employee');
		$passwordResetModel = $this->loadModel('PasswordReset');
		$this->error_message = NULL;

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;

			$employee = $employeeModel->getByGumiEmail($_POST['email']);
			if ($employee != NULL && is_array($employee))
			{
				$code = generate_random_string(100);
				$passwordResetModel->create(array(
					'employee_id'   => $employee['empid'],
					'date'          => date('Y-m-d'),
					'code'          => $code
				));

				$email_body = include_template(
					'account/forgotPassword.email', 
					array(
						'employee'    => $employee,
						'code'        => $code
					), 
					true
				);
				
				$mailer = new PHPMailer();
				if (send_email($mailer, 'Change Password', $email_body, $employee['gumi_email']))
				{
					$this->email = $_POST['email'];
					$this->setTemplate('account/forgotPassword.success');
				}
				else
				{
					$this->error_message = "Email not sent. Please retry forgot password.";
				}
			}
			else
			{
				$this->error_message = "Account not found with email equal to ".$_POST['email'];
			}
		}
		else
		{
			$this->fields = array(
				'email'   => ''
			);
		}
	}

	/**
	 *
	 */
	public function executeChangePassword()
	{
		$passwordResetModel = $this->loadModel('PasswordReset');
		$employeeModel = $this->loadModel('Employee');

		$this->redirectUnless(array_key_exists('code', $_GET), $this->getConfig()->get('base_url').'/account/login');

		$codeInfo = $passwordResetModel->getByCode($_GET['code']);
		$employee = $employeeModel->getById($codeInfo['employee_id']);
		$this->redirectUnless((NULL != $codeInfo && NULL != $employee), $this->getConfig()->get('base_url').'/account/login');

		$codeInfoDateStrToTime = strtotime($codeInfo['date']);

		if ((time() - $codeInfoDateStrToTime) > $this->getConfig()->get('PASSWORD_RESET_EXPIRATION_DURATION', 86400))
		{
			$passwordResetModel->delete($codeInfo['id']);
			$this->setTemplate('account/changePassword.error');
		}
		else
		{
			$this->error_message = NULL;
			$this->success_message = NULL;
			$this->code = $_GET['code'];

			if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
			{
				if ($_POST['password'] != $_POST['confirm_password'])
				{
					$this->error_message = 'Password and Confirm Password is not equal.';
				}
				else
				{
					$ret = $employeeModel->update($employee['empid'], array(
						'password'       => $_POST['password'],
						'dateupdated'    => date('Y-m-d')
					));

					if ($ret)
					{
						$passwordResetModel->delete($codeInfo['id']);

						$this->success_message = 'You have successfully changed your password.';
					}
					else
					{
						$this->error_message = 'An unexpected error occurs.';
					}
				}
			}
		}
	}
}