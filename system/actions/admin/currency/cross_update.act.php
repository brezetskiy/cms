<?php
/** 
 * Обновление кросс-курса валют 
 * @package Pilot 
 * @subpackage Currency 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */
$currencies = globalVar($_REQUEST['currency'], array());
$crossrate = globalVar($_REQUEST['crossrate'], array());
$values = $current_values = array();
$cross_rate = array();

// Добавлем в систему переданные валюты
$query = "
	SELECT
		CONCAT(currency_from_id, '-', currency_to_id) AS `key`,
		currency_from_id,
		currency_to_id,
		rate,
		admin_login,
		DATE_FORMAT(dtime,'".LANGUAGE_DATETIME_SQL."') AS dtime
	FROM currency_rate_current
";
$check_rate = $DB->query($query, 'key');

reset($crossrate); 
while (list($currency_from_id, $row) = each($crossrate)) { 
	reset($row);
	while (list($currency_to_id, $rate) = each($row)) {
		$rate = str_replace(',', '.', round($rate, 4));
		if ($rate < 0) $rate = 0;
		if ($currency_from_id == $currency_to_id) continue;
		if (isset($check_rate[$currency_from_id.'-'.$currency_to_id]['rate']) && $check_rate[$currency_from_id.'-'.$currency_to_id]['rate'] != $rate) {
			$values[] = "('".intval($currency_from_id)."', '".intval($currency_to_id)."', '$rate', NOW(), '".$_SESSION['auth']['login']."')";
		}
		$cross_rate[$currency_from_id][$currency_to_id] = $rate;
		$current_values[] = "('".intval($currency_from_id)."', '".intval($currency_to_id)."', '$rate', NOW(), '".$_SESSION['auth']['login']."')";
	}
}

$cross_rate[CURRENCY_CROSS_CURRENCY][CURRENCY_CROSS_CURRENCY] = 1;  

$currencies_from = array_keys($crossrate);
$currencies_to   = array_keys($crossrate);

reset($currencies_from);
while(list(, $from) = each($currencies_from)){
	
	reset($currencies_to);  
	while(list(, $to) = each($currencies_to)){ 
		
		if($from == $to){
			continue; 
		} elseif(isset($cross_rate[$from][$to])){
			continue;
		} else{
			$rate = $cross_rate[$from][CURRENCY_CROSS_CURRENCY] / $cross_rate[$to][CURRENCY_CROSS_CURRENCY];
		}
		
		$current_values[] = "('".intval($from)."', '".intval($to)."', '".str_replace(',', '.', round($rate, 4))."', NOW(), '".$_SESSION['auth']['login']."')";
	}
}


/**
 * Записываем историю кросс-курсов
 */
if (count($current_values) > 0) {
	$query = "LOCK TABLES currency_rate_current WRITE, currency_rate WRITE";
	$DB->query($query);
	
	$query = "INSERT INTO currency_rate (currency_from_id, currency_to_id, rate, dtime, admin_login) VALUES".implode(',', $current_values);
	$DB->insert($query);
	
	$query = "DELETE FROM currency_rate_current";
	$DB->delete($query);
	
	$query = "INSERT INTO currency_rate_current (currency_from_id, currency_to_id, rate, dtime, admin_login) VALUES".implode(',', $current_values);
	$DB->insert($query);
	
	$query = "UNLOCK TABLES"; 
	$DB->query($query);
}

?>