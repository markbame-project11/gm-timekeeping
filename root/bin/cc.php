#!/usr/bin/php -q
<?php	
	require_once(dirname(dirname(__FILE__)).'/framework/version_0.0.1/code/lib/app/CcApp.php');
	$project = CcApp::getInstance();
	$project->runShell(dirname(dirname(__FILE__)), $argv);