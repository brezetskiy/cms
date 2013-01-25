<?php
//x($_REQUEST);
$day = globalVar($_REQUEST['date']['day'], date('d'));
$month = globalVar($_REQUEST['date']['month'], date('m'));
$year = globalVar($_REQUEST['date']['year'], date('Y'));
$date = mktime(23,59,59,$month, $day, $year);

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

$TmplContent = new Template('content/cms_structure/currency/nbucbr');
$TmplContent->set('date', date(LANGUAGE_DATE, $date));
$TmplContent->set('day', date('d', $date));
$TmplContent->set('month', date('m', $date));
$TmplContent->set('year', date('Y', $date));

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
$_RESULT['currency'] = $TmplContent->display();
?>