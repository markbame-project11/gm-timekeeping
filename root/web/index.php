<?php
	require_once(dirname(dirname(__FILE__)).'/framework/version_0.0.1/code/lib/app/CcApp.php');
	$project = CcApp::getInstance();
	$project->run(dirname(dirname(__FILE__)), basename(__FILE__), 'frontend', true);