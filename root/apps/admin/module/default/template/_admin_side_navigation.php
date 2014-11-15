<div class="col-sm-3 col-md-2 sidebar">
	<ul class="nav nav-sidebar">
		<li class="<?php echo ($active_tab == 'department') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url').'/department/list'; ?>">Department</a>
		</li>
		<li class="<?php echo ($active_tab == 'employee') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url').'/employee/search'; ?>">Employees</a>
		</li>
		<li class="<?php echo ($active_tab == 'payroll') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url').'/payroll'; ?>">Payroll</a>
		</li>
		<li class="<?php echo ($active_tab == 'timekeeping') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url'); ?>/timekeeping">Timekeeping</a>
		</li>
		<li class="<?php echo ($active_tab == 'holiday') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url'); ?>/holiday">Holiday</a>
		</li>
		<li class="<?php echo ($active_tab == 'overtime') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url'); ?>/overtime">Overtime</a>
		</li>
		<li class="<?php echo ($active_tab == 'leave') ? 'active' : ''; ?>">
			<a href="<?php echo $ccConfig->get('base_url'); ?>/leave">Leaves</a>
		</li>
	</ul>
</div>