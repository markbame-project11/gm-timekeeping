<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form method="post" action="<?php echo $ccConfig->get('base_url').'/timekeeping/changeTimesheet'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>Add/Change Employee Timesheet</legend>
					
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
							<select id="field_employee_id" name="employee_id" class="form-control" onchange="GetEmployeeTimesheet();">
								<option value="0">Select Employee</option>
								<?php foreach ($employees as $employee): ?>
									<option value="<?php echo $employee['empid']; ?>" <?php echo ($fields['employee_id'] == $employee['empid']) ? 'selected="selected"' : ''; ?> ><?php echo $employee['lastname'].', '.$employee['firstname']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Date :  </label>
						<div class="col-sm-8">
							<input id="field_date" type="date" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo $fields['date']; ?>" name="date" onchange="GetEmployeeTimesheet();" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Check In Time :  </label>
						<div class="col-sm-8">
							<input id="field_checkin_time" type="time" class="form-control" value="<?php echo $fields['checkin_time']; ?>" name="checkin_time" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Check Out Date :  </label>
						<div class="col-sm-8">
							<input id="field_checkout_date" type="date" class="form-control" value="<?php echo $fields['checkout_date']; ?>" name="checkout_date" required="required" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Check Out Time :  </label>
						<div class="col-sm-8">
							<input id="field_checkout_time" type="time" class="form-control" value="<?php echo $fields['checkout_time']; ?>" name="checkout_time" required="required" />
						</div>
					</div>

					<br />

					<div class="form-actions">
						<input type="submit" name="Submit" value="Change/Add Timesheet" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/timekeeping'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	function GetEmployeeTimesheet()
	{
		if ($('#field_date').val() != '' && $('#field_employee_id').val() != '0')
		{
			var url = '<?php echo $ccConfig->get('base_url').'/timekeeping/getTimesheetOf'; ?>';
			var params = 'employee_id=' + $('#field_employee_id').val();
			params += '&date=' + $('#field_date').val();
			$.post(url, params, function(response) {
				if (response.success && response.record_found)
				{
					$('#field_checkin_time').val(response.record.checkin_time);
					if (response.record.checkout_time != '00:00')
					{
						$('#field_checkout_time').val(response.record.checkout_time);
					}

					if (response.record.checkout_date != '0000-00-00')
					{
						$('#field_checkout_date').val(response.record.checkout_date);
					}

					$('#field_checkout_date').attr('min', $('#field_date').val());
				}
			}, 'json');
		}
	}

	<?php if (isset($success_message)): ?>
		setTimeout(function(){
			window.location.href = '<?php echo $ccConfig->get('base_url').'/timekeeping'; ?>';
		}, 5000);
	<?php endif; ?>
</script>