<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'leave'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form method="post" action="<?php echo $ccConfig->get('base_url').'/leave/add'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>Add Leave</legend>
					
					<?php if (isset($success_message)): ?>
						<div class="alert alert-success" role="alert">
							<?php echo $success_message; ?>
						</div>
					<?php endif; ?>
					
					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Employee :  </label>
						<div class="col-sm-8">
							<select name="employee_id" class="form-control" required="required" >
								<option value="">Select Employee</option>
								<?php foreach ($employees as $employee): ?>
									<option value="<?php echo $employee['empid']; ?>" <?php echo ($employee['empid'] == $fields['employee_id']) ? 'selected="selected"' : ''; ?> ><?php echo $employee['lastname'].', '.$employee['firstname']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['date']; ?>" name="date" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Leave Type :  </label>
						<div class="col-sm-8">
							<select name="leave_type" class="form-control" required="required">
								<?php foreach ($leave_types as $key => $value): ?>
									<option value="<?php echo $key; ?>" <?php echo ($key == $fields['leave_type']) ? 'selected="selected"' : ''; ?>><?php echo $value; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Is Paid:  </label>
						<div class="col-sm-8">
							<select name="is_paid" class="form-control" required="required">
								<option value="yes" <?php echo ('yes' == $fields['is_paid']) ? 'selected="selected"' : ''; ?>>Yes</option>
								<option value="no" <?php echo ('no' == $fields['is_paid']) ? 'selected="selected"' : ''; ?>>No</option>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Reason :  </label>
						<div class="col-sm-8">
							<textarea name="reason" class="form-control" required="required"><?php echo $fields['reason']; ?></textarea>
						</div>
					</div>

					<br />

					<div class="form-actions">
						<input type="submit" name="Submit" value="Save" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/leave'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	<?php if (isset($success_message)): ?>
		setTimeout(function(){
			window.location.href = '<?php echo $ccConfig->get('base_url').'/leave'; ?>';
		}, 4000);
	<?php endif; ?>
</script>