<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'holiday'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div class="nav navbar-nav navbar-right">
					<div class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="font-size:15px; color:#777;">Actions <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo $ccConfig->get('base_url').'/holiday/add'; ?>">Add Holiday</a></li>
							<li><a href="<?php echo $ccConfig->get('base_url').'/holiday/addHolidayPay'; ?>">Add Employee Holiday Pay</a></li>
						</ul>
					</div>
				</div>

				Holidays
			</h1>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Weekday</th>
						<th>Date</th>
						<th>Holiday Name</th>
						<th>Actions</th>
					</tr>
				</thead>

				<tbody id="">
					<?php if (0 < count($holidays)): ?>
						<?php foreach ($holidays as $holiday): ?>
							<tr>
								<td><?php echo date('l', strtotime($holiday['date'])); ?></td>
								<td><?php echo date('M d, Y', strtotime($holiday['date'])); ?></td>
								<td><?php echo $holiday['name']; ?></td>
								<td><a href="" onclick="DeleteHoliday('<?php echo $holiday['id']; ?>');">Delete</a></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="4">No holidays yet.</td>
						</tr>		
					<?php endif; ?>
				</tbody>
			</table>
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

	function DeleteHoliday(leave_id, html_id, btn)
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