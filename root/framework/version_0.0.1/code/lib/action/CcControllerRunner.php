<?php
/**
 * The runners of controller.
 * 
 * @author     Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcControllerRunner
{
	/**
	 * The base directory of apps files.
	 * 
	 * @var string
	 */
	private $mBaseDirectory = "";
	
	/**
	 * The application object.
	 * 
	 * @var CcApp
	 */
	private $mApp;
	
	/**
	 * Constructs a new GLControllerRunner object.
	 * 
	 * @param        string         $baseDirectory        The base directory of apps files.
	 * @param        CcApp          $app                  The current project.
	 */
	public function __construct($baseDirectory, CcApp $app)
	{
		$this->mBaseDirectory = $baseDirectory;
		$this->mApp = $app;
	}
	
	/**
	 * Runs the given controller.
	 * 
	 * @param      string        $module         The module to run
	 * @param      string        $action         The action of the controller.
	 */
	public function run($module, $action)
	{	
		try
		{
			$this->runController($module, $action);
		}
		catch (Exception $ex)
		{
			if (is_subclass_of($ex, 'CcControllerException') && file_exists(FRAMEWORK_BASE_DIR."/data/templates/".$ex->getErrorType().".php"))
			{
				$this->mApp->getTemplateLoader()
					->loadTemplate(FRAMEWORK_BASE_DIR."/data/templates/".$ex->getErrorType().".php", true)
					->setVar('exception', $ex)
					->render();
			}
			else
			{
				$this->mApp->getTemplateLoader()
					->loadTemplate(FRAMEWORK_BASE_DIR."/data/templates/generic.php", true)
					->setVar('exception', $ex)
					->render();
			}
		}
	}
	
	/**
	 * 
	 */
	private function runController($module, $action)
	{
		$controller = ucfirst($module).'Controller';
		$method = 'execute'.ucfirst($action);
		
		$controller_file = $this->mBaseDirectory.'/module/'.$module.'/'.$controller.'.php';
		
		// Check if controller file exists
		if (file_exists($controller_file))
		{
			require_once($controller_file);
			
			// Check if class exists
			if (class_exists($controller))
			{
				$obj = new ReflectionClass($controller);
				if ($obj->isSubclassOf('CcController'))
				{	
					try
					{				
						$control = $obj->newInstance($module, $action, $this->mApp);
						$control->setTemplate($action);
						
						if (is_callable(array($control, $method)))
						{
							$control->preExecute();
							call_user_func(array($control, $method), array(array()));
							$control->postExecute();
							$control->execute(get_object_vars($control));
						}
						else
						{
							throw new CcControllerException("Class [".$controller."] has no callable method [".$method."]");
						}
					}
					catch (CcForwardedAction $f)
					{
						$this->runController($f->getModule(), $f->getAction());
					}
					catch (Exception $ex)
					{
						// $exception = new CcControllerException($ex->getMessage(), 404, $ex);
						// $exception->setFile($ex->getFile());
						// $exception->setLine($ex->getLine());
						
						throw $ex; // ception;
					}
				}
				else
				{
					throw new CcControllerException("The class ".$controller." should be a subclass of CcController.");
				}	
			}
			else
			{
				throw new CcControllerException("The expected class [".$controller."] not found in file [".$controller_file."]");				
			}	
		}
		else
		{
			throw new CcControllerException("The controller file [".$controller_file."] does not exists.");			
		}
	}
}