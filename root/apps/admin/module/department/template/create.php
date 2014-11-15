<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'department'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">

			<form method="post" action="<?php echo $ccConfig->get('base_url').'/department/create'; ?>" role="form" clas="form-horizontal">
				<fieldset style="width: 800px;">
					<legend>Enter New Department</legend>

					<?php
						include_template(
							'department/_form',
							array(
								'deptname'         => '',
								'deptdesc'         => ''
							)
						); 
					?>

					<div class="form-actions">
						<input type="submit" name="Submit" value="Create Department" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/department/list'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>