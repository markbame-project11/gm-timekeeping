<?php
/**
 * The default controller class.
 */
class DefaultController extends BaseAdminController
{	
	public function executeIndex()
	{
		$deptModel = $this->loadModel('Department');
		$this->departments = $deptModel->getAll();
	}
}