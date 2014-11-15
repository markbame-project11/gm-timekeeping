<?php

/**
 * The base logger
 *
 * @author       Antonio P. Cruda Jr. <antonio.cruda@gmail.com>
 */
abstract class CcBaseLogger
{
	/**
	 * Logs the given string
	 */
	abstract public function log($string, $log_level = 1);
}