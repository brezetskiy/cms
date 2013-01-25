<?php
/**
* Получение курса валют ЦБРФ с сайта xml.delta-x.com.ua
*
* @package Pilot
* @subpackage Currency
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
* @cron 0 1 * * *
*/

/**
 * Определяем интерфейс
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

// Устанавливаем правильную рабочую директорию
chdir(dirname(__FILE__));

/**
* Конфигурационный файл
*/
require_once('../../config.inc.php');

$DB = DB::factory('default');

$DB->query("SHOW TABLES LIKE 'currency_cbr_rate'");

if ($DB->rows != 1) {
	echo "[e] CBR currency module not installed\n";
	exit;
}

if(!$xml_currency = @file_get_contents('http://xml.delta-x.com.ua/cbr.php')) {
	echo "[e] Unable to retrive XML document\n";
	exit;
}

$xml = new XMLToArray();

$parsed = $xml->parseXml($xml_currency);

if (!isset($parsed[0]['_elements']) || !is_array($parsed[0]['_elements']) || count($parsed[0]['_elements']) == 0) {
	echo "[e] CBR currency: XML document doesn't contain currency data\n";
	x($parsed);
	exit;
}

reset($parsed[0]['_elements']);
while (list(,$row)=each($parsed[0]['_elements'])) {
	if ($row['_name'] != 'currency' || !isset($row['id'])) {
		echo "[w] Unknown node format. Node name: ".$row['_name']."\n";
		continue;
	}
	
	$currency = array(
		'date' => '',
		'code' => '',
		'description' => '',
		'amount' => '',
		'rate' => '',
		'id' => $row['id']
	);
	
	reset($row['_elements']);
	while (list(,$nodes)=each($row['_elements'])) {
		$currency[ $nodes['_name'] ] = $nodes['_data'];
	}
	
	if (empty($currency['date']) || empty($currency['code']) || empty($currency['amount']) ||
		empty($currency['rate']) || empty($currency['id'])) {
		
		echo "[e] Bad currency format\n";
		x($currency);
		continue;
	}
	
	echo "[i] Update currency ".$currency['description']."\n";
	
	$query = "
		INSERT INTO currency_list
		SET
			id = '".$currency['id']."',
			code = '".$currency['code']."',
			name = '".iconv('UTF-8', LANGUAGE_CHARSET, $currency['description'])."'
		ON DUPLICATE KEY UPDATE
			name = '".iconv('UTF-8', LANGUAGE_CHARSET, $currency['description'])."'
	"; 
	$DB->insert($query);
	
	$query = "
		REPLACE INTO currency_cbr_rate
		SET
			currency_id = '".$currency['id']."',
			amount = '".$currency['amount']."',
			rate = '".$currency['rate']."',
			date = '".$currency['date']."'
	";
	$DB->insert($query);
	$query = "
		REPLACE INTO currency_cbr_current
		SET
			currency_id = '".$currency['id']."',
			amount = '".$currency['amount']."',
			rate = '".$currency['rate']."',
			date = '".$currency['date']."'
	";
	$DB->insert($query);
}

?>