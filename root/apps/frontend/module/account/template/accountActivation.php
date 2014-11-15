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
			<div class="alert alert-info" role="alert">
				Your account is not yet activated. Enter your gumi email to activate your account. Note that this email will be used for transactions coming from Gumi Timekeeping System.
			</div>

			<?php if ($error_message != NULL): ?>
				<div class="alert alert-danger" role="alert">
					<?php echo $error_message; ?>
				</div>
			<?php endif; ?>

			<form method="post" action="<?php echo $ccConfig->get('base_url').'/account/accountActivation'; ?>" role="form">
				<div id="current_password-group" class="form-group clearfix">
					<label>Email : </label>
					<input type="email" name="email" class="form-control" size="20" value="<?php echo $fields['email']; ?>" required="required" />
				</div>

				<div class="form-actions">
					<input type="submit" name="Submit" value="Submit" class="btn btn-primary" />
					<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/account/login'; ?>" >Back to Login</a>
				</div>
			</form>

		</div>
	</div>
</div>