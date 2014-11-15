<?php 
	include_template('default/_navigation', array(
		'is_admin'      => $is_admin,
		'active'        => 'home'
	)); 
?>

<div class="container" style="width:800px;">
	<div class="panel panel-info">
		<div class="panel-heading">
			<h3 class="panel-title">Employee Information</h3>
		</div>

		<div class="panel-body">

			<div>
				<strong>Department Name : </strong> 
				<?php echo $employee['deptname']; ?>
			</div>

			<div>
				<strong>Name : </strong> 
				<?php echo $employee['firstname'].' '.$employee['minit'].' '.$employee['lastname']; ?>
			</div>

			<div>
				<strong>Username : </strong> 
				<?php echo $employee['login']; ?>
			</div>

			<div>
				<strong>Nickname : </strong> 
				<?php echo $employee['nickname']; ?>
			</div>

			<div>
				<strong>Skype ID : </strong>
				<?php echo $employee['skype_id']; ?>
			</div>

			<div>
				<strong>Date Hired : </strong>
				<?php echo date('M d, Y', strtotime($employee['date_hired'])); ?>
			</div>

			<div>
				<strong>Birth Date : </strong>
				<?php echo date('M d, Y', strtotime($employee['dob'])); ?>
			</div>

			<div>
				<strong>Position : </strong> 
				<?php echo $employee['position']; ?>
			</div>

			<div>
				<strong>Email : </strong> 
				<?php echo $employee['email']; ?>
			</div>

			<div>
				<strong>Address : </strong>
				<?php echo $employee['address1']; ?>
			</div>

			<div>
				<strong>Cell Phone : </strong>
				<?php echo $employee['cellphone']; ?>
			</div>

			<div>
				<strong>SSS Number : </strong>
				<?php echo $employee['sss_no']; ?>
			</div>

			<div>
				<strong>Tin Number : </strong> 
				<?php echo $employee['tin_no']; ?>
			</div>

			<div>
				<strong>Philhealth Number : </strong>
				<?php echo $employee['philhealth_no']; ?>
			</div>

			<div>
				<strong>Pagibig Number : </strong>
				<?php echo $employee['pagibig_no']; ?>
			</div>

			<hr />
			<h4 class="sub-header">Emergency Contact</h4>

			<div>
				<strong>Contact Person : </strong>
				<?php echo $employee['em_contact_person']; ?>
			</div>

			<div>
				<strong>Contact Number: </strong>
				<?php echo $employee['em_contact_no']; ?>
			</div>

			<div>
				<strong>Contact Address: </strong>
				<?php echo $employee['em_contact_address']; ?>
			</div>

			<hr />
			<h4 class="sub-header">Shift Schedule</h4>
			<table class="table table-striped">
				<thead>
					<tr>
						<th>Day</th>
						<th>Time In</th>
						<th>Time Out</th>
					</tr>
				</thead>

				<tbody>
					<?php if (NULL == $schedule): ?>
						<tr>
							<td colspan="4" align="center">
								You have no shift schedule yet. Ask the admin to add a shift schedule for you.
							</td>
						</tr>
					<?php else: ?>
						<?php foreach ($days as $key => $day): ?>
							<?php if ($schedule[$key.'_time'] == '00:00:00'): ?>
								<?php continue; ?>
							<?php else: ?>
								<?php $time_in = strtotime($schedule[$key.'_time']); ?>
								<?php $time_out = strtotime("+".$schedule[$key.'_num_hours']." hours", $time_in); ?>
								<tr>
									<td><?php echo $days[$key]; ?></td>
									<td><?php echo $hours[date('H:i', $time_in)]; ?></td>
									<td><?php echo $hours[date('H:i', $time_out)]; ?></td>
								</tr>
							<?php endif; ?>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>