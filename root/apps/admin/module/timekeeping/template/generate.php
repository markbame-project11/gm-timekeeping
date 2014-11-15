<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form id="generateEmployeeAttendanceForm" method="post" action="<?php echo $ccConfig->get('base_url').'/payroll/generateAttendancePopup'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>Generate Employee Attendance</legend>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Start Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['start_date']; ?>" name="start_date" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">End Date :  </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['end_date']; ?>" name="end_date" max="<?php echo date('Y-m-d', strtotime('-1day')); ?>" />
						</div>
					</div>

					<br />
					<br />

					<div class="form-actions">
						<!--input type="submit" name="Submit" value="Show Attendance" class="btn btn-primary" onclick="SubmitShowAttendanceBtn();" /-->
						<input type="submit" name="Submit" value="Download As Spreadsheet" class="btn btn-primary" onclick="SubmitDownloadAsExcelBtn();" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/timekeeping'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	function SubmitShowAttendanceBtn()
	{
		$('#generateEmployeeAttendanceForm').attr('target', 'attendance_popup');
		$('#generateEmployeeAttendanceForm').attr('action', '<?php echo $ccConfig->get('base_url').'/timekeeping/generateAttendancePopup'; ?>');
		window.open('test.html', 'attendance_popup', 'scrollbars=no,menubar=no,height=600,width=800,resizable=yes,toolbar=no,status=no');		
	}

	function SubmitDownloadAsExcelBtn()
	{
		$('#generateEmployeeAttendanceForm').attr('target', '_blank');
		$('#generateEmployeeAttendanceForm').attr('action', '<?php echo $ccConfig->get('base_url').'/timekeeping/downloadAttendanceExcel'; ?>');		
	}
</script>