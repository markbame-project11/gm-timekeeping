<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'holiday'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form method="post" action="<?php echo $ccConfig->get('base_url').'/holiday/addHolidayPay'; ?>" role="form" clas="form-horizontal" >
				<fieldset style="width: 850px;">
					<legend>Add Holiday Pay</legend>

					<?php if (isset($success_message)): ?>
						<div class="alert alert-success" role="alert">
							<?php echo $success_message; ?>
						</div>
					<?php endif; ?>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Holiday  :  </label>
						<div class="col-sm-8">
							<select name="holiday_id" class="form-control" required="required" >
								<option value="">Select Holiday</option>
								<?php foreach ($holidays as $holiday): ?>
									<option value="<?php echo $holiday['id']; ?>" <?php echo ($holiday['id'] == $fields['holiday_id']) ? 'selected="selected"' : ''; ?> >
										<?php echo $holiday['name']; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Employee :  </label>
						<div class="col-sm-8">
							<select name="employee_id" class="form-control" required="required" >
								<option value="">Select Employee</option>
								<?php foreach ($employees as $employee): ?>
									<option value="<?php echo $employee['empid']; ?>" <?php echo ($employee['empid'] == $fields['employee_id']) ? 'selected="selected"' : ''; ?> >
										<?php echo $employee['lastname'].', '.$employee['firstname']; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<br />

					<div class="form-actions">
						<input type="submit" name="Submit" value="Add Holiday Pay" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/holiday'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>4

<script type="text/javascript">
	<?php if (isset($success_message)): ?>
		setTimeout(function(){
			window.location.href = '<?php echo $ccConfig->get('base_url').'/holiday'; ?>';
		}, 4000);
	<?php endif; ?>
</script>