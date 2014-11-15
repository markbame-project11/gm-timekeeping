<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'overtime'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div class="nav navbar-nav navbar-right">
					<div class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="font-size:15px; color:#777;">Actions <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo $ccConfig->get('base_url').'/overtime/add'; ?>">Add Overtime</a></li>
						</ul>
					</div>
				</div>

				Overtimes(<?php echo date('F'); ?>)
			</h1>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Date</th>
						<th>Lastname</th>
						<th>Firstname</th>
						<th>Notes</th>
						<th>Actions</th>
					</tr>
				</thead>

				<tbody>
					<?php if (0 < count($overtimes)): ?>
						<?php foreach ($overtimes as $overtime): ?>
							<tr>
								<td><?php echo $overtime['date']; ?></td>
								<td><?php echo $overtime['lastname']; ?></td>
								<td><?php echo $overtime['firstname']; ?></td>
								<td><?php echo $overtime['notes']; ?></td>
								<td></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr>
							<td colspan="5" style="text-align:center;">No entry</td>
						</tr>		
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	function DeleteOvertime(html_id, leave_id)
	{
		var url = '<?php echo $ccConfig->get("base_url")."/leave/delete"; ?>';
		var params = 'leave_id=' + leave_id;
		$.post(url, params, function(obj) {
			$('#' + html_id).remove();
		}, 'json');
	}
</script>