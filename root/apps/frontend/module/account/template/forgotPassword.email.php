<html>
	<head></head>
	<body>
		Hello <?php echo $employee['firstname'].' '.$employee['lastname']; ?>,
		<br />
		You requested to change your password. Click the link below to change your password.
		<br />
		<a href="<?php echo $ccConfig->get('base_url').'/account/changePassword?code='.urlencode($code); ?>"><?php echo $ccConfig->get('base_url').'/account/changePassword?code='.urlencode($code); ?></a>
		<br />
		<br />
	</body>
</html>