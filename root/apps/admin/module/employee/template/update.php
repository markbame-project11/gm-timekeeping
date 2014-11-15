<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'employee'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<form method="post" action="<?php echo $ccConfig->get('base_url').'/employee/update?employee_id='.$employee['empid']; ?>" role="form" clas="form-horizontal">
				<fieldset style="width: 850px;">
					<legend>Update Employee</legend>

					<?php if (0 < count($fieldsWithErrors)): ?>
						<div class="alert alert-danger" role="alert">The following fields are mandatory <?php echo implode(', ', $fieldsWithErrors); ?></div>
					<?php endif; ?>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Department : </label>
						<div class="col-sm-8">
							<select class="form-control" name="deptid">
								<?php foreach ($departments as $department): ?>
									<option value="<?php echo $department['deptid']; ?>" <?php echo ($fields['deptid'] == $department['deptid']) ? 'selected="selected"' : ''; ?> >
										<?php echo $department['deptname']; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Employment Status : <span style="color:red;">*</span> </label>
						<div class="col-sm-8">
							<select class="form-control" name="employment_status">
								<option value="">Choose Employment Status</option>

								<?php foreach ($employmentStatuses as $key => $status): ?>
									<option value="<?php echo $key; ?>" <?php echo ($key == $fields['employment_status']) ? 'selected="selected"' : ''; ?> >
										<?php echo $status; ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>					

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
							<input type="date" class="form-control" value="<?php echo $fields['birth_date']; ?>" name="birth_date" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Tax Status: </label>
						<div class="col-sm-8">
							<select class="form-control"  name="tax_status">
								<?php foreach ($marital_statuses as $key => $marital_status): ?>
									<option value="<?php echo $key; ?>" <?php echo ($fields['tax_status'] == $key) ? 'selected="selected"' : ''; ?> >
										<?php echo $marital_status; ?>
									</option>	
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Gender: </label>
						<div class="col-sm-8">
							<input type="radio" <?php echo ($fields['gender'] == 'm') ? 'checked="checked"' : ''; ?> value="m" name="gender"> Male &nbsp; &nbsp; 
							<input type="radio" <?php echo ($fields['gender'] == 'f') ? 'checked="checked"' : ''; ?> value="f" name="gender"> Female
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">SSS Number : </label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $fields['sss_no']; ?>" name="sss_no" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Tin(BIR) Number : </label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $fields['tin_no']; ?>" name="tin_no" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Philhealth Number : </label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $fields['philhealth_no']; ?>" name="philhealth_no" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Pagibig Number : </label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $fields['pagibig_no']; ?>" name="pagibig_no" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Position : </label>
						<div class="col-sm-8">
							<input type="text" class="form-control" value="<?php echo $fields['position']; ?>" name="position" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Date Hired : </label>
						<div class="col-sm-8">
							<input type="date" class="form-control" value="<?php echo $fields['date_hired']; ?>" name="date_hired" />
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Address : </label>
						<div class="col-sm-8">
							<textarea cols="30" name="address1" class="form-control"><?php echo $fields['address1']; ?></textarea>
						</div>
					</div>

					<div class="form-group clearfix">
						<label class="col-sm-3 control-label">Cell Phone Number : </label>
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
							<input type="text" class="form-control" value="<?php echo $fields['em_contact_number']; ?>" name="em_contact_number" />
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
						<input type="submit" name="Submit" value="Save" class="btn btn-primary" />
						<a class="btn btn-default" href="<?php echo $ccConfig->get('base_url').'/employee/search'; ?>" >Cancel</a>
					</div>
				</fieldset>
			</form>
		</div>
	</div>
</div>