<html>
	<head></head>
	<body>
		Hello <?php echo $employee['firstname'].' '.$employee['lastname']; ?>,
		<br />
		To activate your account click the link below:
		<br />
		<a href="<?php echo $ccConfig->get('base_url').'/account/activateAccount?code='.urlencode($code); ?>"><?php echo $ccConfig->get('base_url').'/account/activateAccount?code='.urlencode($code); ?></a>
		<br />
		<br />
	</body>
</html>