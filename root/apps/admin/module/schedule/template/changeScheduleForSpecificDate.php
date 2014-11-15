<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form method="post" action="<?php echo $ccConfig->get('base_url').'/schedule/changeScheduleForSpecificDate'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>Change Schedule For Specific Date</legend>
					
					<?php if (isset($error_message)): ?>
						<div class="alert alert-danger" role="alert">
							<?php echo $error_message; ?>
						</div>
					<?php endif; ?>

					<?php if (isset($success_message)): ?>
						<div class="alert alert-success" role="alert">
							<?php echo $success_message; ?>
						</div>
					<?php endif; ?>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Employee :  </label>
						<div class="col-sm-8">
							<select name="employee_id" class="form-control" onchange="GetEmployeeTimesheet();" required="required">
								<option value="">Select Employee</option>
								<?php foreach ($employees as $employee): ?>
									<option value="<?php echo $employee['empid']; ?>" <?php echo ($fields['employee_id'] == $employee['empid']) ? 'selected="selected"' : ''; ?> ><?php echo $employee['lastname'].', '.$employee['firstname']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">For Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['for_date']; ?>" name="for_date" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">New Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['new_date']; ?>" name="new_date" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Start Time :  </label>
						<div class="col-sm-8">
							<input type="time" class="form-control" value="<?php echo $fields['start_time']; ?>" name="start_time" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Number of Hours :  </label>
						<div class="col-sm-8">
							<input class="form-control" type="number" name="number_of_hours" min="1" max="24" value="<?php echo $fields['number_of_hours']; ?>" required="required" />
						</div>
					</div>

					<br />

					<div class="form-actions">
						<input type="submit" name="Submit" value="Change Schedule" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/timekeeping'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>4

<script type="text/javascript">
	<?php if (isset($success_message)): ?>
		setTimeout(function(){
			window.location.href = '<?php echo $ccConfig->get('base_url').'/timekeeping'; ?>';
		}, 4000);
	<?php endif; ?>
</script>