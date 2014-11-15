<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'leave'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div class="nav navbar-nav navbar-right">
					<div class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="font-size:15px; color:#777;">Actions <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a class="" href="<?php echo $ccConfig->get('base_url').'/leave/viewEmployeeLeaves'; ?>">Show Employee Leaves</a></li>
						</ul>
					</div>
				</div>

				Leaves
			</h1>

			<h4>For Approval</h4>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Name</th>
						<th>Type</th>
						<th>Reason</th>
						<th>Paid?</th>
						<th>Actions</th>
					</tr>
				</thead>

				<tbody>
					<?php if (0 < count($pending_leaves)): ?>
						<?php foreach ($pending_leaves as $pending_leave): ?>
							<tr>
								<td><?php echo date('M d, Y', strtotime($pending_leave['date'])); ?></td>
								<td><?php echo $pending_leave['firstname'].' '.$pending_leave['lastname']; ?></td>
								<td>
									<?php if ($pending_leave['leave_type'] == 'sick'): ?>
										Sick
									<?php elseif ($pending_leave['leave_type'] == 'vacation'): ?>
										Vacation
									<?php endif; ?>
								</td>
								<td><?php echo $pending_leave['reason']; ?></td>
								<td>
									<?php if ($pending_leave['is_paid'] == 1): ?>
										&#x2713;
									<?php else: ?>
										&#x2717;
									<?php endif; ?>
								</td>
								<td>
									<a class="btn btn-primary" href="" onclick="DenyLeave('<?php echo $pending_leave['id']; ?>'); return false;">Deny</a>
									<a class="btn btn-primary" href="" onclick="ApproveLeave('<?php echo $pending_leave['id']; ?>'); return false;">Approve</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5" style="text-align:center;">No leaves to approve/deny.</td>
						</tr>		
					<?php endif; ?>
				</tbody>
			</table>

			<br />
			<div id="leaves_container">
				<?php 
					include_template('leave/_leaves_in_month', array(
						'leaves'     => $incoming_leaves,
						'date'       => $date,
						'dTime'      => strtotime($date)
					));
				?>
			</div>
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
	function LoadLeavesInMonth(date)
	{
		var url = '<?php echo $ccConfig->get("base_url")."/leave/getLeavesInMonth"; ?>';
		var params = 'date=' + date;
		$.post(url, params, function(response) {
			$('#leaves_container').html(response);
		}, 'html');
	}

	function DenyLeave(leave_id)
	{
		var c = function()
		{
			var url = '<?php echo $ccConfig->get("base_url")."/leave/deny?leave_id="; ?>' + leave_id;
			$.getJSON(url, function(json) {
				if (json.success)
				{
					window.location.href = '<?php echo $ccConfig->get("base_url")."/leave"; ?>';
				}
				else
				{
					dialog_alert('Error Message', json.message);
				}
			});
		};

		var btns =
		{
			'ok_label'     : 'OK',
			'cancel_label' : 'Cancel',
			'callback'     : c
		};

		dialog_confirm('Confirm Deny?', 'Are you sure you want to deny the leave application?', btns);
	}

	function ApproveLeave(leave_id)
	{
		var c = function()
		{
			var url = '<?php echo $ccConfig->get("base_url")."/leave/approve?leave_id="; ?>' + leave_id;
			$.getJSON(url, function(json) {
				if (json.success)
				{
					window.location.href = '<?php echo $ccConfig->get("base_url")."/leave"; ?>';
				}
				else
				{
					dialog_alert('Error Message', json.message);
				}
			});
		};

		var btns =
		{
			'ok_label'     : 'OK',
			'cancel_label' : 'Cancel',
			'callback'     : c
		};

		dialog_confirm('Confirm Approve?', 'Are you sure you want to approve the leave application?', btns);
	}

	function DeleteLeave(html_id, leave_id)
	{ 

		var url = '<?php echo $ccConfig->get("base_url")."/leave/delete"; ?>';
		var params = 'leave_id=' + leave_id;
		$.post(url, params, function(obj) {
			$('#' + html_id).remove();
		}, 'json');
	}

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
			$('#message_dialog').modal('hide');
			$('#message_dialog .ok_btn').unbind('click');
			// add set timeout because issue occurs when you call dialog alert in callback
			setTimeout(
				function() {
					buttons.callback();
				}, 
				1000
			);
		});
		$('#message_dialog .cancel_btn').css('display', 'inline');
		$('#message_dialog .cancel_btn').html(buttons.cancel_label);
		$('#message_dialog').modal('show');
	}
</script>