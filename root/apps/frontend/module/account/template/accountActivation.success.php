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
			<div id="error_message" class="alert alert-success" role="alert">
				The email have been sent to <?php echo $email; ?>. Follow the email to activate the account.
			</div>
		</div>
	</div>
</div>