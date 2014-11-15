<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'leave'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h2 class="page-header">
				<div class="nav navbar-nav navbar-right">
					<a href="<?php echo $ccConfig->get('base_url').'/leave'; ?>" class="btn btn-primary">Go Back To Leaves</a>
				</div>

				Employee Leaves
			</h2>

			<form method="post" action="" onsubmit="ShowEmployeeLeavesOf(this); return false;" >
				<div class="alert alert-info" role="alert">
					Select the employee then click show to show all employee leaves for year <?php echo date('Y'); ?>.
				</div>

				<div class="form-group">
					<label>Employee : </label>
					<select name="employee_id" class="form-control" style="display:inline;width:400px;" required="required" >
						<option value="">Select Employee</option>
						<?php foreach ($employees as $employee): ?>
							<option value="<?php echo $employee['empid']; ?>" ><?php echo $employee['lastname'].', '.$employee['firstname']; ?></option>
						<?php endforeach; ?>
					</select>

					<input type="submit" name="Submit" value="Show" class="btn btn-primary" />
				</div>
			</form>

			<hr />

			<div id="results_container">
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function ShowEmployeeLeavesOf(form)
	{
		var url = '<?php echo $ccConfig->get("base_url")."/leave/employeeLeavesOf"; ?>';

		$.post(url, $(form).serialize(), function(html) {
			$('#results_container').html(html);
		});
	}
</script>