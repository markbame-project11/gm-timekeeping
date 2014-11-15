<?php
if (!function_exists('spl_autoload_register'))
{
	throw new Exception("CcAutoload requires SPL PHP extension");
}

require_once dirname(__FILE__).'/CcAutoloadException.php';

/**
 * Autoloading class is useful to have a cleaner code.
 * 
 * Example of use:
 *   CcAutoload::getInstance()
 *   	->setMainDirectory('/home/user/project/')
 *   	->registerClasses(include('/home/user/project/autoload/autoload.php'))
 *   	->registerClasses(array(
 *   		'GLLoader'          => '/lib/loader/CcLoader.php'
 *   	));
 * 
 * @author     Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcAutoload
{	
	/**
	 * The registered classes that can be autoloaded.
	 * 
	 * @var string[]
	 */
	private $classes = array();
	
	/**
	 * Constructs a new CcAutoload object.
	 */
	public function __construct()
	{
		spl_autoload_register(array($this, 'autoload'));
	}
	
	/**
	 * Registers classes that are found in the file. The file shoud return an array with of (className => classPath) pair
	 * 
	 * Example of content of file:
	 *   return array('CcAutoload' => '/framework/lib/autoload/CcAutoload.php');
	 * 
	 * @param       string                  $filename          The file to be read
	 * @param       string                  $rootDirectory     The root directory of the files loaded.
	 * @return      CcAutoload                                 This object
	 * @throws      CcAutoloadException                        Throws exception if the file does not exists or is not readable.
	 */
	public function registerClassesFromFile($filename, $rootDirectory)
	{
		if (!file_exists($filename))
		{
			throw new CcAutoloadException("The file {".$filename."} does not exists.");
		}
		
		if (!is_readable($filename))
		{
			throw new CcAutoloadException("The file {".$filename."} is not readable.");
		}
		
		$contents = include($filename);
		
		if (!is_array($contents))
		{
			$type = (is_object($contents)) ? get_class($contents) : gettype($contents);
						
			throw new CcAutoloadException("The file {".$filename."} is expected to return array but instead return {".$type."}.");			
		}
		
		$this->registerClasses($contents, $rootDirectory);
		
		return $this;
	}
	
	/**
	 * Registers classes to be autoloaded.
	 * 
	 * @param       string[]          $classes         The classes to be autoloaded as $className => $classPath associative pair.
	 * @param       string            $rootDirectory   The root directory of the path of class files loaded.
	 * @return      CcAutoload                         This object
	 */
	public function registerClasses(array $classes, $rootDirectory)
	{
		$rootDirectory = (string) $rootDirectory;
		$rootDirectory = ("/" == substr($rootDirectory, -1)) ? substr($rootDirectory, 0, strlen($rootDirectory) - 1) : $rootDirectory;
		
		foreach ($classes as $className => $classPath)
		{
			$this->registerClass($className, $rootDirectory.$classPath);
		}
		
		return $this;
	}
	
	/**
	 * Registers a class.
	 * 
	 * @param       string          $className         The name of the class.
	 * @param       string          $classPath         The absolute path of the class.
	 * @return      CcAutoload                         This object     
	 */
	public function registerClass($className, $classPath)
	{
		$this->classes[(string)$className] = (string)$classPath;
		
		return $this;
	}
	
	/**
	 * 
	 */
	
	/**
	 * The handler of autoloading.
	 * 
	 * @param     string     $class_name     The name of the class.
	 */
	public function autoload($class_name)
	{
		if (array_key_exists((string)$class_name, $this->classes))
		{
			if (file_exists($this->classes[(string)$class_name]))
			{
				require_once($this->classes[(string)$class_name]);	
			}
			else
			{
				throw new CcAutoloadException("The class {".((string)$class_name)."} failed to autoload because the class path {".($this->classes[(string)$class_name])."} does not exists.");
			}
		}
	}
}