<div class="navbar navbar-default navbar-fixed-top" role="navigation">
	<div class="container">
		<div class="navbar-header">
			<a class="navbar-brand" href="<?php echo $ccConfig->get('base_url'); ?>">Gumi Online Timesheet</a>
		</div>
		<div class="navbar-collapse collapse">
			<ul class="nav navbar-nav">
				<li class="<?php echo ($active == 'home') ? 'active' : ''; ?>"><a href="<?php echo $ccConfig->get('base_url'); ?>">Home</a></li>
				<li class="<?php echo ($active == 'leave') ? 'active' : ''; ?>"><a href="<?php echo $ccConfig->get('base_url').'/leave'; ?>">Leaves</a></li>
				<!--li><a class="<?php echo ($active == 'timesheet') ? 'active' : ''; ?>" href="<?php echo $ccConfig->get('base_url').'/timesheet'; ?>">Timesheet</a></li-->
			</ul>

			<ul class="nav navbar-nav navbar-right">
				<?php if ($is_admin): ?>
					<li><a href="<?php echo $ccConfig->get('admin_base_url'); ?>" >Go To Admin</a></li>
				<?php endif; ?>
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown">Actions <span class="caret"></span></a>
					<ul class="dropdown-menu" role="menu">
						<li><a href="<?php echo $ccConfig->get('base_url').'/employee/view'; ?>">View Employee Information</a></li>
						<li><a href="<?php echo $ccConfig->get('base_url').'/employee/update'; ?>">Update Employee Information</a></li>
						<li><a href="<?php echo $ccConfig->get('base_url').'/employee/changePassword'; ?>">Change Password</a></li>
						<li class="divider"></li>
						<li><a href="<?php echo $ccConfig->get('base_url').'/account/logout'; ?>">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div><!--/.nav-collapse -->
	</div>
</div>