<?php
/**
 * The model loader class.
 *
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcModelLoader
{
	/**
	 * The single instance of CcConfig used in the framework.
	 */
	private $mConfig = NULL;

	/**
	 * The top-most directory.
	 * 
	 * @var string
	 */
	private $mBaseDirectory = "";

	/**
	 * The directory of the app.
	 * 
	 * @var string
	 */
	private $mAppDirectory = "";

	/**
	 * Associative array for loaded configuration of database.
	 * 
	 * @var string
	 */
	private $mLoadedConfiguration = array();

	/**
	 * The single PDO used by all models.
	 */
	private $mPDO = NULL;

	/**
	 * The array that will contain the single object per model loaded.
	 */
	private $mLoadedModels = array();
	
	/**
	 * Creates an empty CcModelLoader object.
	 */
	public function __construct($baseDirectory, $appDirectory, CcConfig $config)
	{
		$this->mBaseDirectory = $baseDirectory;
		$this->mAppDirectory = $appDirectory;
		$this->mConfig = $config;

		// load global database config
		$databaseFile = $this->mBaseDirectory.'/config/database.php';
		if (file_exists($databaseFile))
		{
			$data = include($databaseFile);
			if (is_array($data))
			{
				$this->mLoadedConfiguration = array_merge($this->mLoadedConfiguration, $data);
			}
		}

		// load application database config
		$databaseFile = $this->mAppDirectory.'/config/database.php';
		if (file_exists($databaseFile))
		{
			$data = include($databaseFile);
			if (is_array($data))
			{
				$this->mLoadedConfiguration = array_merge($this->mLoadedConfiguration, $data);
			}
		}

		if (
			array_key_exists('dsn', $this->mLoadedConfiguration) &&
			array_key_exists('user', $this->mLoadedConfiguration) &&
			array_key_exists('password', $this->mLoadedConfiguration)
		)
		{
			$this->mPDO = new CcPDO($this->mLoadedConfiguration['dsn'], $this->mLoadedConfiguration['user'], $this->mLoadedConfiguration['password']);
		}
		else
		{
			// log error, PDO will be null
		}
	}

	/**
	 * Loads a model object. Returns null if the model doesn't exists.
	 *
	 * @param      string     $modelName     The name of the model.
	 * @return     $modelName.'Model'                The model object if it exists or null if it doesn't exists.
	 */
	public function load($modelName)
	{
		$model = ucfirst($modelName).'Model';
		if (array_key_exists($model, $this->mLoadedModels))
		{
			return $this->mLoadedModels[$model];
		}

		// load model in global folder first
		$model_file = $this->mBaseDirectory.'/model/'.$model.'.php';
		
		// Check if controller file exists
		if (file_exists($model_file))
		{
			require_once($model_file);
			
			// Check if class exists
			if (class_exists($model))
			{
				$obj = new ReflectionClass($model);
				if ($obj->isSubclassOf('CcBaseModel'))
				{	
					try
					{	
						$this->mLoadedModels[$model] = 	$obj->newInstance($this->mPDO, $this->mConfig);
						$this->mLoadedModels[$model]->configure();
						
						return $this->mLoadedModels[$model];
					}
					catch (Exception $ex)
					{
						// log error
						return NULL;
					}
				}
				else
				{
					// log error
					return NULL;
				}	
			}
			else
			{
				// log error
				return NULL;			
			}	
		}
		else
		{
			// load model in app folder if nothing is seen in global
			$model_file = $this->mAppDirectory.'/model/'.$model.'.php';
			
			// Check if controller file exists
			if (file_exists($model_file))
			{
				require_once($model_file);
				
				// Check if class exists
				if (class_exists($model))
				{
					$obj = new ReflectionClass($model);
					if ($obj->isSubclassOf('CcBaseModel'))
					{	
						try
						{	
							$this->mLoadedModels[$model] = 	$obj->newInstance($this->mPDO, $this->mConfig);
							$this->mLoadedModels[$model]->configure();
							
							return $this->mLoadedModels[$model];
						}
						catch (Exception $ex)
						{
							// log error
							return NULL;
						}
					}
					else
					{
						// log error
						return NULL;
					}	
				}
				else
				{
					// log error
					return NULL;			
				}	
			}
			else
			{
				// log error
				return NULL;		
			}		
		}
	}
}