<?php
/**
 * The exception class of a forwarded error.
 * 
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcForwardedError extends Exception
{
	/**
	 * The error type
	 * 
	 * @var string
	 */
	private $errorType = "";
	
	/**
	 * Creates a new CcForwardedError object using the given message and errorType.
	 * 
	 * @param      string      $message        The message
	 * @param      string      $errorType      The error type
	 */
	public function __construct($message, $errorType)
	{
		$this->errorType = $errorType;
		
		parent::__construct($message);
	}
	
	/**
	 * Returns the error type.
	 * 
	 * @return      string      The error type.
	 */
	public function getErrorType()
	{
		return $this->errorType;
	}
	
	/**
	 * Sets the error type.
	 * 
	 * @param        string      $errorType        The error type
	 */
	public function setErrorType($errorType)
	{
		$this->errorType = $errorType;
		
		return $this;
	}
}