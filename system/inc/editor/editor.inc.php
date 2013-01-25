<?php
/**
 * Парсер таблиц стилей
 * @package Pilot
 * @subpackage Editor
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Парсер таблиц стилей с комментариями
 *
 * @param string $file
 * @return array
 */
function parse_css($file) {
	if (!is_file($file)) {
		return array();
	}
	
	$return = array();
	
	preg_match_all("/\/\*.+@name[\s\n\r\t]+([^\n\r]+)[\n\r\*]+\*\/[\s\n\r\t]+([a-z0-9_]*)(?:\.([a-z0-9_]+))?[\s\n\r\t]*\{[\s\n\r\t]([^\}]+)\}/isU", file_get_contents($file), $matches);
	reset($matches);
	while(list($index,) = each($matches[1])) {
		$return[$index]['title'] = $matches[1][$index];
		$return[$index]['element'] = (empty($matches[2][$index])) ? 'SPAN' : strtoupper($matches[2][$index]);
		$return[$index]['class'] = $matches[3][$index];
		$return[$index]['element_class'] = (!empty($matches[3][$index])) ? $matches[2][$index].'.'.$matches[3][$index] : $matches[2][$index];
		$return[$index]['style'] = $matches[4][$index];
	}
	
	return $return;
}

?>