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
			<p>
				The given code is already expired. Click <a href="<?php echo $ccConfig->get('base_url').'/account/forgotPassword' ?>">here</a> to request again for a change password.
			</p>

			<br >
			<div class="form-actions">
				<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/account/login'; ?>" >Go To Login</a>
			</div>
		</div>
	</div>
</div>