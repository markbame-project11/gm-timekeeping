<?php 
	include_template('default/_navigation', array(
		'is_admin'      => $is_admin,
		'active'        => 'home'
	)); 

	//var_dump($showCheckin);
?>

<div class="container">
	<h3>Hello <?php echo $employee['firstname'].' '.$employee['lastname']; ?>!</h3>

	<hr />

	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">
				<?php 
				  //echo $_SESSION['empcheckin'] . ' ' . $_SESSION['empcheckout'];

				?>


				<?php if ($showCheckin): ?>
					<div style="float:right;">
						<a class="btn btn-xs btn-primary" href="" onclick="checkin(); return false;" >Time In</a>
					</div>
				<?php else: 
                    /*

                       this should be disabled when information is stored in database

                    */
                       //echo $_SESSION['tmesheet_checkout'];
                       //echo $_SESSION['tmesheet_checkout'];
                       $disabled_btn = "";
                       // not required as of this moment
                       //!!if($_SESSION['tmesheet_checkout'] == '1') $disabled_btn = "DISABLED";
                       //echo $disabled_btn;

				?>

					<div style="float:right;">
						<a <?php echo $disabled_btn; ?> class="btn btn-xs btn-primary" href="" onclick="checkout(); return false;">Time Out</a>
					</div>

				<?php endif; ?>

				Your Timesheet(Past 15 days)
			</h3>
		</div>

		<div class="panel-body">
			<div class="alert alert-success" role="alert">
				Days: [<?php echo $data['sched']['days']; ?>], &nbsp; &nbsp; Time: [<?php echo $data['sched']['time']; ?>]
			</div>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Time In</th>
						<th>Time Out</th>
						<th>Total</th>
						<th>Notes</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($data['timesheets'] as $date => $timesheet): ?>
						<tr>
							<td><?php echo date('F j, Y', strtotime($date)); ?></td>
							<td>
								<?php if ($timesheet['status'] == 'A' || $timesheet['status'] == 'RD'): ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status($timesheet['status']); ?>;">
										<?php echo $timesheet['time_in']; ?>
									</span>
								<?php else: ?>
									<?php echo $timesheet['time_in']; ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($timesheet['status'] == 'A' || $timesheet['status'] == 'RD'): ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status($timesheet['status']); ?>;">
										<?php echo $timesheet['time_out']; ?>
									</span>
								<?php else: ?>
									<?php echo $timesheet['time_out']; ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($timesheet['status'] != 'PR'): ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status($timesheet['status']); ?>;">
										<?php echo $timesheet['total']; ?>
									</span>
								<?php else: ?>
									<?php echo $timesheet['total']; ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($timesheet['status'] == 'PR'): ?>
									&#x2713;
								<?php elseif ($timesheet['status'] == 'L'): ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status('L'); ?>;">
										-<?php echo $timesheet['status_val']; ?>hr
									</span>
								<?php else: ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status($timesheet['status']); ?>;">
										<?php echo $timesheet['status']; ?>
									</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>

	<br />
	<br />
	
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Department Timesheet(Today)</h3>
		</div>

		<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Last Name</th>
						<th>First Name</th>
						<th>Days</th>
						<th>Sched</th>
						<th>Time In</th>
						<th>Time Out</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($department_data as $employee_id => $employee): ?>
						<tr>
							<td><?php echo $employee['lastname']; ?></td>
							<td><?php echo $employee['firstname']; ?></td>
							<td><?php echo $employee['sched']['days']; ?></td>
							<td><?php echo $employee['sched']['time']; ?></td>
							<td>
								<?php if ($employee['timesheet']['status'] == 'RD'): ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status($timesheet['status']); ?>;">
										<?php echo $employee['timesheet']['time_in']; ?>
									</span>
								<?php else: ?>
									<?php echo $employee['timesheet']['time_in']; ?>
								<?php endif; ?>
							</td>
							<td>
								<?php if ($employee['timesheet']['status'] == 'RD'): ?>
									<span style="font-weight:bold;color:<?php echo html_color_for_attendance_status($timesheet['status']); ?>;">
										<?php echo $employee['timesheet']['time_out']; ?>
									</span>
								<?php else: ?>
									<?php echo $employee['timesheet']['time_out']; ?>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
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
				<button type="button" class="btn btn-default cancel_btn" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary ok_btn">OK</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function dialog_confirm(title, body, buttons)
	{
		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog .ok_btn').html(buttons.ok_label);
		$('#message_dialog .ok_btn').click(function() {
			$('#message_dialog .ok_btn').unbind('click');
			$('#message_dialog').modal('hide');
			buttons.callback();
		});
		$('#message_dialog .cancel_btn').html(buttons.cancel_label);

		$('#message_dialog').modal('show');
	}

	function checkout()
	{
		var btns = {
			'ok_label' : 'OK',
			'cancel_label' : 'Cancel',
			'callback' : function() {
				var url = '<?php echo $ccConfig->get("base_url")."/bundyclock/checkout"; ?>';
				$.getJSON(url, function(json) {
					if (json.is_successful)
					{
						window.location.href = '<?php echo $ccConfig->get("base_url"); ?>';
					}
					else
					{
						alert(json.message);
					}
				});
			} 
		};

		dialog_confirm('Confirm Checkout', 'Are you sure you want to checkout?', btns);
	}

	function checkin()
	{
		var btns = {
			'ok_label' : 'OK',
			'cancel_label' : 'Cancel',
			'callback' : function() {
				var url = '<?php echo $ccConfig->get("base_url")."/bundyclock/checkin"; ?>';
				$.getJSON(url, function(json) {
					if (json.is_successful)
					{
						window.location.href = '<?php echo $ccConfig->get("base_url"); ?>';
					}
					else
					{
						alert(json.message);
					}
				});
			} 
		};

		dialog_confirm('Confirm Checkin', 'Are you sure you want to checkin?', btns);
	}
</script>