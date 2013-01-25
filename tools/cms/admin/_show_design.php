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

// Аунтификация при  работе с запароленными разделами
new Auth(true);

$design_id = globalVar($_REQUEST['design_id'], 0);
$query = "select name from site_template where id='$id'";
$name = $DB->result($query);

$file = TEMPLATE_ROOT.'design/site/'.$name.'.ru.tmpl';
if (is_file($file)) {
	$Template = new Template('design/site/'.$name);
	echo $Template->display();
}

?>