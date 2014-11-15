<?php 
	include_template('default/_navigation', array(
		'is_admin'      => $is_admin,
		'active'        => 'home'
	)); 
?>

<div class="container" style="width:600px;">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Change Password</h3>
		</div>

		<div class="panel-body">
			<div id="error_message" style="<?php echo ($error_message == NULL) ? 'display:none;' : ''; ?>" class="alert alert-danger" role="alert">
				<?php echo $error_message; ?>
			</div>
			<div style="<?php echo ($success_message == NULL) ? 'display:none;' : ''; ?>" class="alert alert-success" role="alert">
				<?php echo $success_message; ?>
			</div>

			<form method="post" onsubmit="return ValidateForm(this);" action="<?php echo $ccConfig->get('base_url').'/employee/changePassword'; ?>" role="form">
				<div id="current_password-group" class="form-group clearfix">
					<label>Current Password : </label>
					<input type="password" name="password" class="form-control" size="20" value="" />
				</div>

				<div id="new_password-group" class="form-group clearfix">
					<label>New Password : </label>
					<input type="password" name="new_password" class="form-control" size="20" value="" />
				</div>

				<div id="confirm_password-group" class="form-group clearfix">
					<label>Confirm Password : </label>
					<input type="password" name="confirm_password" class="form-control" size="20" value="" />
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
	function ValidateForm(form)
	{
		var hasError = false;
		var assocFormFields = {};
		var formFields = $(form).serializeArray();
		for (x in formFields)
		{
			assocFormFields[formFields[x].name] = formFields[x].value;

			if (formFields[x].value == '')
			{
				$('#' + formFields[x].name + '-group').addClass('has-error');

				$('#error_message').html('All fields are mandatory');
				$('#error_message').css('display', 'block');

				hasError = true;
			}
		}

		if (hasError)
		{
			return false;
		}
		else
		{
			if (assocFormFields['new_password'] == assocFormFields['confirm_password'])
			{
				$('#error_message').css('display', 'none');

				return true;
			}
			else
			{
				$('#error_message').html('New password and confirm password should be the same');
				$('#error_message').css('display', 'block');
				$('#confirm_password-group').addClass('has-error');

				return false;
			}
		}
	}

	<?php if ($success_message != NULL): ?>
		setTimeout(function(){ window.location.href = '<?php echo $ccConfig->get('base_url'); ?>'; }, 5000);
	<?php endif; ?>
</script>