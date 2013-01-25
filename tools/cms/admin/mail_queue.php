<?php
/** 
 * Подгружаемый список контрагентов
 * @package Pilot 
 * @subpackage Billing 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
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

if (!Auth::selectTable('cms_mail_queue')) {
	echo "Нет прав доступа";
	exit;
}

$id = globalVar($_GET['id'], 0);

$query = "select * from cms_mail_queue where id='$id'";
$info = $DB->query_row($query);

if(empty($info)){
	echo "<center>Не найдено сообщение</center>";
} else {
	echo "<div style=\"width:500px;\">".$info['message']."</div>";
}



?>
