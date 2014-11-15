<?php
/**
 * The container of configurations.
 * 
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcConfig
{	
	/**
	 * The configuration variables.
	 * 
	 * @var string
	 */
	private $vars = array();
	
	/**
	 * Creates an empty CcConfig object.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Registers vars that are found in the file. The file shoud return an array with of (varName => value) pair
	 * 
	 * Example of content of file:
	 *   return array('BASE_URL' => 'http://localhost/');
	 * 
	 * @param       string                  $filename          The file to be read
	 * @return      CcConfig                                   This object
	 * @throws      CcConfigException                          Throws exception if the file does not exists or is not readable.
	 */
	public function registerVarsFromFile($filename)
	{
		if (!file_exists($filename))
		{
			throw new CcConfigException("The file {".$filename."} does not exists.");
		}
		
		if (!is_readable($filename))
		{
			throw new CcConfigException("The file {".$filename."} is not readable.");
		}
		
		$contents = include($filename);
		
		if (!is_array($contents))
		{
			$type = (is_object($contents)) ? get_class($contents) : gettype($contents);
						
			throw new CcConfigException("The file {".$filename."} is expected to return array but instead return {".$type."}.");			
		}
		
		$this->registerVars($contents);
		
		return $this;
	}
	
	/**
	 * Registers vars to this configuration object.
	 * 
	 * @param       string[]          $vars            The variables to be registered as [varname] => value pair.
	 * @return      CcConfig                           This object
	 */
	public function registerVars(array $vars)
	{
		foreach ($vars as $varName => $value)
		{
			$this->registerVar($varName, $value);
		}
		
		return $this;
	}
	
	/**
	 * Registers a variable.
	 * 
	 * @param       string          $varName         The name of the variable.
	 * @param       string          $value           The value of the variable.
	 * @return      CcConfig                         This object     
	 */
	public function registerVar($varName, $value)
	{
		$this->vars[(string)$varName] = $value;
		
		return $this;
	}
	
	/**
	 * Checks whether a configuration variable exists.
	 * 
	 * @param      string      $key     The configuration key
	 * @return     boolean              true, if the configuration variable exists and false otherwise.
	 */
	public function has($key)
	{
		return (array_key_exists($key, $this->vars));
	}
	
	/**
	 * Gets the value of a configuration variable.
	 * 
	 * @param      string      $key          The configuration key
	 * @param      string      $default      The default returned value if key does not exists.
	 * @return     string                    The value of the key.
	 */
	public function get($key, $default = 'Not supported')
	{
		return ($this->has($key)) ? $this->vars[$key] : $default;
	}
}