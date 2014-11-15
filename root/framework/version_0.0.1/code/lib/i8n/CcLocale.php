<?php

/**
 * The locale object
 *
 * @author     Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcLocale
{
	/**
	 * The messages
	 *
	 * @var string[]
	 */
	private $messages = array();

	/**
	 * The language name.
	 *
	 * @var string
	 */
	private $name = "";

	/**
	 * Constructs a new CcLocale object using the given language
	 *
	 * @param     string      $langName      The language name
	 */
	public function __construct($langName)
	{
		$this->name = $langName;
	}
	
	/**
	 * Registers a localization text.
	 * 
	 * @param       string          $key          The key of localization text.
	 * @param       string          $value        The value of localization text.
	 * @return      CcLocale                      This object     
	 */
	public function register($key, $value)
	{
		$this->messages[(string)$key] = $value;
		
		return $this;
	}	

	/**
	 * Returns the message of the given localization key.
	 *
	 * @param      string     $key         The localization key
	 * @param      string     $default     The default value if key does not exists.
	 * @return     string                  The corresponding message of the localization key.
	 */
	public function get($key, $default = "Unsupported key")
	{
		return ($this->has($key)) ? $this->messages[$key] : $default;
	}

	/**
	 * Checks if a given localization message exists.
	 *
	 * @param      string     $key     The localization key
	 * @return     string              The corresponding message of the localization key.
	 */
	public function has($key)
	{
		return (array_key_exists($key, $this->messages));
	}

	/**
	 * Returns the JSON encoded messages.
	 *
	 * @return     string     The JSON encoded messages.
	 */
	public function getJSONEncodedMessages()
	{
		$messages = $this->getMessages();
		$toBeEncoded = array();
		foreach ($messages as $key => $message)
		{
			$toBeEncoded[$key] = htmlentities($message);
		}
		
		return json_encode($toBeEncoded);
	}

	/**
	 * Returns the messages of the language.
	 *
	 * @return     string     The messages
	 */
	public function getMessages()
	{
		return $this->messages;
	}

	/**
	 * Returns the language used.
	 *
	 * @return     string     The language used.
	 */
	public function getName()
	{
		return $this->name;
	}
}