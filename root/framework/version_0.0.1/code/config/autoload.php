<?php
// This file is used for autoloading.
// The pattern for class autoloading is "class name" => "path of file with respect to a certain directory"
return array(
	'CcApp'                                 => '/lib/app/CcApp.php',
	'CcController'                          => '/lib/action/CcController.php',
	'CcControllerException'                 => '/lib/action/CcControllerException.php',
	'CcControllerRunner'                    => '/lib/action/CcControllerRunner.php',
	'CcForwardedAction'                     => '/lib/action/CcForwardedAction.php',
	'CcForwardedError'                      => '/lib/action/CcForwardedError.php',
	'CcTemplate'                            => '/lib/templating/CcTemplate.php',
	'CcTemplateException'                   => '/lib/templating/CcTemplateException.php',
	'CcTemplateLoader'                      => '/lib/templating/CcTemplateLoader.php',
	'CcConfig'                              => '/lib/config/CcConfig.php',
	'CcConfigException'                     => '/lib/config/CcConfigException.php',
	'CcLocaleLoader'                        => '/lib/i8n/CcLocaleLoader.php',
	'CcLocale'                              => '/lib/i8n/CcLocale.php',
	'CcBaseModel'                           => '/lib/db/CcBaseModel.php',
	'CcPDO'                                 => '/lib/db/CcPDO.php',
	'CcModelLoader'                         => '/lib/db/CcModelLoader.php',
	'CcTask'                                => '/lib/task/CcTask.php',
	'CcBaseLogger'                          => '/lib/logger/CcBaseLogger.php',
	'CcFileLogger'                          => '/lib/logger/CcFileLogger.php',
);