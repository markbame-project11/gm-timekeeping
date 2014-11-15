<?php
/**
 * Exception thrown when a configuration related exception occurs.
 * 
 * @author     Antonio Pepito Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcConfigException extends Exception
{
	/**
	 * Creates a new CcConfigException object.
	 * 
	 * @param     string     $message     The message of the exception.
	 */
	public function __construct($message = "Configuration Exception")
	{
		parent::__construct($message);
	}
}