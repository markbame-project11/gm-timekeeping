<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'employee'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">View Employee</h1>

			<div>
				<strong>Department Name : </strong> 
				<a href="<?php echo $ccConfig->get('base_url').'/department/view?deptid='.$employee['deptid']; ?>"><?php echo $employee['deptname']; ?></a>
			</div>

			<div>
				<strong>Employment Status : </strong> 
				<?php echo $employmentStatuses[$employee['employment_status']]; ?>
			</div>

			<div>
				<strong>Username : </strong> 
				<?php echo $employee['login']; ?>
			</div>

			<div>
				<strong>Nickname : </strong> 
				<?php echo $employee['nickname']; ?>
			</div>

			<div>
				<strong>Skype ID : </strong>
				<?php echo $employee['skype_id']; ?>
			</div>

			<div>
				<strong>Date Hired : </strong>
				<?php echo date('M d, Y', strtotime($employee['date_hired'])); ?>
			</div>

			<div>
				<strong>Birth Date : </strong>
				<?php echo date('M d, Y', strtotime($employee['dob'])); ?>
			</div>

			<div>
				<strong>Position : </strong> 
				<?php echo $employee['position']; ?>
			</div>

			<div>
				<strong>Gender : </strong>
				<?php echo $genderList[$employee['gender']]; ?>
			</div>

			<div>
				<strong>Email : </strong> 
				<?php echo $employee['email']; ?>
			</div>

			<div>
				<strong>Address : </strong>
				<?php echo $employee['address1']; ?>
			</div>

			<div>
				<strong>Cell Phone : </strong>
				<?php echo $employee['cellphone']; ?>
			</div>

			<div>
				<strong>SSS Number : </strong>
				<?php echo $employee['sss_no']; ?>
			</div>

			<div>
				<strong>Tin Number : </strong> 
				<?php echo $employee['tin_no']; ?>
			</div>

			<div>
				<strong>Philhealth Number : </strong>
				<?php echo $employee['philhealth_no']; ?>
			</div>

			<div>
				<strong>Pagibig Number : </strong>
				<?php echo $employee['pagibig_no']; ?>
			</div>

			<br />
			<h4 class="sub-header">Emergency Contact</h4>

			<div>
				<strong>Contact Person : </strong>
				<?php echo $employee['em_contact_person']; ?>
			</div>

			<div>
				<strong>Contact Number: </strong>
				<?php echo $employee['em_contact_no']; ?>
			</div>

			<div>
				<strong>Contact Address: </strong>
				<?php echo $employee['em_contact_address']; ?>
			</div>

			<br />
			<h4 class="sub-header">Shift Schedule</h4>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Day</th>
						<th>Time In</th>
						<th>Time Out</th>
					</tr>
				</thead>

				<tbody>
					<?php if (NULL == $schedule): ?>
						<tr>
							<td colspan="4" align="center">
								The shift schedule for this employee is not yet set. Click <a href="<?php echo $ccConfig->get('base_url').'/schedule/change?employeeid='.$employee['empid']; ?>">here</a> to set the schedule.
							</td>
						</tr>
					<?php else: ?>
						<?php foreach ($days as $key => $day): ?>
							<?php if ($schedule[$key.'_time'] == '00:00:00'): ?>
								<?php continue; ?>
							<?php else: ?>
								<?php $time_in = strtotime($schedule[$key.'_time']); ?>
								<?php $time_out = strtotime("+".$schedule[$key.'_num_hours']." hours", $time_in); ?>
								<tr>
									<td><?php echo $days[$key]; ?></td>
									<td><?php echo $hours[date('H:i', $time_in)]; ?></td>
									<td><?php echo $hours[date('H:i', $time_out)]; ?></td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<hr />

			<p>
				<br />
				<br />
				<a type="button" class="btn btn-primary" href="<?php echo $ccConfig->get('base_url').'/employee/update?employee_id='.$employee['empid']; ?>">Update</a>
				<a type="button" class="btn btn-primary" href="<?php echo $ccConfig->get('base_url').'/schedule/change?employeeid='.$employee['empid']; ?>">Change Schedule</a>
				<a type="button" class="btn btn-primary" style="<?php echo ($employee['admin'] == 0) ? '' : 'display:none;'; ?>" id="add_as_admin_btn" href="" onclick="AddAsAdmin('<?php echo $employee['empid']; ?>', this); return false;">Add As Admin</a>
				<a type="button" class="btn btn-primary" style="<?php echo ($employee['admin'] == 1) ? '' : 'display:none;'; ?>" id="remove_as_admin_btn" href="" onclick="RemoveAsAdmin('<?php echo $employee['empid']; ?>', this); return false;">Remove As Admin</a>
				<a type="button" class="btn btn-primary" style="<?php echo ($userIsInFlexiSched) ? 'display:none;' : ''; ?>" id="flexi_sched_btn" href="" onclick="SetAsFlexible('<?php echo $employee['empid']; ?>', this); return false;">Set Schedule As Flexible</a>
				<a type="button" class="btn btn-primary" style="<?php echo ($userIsInFlexiSched) ? '' : 'display:none;'; ?>" id="not_flexi_sched_btn" href="" onclick="SetAsNotFlexible('<?php echo $employee['empid']; ?>', this); return false;">Set Schedule As Not Flexible</a>
				<a type="button" class="btn btn-primary" href="<?php echo $ccConfig->get('base_url').'/employee/search'; ?>">Go Back To Search</a>
			</p>
		</div>
	</div>
</div>

<div id="message_dialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Modal title</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default cancel_btn" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function dialog_message(title, body)
	{
		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog').modal('show');
	}

	function AddAsAdmin(employee_id, btn_obj)
	{
		var url = '<?php echo $ccConfig->get("base_url") ?>/employee/addAsAdmin';
		var data = 'employee_id=' + employee_id;
		var btn_obj_val = $(btn_obj).val();
		$(btn_obj).attr('disabled', true);
		$(btn_obj).attr('value', 'Loading...');
		$.post(url, data, function (json) {
			$(btn_obj).attr('disabled', false);
			$(btn_obj).attr('value', btn_obj_val);
			
			if (json.is_successful)
			{
				$('#add_as_admin_btn').css('display', 'none');
				$('#remove_as_admin_btn').css('display', '');	
			}
			else
			{
				dialog_message('Message', json.message);
			}
		}, 'json');
	}

	function RemoveAsAdmin(employee_id, btn_obj)
	{
		var url = '<?php echo $ccConfig->get("base_url") ?>/employee/removeAsAdmin';
		var data = 'employee_id=' + employee_id;
		var btn_jq_obj = $(btn_obj);
		btn_jq_obj.button('loading');
		$.post(url, data, function (json) {
			btn_jq_obj.button('reset');
			if (json.is_successful)
			{
				$('#add_as_admin_btn').css('display', '');
				$('#remove_as_admin_btn').css('display', 'none');	
			}
			else
			{
				dialog_message('Message', json.message);
			}
		}, 'json');
	}

	function SetAsFlexible(employee_id, btn_obj)
	{
		var url = '<?php echo $ccConfig->get("base_url") ?>/schedule/addToFlexiSched';
		var data = 'employee_id=' + employee_id;
		var btn_jq_obj = $(btn_obj);
		btn_jq_obj.button('loading');
		$.post(url, data, function (json) {
			btn_jq_obj.button('reset');
			if (json.is_successful)
			{
				$('#not_flexi_sched_btn').css('display', '');
				$('#flexi_sched_btn').css('display', 'none');
			}
			else
			{
				dialog_message('Message', json.message);
			}
		}, 'json');
	}

	function SetAsNotFlexible(employee_id, btn_obj)
	{
		var url = '<?php echo $ccConfig->get("base_url") ?>/schedule/removeFromFlexiSched';
		var data = 'employee_id=' + employee_id;
		var btn_jq_obj = $(btn_obj);
		btn_jq_obj.button('loading');
		$.post(url, data, function (json) {
			btn_jq_obj.button('reset');
			if (json.is_successful)
			{
				$('#flexi_sched_btn').css('display', '');
				$('#not_flexi_sched_btn').css('display', 'none');
			}
			else
			{
				dialog_message('Message', json.message);
			}
		}, 'json');
	}
</script>