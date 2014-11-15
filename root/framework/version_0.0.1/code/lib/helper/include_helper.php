<?php

/**
 * Includes template
 */
if (!function_exists("include_template"))
{
	function include_template($template, $vars = array(), $returnContents = false, $isAbsolutePath = false)
	{
		if ($isAbsolutePath)
		{
			$tplFile = $template;
		}
		else
		{
			$tplPieces = explode('/', $template);
			
			$module = (1 < count($tplPieces)) ? $tplPieces[0] : "default";
			$tplName = (1 < count($tplPieces)) ? $tplPieces[1] : $tplPieces[0];
		
			$tplFile = "module/".$module."/template/".$tplName.".php";
		}
		
		$tpl = CcApp::getInstance()->getTemplateLoader()->loadTemplate($tplFile, $isAbsolutePath);
		$tpl->setVars($vars);
		
		return $tpl->render($returnContents);
	}
}

/**
 * Includes css to html output
 */
if (!function_exists("include_css"))
{
	function include_css($css)
	{
		$config = CcApp::getInstance()->getConfig();

		if ('http://' == substr($css, 0, 7))
		{
			echo "<link rel='stylesheet' type='text/css' href='".$css."' />";
		}
		else if ('.css' == substr($css, -4))
		{
			$css_url = $config->get('CSS_URL', null);
			if (null == $css_url)
			{
				echo "<link rel='stylesheet' type='text/css' href='".$css."' />";
			}
			else
			{
				echo "<link rel='stylesheet' type='text/css' href='".$css_url.$css."' />";
			}
		}
		else
		{
			echo "<style>".$cssCode."</style>";
		}
	}
}

/**
 * Includes js to html output
 */
if (!function_exists("include_js"))
{
	function include_js($js)
	{
		$config = CcApp::getInstance()->getConfig();

		if ('http://' == substr($css, 0, 7))
		{
			echo "<script type='text/javascript' src='".$js."' ></script>";
		}
		else if ('.js' == substr($css, -3))
		{
			$js_url = $config->get('JS_URL', null);
			if (null == $js_url)
			{
				echo "<script type='text/javascript' src='".$js."' ></script>";
			}
			else
			{
				echo "<script type='text/javascript' src='".$js_url."' ></script>";
			}
		}
		else
		{
			echo "<script type='text/javascript'>".$js."</script>";
		}
	}
}

/**
 * Includes helper for it to be usable
 */
if (!function_exists("include_helper"))
{
	function include_helper($helper)
	{
		$config = CcApp::getInstance()->getConfig();
		$file = $config->get('__APP_DIR__').'/lib/helper/'.$helper.'_helper.php';
		
		if (file_exists($file))
		{
			include($file);	
		}
		else
		{
			$file = FRAMEWORK_BASE_DIR.'/lib/helper/'.$helper.'_helper.php';
			
			if (file_exists($file))
			{
				include($file);
			}
			else
			{
				// Log the helper wasn't loaded.
			}
		}
	}
}