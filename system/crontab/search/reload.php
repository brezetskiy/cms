<?php 
/**
 * Создает индекс для поиска
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 * @cron none
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

// Блокировка паралельного запуска скрипта
Shell::collision_catcher();

// Обновление поискового индекса
Search::reload();

?>