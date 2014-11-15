<h4>Pending Leaves</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Date</th>
			<th>Type</th>
			<th>Reason</th>
			<th>Is Paid?</th>
		</tr>
	</thead>

	<tbody>
		<?php if (0 < count($pending_leaves)): ?>
			<?php foreach ($pending_leaves as $pending_leave): ?>
				<tr>
					<td><?php echo date('M d, Y', strtotime($pending_leave['date'])); ?></td>
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
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="5" style="text-align:center;">No leaves to pending leaves.</td>
			</tr>		
		<?php endif; ?>
	</tbody>
</table>

<br />
<h4>Approved Leaves</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Date</th>
			<th>Type</th>
			<th>Reason</th>
			<th>Is Paid?</th>
		</tr>
	</thead>

	<tbody>
		<?php if (0 < count($approved_leaves)): ?>
			<?php foreach ($approved_leaves as $approved_leave): ?>
				<tr>
					<td><?php echo date('M d, Y', strtotime($approved_leave['date'])); ?></td>
					<td>
						<?php if ($approved_leave['leave_type'] == 'sick'): ?>
							Sick
						<?php elseif ($approved_leave['leave_type'] == 'vacation'): ?>
							Vacation
						<?php endif; ?>
					</td>
					<td><?php echo $approved_leave['reason']; ?></td>
					<td>
						<?php if ($approved_leave['is_paid'] == 1): ?>
							&#x2713;
						<?php else: ?>
							&#x2717;
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="5" style="text-align:center;">No leaves to approved leaves.</td>
			</tr>		
		<?php endif; ?>
	</tbody>
</table>

<br />
<h4>Denied Leaves</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Date</th>
			<th>Type</th>
			<th>Reason</th>
			<th>Is Paid?</th>
		</tr>
	</thead>

	<tbody>
		<?php if (0 < count($denied_leaves)): ?>
			<?php foreach ($denied_leaves as $denied_leave): ?>
				<tr>
					<td><?php echo date('M d, Y', strtotime($denied_leave['date'])); ?></td>
					<td>
						<?php if ($denied_leave['leave_type'] == 'sick'): ?>
							Sick
						<?php elseif ($denied_leave['leave_type'] == 'vacation'): ?>
							Vacation
						<?php endif; ?>
					</td>
					<td><?php echo $denied_leave['reason']; ?></td>
					<td>
						<?php if ($denied_leave['is_paid'] == 1): ?>
							&#x2713;
						<?php else: ?>
							&#x2717;
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="5" style="text-align:center;">No leaves to denied leaves.</td>
			</tr>		
		<?php endif; ?>
	</tbody>
</table>