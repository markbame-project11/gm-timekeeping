<?php

/**
 * The loader for locale.
 * 
 * CcLocaleLoader::getInstance()
 * 	->setLanguageFolder($this->baseDirectory.$configuration['LANGUAGE_DIR'])
 * 	->setLanguageFilePrefix($configuration['LANGUAGE_PREFIX'])
 * 	->setActiveLocale($configuration['LANG']);
 * 
 * $locale = CcLocaleLoader::getInstance()->loadLocale('ES');
 * 
 * @author      Antonio P. Cruda Jr. <antonio.cruda@gameloft.com>
 */
class CcLocaleLoader
{
	/**
	 * The directory of the language files.
	 * 
	 * @var string
	 */
	private $languageFolder = "";
	
	/**
	 * The language file prefix.
	 * 
	 * @var string
	 */
	private $languageFilePrefix = "";
	
	/**
	 * The active locale text.
	 * 
	 * @var string
	 */
	private $activeLocale = "EN";
	
	/**
	 * Constructs an empty GLLocaleLoader object.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Sets the language folder.
	 * 
	 * @param       string       $languageFolder
	 */
	public function setLanguageFolder($languageFolder)
	{
		$this->languageFolder = $languageFolder;
		
		return $this;
	}
	
	/**
	 * Returns the language folder.
	 * 
	 * @return     string
	 */
	public function getLanguageFolder()
	{
		return $this->languageFolder;
	}
	
	/**
	 * Sets the language prefix
	 * 
	 * @param       string       $languagePrefix
	 */
	public function setLanguageFilePrefix($languageFilePrefix)
	{
		$this->languageFilePrefix = $languageFilePrefix;
		
		return $this;
	}
	
	/**
	 * Returns the language file prefix.
	 * 
	 * @return     string
	 */
	public function getLanguageFilePrefix()
	{
		return $this->languageFilePrefix;
	}
	
	/**
	 * Sets the active locale
	 * 
	 * @param      string        $activeLocale
	 */
	public function setActiveLocale($activeLocale)
	{
		$this->activeLocale = $activeLocale;
		
		return $this;
	}
	
	/**
	 * Returns the active locale
	 * 
	 * @return     string
	 */
	public function getActiveLocale()
	{
		return $this->activeLocale;
	}
	
	/**
	 * Returns the active locale object
	 * 
	 * @return     string
	 */
	public function getActiveLocaleObject()
	{
		return $this->loadLocale($this->activeLocale);
	}
	
	/**
	 * Loads a locale.
	 * 
	 * @return    CcLocale
	 */
	public function loadLocale($locale)
	{
		$glLocale = new CcLocale($locale);
		
		$langFile = $this->languageFolder.'/'.$this->languageFilePrefix.$locale.'.php';
		
		if (file_exists($langFile))
		{
			$messages = include($langFile);
			if (!is_array($messages) && isset($locale_strings))
			{
				$messages = $locale_strings;
				
				foreach ($messages as $key => $val)
				{
					$glLocale->register($key, $val);	
				}
			}
		}
		
		return $glLocale;
	}
	
	/*** Static properties and methods ***/
	
	/**
	 * The single instance of this object.
	 * 
	 * @var CcLocaleLoader
	 */
	private static $instance = null;
	
	/**
	 * Returns the single instance of this object.
	 * 
	 * @return      CcLocaleLoader      The single instance of this object.
	 */
	public static function getInstance()
	{
		if (null == self::$instance)
		{
			self::$instance = new CcLocaleLoader();
		}
		
		return self::$instance;
	}
}