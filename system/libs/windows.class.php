<?php
/**
* Заглушки функции, которые не реализованы на платформе Windows
* @package Pilot
* @subpackage CMS
* @version 5.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/


/**
 * Заглушка для комманды getrusage
 * @ignore 
 * @return array
 */
function getrusage() {
	return array(
		'ru_nswap' => 0,
		'ru_majflt' => 0,
		'ru_utime.tv_sec' => 0,
		'ru_utime.tv_usec' => 0,
		'ru_stime.tv_sec' => 0,
		'ru_stime.tv_usec' => 0
	);
}

?>