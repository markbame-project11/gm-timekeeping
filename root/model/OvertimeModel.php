<?php

/**
 * The model for overtimes.
 */
class OvertimeModel extends CcBaseModel
{
	/**
	 * Returns the overtimes between the given set of dates.
	 * 
	 * @return     associative array         Structure for return value { <date> : [ <employee id>, ... ], ... }
	 */
	public function getEmployeeOvertimeBetween($employee_id, $start_date, $end_date)
	{
		$results = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'date',
						'condition'  => '<=',
						'value'      => $end_date
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				)
			)
		));

		$ret_val = array();
		foreach ($results as $result)
		{
			$ret_val[$result['date']] = $result['employee_id'];
		}

		return $ret_val;
	}

	/**
	 * Returns the overtimes between the given set of dates.
	 * 
	 * @return     associative array         Structure for return value { <date> : [ <employee id>, ... ], ... }
	 */
	public function getOvertimeBetweenGroupedByDate($start_date, $end_date)
	{
		$results = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '>=',
						'value'      => $start_date
					),
					array(
						'field'      => 'date',
						'condition'  => '<=',
						'value'      => $end_date
					)
				)
			)
		));

		$ret_val = array();
		foreach ($results as $result)
		{
			if (!array_key_exists($result['date'], $ret_val))
			{
				$ret_val[$result['date']] = array();
			}

			$ret_val[$result['date']][] = $result['employee_id'];
		}

		return $ret_val;
	}

	/**
	 * Returns the overtimes between the given set of dates.
	 * 
	 * @return     associative array         Structure for return value { [ <overtime object>, ... ] }
	 */
	public function getOvertimeBetween($start_date, $end_date)
	{
		$sql = 'SELECT `'.$this->getTableName().'`.*, employee.lastname, employee.firstname ';
		$sql .= 'FROM `'.$this->getTableName().'` INNER JOIN `employee` ON (`'.$this->getTableName().'`.`employee_id` = `employee`.`empid`) ';
		$sql .= 'WHERE `'.$this->getTableName().'`.`date` >= ? AND `'.$this->getTableName().'`.`date` <= ? ';

		$values = array($start_date, $end_date);

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute($values);

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			
			return $results;
		}
		catch (Exception $ex)
		{
			if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
			{
				openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
				syslog(LOG_WARNING, $ex->getFile().':'.$ex->getLine().' -- '.$ex->getMessage());
				closelog();
			}

			return array();
		}
	}

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'id'             => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'employee_id'      => array(
				'type'              => 'int',
				'default_value'     => '0'
			),
			'date'   => array(
				'type'              => 'string',
				'default_value'     => '0000-00-00'
			),
			'notes'  => array(
				'type'              => 'string',
				'default_value'     => ''
			)
		);

		$this->setTableName('overtime');
		$this->setFields($fields, 'id');
	}
}