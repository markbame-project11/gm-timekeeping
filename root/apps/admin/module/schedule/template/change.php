<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'employee'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

			<form method="post" action="<?php echo $ccConfig->get('base_url').'/schedule/change?employeeid='.$employee['empid']; ?>" role="form" clas="form-horizontal">
				<fieldset style="width: 800px;">
					<legend>Change Schedule for <?php echo $employee['firstname'].' '.$employee['lastname']; ?></legend>

					<?php if (isset($success_message)): ?>
						<div class="alert alert-success" role="alert">
							<?php echo $success_message; ?>
						</div>
					<?php endif; ?>

					<div class="form-group clearfix">
						<label class="col-sm-2 control-label">Start Date : </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d', strtotime($employee['date_hired'])); ?>" name="start_date" />
						</div>
					</div>

					<table class="table table-striped">
						<thead>
							<tr>
								<th>&nbsp;</th>
								<th>Day</th>
								<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Start Time</th>
								<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Number of Hours</th>
							</tr>
						</thead>

						<tbody>
							<?php foreach ($days as $key => $day): ?>
								<?php $is_checked = ($schedule[$key.'_time'] != '00:00:00'); ?>
								<?php $start_time = ($is_checked) ? date('H:i', strtotime($schedule[$key.'_time'])) : '09:00' ?>
								<?php $number_of_hours = ($is_checked) ? $schedule[$key.'_num_hours'] : 9 ?>
								<tr >
									<td>
										<input class="form_control" name="<?php echo $key; ?>_checked" type="checkbox" <?php echo ($is_checked) ? 'checked="checked"' : ''; ?> />
									</td>
									<td><?php echo $day; ?></td>
									<td >
										<div class="col-xs-10">
											<select name="<?php echo $key; ?>_start_time" class="form-control" required="required">
												<?php foreach ($hours as $hour_key => $hour_val): ?>
													<option value="<?php echo $hour_key; ?>" <?php echo ($hour_key == $start_time) ? 'selected="selected"' : ''; ?> >
														<?php echo $hour_val; ?>
													</option>
												<?php endforeach; ?>
											</select>
										</div>
									</td>
									<td >
										<div class="col-xs-8">
											<select name="<?php echo $key; ?>_number_of_hours" class="form-control" required="required">
												<?php for ($i = 1; $i < 24; $i++): ?>
													<option value="<?php echo $i; ?>" <?php echo ($i == $number_of_hours) ? 'selected="selected"' : ''; ?>>
														<?php echo $i; ?>
													</option>
												<?php endfor; ?>
											</select>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<div class="form-actions">
						<input type="submit" name="Submit" value="Change Schedule" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/employee/search'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	<?php if (NULL != $success_message): ?>
		setTimeout(function() {
			window.location.href = '<?php echo $ccConfig->get("base_url")."/employee/view?employeeid=".$employee["empid"]; ?>';
		}, 4000);
	<?php endif; ?>
</script>