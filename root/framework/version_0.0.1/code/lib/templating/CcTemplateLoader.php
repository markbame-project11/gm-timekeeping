<?php

/**
 * The template loader class.
 * 
 * Example: 
 *   Main code
 *    $loader = new CcTemplateLoader("C:/projects/project1/");
 *    $loader->setTemplateVars(
 *    	array(
 *    		'glLoader' => $instance,
 *    		'test'     => 'test'
 *    	)
 *    );
 *    
 *    $loader->loadTemplate("apps/v1/module1/template/index.php")->render(false);
 * 
 * @author      Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcTemplateLoader
{
	/**
	 * The root directory of the templates loaded.
	 * 
	 * @var string
	 */
	private $mRootDirectory = "";
	
	/**
	 * The variables that are accessible by the templates loaded.
	 * 
	 * @var mixed
	 */
	private $mVars = array();
	
	/**
	 * Constructs a new CcTemplateLoader object.
	 */
	public function __construct($rootDirectory = "")
	{
		$this->mRootDirectory = $rootDirectory;
		$this->mRootDirectory .= ("/" == substr($this->mRootDirectory, -1)) ? "" : "/";
	}
	
	/**
	 * The same functionality with setTemplateVar but instead of receiving a variable name
	 * and value this accepts a mapping of variable name => value.
	 * 
	 * @param      mixed[]             $vars         An associative array of variables with keys equal to the variable name.
	 * @return     CcTemplateLoader                  This object.
	 */
	public function setTemplateVars(array $vars)
	{
		foreach ($vars as $varName => $value)
		{
			$this->setTemplateVar($varName, $value);
		}
		
		return $this;
	}
	
	/**
	 * Sets a variable that are accessible by all templates.
	 * 
	 * @param       string               $name        The name of the variable.
	 * @param       mixed                $value       The value of the variable.
	 * @return      CcTemplateLoader                  This object.
	 */
	public function setTemplateVar($name, $value)
	{
		$this->mVars[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Sets the layout used.
	 * 
	 * @param      string               $fileName            The filename of the template loaded.
	 * @param      boolean              $isAbsoluteDir       Flag whether the given file name is an absolute directory or not.
	 * @return     CcTemplate                                This object  
	 */
	public function loadTemplate($fileName, $isAbsoluteDir = false)
	{
		$template = new CcTemplate((($isAbsoluteDir) ? "" : $this->mRootDirectory).$fileName);
		$template->setVars($this->mVars);
		
		return $template;
	}
}