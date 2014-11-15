<?php

/**
 * The base class of tasks.
 *
 * @author     Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
abstract class CcTask
{
	/**
	 * The current app object.
	 *
	 * @var CcApp
	 */
	private $mApp = null;

	/**
	 * Constructs a new CcTask
	 *
	 * @param     CcApp        $app        The current app object.
	 */
	public function __construct(CcApp $app)
	{
		$this->mApp    = $app;
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
	 * Returns the CcBaseLogger singleton object.
	 *
	 * @return     CcBaseLogger
	 */
	public function getLogger()
	{
		return $this->mApp->getLogger();
	}

	/**
	 * Loads the model and creates an object with the given model name.
	 *
	 * @param      string|string[]        $modelName|$modelNames      	The name|names of the model class|classes.
	 * @return     bool|none                                            Returns true if the model exists and false otherwise.|No return, you can check if the model exists by checking $this->$modelName										
	 */
	public function loadModel($modelNames)
	{
		if (is_array($modelNames))
		{
			foreach ($modelNames as $modelName)
			{
				$model = $this->mApp->getModelLoader()->load($modelName);
				if ($model != NULL)
				{
					$this->$modelName = $model;
				}
			}
		}
		else
		{
			$model = $this->mApp->getModelLoader()->load($modelNames);
			if ($model != NULL)
			{
				$this->$modelNames = $model;

				return true;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Load helpers
	 */
	public function loadHelpers(array $helpers)
	{
		return $this->mApp->loadHelpers($helpers);	
	}
}