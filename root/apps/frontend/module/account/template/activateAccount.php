<div class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="<?php echo $ccConfig->get('base_url'); ?>">Gumi Online Timesheet</a>
		</div>
	</div>
</div>

<div class="container" style="width:600px;">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Account Activation</h3>
		</div>

		<div class="panel-body">
			<?php if ($success_message != NULL): ?>
				<div class="alert alert-success" role="alert">
					<?php echo $success_message; ?> Please change your password after logging in.
					Redirecting to login in 5 seconds or you can click <a href="<?php echo $ccConfig->get('base_url').'/account/login'; ?>">here</a> to login.
				</div>
			<?php else: ?>
				<div class="alert alert-error" role="alert">
					<?php echo $error_message; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	<?php if ($success_message != NULL): ?>
		setTimeout(function() {
			window.location.href = '<?php echo $ccConfig->get("base_url")."/account/login"; ?>';
		}, 5000);
	<?php endif; ?>
</script>