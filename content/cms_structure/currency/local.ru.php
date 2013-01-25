<?php
/** 
 * Страница изменения курса валют 
 * @package Currency
 * @subpackage Pilot 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 


/**
 * Период - с
 */
$dateshow = globalVar($_GET['date'], date('d.m.Y', mktime(0, 0, 0, date("m")  , date("d"), date("Y"))));

if ($dateshow === false) {
	$dateshow = date('d.m.Y');
}
$TmplContent->set('date', $dateshow);

$showdate_str = date('Y', strtotime($dateshow)).'-'.date('m', strtotime($dateshow)).'-'.date('d', strtotime($dateshow));


$date = mktime(23,59,59,date('m', strtotime($dateshow)), date('d', strtotime($dateshow)), date('Y', strtotime($dateshow)));

/**
 * Вывод курса валют на запрошенный период
 */
$query = "
	SELECT
		id,
		code
	FROM currency_list
	WHERE active='true'
";
$currency_list = $DB->query($query, 'id');

$query = "
	SELECT
		tb_rate.currency_id, 
		tb_rate.amount,
		tb_rate.rate,
		DATE_FORMAT(tb_rate.date, '".LANGUAGE_DATE_SQL."') AS date
	FROM currency_nbu_rate AS tb_rate
	WHERE date<=FROM_UNIXTIME($date)
";
$currency_nbu = $DB->query($query, 'currency_id');

$query = "
	SELECT
		tb_rate.currency_id,
		tb_rate.amount,
		tb_rate.rate,
		DATE_FORMAT(tb_rate.date, '".LANGUAGE_DATE_SQL."') AS date
	FROM currency_cbr_rate AS tb_rate
	WHERE date<=FROM_UNIXTIME($date)
";
$currency_cbr = $DB->query($query, 'currency_id');

if (empty($currency_nbu) && empty($currency_cbr)) {
	$TmplContent->set('show_rates', false);
} else {
	$TmplContent->set('show_rates', true);
}

// Выводим таблицу с курсами
$counter = 0;
reset($currency_list);
while(list($currency_id, $row) = each($currency_list)) {
	$counter++;
	$row['class'] = ($counter % 2) ? 'odd' : 'even';
	
	if (isset($currency_nbu[$currency_id])) {
		$row['nbu_amount'] = $currency_nbu[$currency_id]['amount'];
		$row['nbu_rate'] = $currency_nbu[$currency_id]['rate'];
		$row['nbu_date'] = $currency_nbu[$currency_id]['date'];
	} else {
		$row['nbu_date'] = $row['nbu_amount'] = $row['nbu_rate'] = '-';
	}
	
	if (isset($currency_cbr[$currency_id])) {
		$row['cbr_amount'] = $currency_cbr[$currency_id]['amount'];
		$row['cbr_rate'] = $currency_cbr[$currency_id]['rate'];
		$row['cbr_date'] = $currency_cbr[$currency_id]['date'];
	} else {
		$row['cbr_date'] = $row['cbr_amount'] = $row['cbr_rate'] = '-';
	}
	
	$TmplContent->iterate('/row/', null, $row);
}





/**
 * Выводим форму установки кросс-курсов
 */
$query = "
	SELECT 	
		distinct(CONCAT(currency_from_id, '-', currency_to_id)) AS `key`,
		currency_from_id,
		currency_to_id,
		rate,
		admin_login,
		DATE_FORMAT(dtime, '".LANGUAGE_DATETIME_SQL."') AS dtime
	FROM currency_rate_current
	order by dtime 
";
$cross_rate = $DB->query($query, 'key');

$query = "
	select 
		admin_login,
		DATE_FORMAT(dtime, '".LANGUAGE_DATETIME_SQL."') AS dtime
	from currency_rate
	where dtime=(select max(dtime) from currency_rate)
";
$lastupdate = $DB->query($query);
if ($DB->rows > 0) {
	$TmplContent->set('crossrate_definer', reset($lastupdate));
}

$TmplContent->setGlobal("current_currency", CURRENCY_CROSS_CURRENCY);

$counter = 0;
$currency_cross_list = $currency_list;
reset($currency_list);
while(list($currency_from_id,$row) = each($currency_list)) {
	$counter++;
	$row['class'] = ($counter % 2) ? 'odd' : 'even';
	
	$TmplContent->iterate('/header_column/', null, $row);
	$tmpl_row = $TmplContent->iterate('/crossrow/', null, $row);
	
	reset($currency_cross_list); 
	while (list($currency_to_id, $crossrow) = each($currency_cross_list)) {
		
		$crossrow['currency_from_id'] = $currency_from_id;
		$crossrow['currency_to_id']   = $currency_to_id;
		
		if (isset($cross_rate[$currency_from_id.'-'.$currency_to_id])) {
			$crossrow['rate'] = $cross_rate[$currency_from_id.'-'.$currency_to_id]['rate'];
			$crossrow['admin_login'] = $cross_rate[$currency_from_id.'-'.$currency_to_id]['admin_login'];
			$crossrow['dtime'] = $cross_rate[$currency_from_id.'-'.$currency_to_id]['dtime'];
		}
			if (isset($crossrow['rate']) && $crossrow['rate'] == 0) {
			$crossrow['rate'] = '';
		}
		$TmplContent->iterate('/crossrow/crossrate/', $tmpl_row, $crossrow);
	}
}

$TmplContent->set('cross_table_colspan', 2 + count($currency_list));
$TmplContent->set('crossrate_default_currency', $DB->result("SELECT code FROM currency_list WHERE id = '".CURRENCY_CROSS_CURRENCY."'"));

//частоь статистики

?>