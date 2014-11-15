<?php
		ob_start(); 
/**
 * The base class of controller classes.
 *
 * @author     Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
abstract class CcController
{
	/**
	 * The template name used.
	 *
	 * @var string
	 */
	private $mTemplate = null;

	/**
	 * The layout used.
	 *
	 * @var string
	 */
	private $mLayout = null;

	/**
	 * The current app object.
	 *
	 * @var CcApp
	 */
	private $mApp = null;

	/**
	 * The language of the current request.
	 *
	 * @var CcLocale
	 */
	protected $mLanguage = null;

	/**
	 * The module name of this controller.
	 *
	 * @var string
	 */
	protected $mModule = null;

	/**
	 * The action of the controller.
	 */
	protected $mAction = null;

	/**
	 * Constructs a new CcController
	 *
	 * @param     string       $module     The module name of the controller.
	 * @param     CcApp        $app        The current app object.
	 */
	public function __construct($module, $action, CcApp $app)
	{
		$this->mModule = $module;
		$this->mApp   = $app;
		$this->mAction = $action;

		$this->setLayout('layout');
	}

	/**
	 * The function to be called before execution of action.
	 */
	public function preExecute()
	{
	}

	/**
	 * The function to be called before showing the desired template.
	 */
	public function postExecute()
	{
	}

	/**
	 * Sets the language used by this controller.
	 *
	 * @var		string		$language         The language
	 */
	public function setLanguage($language)
	{
		CcLocaleLoader::getInstance()->setActiveLocale($language);
		$this->mLanguage = CcLocaleLoader::getInstance()->getActiveLocaleObject();

		$tplLoader = $this->mApp->getTemplateLoader();
		$tplLoader->setTemplateVar('ccLanguage', $this->mLanguage);
	}

	/**
	 * Sets the template to be used.
	 *
	 * @param     string                   $template     The template to be used with respect to the controller.
	 * @return    CcController                           This object
	 */
	public function setTemplate($template)
	{
		$this->mTemplate = ($template) ? preg_replace('/^\/|\/$/', '', $template) : null;

		return $this;
	}

	/**
	 * Sets the layout used.
	 *
	 * @param     string                   $layout
	 * @return    CcController                   		This object
	 */
	public function setLayout($layout)
	{
		$this->mLayout = ($layout) ? preg_replace('/^\/|\/$/', '', $layout) : null;

		return $this;
	}

	/**
	 * Run task from controller.
	 */
	public function runTask($action, array $params = array(), $blocking = false, $module = NULL)
	{
		$params_string = NULL;

		$l_module = ($module == NULL) ? $this->mModule : $module;

		foreach ($params as $key => $value)
		{
			if (is_string($key) && is_string($value))
			{
				if ($params_string == NULL)
				{
					$params_string = $key.':'.$value;
				}
				else
				{
					$params_string .= ','.$key.':'.$value;
				}
			}
		}

		$config = $this->getConfig();

		$command = $config->get('__BASE_DIR__').'/bin/cc.php '.$this->mApp->getAppName().' '.$l_module.':'.$action.' --params='.$params_string;
		$command .= ($blocking) ? '' : ' > /dev/null &';

		return exec($command);
	}

	/**
	 * Returns the CcBaseLogger singleton object.
	 *
	 * @return     CcBaseLogger
	 */
	public function getLogger()
	{
		return $this->mApp->getLogger();
	}

	/**
	 * Forwards the requests to 404 if the given parameter is false.
	 *
	 * @param     boolean     $param       The parameter to be tested
	 * @param     string      $message     The message why thrown to 404.
	 */
	public function forward404Unless($param, $message = "404")
	{
		if (!$param)
		{
			throw new CcForwardedError($message, '404');
		}
	}

	/**
	 * Forwards the requests to other module and action.
	 *
	 * @param     string      $module      The module
	 * @param     string      $action      The action
	 */
	public function forward($module, $action)
	{
		throw new CcForwardedAction($module, $action);
	}

	/**
	 * Redirects the page to the given location if $param is false.
	 *
	 * @param     boolean     $param       The parameter to be tested
	 * @param     string      $location    The location
	 */
	public function redirectUnless($param, $location)
	{
		if (!$param)
		{
			$this->redirect($location);
		}
	}

	/**
	 * Redirects the page to the given location
	 *
	 * @param     string      $location      The location
	 */

		function redirect($filename) {
		    if (!headers_sent())
		        header('Location: '.$filename);
		    else {
		    	//if (!isset($_SESSION['redir_filename'])) $filename = $_SESSION['redir_filename'];
		        echo '<script type="text/javascript">';
		        echo 'window.location.href="'.$filename.'";';
		        echo '</script>';
		        echo '<noscript>';
		        echo '<meta http-equiv="refresh" content="0;url='.$filename.'" />';
		        echo '</noscript>';
		        $_SESSION['redir_filename'] = $filename;
		    }
		exit;

		}

	public function redirect_old($location)
	{
		//if (!headers_sent()) {
		//echo $location;
		//exit;
		//Header("HTTP/1.1 301 Moved Permanently");
		header("Location: ".$location);
		/*
		echo '<script>';
		echo "    window.location = ".$location.";";
		echo '</script>';
		*/
		exit;
	    //}
	}

	/**
	 * Returns the CcConfig singleton object.
	 *
	 * @return     CcConfig
	 */
	public function getConfig()
	{
		return $this->mApp->getConfig();
	}

	/**
	 * Returns the CcPDO singleton object.
	 *
	 * @param      string        $modelName      The name of the model class.
	 * @return     $modelName                    Returns the model if it exists and null if not.
	 */
	public function loadModel($modelName)
	{
		return $this->mApp->getModelLoader()->load($modelName);
	}

	/**
	 * Load helpers
	 */
	public function loadHelpers(array $helpers)
	{
		return $this->mApp->loadHelpers($helpers);	
	}

	/**
	 * This is the main method called for execution.
	 *
	 * @param      mixed[]       $params         The public variables of this class returned by calling the method get_object_vars outside this class.
	 */
	public function execute($params)
	{
		$tplLoader = $this->mApp->getTemplateLoader();

		// if $this->mTemplate is null, no view will be shown
		// normally made if you want to show json encoded string in controller.
		if (null != $this->mTemplate)
		{
			$tplPieces = explode('/', $this->mTemplate);

			$module = (1 < count($tplPieces)) ? $tplPieces[0] : $this->mModule;
			$tplName = (1 < count($tplPieces)) ? $tplPieces[1] : $tplPieces[0];

			$tplFile = "module/".$module."/template/".$tplName.".php";

			// if layout is not null this means that we wrap our view
			// in a layout
			if (null != $this->mLayout)
			{
				$tplData = $tplLoader
					->loadTemplate($tplFile)
					->setVars($params)
					->render(true);
					
				$tplLoader
					->loadTemplate('layout/'.$this->mLayout.'.php')
					->setVars(array('contents' => $tplData))
					->render();
			}
			else
			{
				$tplLoader
					->loadTemplate($tplFile)
					->setVars($params)
					->render();
			}
		}
	}
}
		//ob_flush();