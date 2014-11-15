<html>
	<head>
		<title>404 Not Found</title>
	</head>
	<body>
		404 Not Found
		
		<?php if (true === $ccConfig->get('__IN_DEV_MODE__', false)): ?>
			<br />
			Message: <?php echo $exception->getMessage(); ?> <br />
			Line Number: <?php echo $exception->getLine(); ?> <br />
			File: <?php echo $exception->getFile(); ?> <br />
			<?php foreach ($exception->getTrace() as $trace): ?>
				<?php if (array_key_exists('file', $trace) && array_key_exists('line', $trace)): ?>
					<?php echo $trace['file'].':'.$trace['line']; ?> <br />
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</body>
</html>