<h4>
	<div class="nav navbar-nav navbar-right">
		<button type="button" class="btn btn-primary" onclick="LoadLeavesInMonth('<?php echo date("Y-m-d", strtotime("-1month", $dTime)); ?>');"><<</button>
		<button type="button" class="btn btn-primary" onclick="LoadLeavesInMonth('<?php echo date("Y-m-15"); ?>');">*</button>
		<button type="button" class="btn btn-primary" onclick="LoadLeavesInMonth('<?php echo date("Y-m-d", strtotime("+1month", $dTime)); ?>');">>></button>
	</div>

	<?php echo date('M Y', $dTime); ?>
</h4>
<table class="table table-striped">
	<thead>
		<tr>
			<th>Date</th>
			<th>Lastname</th>
			<th>Firstname</th>
			<th>Type</th>
			<th>Reason</th>
			<th>Actions</th>
		</tr>
	</thead>

	<tbody>
		<?php if (0 < count($leaves)): ?>
			<?php foreach ($leaves as $leave): ?>
				<tr id="month_leaves_<?php echo $leave['id']; ?>">
					<td><?php echo date('M d, Y', strtotime($leave['date'])); ?></td>
					<td><?php echo $leave['lastname']; ?></td>
					<td><?php echo $leave['firstname']; ?></td>
					<td>
						<?php if ($leave['leave_type'] == 'sick'): ?>
							Sick
						<?php elseif ($leave['leave_type'] == 'vacation'): ?>
							Vacation
						<?php endif; ?>
					</td>
					<td><?php echo $leave['reason']; ?></td>
					<td><a onclick="DeleteLeave('month_leaves_<?php echo $leave['id']; ?>', '<?php echo $leave['id']; ?>');" href="javascript:void(0);">Delete</a></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="5" style="text-align:center;">No entry</td>
			</tr>		
		<?php endif; ?>
	</tbody>
</table>