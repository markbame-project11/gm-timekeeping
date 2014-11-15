<?php
/**
 * The base controller for frontend controllers that require users to login
 */
class BaseFrontendLoggedInController extends CcController
{
	public function preExecute()
	{
		$this->redirectUnless((array_key_exists('auth', $_SESSION) && $_SESSION['auth'] == 1), $this->getConfig()->get('base_url').'/account/login');

		$this->is_admin = false;
		if (array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == 1)
		{
			$this->is_admin = true;
		}
	}
}