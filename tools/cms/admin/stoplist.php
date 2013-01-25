<?php
/** 
 * Вывод полного текста письма из таблицы maillist_stoplist
 * @package Pilot 
 * @subpackage Billing 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

/**
 * Определяем языковой интерфейс
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

/**
 * Конфигурация
 */
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

if (!Auth::selectTable('maillist_stoplist')) {
	echo "Нет прав доступа";
	exit;
}

$id = globalVar($_GET['id'], 0);

$query = "select * from maillist_stoplist where id='$id'";
$info = $DB->query_row($query);

if(empty($info)){
	echo "<center>Не найдено сообщение</center>";
} else {
	echo "<div style=\"width:500px;\"><pre>".$info['message']."</pre></div>";
}



?>
