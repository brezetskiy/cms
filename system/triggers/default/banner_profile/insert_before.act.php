<?php
/** 
 * Парсит время показа баннеров 
 * @package Pilot 
 * @subpackage Banner 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 

$integer_hours = array();

function add_hour($hour, &$stack) {
	if ($hour >= 0 && $hour <= 23 && !in_array($hour, $stack)) {
		$stack[] = $hour;
	}
}

$showtime = $this->NEW['show_hours'];
$hours = preg_split("~[\s\t,;]+~", $showtime, -1, PREG_SPLIT_NO_EMPTY);

/**
 * Если время не указано - показы круглосуточно
 */
if (count($hours)==0) {
	$hours = array('0-23');
}

reset($hours); 
while (list(,$row) = each($hours)) { 
	if (is_numeric($row)) {
		add_hour($row, $integer_hours);
	} elseif (preg_match("~^([0-9]+)\-([0-9]+)$~", $row, $match)) {
		for ($i=$match[1];$i<=$match[2];$i++) {
			add_hour($i, $integer_hours);
		}
	}
}

sort($integer_hours);

$insert_values = array();
$intervals = array();
$interval_start = null;
$last_hour = null;
reset($integer_hours); 
while (list($index,$row) = each($integer_hours)) { 
	$row = intval($row);
	$insert_values[] = "('".$this->NEW['id']."', '$row')";
	
	/**
	 * Первый пункт
	 */
	if ($interval_start === null) {
		$interval_start = $row;
		continue;
	}
	
	if ($integer_hours[$index-1]+1 == $row) {
		// предыдущий час = текущий - 1 : продолжение интервала
//		echo $integer_hours[$index-1]." = $row -1 : continue interval<br>";
		continue;
	} else {
		// разрыв интервала
		if ($integer_hours[$index-1] == $interval_start) {
			// интервал из одного часа
			$intervals[] = $interval_start;
		} else {
			// интервал из нескольких часов
			$intervals[] = $interval_start.'-'.$integer_hours[$index-1];
		}
		$interval_start = $row;
	}
}

if ($interval_start == end($integer_hours)) {
	$intervals[] = $interval_start;
} else {
	$intervals[] = $interval_start.'-'.end($integer_hours);
}

$this->NEW['show_hours'] = implode(',', $intervals);

$DB->query("LOCK TABLES banner_profile_hour WRITE");
$DB->delete("DELETE FROM banner_profile_hour WHERE profile_id = '".$this->NEW['id']."'");
$DB->insert("INSERT IGNORE INTO banner_profile_hour (profile_id, hour) VALUES".implode(",", $insert_values));
$DB->query("UNLOCK TABLES");

?>