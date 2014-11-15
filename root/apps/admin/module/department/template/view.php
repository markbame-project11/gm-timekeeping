<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'department'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">View Department</h1>

			<div>
				<strong>Department Name : </strong> 
				<a href="<?php echo $ccConfig->get('base_url').'/department/view?deptid='.$department['deptid']; ?>">
					<?php echo $department['deptname']; ?>
				</a>
			</div>

			<div>
				<strong>Description : </strong> 
				<?php echo $department['deptdesc']; ?>
			</div>

			<br />
			<br />
			<h2 style="font-size: 23px;" class="sub-header">Employees</h2>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Last Name</th>
						<th>First Name</th>
						<th>Email</th>
						<th>Action</th>
					</tr>
				</thead>

				<tbody>
					<?php if (0 == count($employees)): ?>
						<tr>
							<td colspan="4" align="center">No entry found</td>
						</tr>
					<?php else: ?>
						<?php foreach ($employees as $i => $employee): ?>
							<tr >
								<td><?php echo $employee['lastname']; ?></td>
								<td><?php echo $employee['firstname']; ?></td>
								<td><?php echo $employee['email']; ?></td>
								<td>
									<a href="<?php echo $ccConfig->get('base_url').'/employee/view?employeeid='.$employee['empid']; ?>">View Details</a> |
									<a href="<?php echo $ccConfig->get('base_url').'/schedule/change?employeeid='.$employee['empid']; ?>">Change Schedule</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>