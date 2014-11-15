<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title><?php echo $ccConfig->get('title'); ?></title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="">
		<meta name="author" content="">

		<link href="<?php echo $ccConfig->get('assets_base_url'); ?>vendor/bootstrap/css/bootstrap.min.css?v=1.1" rel="stylesheet" />
		<link href="<?php echo $ccConfig->get('assets_base_url'); ?>vendor/bootstrap/css/non-responsive.css" rel="stylesheet" />
		<link href="<?php echo $ccConfig->get('assets_base_url'); ?>vendor/bootstrap/css/dashboard.css" rel="stylesheet" />
		<link href="<?php echo $ccConfig->get('assets_base_url'); ?>css/custom-theme/jquery-ui-1.10.0.custom.css" rel="stylesheet" />

		<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

		<script src="<?php echo $ccConfig->get('assets_base_url'); ?>js/jquery.min.js"></script>
		<script src="<?php echo $ccConfig->get('assets_base_url'); ?>vendor/bootstrap/js/bootstrap.min.js?v=0.1"></script>
		<script src="<?php echo $ccConfig->get('assets_base_url'); ?>js/jquery-ui-1.10.0.custom.min.js"></script>
	</head>

	<body>
		
		<?php echo $contents; ?>
	</body>
</html>