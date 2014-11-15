<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form method="post" action="<?php echo $ccConfig->get('base_url').'/timekeeping/viewEmployeeTimesheet'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>View Employee Attendance</legend>
					
					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Employee :  </label>
						<div class="col-sm-8">
							<select name="employee_id" class="form-control">
								<?php foreach ($employees as $employee): ?>
									<option value="<?php echo $employee['empid']; ?>"><?php echo $employee['lastname'].', '.$employee['firstname']; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Start Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('-1day')); ?>" name="start_date" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">End Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" name="end_date" />
						</div>
					</div>

					<br />

					<div class="form-actions">
						<input type="submit" name="Submit" value="Show Attendance" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/timekeeping'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>