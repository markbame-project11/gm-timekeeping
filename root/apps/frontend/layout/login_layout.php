<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php echo $ccConfig->get('title'); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link href="<?php echo $ccConfig->get('assets_base_url'); ?>/vendor/bootstrap/css/bootstrap.min.css?v=1.1" rel="stylesheet">
		<link href="<?php echo $ccConfig->get('assets_base_url'); ?>/css/login.css" rel="stylesheet">
	</head>

	<body>
		<div class="container"><?php echo $contents; ?></div>
	</body>
</html>