<?php
/**
 * Формирует условие WHERE в SQL запросах
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */


class sqlWhere {

	/**
	 * Добавляет where условие в SQL запрос
	 *
	 * @param string $field
	 * @param mixed $value
	 * @param string $function
	 * @return string
	 */
	static function equal($field, $value, $alternative = '') {
		if (empty($value) && !empty($alternative)) {
			return "AND $alternative";
		} elseif (empty($value)) {
			return '';
		} elseif (is_array($value)) {
			return " AND $field in ('".implode("','", $value)."')";
		} else {
			return " AND $field='$value'";
		}
	}
	
	static function boolean($boolean, $true, $false) {
		if ($boolean && !empty($true)) {
			return $true;
		} elseif (!$boolean && !empty($false)) {
			return $false;
		} else {
			return '';
		}
	}
	
	static function between($field, $from, $to) {
		$return = "";
		if (!empty($from)) $return .= " AND $field >= $from";
		if (!empty($to)) $return .= " AND $field <= $to";
		return $return;
	}
	
}


?>