<?php
/**
 * Exception thrown when a controller exception occurs.
 * 
 * @author    Antonio P. Cruda Jr. <antonio.cruda@gmail.com> 
 */
class CcControllerException extends Exception
{
	/**
	 * The error type
	 * 
	 * @var string
	 */
	private $errorType = "";
	
	/**
	 * Creates a new GLControllerException with the given message.
	 * 
	 * @param      string      $message        The message
	 * @param      string      $errorType      The error type
	 */
	public function __construct($message = "A controller exception occurs.", $errorType = "generic")
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
	
	/**
	 * Sets the name of the file where the error occurs.
	 * 
	 * @param      string      $filename      The name of the file
	 * @return     Exception                  This object
	 */
	public function setFile($filename)
	{
		$this->file = $filename;
		
		return $this;
	}
	
	/**
	 * Sets the line number where the error occurs.
	 * 
	 * @param      integer      $line      The line number in the file where the error occurs.
	 * @return     Exception               This object
	 */
	public function setLine($line)
	{
		$this->line = $line;
		
		return $this;
	}
}