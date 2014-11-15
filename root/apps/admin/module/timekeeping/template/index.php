<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'timekeeping'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div class="nav navbar-nav navbar-right">
					<div class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" style="font-size:15px; color:#777;">Actions <span class="caret"></span></a>
						<ul class="dropdown-menu" role="menu">
							<li><a href="<?php echo $ccConfig->get('base_url').'/timekeeping/viewEmployeeTimesheet'; ?>">View Employee Timesheet</a></li>
							<li><a href="<?php echo $ccConfig->get('base_url').'/timekeeping/changeTimesheet'; ?>">Change/Add Timesheet</a></li>
							<li><a href="<?php echo $ccConfig->get('base_url').'/schedule/changeScheduleForSpecificDate'; ?>">Change Schedule For Specific Date</a></li>
							<li><a href="<?php echo $ccConfig->get('base_url').'/timekeeping/generate'; ?>">Generate Report</a></li>
						</ul>
					</div>
				</div>

				Today's Attendance
			</h1>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Lastname</th>
						<th>Firstname</th>
						<th>Days</th>
						<th>Sched</th>
						<th>In</th>
						<th>Out</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($data as $employee): ?>
						<tr>
							<td><?php echo $employee['lastname']; ?></td>
							<td><?php echo $employee['firstname']; ?></td>
							<td><?php echo $employee['sched']['days']; ?></td>
							<td><?php echo $employee['sched']['time']; ?></td>
							<td><?php echo $employee['timesheet']['time_in']; ?></td>
							<td><?php echo $employee['timesheet']['time_out']; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>