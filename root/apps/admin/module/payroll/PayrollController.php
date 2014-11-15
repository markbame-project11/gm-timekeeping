<?php

/**
 * The payroll controller class.
 */
class PayrollController extends BaseAdminController
{	
	public function executeIndex()
	{
		$payrollModel = $this->loadModel('Payroll');

		$this->payrolls = $payrollModel->getPayrollsOnYear(date('Y'));
	}

	public function executeGenerate()
	{
		$employeeModel = $this->loadModel('Employee');
		$payrollModel = $this->loadModel('Payroll');
		$employeePayrollModel = $this->loadModel('EmployeePayroll');

		if (strtolower($_SERVER['REQUEST_METHOD']) == 'post')
		{
			$this->fields = $_POST;


			$payroll_id = $payrollModel->create(array(
				'start_date'                 => $_POST['start_date'],
				'end_date'                   => $_POST['end_date'],
				'payroll_date'               => $_POST['payroll_date']
			));

			if ($payroll_id > 0)
			{

				$employees = $employeeModel->getPayrollEmployees(); //getByEmployeeIDgross_pay();
				foreach ($employees as $employee)
				{
					$empgross_pay = $employeeModel->getgrosspaybyempid($employee['empid']); //$employeePayrollModel->getgrosspaybyempid($employee['empid']);					
					$employeePayrollModel->create(array(
						'payroll_id'                => $payroll_id,
						'employee_id'               => $employee['empid'],
						'gross_pay'                 => $empgross_pay, //$this->tks_GetGrossPay($employee['empid']), //'10000'
						'has_sss_deduction'         => array_key_exists('sss_checkbox', $_POST) ? 1 : 0,
						'has_philhealth_deduction'  => array_key_exists('philhealth_checkbox', $_POST) ? 1 : 0,
						'has_pagibig_deduction'     => array_key_exists('pagibig_checkbox', $_POST) ? 1 : 0,
					));
                    //$employee['empid'] . ' - ' . 
                    //echo $employee['empid']  . ' - ' . $empgross_pay . '<br />';

				}
			}

            //for debugging 03 Nov 2014
            //!!exit;

			$this->redirect($this->getConfig()->get('base_url').'/payroll/view?id='.$payroll_id);
		}
		else
		{
			$this->fields = array(
				'start_date'   => '',
				'end_date'     => '',
				'payroll_date' => ''
			);
		}
	}
    //-----------------------------------------------------------------------
      /*
         Gets individual GrossPay per employee for inclusion in
         the report generation
      */
    public function tks_GetGrossPay($empid)
     {
		$payrollModel = $this->loadModel('Payroll');
		$employeePayrollModel = $this->loadModel('EmployeePayroll');     	
		$this->employeePayrolls = $employeePayrollModel->getgrosspaybyempid($empid);
     }
    //-----------------------------------------------------------------------

	public function executeView()
	{
		$payrollModel = $this->loadModel('Payroll');
		$employeePayrollModel = $this->loadModel('EmployeePayroll');

		$this->redirectUnless((array_key_exists('id', $_GET)), $this->getConfig()->get('base_url').'/payroll');
		$this->payroll = $payrollModel->getById($_GET['id']);
		$this->redirectUnless((array_key_exists('id', $_GET)), $this->getConfig()->get('base_url').'/payroll');

		$this->employeePayrolls = $employeePayrollModel->getByPayrollIDJoinEmployees($_GET['id']);
	}

	public function executeRunGenerateSpreadsheet()
	{
		$payrollModel = $this->loadModel('Payroll');

		$this->redirectUnless((array_key_exists('id', $_GET)), $this->getConfig()->get('base_url').'/payroll');
		$payroll = $payrollModel->getById($_GET['id']);
		$this->redirectUnless(($payroll != NULL), $this->getConfig()->get('base_url').'/payroll');

		// if the generate spread task doesn't respond after 5 minutes assume that there is a problem
		// and then change script_is_running to 0. 
		if ($payroll['script_is_running'] == 1)
		{
			if ((time() - strtotime($payroll['script_run_start'])) > 600)
			{
				$payrollModel->update($payroll['id'], array(
					'script_is_running'  => 0
				));

				$this->runTask('generate_spreadsheet', array('id' => $payroll['id']));

				echo json_encode(array(
					'is_successful' => true,
					'message'       => 'Generating spreadsheet is now running.' 
				));
			}
			else
			{
				echo json_encode(array(
					'is_successful' => false,
					'message'       => 'The script generator is already running.' 
				));
			}
		}
		else
		{
			$this->runTask('generate_spreadsheet', array('id' => $payroll['id']));

			echo json_encode(array(
				'is_successful' => true,
				'message'       => 'Generating spreadsheet is now running.' 
			));
		}

		exit;
	}

	public function executeCheckSpreadsheetGeneration()
	{
		$payrollModel = $this->loadModel('Payroll');

		$this->redirectUnless((array_key_exists('id', $_GET)), $this->getConfig()->get('base_url').'/payroll');
		$payroll = $payrollModel->getById($_GET['id']);
		$this->redirectUnless(($payroll != NULL), $this->getConfig()->get('base_url').'/payroll');
		
		if ($payroll['script_is_running'] == 1)
		{
			if ((time() - strtotime($payroll['script_run_start'])) > 600)
			{
				$payrollModel->update($payroll['id'], array(
					'script_is_running'  => 0
				));

				echo json_encode(array(
					'is_running'           => false,
					'has_reached_timeout'  => true,
					'test'                 => (time() - strtotime($payroll['script_run_start']))
				));
			}
			else
			{
				echo json_encode(array(
					'is_running' => true
				));
			}
		}
		else
		{
			echo json_encode(array(
				'is_running'          => false,
				'has_reached_timeout' => false,
				'download_url'        => $payroll['file_url']
			));
		}

		exit;
	}

	public function executeTest()
	{
		// $config = $this->getConfig();

		// // Create new PHPExcel object
		// $objPHPExcel = new PHPExcel();

		// // use template
		// $objReader = new PHPExcel_Reader_Excel5();
		// $objPHPExcel = $objReader->load($this->getConfig()->get('__APP_DIR__').'/data/payroll_template.xls');

		// $objPHPExcel->setActiveSheetIndex(6);
		// $objWorksheet = $objPHPExcel->getActiveSheet();

		// // $data = array();
		// // foreach ($objWorksheet->getRowIterator() as $row)
		// // {
		// // 	$cellIterator = $row->getCellIterator();
		// // 	$cellIterator->setIterateOnlyExistingCells(false);

		// // 	$d = array();
		// // 	foreach ($cellIterator as $cell)
		// // 	{
		// // 		$d[] = $cell->getValue();
		// // 	}

		// // 	$data[] = $d;
		// // }

		// $data = array();
		// $data = array(
		// 	's'  => array(),
		// 	'm1' => array(),
		// 	'm2' => array(),
		// 	'm3' => array(),
		// 	'm4' => array()
		// );

		// $tax_status = array('s', 'm1', 'm2', 'm3', 'm4');
		// $tax_status_len = count($tax_status);

		// for ($j = 0; $j < $tax_status_len; $j++)
		// {
		// 	for ($i = 0; $i < 8; $i++)
		// 	{
		// 		$data[$tax_status[$j]][] = array(
		// 			'start_range'      => $objWorksheet->getCellByColumnAndRow(5 + ($i * 3), 38 + $j)->getValue(),
		// 			'base_tax'         => $objWorksheet->getCellByColumnAndRow(3 + ($i * 3), 34)->getValue(),
		// 			'add_percentage'   => $objWorksheet->getCellByColumnAndRow(4 + ($i * 3), 35)->getValue(),
		// 		);
		// 	}
		// }

		// echo "return array(\n";
		// foreach ($data as $key => $dt)
		// {
		// 	echo "\t'".$key."' => array(\n";
		// 	foreach ($dt as $d)
		// 	{
		// 		echo "\t\tarray(\n";
		// 		echo "\t\t\t'start_range'        => ".$d['start_range'].", \n";
		// 		echo "\t\t\t'base_tax'           => ".$d['base_tax'].", \n";
		// 		echo "\t\t\t'add_percentage'     => ".$d['add_percentage']." \n";
		// 		echo "\t\t),\n";
		// 	}
		// 	echo "\t),\n";
		// }
		// echo ");";

		// exit;

		// Pass to writer and output as needed
		// $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		// $objWriter->save($config->get('__WEB_DIR__').$config->get('upload_directory').'/testexcelfile.xlsx');
		// exit;

		$this->loadHelpers(array('payroll'));

		var_dump(get_tax_object_for_half_month_pay('26392.56', 's'));

		exit;
	}
}