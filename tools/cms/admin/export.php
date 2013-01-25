<?php
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



?>