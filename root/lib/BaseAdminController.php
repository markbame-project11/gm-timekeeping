<?php
/**
 * The base controller for admin controllers.
 */
class BaseAdminController extends CcController
{
	public function preExecute()
	{
		$url = $this->getConfig()->get('frontend_base_url').'/account/login';
		$url .= '?referrer='.$this->getConfig()->get('base_url');
		$this->redirectUnless((array_key_exists('auth', $_SESSION) && $_SESSION['auth'] == 1), $url);

		if (
			!(
				(array_key_exists('superadmin', $_SESSION) && $_SESSION['superadmin'] == 1) ||
				(array_key_exists('admin', $_SESSION) && $_SESSION['admin'] == 1)
			)
		)
		{
			$this->redirect($this->getConfig()->get('frontend_base_url'));
		}
	}
}