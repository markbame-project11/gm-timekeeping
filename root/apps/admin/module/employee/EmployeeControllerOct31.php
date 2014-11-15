<?php
            //require once $_SERVER['DOCUMENT_ROOT'] . "../lib/vendor/PHPExcel/Classes/PHPExcel/IOFactory.php";
            require_once "/Applications/MAMP/htdocs/root/lib/vendor/PHPExcel/Classes/PHPExcel/IOFactory.php";
            //echo dirname(__FILE__) . '';
            //echo $_SERVER['DOCUMENT_ROOT'];
/**
 * The employee controller class.
 */
class EmployeeController extends BaseAdminController
{
	/**
	 *
	 */
	public function executeUpdate()
	{
		$this->redirectUnless(array_key_exists('employee_id', $_GET), $this->getConfig()->get('base_url'));

		$deptModel = $this->loadModel('Department');
		$employeeModel = $this->loadModel('Employee');

		$this->employee = $employeeModel->getById($_GET['employee_id']);
		$this->redirectUnless((NULL != $this->employee), $this->getConfig()->get('base_url').'/employee');
		$this->fieldsWithErrors = array();
		$this->employmentStatuses = array(
			'regular'       => 'Regular',
			'probationary'  => 'Probationary',
			'resigned'      => 'Resigned'
		);

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$fieldsToCheck = array(
				'firstname'    => 'First Name', 
				'lastname'     => 'Last Name', 
				'address1'     => 'Address 1'
			);
			$fieldsWithErrors = $this->checkMandatoryFields(array_keys($fieldsToCheck), $_POST);

			if (0 == count($fieldsWithErrors))
			{	

			/*
			  fixed the $_POST index error and model loading, using isset
			  29 Oct 2014
			  -rye
			*/

				  $deptid = "";				
				if (isset($_POST['deptid'])) {
				  $deptid = $_POST["deptid"];
				}
				  $lastname = "";				
				if (isset($_POST['lastname'])) {
				  $lastname = $_POST["lastname"];
				}
				  $firstname = "";				
				if (isset($_POST['firstname'])) {
				  $firstname = $_POST["firstname"];
				}
				  $minit = "";				
				if (isset($_POST['minit'])) {
				  $minit = $_POST["minit"];
				}
				  $birth_date = "";				
				if (isset($_POST['birth_date'])) {
				  $birth_date = $_POST["birth_date"];
				}
				  $gender = "";				
				if (isset($_POST['gender'])) {
				  $gender = $_POST["gender"];
				}
				  $tax_status = "";				
				if (isset($_POST['tax_status'])) {
				  $tax_status = $_POST["tax_status"];
				}
				  $address1 = "";				
				if (isset($_POST['address1'])) {
				  $address1 = $_POST["address1"];
				}
				  $email = "";				
				if (isset($_POST['email'])) {
				  $email = $_POST["email"];
				}
				  $homephone = "";				
				if (isset($_POST['homephone'])) {
				  $homephone = $_POST["homephone"];
				}
				  $cellphone = "";				
				if (isset($_POST['cellphone'])) {
				  $cellphone = $_POST["cellphone"];
				}
				/*
				  $username = "";				
				if (isset($_POST['username'])) {
				  $username = $_POST["username"];
				}
				*/
				  $em_contact_person = "";				
				if (isset($_POST['em_contact_person'])) {
				  $em_contact_person = $_POST["em_contact_person"];
				}
				  $em_contact_number = "";				
				if (isset($_POST['em_contact_number'])) {
				  $em_contact_number = $_POST["em_contact_number"];
				}
				  $em_contact_address = "";				
				if (isset($_POST['em_contact_address'])) {
				  $em_contact_address = $_POST["em_contact_address"];
				}
				  $skype_id = "";				
				if (isset($_POST['skype_id'])) {
				  $skype_id = $_POST["skype_id"];
				}
				  $date_hired = "";				
				if (isset($_POST['date_hired'])) {
				  $date_hired = $_POST["date_hired"];
				}
				  $position = "";				
				if (isset($_POST['position'])) {
				  $position = $_POST["position"];
				}
				  $pagibig_no = "";				
				if (isset($_POST['pagibig_no'])) {
				  $pagibig_no = $_POST["pagibig_no"];
				}
				  $philhealth_no = "";				
				if (isset($_POST['philhealth_no'])) {
				  $philhealth_no = $_POST["philhealth_no"];
				}
				  $tin_no = "";				
				if (isset($_POST['tin_no'])) {
				  $tin_no = $_POST["tin_no"];
				}
				  $sss_no = "";				
				if (isset($_POST['sss_no'])) {
				  $sss_no = $_POST["sss_no"];
				}
				  $nickname = "";				
				if (isset($_POST['nickname'])) {
				  $nickname = $_POST["nickname"];
				}
				  $employment_status = "";				
				if (isset($_POST['employment_status'])) {
				  $employment_status = $_POST["employment_status"];
				}

				$valuesToUpdate = array(
					'deptid'               => $deptid, //$_POST['deptid'],
					'lastname'             => $lastname, //$_POST['lastname'],
					'firstname'            => $firstname, //$_POST['firstname'],
					'minit'                => $minit, //$_POST['minit'],
					'dob'                  => $birth_date, //$_POST['birth_date'],
					'gender'               => $gender, //$_POST['gender'],
					'tax_status'           => $tax_status, //$_POST['tax_status'],
					'address1'             => $address1, //$_POST['address1'],
					'email'                => $email, //$_POST['email'],
					'homephone'            => $homephone, //$_POST['homephone'],
					'cellphone'            => $cellphone, //$_POST['cellphone'],
					'em_contact_person'    => $em_contact_person, //$_POST['em_contact_person'],
					'em_contact_no'        => $em_contact_number, //$_POST['em_contact_number'],
					'em_contact_address'   => $em_contact_address, //$_POST['em_contact_address'],
					'skype_id'             => $skype_id, //$_POST['skype_id'],
					'date_hired'           => $date_hired, //$_POST['date_hired'],
					'position'             => $position, //$_POST['position'],
					'pagibig_no'           => $pagibig_no, //$_POST['pagibig_no'],
					'philhealth_no'        => $philhealth_no, //$_POST['philhealth_no'],
					'tin_no'               => $tin_no, //$_POST['tin_no'],
					'sss_no'               => $sss_no, //$_POST['sss_no'],
					'nickname'             => $nickname, //$_POST['nickname'],
					'employment_status'    => $employment_status, //$_POST['employment_status'],
					'dateupdated'          => date('Y-m-d H:i:s')
				);

				//if ($employee['employment_status'] != 'regular' && $_POST['employment_status'] == 'regular')
                if ($this->employee['employment_status'] != 'regular' && $_POST['employment_status'] == 'regular')
				{
					$valuesToUpdate['regularization_date'] = date('Y-m-d');
				}
				else if ($this->employee['employment_status'] != 'regular' && $_POST['employment_status'] == 'regular')
				{
					$valuesToUpdate['resignation_date'] = date('Y-m-d');
				}

				$employeeModel->update($this->employee['empid'], $valuesToUpdate);
 
				$this->redirect($this->getConfig()->get('base_url').'/employee/view?employeeid='.$this->employee['empid']);
			}
			else
			{
				foreach ($fieldsWithErrors as $fieldWithError)
				{
					$this->fieldsWithErrors[] = $fieldsToCheck[$fieldWithError];
				}

				$this->loadHelpers(array('generic'));

				$this->departments = $deptModel->getAll();
				$this->marital_statuses = get_all_marital_statuses();
				$this->fields = $_POST;
			}
		}
		else
		{
			$this->loadHelpers(array('generic'));

			$this->departments = $deptModel->getAll();
			$this->marital_statuses = get_all_marital_statuses();
			$this->fields = array(
				'deptid'               => $this->employee['deptid'],
				'firstname'            => $this->employee['firstname'],
				'minit'                => $this->employee['minit'],
				'lastname'             => $this->employee['lastname'],
				'birth_date'           => $this->employee['dob'],
				'tax_status'           => $this->employee['tax_status'],
				'gender'               => $this->employee['gender'],
				'email'                => $this->employee['email'],
				'cellphone'            => $this->employee['cellphone'],
				'address1'             => $this->employee['address1'],
				'em_contact_person'    => $this->employee['em_contact_person'],
				'em_contact_number'    => $this->employee['em_contact_no'],
				'em_contact_address'   => $this->employee['em_contact_address'],
				'skype_id'             => $this->employee['skype_id'],
				'date_hired'           => $this->employee['date_hired'],
				'position'             => $this->employee['position'],
				'pagibig_no'           => $this->employee['pagibig_no'],
				'philhealth_no'        => $this->employee['philhealth_no'],
				'tin_no'               => $this->employee['tin_no'],
				'sss_no'               => $this->employee['sss_no'],
				'employment_status'    => $this->employee['employment_status'],
				'nickname'             => $this->employee['nickname']
			);
		}
	}

	/**
	 *
	 */
	public function executeCreate()
	{
		$deptModel = $this->loadModel('Department');
		$jobTitleModel = $this->loadModel('JobTitle');
		$employeeTypeModel = $this->loadModel('EmployeeType');
		$employeeCategoryModel = $this->loadModel('EmployeeCategory');
		$employeeModel = $this->loadModel('Employee');

		$scheduleModel = $this->loadModel('EmployeeSchedule');

		$this->fieldsWithErrors = array();

		$this->employmentStatus = array(
			'probationary'      => 'Probationary',
			'regular'           => 'Regular',
			'resigned'          => 'Resigned'
		);

		// redirect to creating department if there are no departments yet.
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$fieldsToCheck = array(
				'firstname'    => 'First Name', 
				'lastname'     => 'Last Name', 
				'address1'     => 'Address 1',
				'username'     => 'Username'
			);
			$fieldsWithErrors = $this->checkMandatoryFields(array_keys($fieldsToCheck), $_POST);

			if (0 == count($fieldsWithErrors))
			{	
			/*
			  fixed the $_POST index error and model loading, using isset
			  29 Oct 2014
			  -rye
			*/

				  $deptid = "";				
				if (isset($_POST['deptid'])) {
				  $deptid = $_POST["deptid"];
				}
				  $lastname = "";				
				if (isset($_POST['lastname'])) {
				  $lastname = $_POST["lastname"];
				}
				  $firstname = "";				
				if (isset($_POST['firstname'])) {
				  $firstname = $_POST["firstname"];
				}
				  $minit = "";				
				if (isset($_POST['minit'])) {
				  $minit = $_POST["minit"];
				}
				  $birth_date = "";				
				if (isset($_POST['birth_date'])) {
				  $birth_date = $_POST["birth_date"];
				}
				  $gender = "";				
				if (isset($_POST['gender'])) {
				  $gender = $_POST["gender"];
				}
				  $tax_status = "";				
				if (isset($_POST['tax_status'])) {
				  $tax_status = $_POST["tax_status"];
				}
				  $address1 = "";				
				if (isset($_POST['address1'])) {
				  $address1 = $_POST["address1"];
				}
				  $email = "";				
				if (isset($_POST['email'])) {
				  $email = $_POST["email"];
				}
				  $homephone = "";				
				if (isset($_POST['homephone'])) {
				  $homephone = $_POST["homephone"];
				}
				  $cellphone = "";				
				if (isset($_POST['cellphone'])) {
				  $cellphone = $_POST["cellphone"];
				}
				  $username = "";				
				if (isset($_POST['username'])) {
				  $username = $_POST["username"];
				}
				  $em_contact_person = "";				
				if (isset($_POST['em_contact_person'])) {
				  $em_contact_person = $_POST["em_contact_person"];
				}
				  $em_contact_number = "";				
				if (isset($_POST['em_contact_number'])) {
				  $em_contact_number = $_POST["em_contact_number"];
				}
				  $em_contact_address = "";				
				if (isset($_POST['em_contact_address'])) {
				  $em_contact_address = $_POST["em_contact_address"];
				}
				  $skype_id = "";				
				if (isset($_POST['skype_id'])) {
				  $skype_id = $_POST["skype_id"];
				}
				  $date_hired = "";				
				if (isset($_POST['date_hired'])) {
				  $date_hired = $_POST["date_hired"];
				}
				  $position = "";				
				if (isset($_POST['position'])) {
				  $position = $_POST["position"];
				}
				  $pagibig_no = "";				
				if (isset($_POST['pagibig_no'])) {
				  $pagibig_no = $_POST["pagibig_no"];
				}
				  $philhealth_no = "";				
				if (isset($_POST['philhealth_no'])) {
				  $philhealth_no = $_POST["philhealth_no"];
				}
				  $tin_no = "";				
				if (isset($_POST['tin_no'])) 
				{
				  $tin_no = $_POST["tin_no"];
				}
				  $sss_no = "";				
				if (isset($_POST['sss_no']))
				{
				  $sss_no = $_POST["sss_no"];
				}
				  $nickname = "";				
				if (isset($_POST['nickname']))
				{
				  $nickname = $_POST["nickname"];
				}


				$empID = $employeeModel->create(array(
					'deptid'               => $deptid, //$_POST['deptid'],
					'lastname'             => $lastname, //$_POST['lastname'],
					'firstname'            => $firstname, //$_POST['firstname'],
					'minit'                => $minit, //$_POST['minit'],
					'dob'                  => $birth_date, //$_POST['birth_date'],
					'gender'               => $gender, //$_POST['gender'],
					'tax_status'           => $tax_status, //$_POST['tax_status'],
					'address1'             => $address1, //$_POST['address1'],
					'email'                => $email, //$_POST['email'],
					'homephone'            => $homephone, //$_POST['homephone'],
					'cellphone'            => $cellphone, //$_POST['cellphone'],
					'login'                => $username, //$_POST['username'],
					'em_contact_person'    => $em_contact_person, //$_POST['em_contact_person'],
					'em_contact_number'    => $em_contact_number, //$_POST['em_contact_number'],
					'em_contact_address'   => $em_contact_address, //$_POST['em_contact_address'],
					'skype_id'             => $skype_id, //$_POST['skype_id'],
					'date_hired'           => $date_hired, //$_POST['date_hired'],
					'position'             => $position, //$_POST['position'],
					'pagibig_no'           => $pagibig_no, //$_POST['pagibig_no'],
					'philhealth_no'        => $philhealth_no, //$_POST['philhealth_no'],
					'tin_no'               => $tin_no, //$_POST['tin_no'],
					'sss_no'               => $sss_no, //$_POST['sss_no'],
					'nickname'             => $nickname, //$_POST['nickname'],
					'password'             => 'G0m1Pa$$',
					'datesignup'           => date('Y-m-d'),
					'ipsignup'             => $_SERVER['REMOTE_ADDR'],
					'dateupdated'          => date('Y-m-d H:i:s')
				));

				if ($empID > 0)
				{
					$schedules = array('mon', 'tue', 'wed', 'thu', 'fri');

					foreach ($schedules as $schedule)
					{
						$scheduleModel->create(array(
							'employee_id'      => $empID,
							'start_date'       => date('Y-m-d'),
							'day'              => $schedule,
							'start_time'       => '09:30:00',
							'number_of_hours'  => 9
						));
					}
				}
 
				$this->redirect($this->getConfig()->get('base_url').'/employee/view?employeeid='.$empID);
			}
			else
			{
				foreach ($fieldsWithErrors as $fieldWithError)
				{
					$this->fieldsWithErrors[] = $fieldsToCheck[$fieldWithError];
				}

				$this->loadHelpers(array('generic'));

				$this->departments = $deptModel->getAll();
				$this->marital_statuses = get_all_marital_statuses();
				$this->fields = $_POST;
			}
		}
		else
		{
			$this->loadHelpers(array('generic'));

			$this->departments = $deptModel->getAll();
			$this->marital_statuses = get_all_marital_statuses();
			$this->fields = array(
				'username'             => '',
				'deptid'               => '',
				'firstname'            => '',
				'minit'                => '',
				'lastname'             => '',
				'birth_date'           => date('m/d/Y'),
				'tax_status'           => '',
				'gender'               => 'm',
				'email'                => '',
				'homephone'            => '',
				'cellphone'            => '',
				'address1'             => '',
				'em_contact_person'    => '',
				'em_contact_number'    => '',
				'em_contact_address'   => '',
				'skype_id'             => '',
				'date_hired'           => '',
				'position'             => '',
				'pagibig_no'           => '',
				'philhealth_no'        => '',
				'tin_no'               => '',
				'sss_no'               => '',
				'nickname'             => ''
			);
		}
	}

	public function executeView()
	{
		$this->redirectUnless((array_key_exists('employeeid', $_GET)), $this->getConfig()->get('base_url'));

		$employeeModel = $this->loadModel('Employee');
		$this->employee = $employeeModel->getByEmployeeIDJoinDepartment($_GET['employeeid']);

		$this->forward404Unless(($this->employee != NULL));

		$date_arr = explode('-', $this->employee['dob']);
		$this->birth_date = mktime(0, 0, 0, (int)$date_arr[1], (int)$date_arr[2], (int)$date_arr[0]);

		$scheduleModel = $this->loadModel('EmployeeSchedule');
		$this->schedule = $scheduleModel->getLastEmployeeSchedule($_GET['employeeid']);

		$flexiSchedModel = $this->loadModel('FlexiSchedule');
		$this->userIsInFlexiSched = $flexiSchedModel->employeeIsOnFlexiSchedule($_GET['employeeid']);

		$this->employmentStatuses = array(
			'regular'       => 'Regular',
			'probationary'  => 'Probationary',
			'resigned'      => 'Resigned'
		);

		$this->genderList = array(
			'm'  => 'Male',
			'f'  => 'Female'
		);


		$this->loadHelpers(array('generic'));

		$this->days = get_all_days();
		$this->hours = get_all_hours();
	}

	public function executeSearch()
	{
		$employeeModel = $this->loadModel('Employee');
		
		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->search_results = $employeeModel->searchUsers('%'.$_POST['keyword'].'%');
			$this->fields = $_POST;
		}
		else
		{
			$this->fields = array(
				'keyword'    => ''
			);

			$this->search_results = $employeeModel->getPayrollEmployees();
		}
	}

	public function executeAddAsAdmin()
	{
		$this->redirectUnless((array_key_exists('employee_id', $_POST)), $this->getConfig()->get('base_url'));

		$employeeModel = $this->loadModel('Employee');
		$employee = $employeeModel->getByID($_POST['employee_id']);

		if (NULL != $employee)
		{
			if ($employeeModel->update($employee['empid'], array('admin' => true)))
			{
				echo json_encode(array(
					'is_successful'   => true,
					'message'         => $employee['firstname'].' '.$employee['firstname'].' successfully added as admin.'
				));
			}
			else
			{
				echo json_encode(array(
					'is_successful'   => false,
					'message'         => 'An unexpected error occurs.'
				));
			}
		}
		else
		{
			echo json_encode(array(
				'is_successful'   => false,
				'message'         => 'Invalid request'
			));
		}

		exit;
	}

	//--------------------------------------------------------------------------------------------------------
	  /*
	    additional import xsl to database 30 Oct 2014
	  */
	public function executeImportDB()
	{
            //require once $_SERVER['DOCUMENT_ROOT'] .'../lib/vendor/PHPExcel/Classes/PHPExcel/IOFactory.php';
		    //echo dirname(__FILE__) . '/../../';
            //exit;
			$uploadedStatus = 0;

			if ( isset($_POST["submit"]) ) {
			if ( isset($_FILES["filename"])) {
			//if there was an error uploading the file
			if ($_FILES["filename"]["error"] > 0) {
			   echo "Return Code: " . $_FILES["filename"]["error"] . "<br />";
			  }
			else {
			   if (file_exists($_FILES["filename"]["name"])) 
			    {
			     unlink($_FILES["filename"]["name"]);
			    }
			$storagename = "discussdesk.xlsx";
			move_uploaded_file($_FILES["filename"]["tmp_name"],  $storagename);
			$uploadedStatus = 1;
			}
			} else {
			    echo "No file selected <br />";
			   }
			}
   			   //echo $uploadedStatus;
            //exit;

			   //file successfully uploaded
			   if ($uploadedStatus==1)
			     {
			     	$inputFileName = $storagename;

					try {
						//$objPHPExcel = new PHPExcel();
						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
					} catch(Exception $e) {
						die('Error loading file "'.pathinfo($inputFileName,PATHINFO_BASENAME).'": '.$e->getMessage());
					}

					/*
					$objReader = PHPExcel_IOFactory::createReader('Excel2007');		
					//set to read only
					$objReader->setReadDataOnly(true);
					//load excel file
					*/
					//$objPHPExcel = $objReader->load($inputFileName);
					$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
					$highestRow = $objWorksheet->getHighestRow(); 



					//!!!$allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
					//!!$arrayCount = count($allDataInSheet);  // Here get total count of row in that Excel sheet

					//!!!$objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
					//!!$highestRow = $objWorksheet->getHighestRow(); 


					 //!for($i=2;$i<=$arrayCount;$i++)
					 //!   {
                          //$first_name = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
                     //!     $first_name = trim($allDataInSheet[$i][0]);				    	
                     //!     echo $firstname . "<br />";
					/*
					ini_set("memory_limit","256M");
					*/
					$dsn = pd_DSN; //$_SESSION["pd_DSN"]; //'mysql:dbname=payrolldb;host=127.0.0.1';
					$user = pd_USER; //$_SESSION["pd_USER"]; //'payroll';
					$password = pd_PWD; //$_SESSION["pd_PWD"]; //'5ZnrVBuU2VZuLd2q';

					/*
					$dsn = 'mysql:dbname=payrolldb;host=127.0.0.1';
					$user = 'payroll';
					$password = '5ZnrVBuU2VZuLd2q';
					*/

					$pdo = new PDO($dsn, $user, $password);  


					 for($i=1; $i<=$highestRow; $i++)
					   {
					   $user_name = $objWorksheet->getCellByColumnAndRow(0,$i)->getValue();
					   $full_name = $objWorksheet->getCellByColumnAndRow(1,$i)->getValue();
					   $surname = $objWorksheet->getCellByColumnAndRow(2,$i)->getValue();
					   $firstname = $objWorksheet->getCellByColumnAndRow(3,$i)->getValue();
					   $sex = $objWorksheet->getCellByColumnAndRow(4,$i)->getValue();

					   $department = $objWorksheet->getCellByColumnAndRow(5,$i)->getValue();
					   $tax_status = $objWorksheet->getCellByColumnAndRow(6,$i)->getValue();
					   $mobile = $objWorksheet->getCellByColumnAndRow(7,$i)->getValue();
					   $address = $objWorksheet->getCellByColumnAndRow(8,$i)->getValue();
					   $email = $objWorksheet->getCellByColumnAndRow(9,$i)->getValue();

					   $birth = $objWorksheet->getCellByColumnAndRow(10,$i)->getValue();
					   $sss_no = $objWorksheet->getCellByColumnAndRow(11,$i)->getValue();
					   $tin_no = $objWorksheet->getCellByColumnAndRow(12,$i)->getValue();
					   $philhealth_no = $objWorksheet->getCellByColumnAndRow(13,$i)->getValue();
					   $pagibig_no = $objWorksheet->getCellByColumnAndRow(14,$i)->getValue();

					   $position = $objWorksheet->getCellByColumnAndRow(15,$i)->getValue();
					   $date_hired = $objWorksheet->getCellByColumnAndRow(16,$i)->getValue();					   
					   $skype_id = $objWorksheet->getCellByColumnAndRow(17,$i)->getValue();
					   $em_contact_person = $objWorksheet->getCellByColumnAndRow(18,$i)->getValue();
					   $em_contact_number = $objWorksheet->getCellByColumnAndRow(19,$i)->getValue();
					   $em_contact_address = $objWorksheet->getCellByColumnAndRow(20,$i)->getValue();

					   $gumi_user = $user_name.'@gumi.ph';

					   //$config = $this->getConfig();
                      /*
                       $pdo = new PDO('mysql:dbname=payrolldb;host=127.0.0.1', 'payroll', '5ZnrVBuU2VZuLd2q');

                       $query = "SELECT login FROM employee WHERE login LIKE %'".$user_name."'%'";
						$stmt = $pdo->query($query); 
						$stmt->execute();
						$total = $stmt->rowCount();
					 */
                    $sql = "SELECT login FROM employee WHERE login LIKE '%".$user_name."%'";
                    //echo $sql;
					//$q=$dbh->prepare($sql);						
					    //--------------------------------------------------------------------
						$stmt = $pdo->query($sql); 					
						//$stmt->execute();

						//$existName = $recResult["firstname"];
						//$recResult = mysql_fetch_array($sql);
						$existName = $stmt->fetch(PDO::FETCH_ASSOC);

					//!!!$sql= "SELECT login FROM employee WHERE login = :filmID"; 
					/*
					$stmt = $pdo->prepare($sql);
					$stmt->bindParam(':login', $user_name, PDO::PARAM_INT); 
					$stmt->execute();
					$obj = $stmt->fetchObject();
					$existName = $obj->login;
					*/

							  //echo $existName."<br/>";


						if($existName=="") {

							  //echo "ins<br/>";
							/* guide
					   $sex 

					   $department 
					   $tax_status 
					   $mobile 
					   $address 
					   $email 

					   $birth 
					   $sss_no 
					   $tin_no 
					   $philhealth_no 
					   $pagibig_no 

					   $position 
					   $date_hired 					   
					   $skype_id 
					   $em_contact_person 
					   $em_contact_number 
					   $em_contact_address 

							*/

                 /*
			echo "INSERT into employee (lastname,firstname,gender,tax_status
			                                    ,cellphone,address1,email,dob
			                                    ,sss_no,tin_no,philhealth_no,pagibig_no
			                                    ,position,date_hired,skype_id
			                                    ,em_contact_person,em_contact_no

			                                    ,minit,fscan_id,password
			                                    ,ipsignup,loginip,ipupdated
			                                    ,nickname,gumi_email

			                                    ,em_contact_address,login)

				values (
				  '".$surname."','".$firstname."','".$sex."','".$tax_status."'
				  ,'".$mobile."','".$address."','".$email."','".$birth."'
				  ,'".$sss_no."','".$tin_no."','".$philhealth_no."','".$pagibig_no."'
				  ,'".$position."','".$date_hired."','".$skype_id."'
				  ,'".$em_contact_person."','".$em_contact_number."'
				  ,'".$surname."','0','G0m1Pa$$'
				  ,'127.0.0.1','127.0.0.1','127.0.0.1'
				  ,'".$firstname."','".$user_name."@gumi.ph'
				  ,'".$em_contact_address."','".$user_name."'
				) 

                 <br />";
			*/

							    $stmt = $pdo->prepare("INSERT INTO 
							    	        employee (lastname,firstname,gender,tax_status
							    	        	      ,cellphone,address1,email,dob
							    	        	      ,sss_no,tin_no,philhealth_no,pagibig_no
							    	        	      ,position,date_hired,skype_id
							    	        	      ,em_contact_person,em_contact_no

							    	        	      ,minit,fscan_id,password
							    	        	      ,ipsignup,loginip,ipupdated
							    	        	      ,nickname,gumi_email

							    	        	      ,em_contact_address,login) 
							    	        VALUES (:lastname,:firstname,:gender,:tax_status
							    	        	    ,:cellphone,:address1,:email,:dob
							    	        	    ,:sss_no,:tin_no,:philhealth_no,:pagibig_no
							    	        	    ,:position,:date_hired,:skype_id
							    	        	    ,:em_contact_person,:em_contact_no

							    	        	      ,:minit,:fscan_id,:password
							    	        	      ,:ipsignup,:loginip,:ipupdated
							    	        	      ,:nickname,:gumi_email


							    	        	    ,:em_contact_address,:login
							    	        	)");
							    $params = array(
							    	       ':lastname'=>$surname,
							    	       ':firstname'=>$firstname,
							    	       ':gender'=>$sex,
							    	       ':tax_status'=>$tax_status,
							    	       ':cellphone'=>$mobile,
							    	       ':address1'=>$address,
							    	       ':email'=>$email,
							    	       ':dob'=>$birth,
							    	       ':sss_no'=>$sss_no,
							    	       ':tin_no'=>$tin_no,							    	       
							    	       ':philhealth_no'=>$philhealth_no,
							    	       ':pagibig_no'=>$pagibig_no,
							    	       ':position'=>$position,
							    	       ':date_hired'=>$date_hired,
							    	       ':skype_id'=>$skype_id,
							    	       ':em_contact_person'=>$em_contact_person,
							    	       ':em_contact_no'=>$em_contact_number,

							    	       ':minit'=>$surname,
							    	       ':fscan_id'=>'0',
							    	       ':password'=>'G0m1Pa$$',
							    	       ':ipsignup'=>'127.0.0.1',
							    	       ':loginip'=>'127.0.0.1',
							    	       ':ipupdated'=>'127.0.0.1',
							    	       ':nickname'=>$firstname,
							    	       ':gumi_email'=>$gumi_user,

							    	       ':em_contact_address'=>$em_contact_address,
							    	       ':login'=>$user_name);
                                $stmt->execute($params) or die(print_r($stmt->errorInfo(), true));

							   }
							   else
							   {
							  //echo "upd<br/>";


							     $stmt = $pdo->prepare("UPDATE employee SET 
							     	      gender = ?, 
								          tax_status = ?,  
								          cellphone = ?,  
								          email = ?,  
							     	      lastname = ?, 
							     	      address1 = ?, 							     	      
							     	      dob = ?, 							     	      							     	      
							     	      sss_no = ?, 							     	      							     	      
							     	      tin_no = ?, 							     	      							     	      
							     	      philhealth_no = ?, 							     	      							     	      
							     	      pagibig_no = ?, 							     	      							     	      

							     	      position = ?, 							     	      							     	      
							     	      date_hired = ?, 							     	      							     	      
							     	      skype_id = ?, 							     	      							     	      

							     	      em_contact_no = ?, 							     	      							     	      
							     	      em_contact_person = ?, 							     	      							     	      
							     	      em_contact_address = ?, 							     	      							     	      

							              firstname = ?  

							              WHERE login = ?");							     
							     /*
							  echo "UPDATE employee SET lastname = ?, 
            firstname = ?  
            WHERE login = ".$user_name."<br/>";

							     $stmt = $pdo->prepare("UPDATE employee SET lastname = ?, 
								            firstname = ?,  
								            gender = ?,  

								            WHERE login = ?");


			$stmt->execute(array($surname,$firstname,$sex,$tax_status,$mobile,$address,$email,
				$birth,$sss_no,$tin_no,$philhealth_no,$pagibig_no,$position,$date_hired,$skype_id,
				$em_contact_person,$em_contact_number,$em_contact_address,$existName)); 

            */


					     $stmt->execute(array($sex,$tax_status,$mobile,$email
					     	,$surname,$address,$birth
                            ,$sss_no,$tin_no,$philhealth_no,$pagibig_no
                            ,$position,$date_hired,$skype_id
                            ,$em_contact_person,$em_contact_number,$em_contact_address
					     	,$firstname,$user_name)); 			

							   }

                          //echo $first_name . "<br />";                    
					    //--------------------------------------------------------------------

                        }
                        $pdo = null;


			     }
			     //redirect to reflect changes
				$this->redirect($this->getConfig()->get('base_url').'/employee/search');

			exit;

	}


	//--------------------------------------------------------------------------------------------------------

	public function executeRemoveAsAdmin()
	{
		$this->redirectUnless((array_key_exists('employee_id', $_POST)), $this->getConfig()->get('base_url'));

		$employeeModel = $this->loadModel('Employee');
		$employee = $employeeModel->getByID($_POST['employee_id']);

		if (NULL != $employee)
		{
			if ($employeeModel->update($employee['empid'], array('admin' => false)))
			{
				echo json_encode(array(
					'is_successful'   => true,
					'message'         => $employee['firstname'].' '.$employee['firstname'].' successfully removed as admin.'
				));
			}
			else
			{
				echo json_encode(array(
					'is_successful'   => false,
					'message'         => 'An unexpected error occurs.'
				));
			}
		}
		else
		{
			echo json_encode(array(
				'is_successful'   => false,
				'message'         => 'Invalid request'
			));
		}

		exit;
	}

	private function checkMandatoryFields(array $fieldsToCheck, array $fieldValues)
	{
		$fieldsWithErrors = array();

		foreach ($fieldsToCheck as $key)
		{
			if (
				!array_key_exists($key, $fieldValues) ||
				trim($fieldValues[$key]) == ''
			)
			{
				$fieldsWithErrors[] = $key;
			}
		}

		return $fieldsWithErrors;
	}
}