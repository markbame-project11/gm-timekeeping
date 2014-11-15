<?php

/**
 * Exception thrown when a templating exception occurs.
 * 
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcTemplateException extends Exception
{
	/**
	 * Creates a new CcTemplateException with the given message.
	 * 
	 * @param       string        $message         The message
	 */
	public function __construct($message = "A templating exception occurs.", $code = 1)
	{
		parent::__construct($message, $code);
	}
}