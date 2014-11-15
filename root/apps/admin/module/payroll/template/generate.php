<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'payroll'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form id="generateEmployeeAttendanceForm" method="post" action="<?php echo $ccConfig->get('base_url').'/payroll/generate'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>Generate Payroll</legend>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Payroll Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['payroll_date']; ?>" name="payroll_date" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Start Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['start_date']; ?>" name="start_date" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">End Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['end_date']; ?>" name="end_date" max="<?php echo date('Y-m-d'); ?>" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Deductions :  </label>
						<div class="col-sm-8">
							<input type="checkbox" name="sss_checkbox" /> SSS &nbsp;&nbsp;
							<input type="checkbox" name="philhealth_checkbox" /> Philhealth &nbsp;&nbsp;
							<input type="checkbox" name="pagibig_checkbox" /> Pagibig &nbsp;&nbsp;
						</div>
					</div>

					<br />
					<br />

					<div class="form-actions">
						<input type="submit" name="Submit" value="Generate" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/payroll'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>