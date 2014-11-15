<?php

/**
 * The model for an holiday.
 */
class HolidayModel extends CcBaseModel
{
	/**
	 * Returns the holidays between $start_date and $end_date but as associative array with keys equal to date
	 */
	public function getHolidaysBetweenAsAssoc($start_date, $end_date)
	{
		$ret_val = array();

		return $ret_val;
		
		// $holidays = $this->getHolidaysBetween($start_date, $end_date);

		// foreach ($holidays as $holiday)
		// {
		// 	$ret_val[$holiday['date']] = $holiday;
		// }

		// return $ret_val;
	}

	/**
	 * Returns the holidays between $start_date and $end_date
	 */
	public function getHolidaysBetween($start_date, $end_date)
	{
		return $this->find(array(
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
	}

	/**
	 * Returns the holidays greater than the given $date.
	 */
	public function getHolidaysGreaterThanEqual($date)
	{
		return $this->find(array(
			'where' => array(
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '>=',
						'value'      => $date
					)
				)
			)
		));
	}

	/**
	 * Returns the holidays that are equal to the given $date.
	 */
	public function getHolidayOn($date)
	{
		$holidays = $this->find(array(
			'where' => array(
				'conditions'   => array(
					array(
						'field'      => 'date',
						'condition'  => '=',
						'value'      => $date
					)
				)
			)
		));

		return (0 < count($holidays)) ? $holidays[0] : NULL;
	}

	/**
	 * @see CcBaseModel::configure()
	 */
	public function configure()
	{
		$fields = array(
			'id'        => array(
				'type'              => 'int',
				'is_autoincrement'  => true
			),
			'date'      => array(
				'type'              => 'string',
				'default_value'     => '0000-00-00'
			),
			'name'      => array(
				'type'              => 'string',
				'default_value'     => ''
			)
		);

		$this->setTableName('holidays');
		$this->setFields($fields, 'id');
	}
}