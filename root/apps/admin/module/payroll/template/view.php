<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'payroll'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h2 class="page-header">
				Payroll(<?php echo date('M j, Y', strtotime($payroll['payroll_date'])); ?>)
			</h2>

			<div id="accordion">
				<?php foreach ($employeePayrolls as $employeePayroll): ?>
					<h3>
						<a href=""><?php echo $employeePayroll['lastname'].', '.$employeePayroll['firstname']; ?></a>
					</h3>
					<div class="clearfix">
						<table class="table table-bordered">
							<thead>
								<tr>
									<th colspan="3" style="font-weight:bold;text-align:center;">DEDUCTIONS</th>
								</tr>
								<tr>
									<th style="text-align:center;">SSS</th>
									<th style="text-align:center;">Philhealth</th>
									<th style="text-align:center;">Pagibig</th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td style="text-align:center;">
										<?php if ($employeePayroll['has_sss_deduction'] == 1): ?>
											&#x2713;
										<?php else: ?>
											&#x2717;
										<?php endif; ?>
									</td>
									<td style="text-align:center;">
										<?php if ($employeePayroll['has_philhealth_deduction'] == 1): ?>
											&#x2713;
										<?php else: ?>
											&#x2717;
										<?php endif; ?>
									</td>
									<td style="text-align:center;">
										<?php if ($employeePayroll['has_pagibig_deduction'] == 1): ?>
											&#x2713;
										<?php else: ?>
											&#x2717;
										<?php endif; ?>
									</td>
								</tr>
							</tbody>
						</table>

						<table class="table table-bordered">
							<thead>
								<tr>
									<th colspan="3" style="font-weight:bold;text-align:center;">ADDITIONS</th>
								</tr>
								<tr>
									<th style="text-align:center;">Description</th>
									<th style="text-align:center;">Amount</th>
									<th style="text-align:center;">Actions</th>
								</tr>
							</thead>

							<tbody>
								<tr>
									<td style="text-align:center;">
									</td>
									<td style="text-align:center;">
									</td>
									<td style="text-align:center;">
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				<?php endforeach; ?>
			</div>

			<br />
			<div class="form-actions" style="text-align:center;">
				<input
					id="btn_save"
					type="submit" name="Submit"
					class="btn btn-primary"
					style="width:30%;"
					onclick="SaveBtn(this);"
					
					<?php if (trim($payroll['script_is_running']) == 1): ?>
						disabled="disabled"
						value="Generating spreadsheet..." 
					<?php else: ?>
						value="Generate spreadsheet" 
					<?php endif; ?>
				/>
				<input
					id="btn_download"
					type="button" value="Download Spreadsheet" 
					class="btn btn-primary" style="width:30%;"
					<?php if (trim($payroll['file_url']) == ''): ?>
						disabled="disabled"
					<?php else: ?>
						onclick="window.location.href='<?php echo $ccConfig->get("assets_base_url").$payroll['file_url']; ?>';"
					<?php endif; ?>
				/>
				<a class="btn btn-default span-8" href="<?php echo $ccConfig->get('base_url').'/payroll'; ?>" style="width:30%;">
					Cancel
				</a>
			</div>
		</div>
	</div>
</div>

<div id="message_dialog" class="modal fade">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Modal title</h4>
			</div>
			<div class="modal-body"></div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default cancel_btn" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary ok_btn">OK</button>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	function dialog_alert(title, body)
	{
		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog .ok_btn').html('OK');
		$('#message_dialog .ok_btn').click(function() {
			$('#message_dialog .ok_btn').unbind('click');
			$('#message_dialog').modal('hide');
		});
		$('#message_dialog .cancel_btn').css('display', 'none');
		$('#message_dialog').modal('show');
	}

	function dialog_confirm(title, body, buttons)
	{
		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog .ok_btn').html(buttons.ok_label);
		$('#message_dialog .ok_btn').click(function() {
			$('#message_dialog .ok_btn').unbind('click');
			$('#message_dialog').modal('hide');

			setTimeout(function() {
				buttons.callback();
			}, 1000);
		});
		$('#message_dialog .cancel_btn').css('display', 'inline');
		$('#message_dialog .cancel_btn').html(buttons.cancel_label);
		$('#message_dialog').modal('show');
	}

	jQuery('#accordion').accordion();

	function CheckSpreadsheetRunning()
	{
		var url = '<?php echo $ccConfig->get("base_url")."/payroll/checkSpreadsheetGeneration?id=".$payroll["id"]; ?>';
		jQuery.getJSON(url, function(json){
			if (json.is_running)
			{
				jQuery('#btn_save').attr('value', 'Generating spreadsheet...');
				jQuery('#btn_save').attr('disabled', 'disabled');

				setTimeout(function() {
					CheckSpreadsheetRunning();
				}, 10000);
			}
			else
			{
				jQuery('#btn_save').attr('value', 'Generate spreadsheet');
				jQuery('#btn_save').attr('disabled', false);

				if (json.has_reached_timeout)
				{
					dialog_alert('Timeout Error', 'Generating spreadsheet fails. Please click Generate spreadsheet to start again.');
				}
				else
				{
					jQuery('#btn_download').attr('value', 'Download Spreadsheet');
					jQuery('#btn_download').attr('disabled', false);
					jQuery('#btn_download').attr('onclick', '');
					jQuery('#btn_download').unbind('click');
					jQuery('#btn_download').click(function(){
						var url = '<?php echo $ccConfig->get("assets_base_url"); ?>';
						url += json.download_url;
						window.location.href = url;
					});
				}
			}
		});
	}

	function SaveBtn(btn)
	{
		jQuery(btn).attr('value', 'Generating spreadsheet...');
		jQuery(btn).attr('disabled', 'disabled');
				
		var url = '<?php echo $ccConfig->get("base_url")."/payroll/runGenerateSpreadsheet?id=".$payroll["id"]; ?>';
		jQuery.getJSON(url, function(json){
			if (json.is_successful)
			{
				setTimeout(function() {
					CheckSpreadsheetRunning();
				}, 10000);
			}
		});
	}

	<?php if (trim($payroll['script_is_running']) == 1): ?>
		CheckSpreadsheetRunning();
	<?php endif; ?>
</script>