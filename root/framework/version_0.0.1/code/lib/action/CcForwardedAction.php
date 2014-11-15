<?php
/**
 * The exception class of a forwarded action.
 * 
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcForwardedAction extends Exception
{
	/**
	 * The module used.
	 * 
	 * @var string
	 */
	private $module = "";
	
	/**
	 * The action used.
	 * 
	 * @var string
	 */
	private $action = "";
	
	/**
	 * Creates a new CcForwardedAction object using the given module and action as the default value.
	 * 
	 * @param      string      $module         The module used.
	 * @param      string      $action         The action used.
	 */
	public function __construct($module, $action)
	{
		$this->module = $module;
		$this->action = $action;

		parent::__construct("Forward to ".ucfirst($module)."Controller::execute".ucfirst($action), 1);
	}
	
	/**
	 * Returns the module used.
	 * 
	 * @return      string      The module used.  
	 */
	public function getModule()
	{
		return $this->module;
	}
	
	/**
	 * Sets the module used.
	 * 
	 * @param        string      $module      The module used.
	 */
	public function setModule($module)
	{
		$this->module = $module;
		
		return $this;
	}
	
	/**
	 * Returns the action used.
	 * 
	 * @return      string      The action used.
	 */
	public function getAction()
	{
		return $this->action;
	}
	
	/**
	 * Sets the action used.
	 * 
	 * @param       string       $action        The action used.
	 */
	public function setAction($action)
	{
		$this->action = $action;
		
		return $this;
	}
}