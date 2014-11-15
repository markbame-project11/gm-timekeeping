<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h3>Timesheet of <?php echo $employee['firstname'].' '.$employee['lastname']; ?></h3>

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

			<br />
			<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/timekeeping/viewEmployeeTimesheet'; ?>" >Search Another</a>
		</div>
	</div>
</div>