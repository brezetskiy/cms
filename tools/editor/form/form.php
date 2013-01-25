<?php
/**
* Добавляет на сайт форму для отправки данных
* @package Pilot
* @subpackage Editor
* @version 5.3
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2006
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/form/form');
echo $TmplDesign->display();

?>