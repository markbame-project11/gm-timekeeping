<?php

/**
 * The file logger
 *
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcFileLogger extends CcBaseLogger
{
	/**
	 * The current app object.
	 *
	 * @var CcApp
	 */
	private $mApp = null;

	/**
	 * The filename of the log file.
	 */
	private $mLogFileName = '';

	/**
	 * Constructs a new CcFileLogger
	 *
	 * @param     string       $module     The module name of the controller.
	 * @param     CcApp        $app        The current app object.
	 */
	public function __construct(CcApp $app)
	{
		$this->mApp   = $app;

		$base_dir = $app->getConfig()->get('__BASE_DIR__');
		$app_name = $app->getAppName();
		$log_file = $base_dir.'/logs/'.$app_name.'.log';

		$this->mLogFileName = $log_file;
	}

	/**
	 * Logs the given string
	 */
	public function log($string, $log_level = 1)
	{
		$handle = fopen($this->mLogFileName, 'a+');
		fwrite($handle, $string."\n");
		fclose($handle);
	}
}