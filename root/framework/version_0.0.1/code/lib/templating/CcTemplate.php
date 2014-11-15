<?php

/**
 * Represents the template object.
 * 
 * @author         Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcTemplate
{	
	/**
	 * The file name of the template.
	 * 
	 * @var string
	 */
	private $mFileName = "";
	
	/**
	 * The variables that are accessible in the PHP template file.
	 * 
	 * @var mixed[]
	 */
	private $mVars = array();
	
	/**
	 * Constructs a new CcTemplate using the given template file.
	 * 
	 * @param       string       $fileName        The absolute path of the file name of the template. 
	 */
	public function __construct($fileName)
	{
		$this->mFileName = $fileName;
	}
	
	/**
	 * Sets the name of the file that will be used by this template object.
	 * 
	 * @param       string                  $fileName        The name of the file to be used.
	 * @return      CcTemplate                               This object.
	 */
	public function setFileName($fileName)
	{
		$this->mFileName = (string)$fileName;
		
		return $this;
	}
	
	/**
	 * Returns the name of the file used by this template object.
	 * 
	 * @return       string          The name of the file used by this template object.
	 */
	public function getFileName()
	{
		return $this->mFileName;
	}
	
	/**
	 * Adds/Sets the variable that can be used by the php template file used.
	 * 
	 * @param        string                 $name     The name of the variable in the template.
	 * @param        mixed                  $value    The value of the variable in the template.
	 * @return       CcTemplate                       This object
	 */
	public function setVar($name, $value)
	{
		$this->mVars[$name] = $value;
		
		return $this;
	}
	
	/**
	 * Sets variables that can be used by the template.
	 * 
	 * @param        mixed[]              $vars       An associative array of variables with keys equal to variable name.
	 * @return       CcTemplate                       This object  
	 */
	public function setVars(array $vars)
	{
		foreach ($vars as $varName => $value)
		{
			$this->setVar($varName, $value);
		}
		
		return $this;
	}
	
	/**
	 * Echos or returns the content of parsing this template.
	 * 
	 * @param      mixed[]           $vars                  The variables that are available to the template.
	 * @param      boolean           $returnContents        Flag whether to return the contents of template after parsing or not.
	 * 
	 * @throws     CcTemplateException                      Throws exception if $template does not exists.
	 * @return     string|null                              The parsed content if $returnContents is equal to true and null if it is false.
	 */
	public function render($returnContents = false)
	{
		return self::fetchTemplate($this->mFileName, $this->mVars, $returnContents);	
	}
	
	/*** Magic methods ***/
	
	public function __toString()
	{
		$this->render(true);
	}
	
	/*** Static methods and properties ***/
	
	/**
	 * Parses the given template file using $vars as variables in the template.
	 * 
	 * @param       string        $template             The absolute path of a template.
	 * @param       mixed[]       $vars                 Associative array of variables that are accessible by the template. The key of this array 
	 *                                                  corresponds to the name of the variable.
	 * @param       boolean       $returnContents       Flag whether to return the parsed content or show it to the browser after parsing.
	 * 
	 * @throws      CcTemplateException                 Throws exception if $template does not exists.
	 * @return      string|null                         The parsed content if $returnContents is equal to true and null if it is false.
	 */
	private static function fetchTemplate($template, array $vars = array(), $returnContents = false)
	{
		if (!is_readable($template))
		{
			throw new CcTemplateException("The template file {$template} is not readable.", 1);
		}
		
		if (!file_exists($template))
		{
			throw new CcTemplateException("The template file {$template} does not exists.", 2);
		}
		
		extract($vars);
		
		if ( $returnContents )
		{
			ob_start();
			include($template);
			$contents = ob_get_contents();
			ob_end_clean();
			
			return $contents;
		}
		else
		{
			include($template);
			
			return null;
		}		
	}
}