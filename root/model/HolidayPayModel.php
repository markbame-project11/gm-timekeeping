<?php

/**
 * The model for a holiday pay.
 */
class HolidayPayModel extends CcBaseModel
{
	/**
	 * Returns the holiday pays in between the given dates.
	 *
	 *  Return value
	 *  { <date> : { <employee id> : { 'type' : <holiday type>, 'name' : <holiday name> }, ... }, ... }
	 */
	public function getHolidayPayForEmployeeBetween($employee_id, $start_date, $end_date)
	{
		$sql = "SELECT `holidays`.`id`, `holidays`.`date`, `holidays`.`name`, `holidays`.`type`, `holiday_pay`.`employee_id` FROM `holiday_pay` ";
		$sql .= "INNER JOIN `holidays` ON `holiday_pay`.`holiday_id` = `holidays`.`id` ";
		$sql .= "AND `holidays`.`date` >= ? ";
		$sql .= "AND `holidays`.`date` <= ? ";
		$sql .= "AND `holiday_pay`.`employee_id` = ? ";

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array($start_date, $end_date, $employee_id));

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			$ret_val = array();
			foreach ($results as $result)
			{
				if (!array_key_exists($result['date'], $ret_val))
				{
					$ret_val[$result['date']] = array();
				}

				$ret_val[$result['date']] = array(
					'type'     => $result['type'],
					'name'     => $result['name'],
				);
			}

			return $ret_val;
		}
		catch (Exception $ex)
		{
			return array();
		}
	}

	/**
	 * Returns the holiday pays in between the given dates.
	 *
	 *  Return value
	 *  { <date> : { <employee id> : { 'type' : <holiday type>, 'name' : <holiday name> }, ... }, ... }
	 */
	public function getHolidayPayForEmployeesBetween($start_date, $end_date)
	{
		$sql = "SELECT `holidays`.`id`, `holidays`.`date`, `holidays`.`name`, `holidays`.`type`, `holiday_pay`.`employee_id` FROM `holiday_pay` ";
		$sql .= "INNER JOIN `holidays` ON `holiday_pay`.`holiday_id` = `holidays`.`id` ";
		$sql .= "AND `holidays`.`date` >= ? ";
		$sql .= "AND `holidays`.`date` <= ? ";

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array($start_date, $end_date));

			$results = $sth->fetchAll(PDO::FETCH_ASSOC);
			$ret_val = array();
			foreach ($results as $result)
			{
				if (!array_key_exists($result['date'], $ret_val))
				{
					$ret_val[$result['date']] = array();
				}

				$ret_val[$result['date']][$result['employee_id']] = array(
					'type'     => $result['type'],
					'name'     => $result['name'],
				);
			}

			return $ret_val;
		}
		catch (Exception $ex)
		{
			return array();
		}
	}

	/**
	 * Create or update holiday for the given employee on the given holiday
	 */
	public function addHolidayPayFor($holiday_id, $employee_id)
	{
		$holidays = $this->find(array(
			'where' => array(
				'relation'     => 'AND',
				'conditions'   => array(
					array(
						'field'      => 'holiday_id',
						'condition'  => '=',
						'value'      => $holiday_id
					),
					array(
						'field'      => 'employee_id',
						'condition'  => '=',
						'value'      => $employee_id
					)
				)
			)
		));

		if (0 == count($holidays))
		{
			return $this->create(array(
				'holiday_id'   => $holiday_id,
				'employee_id'  => $employee_id
			));
		}
		else
		{
			return true;
		}
	}

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'holiday_id'       => array(
				'type'              => 'int',
				'default_value'     => 0
			),
			'employee_id'      => array(
				'type'              => 'int',
				'default_value'     => 0
			)
		);

		$this->setTableName('holiday_pay');
		$this->setFields($fields, 'holiday_id');
	}
}