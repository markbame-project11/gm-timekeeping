<?php 
	include_template('default/_navigation', array(
		'is_admin'      => $is_admin,
		'active'        => 'leave'
	)); 
?>

<div class="container" style="width:650px;">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Apply Leave</h3>
		</div>

		<div class="panel-body">
			<?php if ($info_message != NULL): ?>
				<div class="alert alert-info" role="alert">
					<?php echo $info_message; ?>
				</div>
			<?php endif; ?>

			<?php if ($error_message != NULL): ?>
				<div id="error_message" class="alert alert-danger" role="alert">
					<?php echo $error_message; ?>
				</div>
			<?php endif; ?>

			<div id="success_message" style="display:none;" class="alert alert-success" role="alert"></div>

			<form method="post" action="<?php echo $ccConfig->get('base_url').'/leave/apply'; ?>" onsubmit="return ValidateForm(this);" role="form">

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Start Date :  </label>
					<div class="col-sm-8">
						<input id="fld_start_date" type="date" class="form-control" value="<?php echo $fields['start_date']; ?>" name="start_date" required="required" onchange="CalculateStartTimeAndNumHours();" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Num. of Days :  </label>
					<div class="col-sm-8">
						<input id="fld_num_days" type="number" class="form-control" value="<?php echo $fields['num_days']; ?>" name="num_days" min="1" max="60" required="required" onchange="CalculateStartTimeAndNumHours();" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Start Time :  </label>
					<div class="col-sm-8">
						<input id="fld_start_time" type="time" class="form-control" value="<?php echo $fields['start_time']; ?>" name="start_time" required="required" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Hrs. Per Day :  </label>
					<div class="col-sm-8">
						<input id="fld_number_of_hours" type="number" class="form-control" value="<?php echo $fields['number_of_hours']; ?>" name="number_of_hours" min="0" required="required" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Leave Type :  </label>
					<div class="col-sm-8">
						<select name="leave_type" class="form-control" required="required">
							<option value="" >Choose Type</option>

							<?php foreach ($leave_types as $key => $value): ?>
								<option value="<?php echo $key; ?>" <?php echo ($key == $fields['leave_type']) ? 'selected="selected"' : ''; ?>><?php echo $value; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Is Paid :  </label>
					<div class="col-sm-8">
						<select id="is_paid_fld" name="is_paid" class="form-control" required="required" <?php echo (!$show_false_only_for_is_paid) ? 'disabled="disabled"' : ''; ?> >
							<option value="1" <?php echo ("1" == $fields['is_paid']) ? 'selected="selected"' : ''; ?> >
								True
							</option>
							<option value="0" <?php echo (!$show_false_only_for_is_paid || "0" == $fields['is_paid']) ? 'selected="selected"' : ''; ?> >
								False
							</option>
						</select>
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Reason :  </label>
					<div class="col-sm-8">
						<textarea rows="8" name="reason" class="form-control" required="required"><?php echo $fields['reason']; ?></textarea>
					</div>
				</div>

				<div class="form-actions">
					<input id="submit_btn" type="submit" name="Submit" value="Apply Leave" class="btn btn-primary" />
					<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url'); ?>" >Cancel</a>
				</div>
			</form>

		</div>
	</div>
</div>

<!-- Dialog -->
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
		$('#message_dialog .ok_btn').unbind('click');

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
		$('#message_dialog .ok_btn').unbind('click');

		$('#message_dialog .modal-title').html(title);
		$('#message_dialog .modal-body').html('<p>' + body + '</p>');
		$('#message_dialog .ok_btn').html(buttons.ok_label);
		$('#message_dialog .ok_btn').click(function() {
			$('#message_dialog').modal('hide');
			buttons.callback();
		});
		$('#message_dialog .cancel_btn').css('display', 'inline');
		$('#message_dialog .cancel_btn').html(buttons.cancel_label);
		$('#message_dialog').modal('show');
	}

	var isFormValidated = false;
	function ValidateForm(form)
	{	
		if (!isFormValidated)
		{
			var url = '<?php echo $ccConfig->get("base_url")."/leave/validateForm"; ?>';

			$.post(url, $(form).serialize(), function(json) {
				if (json.is_successful)
				{
					if (json.show_message)
					{
						var btns =
						{
							'ok_label' : 'OK',
							'cancel_label' : 'Cancel',
							'callback' : function()
							{
								isFormValidated = true;
								$(form).submit();
							}
						};

						dialog_confirm('Confirm Leave?', json.message, btns);
					}
					else
					{
						isFormValidated = true;
						$(form).submit();
					}
				}
				else
				{
					isFormValidated = false;
					dialog_alert("Error Message", json.message);
				}
			}, 'json');
		}

		return isFormValidated;
	}

	function CalculateStartTimeAndNumHours()
	{
		if ($('#fld_start_date').val() != '')
		{
			isFormValidated = false;

			var url = '<?php echo $ccConfig->get("base_url")."/leave/calculateStartTimeAndNumHours"; ?>';
			url += '?start_date=' + $('#fld_start_date').val();
			url += '&num_days=' + $('#fld_num_days').val();
			$('#submit_btn').button('loading');
			$.getJSON(url, function(json){
				$('#submit_btn').button('reset');

				if (json.is_successful)
				{
					if (!json.start_time_and_hours_editable)
					{
						$('#fld_start_time').attr('readonly', true);
						$('#fld_number_of_hours').attr('readonly', true);
					}
					else
					{
						$('#fld_start_time').attr('readonly', false);
						$('#fld_number_of_hours').attr('readonly', false);
					}

					$('#fld_start_time').val(json.start_time);
					$('#fld_number_of_hours').val(json.number_of_hours);
				}
				else
				{
					dialog_alert("Error Message", json.message);
				}
			});
		}
	}
</script>