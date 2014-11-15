<div class="main-content">
	<div class="login-title">Online Timesheet</div>

	<div class="login-form">
		<form method="POST" action="<?php echo $ccConfig->get('base_url'); ?>/account/login">
			<?php if (isset($errorMessage)): ?>
				<div class="alert alert-danger" role="alert"><?php echo $errorMessage; ?></div>
			<?php endif; ?>

			<div class="form-group">
				<label for="inputUsername">Username</label>
				<input type="text" class="form-control" id="inputUsername" placeholder="Username" name="username" />
			</div>
			<div class="form-group">
				<label for="inputPassword">Password</label>
				<input type="password" class="form-control" id="inputPassword" placeholder="Password" name="password" />
			</div>
			<?php
			  if (!isset($referpage)) $referpage = $ccConfig->get('base_url'); ;
			?>

			<input type="hidden" name="referpage" value="<?php echo $referpage; ?>"> 
			<button type="submit" class="btn btn-primary">Login</button>
		</form>

		<br />
		<span style="font-size:12px;">
			Help: <a href="<?php echo $ccConfig->get('base_url'); ?>/account/forgotPassword">I can't sign in or I forgot my username/password</a>
		</span>
	</div>
</div>