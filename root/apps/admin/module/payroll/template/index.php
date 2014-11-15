<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'payroll'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div style="float:right;">
					<a type="button" class="btn btn-primary" href="<?php echo $ccConfig->get('base_url').'/payroll/generate'; ?>">Generate Payroll</a>
				</div>
				Payroll History
			</h1>

			<table class="table table-striped">
				<thead>
					<tr>
						<th >Payroll Date</th>
						<th >Start Date</th>
						<th >End Date</th>
						<th >Actions</th>
					</tr>
				</thead>

				<tbody>
					<?php foreach ($payrolls as $payroll): ?>
						<tr>
							<td >
								<?php echo date('M d, Y', strtotime($payroll['payroll_date'])); ?>
							</td>
							<td >
								<?php echo date('M d, Y', strtotime($payroll['start_date'])); ?>
							</td>
							<td >
								<?php echo date('M d, Y', strtotime($payroll['end_date'])); ?>
							</td>
							<td >
								<a class="btn btn-primary" href='<?php echo $ccConfig->get("base_url")."/payroll/view?id=".$payroll['id']; ?>' >
									View
								</a>

								<?php if (trim($payroll['file_url']) != ''): ?>
									<a class="btn btn-primary" href='<?php echo $ccConfig->get("assets_base_url").$payroll['file_url']; ?>' >
										Download Spreadsheet
									</a> 
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>