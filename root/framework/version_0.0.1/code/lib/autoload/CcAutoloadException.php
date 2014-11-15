<?php
/**
 * Exception thrown when an autoload exception occurs.
 * 
 * @author     Antonio Pepito Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcAutoloadException extends Exception
{
	/**
	 * Creates a new CcAutoloadException object.
	 * 
	 * @param     string     $message     The message of the exception.
	 */
	public function __construct($message = "Autoloading Exception")
	{
		parent::__construct($message);
	}
}