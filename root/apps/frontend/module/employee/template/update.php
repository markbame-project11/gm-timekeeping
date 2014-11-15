<?php 
	include_template('default/_navigation', array(
		'is_admin'      => $is_admin,
		'active'        => 'home'
	)); 
?>

<div class="container" style="width:700px;">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Update Employee Information</h3>
		</div>

		<div class="panel-body">
			<?php if (0 < count($fieldsWithErrors)): ?>
				<div class="alert alert-danger" role="alert">The following fields are mandatory <?php echo implode(', ', $fieldsWithErrors); ?></div>
			<?php endif; ?>

			<form method="post" action="<?php echo $ccConfig->get('base_url').'/employee/update'; ?>" role="form" clas="form-horizontal">
				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">First Name : <span style="color:red;">*</span> </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['firstname']; ?>" name="firstname" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Middle Name : </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['minit']; ?>" name="minit" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Last Name : <span style="color:red;">*</span> </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['lastname']; ?>" name="lastname" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Nickname : </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['nickname']; ?>" name="nickname" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Personal Email : </label>
					<div class="col-sm-8">
						<input type="email" class="form-control" value="<?php echo $fields['email']; ?>" name="email" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Date of Birth : </label>
					<div class="col-sm-8">
						<input type="date" class="form-control" value="<?php echo $fields['dob']; ?>" name="dob" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">SSS Number : </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['sss_no']; ?>" name="sss_no" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Tin No: </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['tin_no']; ?>" name="tin_no" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Philhealth No: </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['philhealth_no']; ?>" name="philhealth_no" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Pagibig No: </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['pagibig_no']; ?>" name="pagibig_no" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Address : </label>
					<div class="col-sm-8">
						<textarea cols="30" name="address1" class="form-control"><?php echo $fields['address1']; ?></textarea>
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Cell No : </label>
					<div class="col-sm-8">
						<input type="text" name="cellphone" class="form-control" size="20" value="<?php echo $fields['cellphone']; ?>" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label">Gumi Skype ID : </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['skype_id']; ?>" name="skype_id" />
					</div>
				</div>

				<br />

				<h4>Emergeny Contact</h4>
				<hr />

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label"> Contact Person: </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['em_contact_person']; ?>" name="em_contact_person" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label"> Contact Number: </label>
					<div class="col-sm-8">
						<input type="text" class="form-control" value="<?php echo $fields['em_contact_no']; ?>" name="em_contact_no" />
					</div>
				</div>

				<div class="form-group clearfix">
					<label class="col-sm-3 control-label"> Address: </label>
					<div class="col-sm-8">
						<textarea class="form-control" name="em_contact_address" ><?php echo $fields['em_contact_address']; ?></textarea>
					</div>
				</div>

				<br />
				<br />

				<div class="form-actions">
					<input type="submit" name="Submit" value="Update Info" class="btn btn-primary" />
					<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url'); ?>" >Cancel</a>
				</div>
			</form>
		</div>
	</div>
</div>