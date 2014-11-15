<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form id="generateEmployeeAttendanceForm" method="post" action="<?php echo $ccConfig->get('base_url').'/timekeeping/uploadFile'; ?>" role="form" clas="form-horizontal" enctype="multipart/form-data" >
				<fieldset style="width: 850px;">
					<legend>Upload Excel For Employee Attendance</legend>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Excel File :  </label>
						<div class="col-sm-8">
							<input type="file" class="form-control" value="" name="file" required="required" />
						</div>
					</div>

					<br />
					<br />

					<div class="form-actions">
						<!--input type="submit" name="Submit" value="Show Attendance" class="btn btn-primary" onclick="SubmitShowAttendanceBtn();" /-->
						<input type="submit" name="Submit" value="Download As Excel" class="btn btn-primary" />
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
</script>