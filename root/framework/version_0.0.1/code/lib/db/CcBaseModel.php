<?php
/**
 * The main class of models.
 *
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
abstract class CcBaseModel
{
	/**
	 * The name of the database table.
	 *
	 * @var     CcPDO
	 */
	private $mPDO;

	/**
	 * The single instance of CcConfig
	 *
	 * @var     CcConfig
	 */
	private $mConfig;

	/**
	 * The table fields list.
	 */
	private $mTableFields = array();

	/**
	 * The name of the table
	 */
	private $mTableName = '';
	
	/**
	 * The primary field in the table.
	 */
	private $mPrimaryField = '';

	/**
	 * Constructor for the base model.
	 */
	public function __construct(CcPDO $pdo, CcConfig $config)
	{
		$this->mPDO = $pdo;
		$this->mConfig = $config;
	}

	/**
	 * Sets the table fields.
	 *
	 * @param      array     $fields      Associative array of fields with field name as the key and value as associative array with type, default value and is_autoincrement as key.
	 */
	public function setFields(array $fields, $primary_field)
	{
		if (!array_key_exists($primary_field, $fields))
		{
			// log error primary field has no definitions in $fields
			return false;
		}

		$this->mPrimaryField = $primary_field;
		$this->mTableFields = array();
		foreach ($fields as $key => $field)
		{
			if (is_array($field))
			{
				$this->mTableFields[$key] = array(
					'type'              => (array_key_exists('type', $field)) ? $field['type'] : 'int',
					'type_values'       => (array_key_exists('type_values', $field) && is_array($field['type_values'])) ? $field['type_values'] : array(),
					'is_autoincrement'  => (array_key_exists('is_autoincrement', $field) && ($key == $primary_field)) ? (bool)$field['is_autoincrement'] : false,
					'default_value'     => (array_key_exists('default_value', $field)) ? $field['default_value'] : '',
					'is_mandatory'      => (array_key_exists('is_mandatory', $field)) ? (bool)$field['is_mandatory'] : false
				);
			}
			else
			{
				// Log error 
				return false;
			}
		}

		return true;
	}

	/**
	 * Sets the table name.
	 *
	 * @param      string     
	 */
	public function setTableName($tableName)
	{
		$this->mTableName = $tableName;
	}

	/**
	 * Returns the table name set in this model object.
	 */
	public function getTableName()
	{
		return $this->mTableName;
	}

	/**
	 * Returns the PDO object used by this model.
	 */
	public function getPDO()
	{
		return $this->mPDO;
	}

	/**
	 * Creates a new entry in the database.
	 * 
	 * @return     mixed       The last inserted ID or -1 if the primary key set is autoincremented and true/false if not.
	 */
	public function create(array $props)
	{
		$fields = array();
		$values = array();
		foreach ($this->mTableFields as $fieldName => $tableField)
		{
			if ($fieldName == $this->mPrimaryField && $tableField['is_autoincrement'])
			{
				continue; // do not set autoincremented primary field.
			}

			if (array_key_exists($fieldName, $props))
			{
				$fields[] = $fieldName;
				switch ($tableField['type'])
				{
					case 'int':
					{
						$values[] = (int)$props[$fieldName];
						break;						
					}

					case 'bool':
					{
						$values[] = ($props[$fieldName]) ? 1 : 0;
						break;
					}

					case 'enum':
					{
						$type_values = $tableField['type_values'];
						if (in_array($props[$fieldName], $type_values))
						{
							$values[] = $props[$fieldName];
						}
						else if (in_array($tableField['default_value'], $type_values))
						{
							$values[] = $tableField['default_value'];
						}
						else
						{
							$values[] = '';	
						}

						break;
					}

					case 'string':
					case 'date':
					case 'datetime':
					case 'time':
					default:
					{
						$values[] = (string)$props[$fieldName];
					}
				}
				
			}
			else
			{
				if ($tableField['is_mandatory'])
				{
					if ($tableField['is_autoincrement'])
					{
						continue;
					}
					else
					{
						if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
						{
							openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
							syslog(LOG_WARNING, "The field name [$fieldName] is set as mandatory but no value passed.");
							closelog();
						}

						return ($this->mTableFields[$this->mPrimaryField]['is_autoincrement']) ? -1 : false;
					}
				}
				else
				{
					$values[] = $tableField['default_value'];
					$fields[] = $fieldName;
				}
			}
		}

		$sql = 'INSERT INTO `'.$this->mTableName.'` (';
		foreach ($fields as $i => $field)
		{
			$sql .= '`'.$field.'`';
			$sql .= ($i < (count($fields) - 1)) ? ', ' : '';
		}
		$sql .= ') VALUES (';
		foreach ($fields as $i => $field)
		{
			$sql .= '?';
			$sql .= ($i < (count($fields) - 1)) ? ', ' : '';
		}
		$sql .= ')';

		try
		{
			$sth = $this->mPDO->prepare($sql);
			$sth->execute($values);

			return ($this->mTableFields[$this->mPrimaryField]['is_autoincrement']) ? $this->mPDO->lastInsertId() : true;
		}
		catch (Exception $ex)
		{
			if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
			{
				openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
				syslog(LOG_WARNING, $ex->getFile().':'.$ex->getLine().' -- '.$ex->getMessage());
				closelog();
			}

			return ($this->mTableFields[$this->mPrimaryField]['is_autoincrement']) ? -1 : false;
		}
	}

	/**
	 * Updates the entry with the given ID.
	 *
	 * @param     int|string      $id     The value of the primary ID.
	 */
	public function update($id, array $props)
	{	
		$fields = array();
		$values = array();
		$tableFields = array_keys($this->mTableFields);
		foreach ($props as $fieldName => $fieldValue)
		{
			if (in_array($fieldName, $tableFields))
			{
				if ($this->mPrimaryField != $fieldName)
				{
					$fields[] = $fieldName;
					$tableField = $this->mTableFields[$fieldName];
					switch ($tableField['type'])
					{
						case 'int':
						{
							$values[] = (int)$props[$fieldName];
							break;						
						}

						case 'bool':
						{
							$values[] = ($props[$fieldName]) ? 1 : 0;
							break;
						}

						case 'enum':
						{
							$type_values = $tableField['type_values'];
							if (in_array($props[$fieldName], $type_values))
							{
								$values[] = $props[$fieldName];
							}
							else if (in_array($tableField['default_value'], $type_values))
							{
								$values[] = $tableField['default_value'];
							}
							else
							{
								$values[] = '';	
							}

							break;
						}

						case 'date':
						case 'datetime':
						case 'time':
						case 'string':
						default:
						{
							$values[] = (string)$props[$fieldName];
						}
					}
				}		
			}
			else
			{
				// log error there is a problem with the field name passed
				// return NULL;
			}
		}

		$sql = 'UPDATE `'.$this->mTableName.'` SET ';
		foreach ($fields as $i => $field)
		{
			$sql .= '`'.$field.'` = ?';
			$sql .= ($i < (count($fields) - 1)) ? ', ' : ' ';
		}
		$sql .= 'WHERE `'.$this->mPrimaryField.'` = ? ';

		$values[] = $id;

		try
		{
			$sth = $this->mPDO->prepare($sql);
			$sth->execute($values);

			return true;
		}
		catch (Exception $ex)
		{
			if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
			{
				openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
				syslog(LOG_WARNING, $ex->getFile().':'.$ex->getLine().' -- '.$ex->getMessage());
				closelog();
			}

			return false;
		}
	}

	/**
	 * Deletes the entry in the database.
	 */
	public function delete($primary_field_value)
	{
		$sql = 'DELETE FROM `'.$this->mTableName.'`  ';
		$sql .= 'WHERE `'.$this->mPrimaryField.'` = ? ';

		try
		{
			$sth = $this->getPDO()->prepare($sql);
			$sth->execute(array($primary_field_value));

			return true;
		}
		catch (Exception $ex)
		{
			if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
			{
				openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
				syslog(LOG_WARNING, $ex->getFile().':'.$ex->getLine().' -- '.$ex->getMessage());
				closelog();
			}

			return false;
		}
	}

	/**
	 * Returns all the entries in the table.
	 */
	public function getAll()
	{
		return $this->find(array());
	}

	/**
	 * Returns all the entries in the table.
	 */
	public function find(array $criteria)
	{
		$fields = array_keys($this->mTableFields);

		$sql = 'SELECT ';
		foreach ($fields as $i => $field)
		{
			$sql .= '`'.$field.'`';
			$sql .= ($i < (count($fields) - 1)) ? ', ' : ' ';
		}
		$sql .= 'FROM `'.$this->mTableName.'` ';

		$whereStringValueHash = array(
			'string'     => '',
			'values'     => array()
		);
		$whereConditions = (array_key_exists('where', $criteria)) ? $criteria['where'] : array();
		$whereStringValueHash = $this->generateWhereCondition($whereConditions, $whereStringValueHash);
		$values = $whereStringValueHash['values'];
		$sql .= (0 < count($values)) ? 'WHERE '.$whereStringValueHash['string'] : '';

		// order by
		if (array_key_exists('order_by', $criteria))
		{
			if (
				array_key_exists('field', $criteria['order_by']) &&
				array_key_exists('order', $criteria['order_by']) &&
				in_array($field, $fields)
			)
			{
				$sql .= ' ORDER BY `'.$criteria['order_by']['field'].'` ';
				$dir = in_array($criteria['order_by']['order'], array('desc', 'asc')) ? $criteria['order_by']['order'] : 'asc';
				$sql .= $dir;				
			}
			else
			{
				$temp_sql = ' ORDER BY ';
				$record_found = false;
				$order_by_len = count($criteria['order_by']);
				foreach ($criteria['order_by'] as $key => $order_by)
				{
					if (
						is_array($order_by) &&
						array_key_exists('field', $order_by) &&
						array_key_exists('order', $order_by)		
					)
					{
						$record_found = true;

						$temp_sql .= ' `'.$order_by['field'].'` ';
						$dir = in_array($order_by['order'], array('desc', 'asc')) ? $order_by['order'] : 'asc';
						$temp_sql .= $dir;
						$temp_sql .= ($key < ($order_by_len - 1)) ? ', ' : '';
					}
				}

				if ($record_found)
				{
					$sql .= $temp_sql;
				}
			}
		}

		// limit
		if (
			array_key_exists('limit', $criteria) && 
			array_key_exists('numrecords', $criteria['limit'])
		)
		{
			$sql .= ' LIMIT ';
			$sql .= (array_key_exists('offset', $criteria['limit'])) ? $criteria['limit']['offset'].',' : '';
			$sql .= $criteria['limit']['numrecords'];
		}

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

			return array(); // return empty array on error
		}
	}

	/**
	 * Returns the entry with value equal to the primary ID.
	 */
	public function getByID($id)
	{
		$results = $this->find(array(
			'where'      => array(
				'conditions'  => array(
					array(
						'field'      => $this->mPrimaryField,
						'condition'  => '=',
						'value'      => $id
					)
				) 
			)
		));

		if (is_array($results))
		{
			return (0  < count($results)) ? $results[0] : NULL;
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Truncates the data of the table.
	 */
	public function truncateTable()
	{	
		$sql = 'TRUNCATE TABLE `'.$this->getTableName().'`';

		try
		{
			$this->getPDO()->query($sql);
		}
		catch (Exception $ex)
		{
			if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
			{
				openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
				syslog(LOG_WARNING, $ex->getFile().':'.$ex->getLine().' -- '.$ex->getMessage());
				closelog();
			}
		}
	}

	/**
	 * Called for every single instantiated model to configure the table name and table fields.
	 */
	public function configure() {}

	/**
	 * Generates the where condition
	 */
	private function generateWhereCondition($whereCondition, $whereStringValueHash)
	{
		$validConditionOperators = array('=', '>', '<', '!=', '>=', '<=', 'LIKE', 'IN');
		$validRelationOperators = array('||', 'AND');
		$tableFields = array_keys($this->mTableFields);

		if (array_key_exists('conditions', $whereCondition) && is_array($whereCondition['conditions']) && 0 < count($whereCondition['conditions']))
		{
			$relation = (array_key_exists('relation', $whereCondition) && in_array($whereCondition['relation'], $validRelationOperators)) ? $whereCondition['relation'] : '||';
			$whereStringValueHash['string'] .= ' ( ';
			foreach ($whereCondition['conditions'] as $i => $condition)
			{
				if (array_key_exists('field', $condition) && array_key_exists('condition', $condition) && array_key_exists('value', $condition))
				{
					if (
						( 
							in_array($condition['field'], $tableFields) || 
							(
								array_key_exists('dont_quote_field', $condition) && 
								$condition['dont_quote_field']
							)
						) && 
						in_array($condition['condition'], $validConditionOperators)
					)
					{
						if ($condition['condition'] == 'IN')
						{
							$whereStringValueHash['string'] .= (array_key_exists('dont_quote_field', $condition) && $condition['dont_quote_field']) ? ' '.$condition['field'].' ' : ' `'.$condition['field'].'` ';
							$whereStringValueHash['string'] .= $condition['condition'].' ('.$condition['value'].') ';
						}
						else
						{
							$whereStringValueHash['values'][] = $condition['value'];
							$whereStringValueHash['string'] .= (array_key_exists('dont_quote_field', $condition) && $condition['dont_quote_field']) ? ' '.$condition['field'].' ' : ' `'.$condition['field'].'` ';
							$whereStringValueHash['string'] .= $condition['condition'].' ? ';
						}

						$whereStringValueHash['string'] .= ($i < count($whereCondition['conditions']) - 1 && 1 < count($whereCondition['conditions'])) ? ' '.$relation.' ' : '';	
					}
					else
					{
						// ignore non-existing fields
						if ($this->mConfig->get('__IN_DEV_MODE__') && $this->mConfig->get('__USE_SYSLOG__'))
						{
							openlog("myScriptLog", LOG_PID | LOG_PERROR, LOG_LOCAL0);
							syslog(LOG_WARNING, 'The field ['.$condition['field'].'] is not a valid table field.');
							closelog();
						}
					}
				}
				else
				{
					$whereStringValueHash = $this->generateWhereCondition($whereCondition, $whereStringValueHash);
				}
			}
			$whereStringValueHash['string'] .= ' ) ';
		}

		return $whereStringValueHash;
	}
}