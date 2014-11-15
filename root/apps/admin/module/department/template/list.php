<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'department'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div style="float:right;">
					<a type="button" class="btn btn-primary" href="<?php echo $ccConfig->get('base_url').'/department/create'; ?>">Add Department</a>
				</div>
				Departments
			</h1>

			<table class="table table-striped">
				<thead>
					<tr>
						<th>Department Name</th>
						<th style="text-align:center;">Actions</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($departments as $key => $department): ?>
						<tr <?php echo (($key % 2) == 1) ? 'bgcolor="#EBEBEB"' : ''; ?> >
							<td><?php echo $department['deptname']; ?></td>
							<td align="center">
								<a href="<?php echo $ccConfig->get('base_url').'/department/view'; ?>?deptid=<?php echo $department['deptid']; ?>">View</a> |
								<a href="<?php echo $ccConfig->get('base_url').'/department/edit'; ?>?deptid=<?php echo $department['deptid']; ?>">Edit</a> |
								<a href="<?php echo $ccConfig->get('base_url').'/department/delete'; ?>?deptid=<?php echo $department['deptid']; ?>">Delete</a>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>