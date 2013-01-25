<?php 
/**
* Выводит список картинок, браузер по директориям
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../../../system/config.inc.php');

$TmplDesign = new Template(dirname(__FILE__).'/image');
echo $TmplDesign->display();
?>