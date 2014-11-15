<table class="table table-bordered" style="width:<?php echo 800 + (count($data_days) * 250);?>px;">
	<thead>
		<tr>
			<th colspan="5" width="500px" style="text-align:center;">ATTENDANCE</th>

			<?php foreach ($data_days as $data_day): ?>
				<th colspan="4" width="250px" style="text-align:center;">
					<?php echo date('d-M-y (D)', strtotime($data_day)); ?>
				</th>
			<?php endforeach; ?>
		</tr>
		<tr>
			<th>Hire Date</th>
			<th>Lastname</th>
			<th>Firstname</th>
			<th>Days</th>
			<th>Sched</th>

			<?php foreach ($data_days as $data_day): ?>
				<th>In</th>
				<th>Out</th>
				<th>Total</th>
				<th>Note</th>
			<?php endforeach; ?>
		</tr>
	</thead>

	<tbody>
		<?php foreach ($data as $employee): ?>
			<tr>
				<td><?php echo date('d-M-y', strtotime($employee['date_hired'])); ?></td>
				<td><?php echo $employee['lastname']; ?></td>
				<td><?php echo $employee['firstname']; ?></td>
				<td><?php echo $employee['sched']['days']; ?></td>
				<td><?php echo $employee['sched']['time']; ?></td>

				<?php foreach ($data_days as $data_day): ?>
					<td>
						<?php if ($employee['dates'][$data_day]['status'] == 'A' || $employee['dates'][$data_day]['status'] == 'RD'): ?>
							<span style="color:<?php echo html_color_for_attendance_status($employee['dates'][$data_day]['status']); ?>;">
								<?php echo $employee['dates'][$data_day]['time_in']; ?>
							</span>
						<?php else: ?>
							<?php echo $employee['dates'][$data_day]['time_in']; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($employee['dates'][$data_day]['status'] == 'A' || $employee['dates'][$data_day]['status'] == 'RD'): ?>
							<span style="color:<?php echo html_color_for_attendance_status($employee['dates'][$data_day]['status']); ?>;">
								<?php echo $employee['dates'][$data_day]['time_out']; ?>
							</span>
						<?php else: ?>
							<?php echo $employee['dates'][$data_day]['time_out']; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($employee['dates'][$data_day]['status'] != 'PR'): ?>
							<span style="color:<?php echo html_color_for_attendance_status($employee['dates'][$data_day]['status']); ?>;">
								<?php echo $employee['dates'][$data_day]['total']; ?>
							</span>
						<?php else: ?>
							<?php echo $employee['dates'][$data_day]['total']; ?>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($employee['dates'][$data_day]['status'] == 'PR'): ?>
							&#x2713;
						<?php elseif ($employee['dates'][$data_day]['status'] == 'L'): ?>
							<span style="color:<?php echo html_color_for_attendance_status('L'); ?>;">
								-<?php echo $employee['dates'][$data_day]['status_val']; ?>hr
							</span>
						<?php else: ?>
							<span style="color:<?php echo html_color_for_attendance_status($employee['dates'][$data_day]['status']); ?>;">
								<?php echo $employee['dates'][$data_day]['status']; ?>
							</span>
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>