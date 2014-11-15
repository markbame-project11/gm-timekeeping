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
			<h3 class="panel-title">Change Password</h3>
		</div>

		<div class="panel-body">
			<div id="error_message" style="<?php echo ($error_message == NULL) ? 'display:none;' : ''; ?>" class="alert alert-danger" role="alert">
				<?php echo $error_message; ?>
			</div>
			<?php if ($success_message != NULL): ?>
				<div class="alert alert-success" role="alert">
					<?php echo $success_message; ?>
					Redirecting to login in 5 seconds or you can click <a href="<?php $ccConfig->get('base_url').'/account/login'; ?>">here</a> to redirect.
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo $ccConfig->get('base_url').'/account/changePassword?code='.urlencode($code); ?>" role="form">
				<div class="form-group clearfix">
					<label>New Password : </label>
					<input type="password" name="password" class="form-control" size="20" value="" required="required" />
				</div>

				<div class="form-group clearfix">
					<label>Confirm Password : </label>
					<input type="password" name="confirm_password" class="form-control" size="20" value="" required="required" />
				</div>

				<div class="form-actions">
					<input type="submit" name="Submit" value="Change Password" class="btn btn-primary" />
					<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url'); ?>" >Cancel</a>
				</div>
			</form>

		</div>
	</div>
</div>

<script type="text/javascript">
	<?php if ($success_message != NULL): ?>
		setTimeout(function() {
			window.location.href = '<?php echo $ccConfig->get("base_url")."/account/login" ?>';
		},5000);
	<?php endif; ?>
</script>