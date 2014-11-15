<?php
function MyErrorHandler($error)
{
	CcApp::getInstance()->getLogger()->log($error);
}

/**
 * This is the facade class of the framework.
 * Example on how to use the framework.
 * <code>
 * 	require_once(dirname(__FILE__).'/lib/framework/lib/project/CcApp.php');
 * 	$project = CcApp::getInstance();
 * 	$project->run(dirname(__FILE__), basename(__FILE__), 'version_0.0.1', true);
 * </code>
 *
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcApp
{
	/**
	 * The top most directory of all the files in the framework. 
	 *
	 * @var string
	 */
	private $mBaseDirectory = "";

	/**
	 * Tracks whether the application is run in development mode or not.
	 * Application run in development mode will show PHP error logs.
	 *
	 * @var boolean
	 */
	private $mIsRunInDev = false;

	/**
	 * The name of the script.
	 *
	 * @var string
	 */
	private $mScriptName = "index.php";

	/**
	 * The base directory of framework.
	 *
	 * @var string
	 */
	private $mFrameworkDirectory = "";

	/**
	 * The name of the application used.
	 *
	 * @var string
	 */
	private $mAppName = "";

	/**
	 * The directory of the main application.
	 *
	 * @var string
	 */
	private $mAppDirectory = "";

	/**
	 * The project config.
	 *
	 * @var CcConfig
	 */
	private $mConfig = NULL;
	
	/**
	 * The loader of templates.
	 *
	 * @var CcTemplateLoader
	 */
	private $mTemplateLoader = NULL;
	
	/**
	 * The project PDO.
	 *
	 * @var CcModelLoader
	 */
	private $mModelLoader = NULL;

	/**
	 * The logger of the application.
	 */
	private $mLogger = NULL;

	/**
	 * Constructs a new CcApp object.
	 */
	public function __construct()
	{
		$this->mFrameworkDirectory = dirname(dirname(dirname(__FILE__)));
	}
	
	/**
	 * Returns the config object used by the project.
	 * 
	 * @return		CcConfig
	 */
	public function getConfig()
	{
		return $this->mConfig;
	}
	
	/**
	 * Returns the single PDO used by the project.
	 * 
	 * @return		CcModelLoader
	 */
	public function getModelLoader()
	{
		if ( NULL == $this->mModelLoader )
		{
			$this->mModelLoader = new CcModelLoader($this->mBaseDirectory, $this->mAppDirectory, $this->getConfig());
		}
		
		return $this->mModelLoader;
	}
	
	/**
	 * Returns the template loader of the project.
	 * 
	 * @return		CcTemplateLoader
	 */
	public function getTemplateLoader()
	{
		return $this->mTemplateLoader;
	}

	/**
	 * Returns the app name used by app.
	 */
	public function getAppName()
	{
		return $this->mAppName;
	}

	/**
	 * Returns the logger of the template.
	 */
	public function getLogger()
	{
		if (NULL == $this->mLogger)
		{
			$this->mLogger = new CcFileLogger($this);
		}

		return $this->mLogger;
	}

	/**
	 * Loads helper files.
	 */
	public function loadHelpers(array $helpers)
	{
		$notFoundHelpers = array();

		foreach ($helpers as $helper)
		{
			$helperFile = $this->mBaseDirectory.'/helper/'.$helper.'_helper.php';
			if (file_exists($helperFile))
			{
				require_once($helperFile);
			}
			else
			{
				$helperFile = $this->mAppDirectory .'/helper/'.$helper.'_helper.php';

				if (file_exists($helperFile))
				{
					require_once($helperFile);
				}
				else
				{
					$notFoundHelpers[] = $helper;
				}
			}
		}

		return $notFoundHelpers;
	}

	/**
	 * Runs the project.
	 *
	 * @param     string     $documentRoot          The bas
	 * @param     string     $appBaseDirectory      The base directory of apps.
	 * @param     string     $scriptName            The name of the script.
	 * @param     string     $appName               The name of the application
	 * @param     boolean    $isRunInDev            The flag whether the application is run in dev or not.
	 */
	public function run($baseDirectory, $scriptName, $appName, $isRunInDev = false)
	{
		$this->mIsRunInDev = $isRunInDev;
		$this->mScriptName = $scriptName;
		$this->mBaseDirectory = $baseDirectory;
		$this->mAppName = $appName;

		if ($this->mIsRunInDev)
		{
			/*
			error_reporting(-1);
			ini_set('display_startup_errors', TRUE);
			ini_set('display_errors', TRUE);
			*/

          
			error_reporting(-1);
			ini_set('display_errors', FALSE);

		}
		else
		{
			error_reporting(0);
			ini_set('display_errors', FALSE);
		}

		$this->loadDefines();
		$this->loadAutoloader();
		$this->loadConfig();
		$this->loadSystemHelpers();
		$this->setUpLanguage();
		$this->setUpTemplating();
		$this->runBootstrap();
		$this->runController();
	}

	/**
	 * Runs the project.
	 *
	 * @param     string     $appBaseDirectory      The base directory of apps.
	 * @param     string     $appName               The name of the application
	 */
	public function runShell($baseDirectory, $argv)
	{
		if (count($argv) > 3)
		{
			$params = array();
			foreach ($argv as $arg)
			{
				if (substr($arg, 0, 9) == '--params=')
				{
					$str = substr($arg, 9);

					$parsed_params = explode(',', $str);
					foreach ($parsed_params as $parsed_param)
					{
						$key_value = explode(':', $parsed_param);
						if (2 <= count($key_value))
						{
							$params[$key_value[0]] = $key_value[1];
						}
						else
						{
							$params[] = $key_value[0];
						}
					}
				}
			}

			$this->mIsRunInDev = true;
			$this->mBaseDirectory = $baseDirectory;
			$this->mAppName = $argv[1];

			if ($this->mIsRunInDev)
			{
				error_reporting(E_ALL);
				ini_set('display_startup_errors', TRUE);
				ini_set('display_errors', TRUE);
			}
			else
			{
				error_reporting(0);
				ini_set('display_errors', FALSE);
			}

			$this->loadDefines();
			$this->loadAutoloader();
			$this->loadConfig();

			set_error_handler('MyErrorHandler');

			$this->loadSystemHelpers();
			$this->runBootstrap();
			$this->runTask($argv[2], $params);
		}
		else
		{
			echo "How to use: \n";
			echo "     bin/cc.php <app_name> <task_name> [--params=]\n";
			echo "Sample:\n";
			echo "     bin/cc.php frontend myTask:test --params=param1:value1,param2:value2\n";
		}
	}

	/**
	 * Runs the task
	 */
	public function runTask($task, $params)
	{
		$task_pieces = explode(':', $task);
		if (2 != count($task_pieces))
		{
			// should throw error.
			return false;
		}

		// should be configurable in config
		set_time_limit(0);

		list($module, $action) = $task_pieces;

		$task = str_underscore_to_camelcase($module).'Task';
		$method = 'execute'.str_underscore_to_camelcase($action);
		
		$task_file = $this->mAppDirectory.'/task/'.$task.'.php';
		
		// Check if controller file exists
		if (file_exists($task_file))
		{
			require_once($task_file);
			
			// Check if class exists
			if (class_exists($task))
			{
				$obj = new $task($this);

				ob_start();

				try
				{				
					if (is_callable(array($obj, $method)))
					{
						call_user_func(array($obj, $method), $params);
					}
					else
					{
						throw new Exception("Class [".$task."] has no callable method [".$method."]");
					}
				}
				catch (Exception $ex)
				{
					echo $ex->getMessage()."\n";
					echo "Line Number: ".$ex->getLine()."\n";
					echo "File: ".$ex->getFile()."\n";
					foreach ($ex->getTrace() as $trace)
					{
						echo $trace['file'].':'.$trace['line']."\n";
					}
				}

				$contents = ob_get_contents();
				ob_end_clean();

				$this->getLogger()->log($contents);
			}
			else
			{
				throw new Exception("The expected class [".$task."] not found in file [".$task_file."]");				
			}	
		}
		else
		{
			throw new Exception("The task file [".$task_file."] does not exists.");			
		}
	}

	/**
	 * Loads the defines.
	 */
	private function loadDefines()
	{
		include($this->mFrameworkDirectory.'/config/defines.php');

		define('FRAMEWORK_BASE_DIR', $this->mFrameworkDirectory);

		$this->mAppDirectory = $this->mBaseDirectory.PROJECT_APPS_FOLDER.'/'.$this->mAppName;
	}

	/**
	 * Loads the autoloader.
	 */
	private function loadAutoloader()
	{
		require_once($this->mFrameworkDirectory.'/lib/autoload/CcAutoload.php');

		$autoload = new CcAutoload();

		try
		{
			$autoload->registerClassesFromFile($this->mBaseDirectory .'/config/autoload.php', $this->mBaseDirectory);
		}
		catch (CcAutoloadException $ex) { }

		try
		{
			$autoload->registerClassesFromFile($this->mAppDirectory .'/config/autoload.php', $this->mAppDirectory);
		}
		catch (CcAutoloadException $ex) { }

		try
		{
			$autoload->registerClassesFromFile($this->mFrameworkDirectory.'/config/autoload.php', $this->mFrameworkDirectory);
		}
		catch (CcAutoloadException $ex) { }
	}

	/**
	 * Loads the database object.
	 */
	private function loadConfig()
	{
		$this->mConfig = new CcConfig();

		try
		{
			$this->mConfig->registerVarsFromFile($this->mBaseDirectory .'/config/config.php');
		}
		catch (CcConfigException $ex) { }

		try
		{
			$this->mConfig->registerVarsFromFile($this->mAppDirectory .'/config/config.php');
		}
		catch (CcConfigException $ex) { }

		$this->mConfig->registerVar('__BASE_DIR__', $this->mBaseDirectory);
		$this->mConfig->registerVar('__APP_DIR__', $this->mAppDirectory);
		$this->mConfig->registerVar('__WEB_DIR__', $this->mBaseDirectory.'/web');
		$this->mConfig->registerVar('__IN_DEV_MODE__', $this->mIsRunInDev);
		$this->mConfig->registerVar('__SCRIPT_NAME__', $this->mScriptName);
	}

	/**
	 * Loads the system helpers or helpers that are always used by templates.
	 */
	private function loadSystemHelpers()
	{
		include($this->mFrameworkDirectory.'/lib/helper/include_helper.php');
		include($this->mFrameworkDirectory.'/lib/helper/string_helper.php');
	}

	/**
	 * Loads the logger.
	 */
	private function loadLogger()
	{
		// For now we will use file logger

	}

	/**
	 * Setups the language.
	 */
	private function setUpLanguage()
	{
		CcLocaleLoader::getInstance()
			->setLanguageFolder($this->mAppDirectory .$this->mConfig->get('LANGUAGE_DIR', '/data/language'))
			->setLanguageFilePrefix($this->mConfig->get('LANGUAGE_PREFIX', ''));
	}

	/**
	 * Setups the templating.
	 */
	private function setUpTemplating()
	{
		$this->mTemplateLoader = new CcTemplateLoader($this->mAppDirectory);
		$this->mTemplateLoader->setTemplateVar(__CC_CONFIG_TEMPLATE_VAR_NAME__, $this->mConfig);
	}

	/**
	 * Runs the bootstrap file.
	 */
	private function runBootstrap()
	{
		$_APP_DIRECTORY = $this->mAppDirectory;
		$_BASE_DIRECTORY = $this->mBaseDirectory;

		$bootstrapFile = $this->mAppDirectory .'/config/bootstrap.php';
		if (file_exists($bootstrapFile))
		{
			include($bootstrapFile);
		}

		$bootstrapFile = $this->mBaseDirectory .'/config/bootstrap.php';
		if (file_exists($bootstrapFile))
		{
			include($bootstrapFile);
		}
	}

	/**
	 * Runs the controller.
	 */
	private function runController()
	{
		$request_uri = $_SERVER['REQUEST_URI'];
		
		$pos = strpos($request_uri, ".php");
		if ( FALSE != $pos )
		{
			$request_uri = substr($request_uri, $pos + 4);
		}
		
		$pos = strpos($request_uri, "?");
		if ( FALSE != $pos )
		{
			$request_uri = substr($request_uri, 0, $pos);
		}
		
		$request_uri = trim($request_uri, "/");
		
		$default_module = $this->mConfig->get('DEFAULT_MODULE', 'default');
		$default_action = $this->mConfig->get('DEFAULT_ACTION', 'index');
		
		if (0 == strlen($request_uri))
		{
			if ( array_key_exists('module', $_GET) )
			{
				$module = $_GET['module'];
				$action = (array_key_exists('action', $_GET)) ? $_GET['action'] : $default_action;
			}
			else
			{
				$module = $default_module;
				$action = $default_action;
			}		
		}
		else
		{
			$uri = explode('/', $request_uri);
			$module = (array_key_exists(0, $uri)) ? $uri[0] : $default_module;
			$action = (array_key_exists(1, $uri)) ? $uri[1] : $default_action;				
		}
		
		$runner = new CcControllerRunner($this->mAppDirectory, $this);
		$runner->run($module, $action);
	}

	/*** Static method and functions ***/

	/**
	 * The single instance of this class.
	 *
	 * @var CcApp
	 */
	private static $sInstance = null;

	/**
	 * Returns the single instance of this class.
	 *
	 * @return    CcApp
	 */
	public static function getInstance()
	{
		if (self::$sInstance == null)
		{
			self::$sInstance = new CcApp();
		}

		return self::$sInstance;
	}
}