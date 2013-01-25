<?php
/**
 * Формирование очереди вывода баннеров
 * @package Pilot
 * @subpackage Banner
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 * @cron 12 0 * * *
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


// Блокировка паралельного запуска скрипта     
Shell::collision_catcher();


$DB = DB::factory('default');

Banner::buldCache(5);

?>