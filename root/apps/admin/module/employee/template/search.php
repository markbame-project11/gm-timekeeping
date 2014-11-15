<?php include_template('default/_admin_navigation'); ?>

<div class="container-fluid">
	<div class="row">
		<?php 
			include_template('default/_admin_side_navigation', array(
				'active_tab'        => 'employee'
			));
		?>

		<div class="col-sm-9 col-sm-offset-3 col-md-10 col-md-offset-2 main">
			<h1 class="page-header">
				<div style="float:right;">
					<a type="button" class="btn btn-primary" href="<?php echo $ccConfig->get('base_url').'/employee/create'; ?>">Add Employee</a>
				</div>
				Employees
			</h1>

            <?php
               /*
                  uploading CSV to Database 30 Oct 2014
                  file will be exported to employee table
               */
            ?>
			<form id="formupload" enctype='multipart/form-data' action='<?php echo $ccConfig->get('base_url').'/employee/importDB' ?>' method='post'>
				<div class="form-group">				
					<label>Employee excel to import : </label>           
		           <input style="display: inline; width: 380px;" size='1500' id="file_input" type='file' name='filename' />
		           <input type='submit' id="uploadButton" disabled name='submit' value='Upload' class="btn btn-primary" />
		           <input type="hidden" name="chk_fileupload" value="1" />		           
		           <input type="hidden" name="fupload" value="1" />
				</div>		           
			</form>


			<form name="form1" method="post" action="<?php echo $ccConfig->get('base_url').'/employee/search' ?>">
				<div class="alert alert-info" role="alert">
					Please put the name or email of the employee you want to search 
					in the textbox below and the program will  search the database for all entries matching your query.
				</div>

				<div class="form-group">
					<label>Keyword : </label>
					<input type="text" class="form-control" style="display: inline; width: 450px;" name="keyword" value="<?php echo $fields['keyword']; ?>" />

					<input type="submit" name="Submit" value="Search" class="btn btn-primary" />
				</div>
			</form>

            <script>
               $(document).ready( function (){
               	  //alert ("jQ enabled");
               	  /*
                   $("#formupload").submit( function(submitEvent) {
					    var ext = this.value.match(/\.(.+)$/)[1];
					    switch (ext) {
					        case 'csv':
					        case 'xls':
					        case 'xlsx':
					            $('#uploadButton').attr('disabled', false);
					            break;
					        default:
					            alert('This is not an allowed file type.');
					            this.value = '';
					    }
					});
                  */


					$('INPUT[type="file"]').change(function () {
					    var ext = this.value.match(/\.(.+)$/)[1];
					    switch (ext) {
					        case 'csv':
					        case 'xls':
					        case 'xlsx':
					            $('#uploadButton').attr('disabled', false);
					            break;
					        default:
					            alert('Kindly upload properly formatted excel/csv master list only.');
					            this.value = '';
					    }
					});


               	});
            </script>


			<?php if (isset($search_results)): ?>
				<hr />
				<h2 style="font-size: 23px;" class="sub-header">Search Results</h2>

				<table class="table table-striped">
					<thead>
						<tr>
							<th>Last Name</th>
							<th>First Name</th>
							<th>Email</th>
							<th>Action</th>
						</tr>
					</thead>

					<tbody>
						<?php if (0 == count($search_results)): ?>
							<tr>
								<td colspan="4" align="center">No entry found</td>
							</tr>
						<?php else: ?>
							<?php foreach ($search_results as $i => $employee): ?>
								<tr <?php echo (($i % 2) == 1) ? 'bgcolor="#EBEBEB"' : ''; ?> >
									<td><?php echo $employee['lastname']; ?></td>
									<td><?php echo $employee['firstname']; ?></td>
									<td><?php echo $employee['email']; ?></td>
									<td>
										<a href="<?php echo $ccConfig->get('base_url').'/employee/view?employeeid='.$employee['empid']; ?>">View Details</a> |
										<a href="<?php echo $ccConfig->get('base_url').'/schedule/change?employeeid='.$employee['empid']; ?>">Change Schedule</a> 
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

			<?php endif; ?>
		</div>
	</div>
</div>