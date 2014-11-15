<?php 
	include_template('default/_navigation', array(
		'is_admin'      => $is_admin,
		'active'        => 'leave'
	)); 
?>

<div class="container">
	<!-- Pending leaves -->
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">
				<div style="float:right;">
					<a class="btn btn-xs btn-primary" href="<?php echo $ccConfig->get('base_url').'/leave/apply'; ?>" >Apply Leave</a>
				</div>

				Pending Leaves
			</h3>
		</div>

		<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Leave Type</th>
						<th>Reason</th>
						<th>Paid?</th>
						<th>Actions</th>
					</tr>
				</thead>

				<?php if (0 == count($pendingLeaves)): ?>
					<tbody>
						<tr>
							<td colspan="5" style="text-align:center;">
								No pending leaves. Apply for leave <a href="<?php echo $ccConfig->get('base_url').'/leave/apply'; ?>">here</a>.
							</td>
						</tr>
					</tbody>
				<?php else: ?>
					<tbody>
						<?php foreach ($pendingLeaves as $pendingLeave): ?>
							<tr id="pending_leave_<?php echo $pendingLeave["id"] ?>">
								<td><?php echo date('F j, Y', strtotime($pendingLeave['date'])); ?></td>
								<td>
									<?php if ($pendingLeave['leave_type'] == 'sick'): ?>
										Sick
									<?php elseif ($pendingLeave['leave_type'] == 'vacation'): ?>
										Vacation
									<?php endif; ?>
								</td>
								<td>
									<?php echo $pendingLeave['reason']; ?>
								</td>
								<td>
									<?php if ($pendingLeave['is_paid'] == 1): ?>
										&#x2713;
									<?php else: ?>
										&#x2717;
									<?php endif; ?>
								</td>
								<td>
									<a href="" class="btn btn-primary" onclick="CancelLeave('<?php echo $pendingLeave["id"] ?>', 'pending_leave_<?php echo $pendingLeave["id"] ?>', this); return false;">Cancel</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>
		</div>
	</div>

	<!-- Upcoming Approved leaves -->
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">
				Upcoming Approved Leaves
			</h3>
		</div>

		<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Leave Type</th>
						<th>Reason</th>
						<th>Paid?</th>
					</tr>
				</thead>

				<?php if (0 == count($upcomingApprovedLeaves)): ?>
					<tbody>
						<tr>
							<td colspan="5" style="text-align:center;">
								No upcoming leaves.
							</td>
						</tr>
					</tbody>
				<?php else: ?>
					<tbody>
						<?php foreach ($upcomingApprovedLeaves as $upcomingApprovedLeave): ?>
							<tr>
								<td><?php echo date('F j, Y', strtotime($upcomingApprovedLeave['date'])); ?></td>
								<td>
									<?php if ($upcomingApprovedLeave['leave_type'] == 'sick'): ?>
										Sick
									<?php elseif ($upcomingApprovedLeave['leave_type'] == 'vacation'): ?>
										Vacation
									<?php endif; ?>
								</td>
								<td>
									<?php echo $upcomingApprovedLeave['reason']; ?>
								</td>
								<td>
									<?php if ($upcomingApprovedLeave['is_paid'] == 1): ?>
										&#x2713;
									<?php else: ?>
										&#x2717;
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
			</table>
		</div>
	</div>

	<!-- Old Approved leaves -->
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">
				Approved Leaves History (<?php echo date('Y'); ?>)
			</h3>
		</div>

		<div class="panel-body">
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Leave Type</th>
						<th>Reason</th>
						<th>Paid?</th>
					</tr>
				</thead>

				<?php if (0 == count($currentYearApprovedLeaves)): ?>
					<tbody>
						<tr>
							<td colspan="5" style="text-align:center;">
								No leaves.
							</td>
						</tr>
					</tbody>
				<?php else: ?>
					<tbody>
						<?php foreach ($currentYearApprovedLeaves as $currentYearApprovedLeave): ?>
							<tr>
								<td><?php echo date('F j, Y', strtotime($currentYearApprovedLeave['date'])); ?></td>
								<td>
									<?php if ($currentYearApprovedLeave['leave_type'] == 'sick'): ?>
										Sick
									<?php elseif ($currentYearApprovedLeave['leave_type'] == 'vacation'): ?>
										Vacation
									<?php endif; ?>
								</td>
								<td>
									<?php echo $currentYearApprovedLeave['reason']; ?>
								</td>
								<td>
									<?php if ($currentYearApprovedLeave['is_paid'] == 1): ?>
										&#x2713;
									<?php else: ?>
										&#x2717;
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				<?php endif; ?>
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
	function dialog_alert(title, body)
	{
		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog .ok_btn').html('OK');
		$('#message_dialog .ok_btn').click(function() {
			$('#message_dialog .ok_btn').unbind('click');
			$('#message_dialog').modal('hide');
		});
		$('#message_dialog .cancel_btn').css('display', 'none');
		$('#message_dialog').modal('show');
	}

	function dialog_confirm(title, body, buttons)
	{
		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog .ok_btn').html(buttons.ok_label);
		$('#message_dialog .ok_btn').click(function() {
			$('#message_dialog .ok_btn').unbind('click');
			$('#message_dialog').modal('hide');

			setTimeout(function() {
				buttons.callback();
			}, 1000);
		});
		$('#message_dialog .cancel_btn').css('display', 'inline');
		$('#message_dialog .cancel_btn').html(buttons.cancel_label);
		$('#message_dialog').modal('show');
	}

	function CancelLeave(leave_id, html_id, btn)
	{
		var c = function()
		{
			$(btn).button('loading');

			var url = '<?php echo $ccConfig->get("base_url")."/leave/delete"; ?>';
			$.post(url, 'leave_id=' + leave_id, function(json) {
				$(btn).button('reset');

				if (json.success)
				{
					$('#' + html_id).fadeOut("slow");
				}
				else
				{
					dialog_alert('Error Message', json.message);
				}
			}, 'json');
		};

		var btns =
		{
			'ok_label'     : 'OK',
			'cancel_label' : 'Cancel',
			'callback'     : c
		};

		dialog_confirm('Confirm Delete?', 'Are you sure you want to cancel the leave application?', btns);
	}
</script>