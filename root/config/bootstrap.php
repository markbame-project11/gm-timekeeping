<?php
	include_once($_BASE_DIRECTORY.'/lib/vendor/PHPMailer-5.2.8/PHPMailerAutoload.php');
	if (array_key_exists('SERVER_NAME', $_SERVER))
	{
		session_cache_limiter('none');
		session_start();
	}