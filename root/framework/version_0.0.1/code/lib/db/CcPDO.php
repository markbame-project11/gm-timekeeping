<?php
/**
 * The overriden PDO class.
 *
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
class CcPDO extends PDO
{
	/**
	 *
	 */
	public function prepare($statement, $options = array())
	{
		return parent::prepare($statement, $options);
	}	
}