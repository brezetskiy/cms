<?php

/**
 * Инсталяционный скрипт. Вывод меню, в котором пользователь указывает наcтройки
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурация
*/
require_once('../system/config.inc.php');

$TmplContent = new Template(SITE_ROOT.'templates/cms/admin/install');

$db_host = globalVar($_POST['db_host'], '');
$db_name = globalVar($_POST['db_name'], '');
$db_login = globalVar($_POST['db_login'], '');
$db_password = globalVar($_POST['db_password'], '');

if (empty($db_login)) {
	$db_login = $db_name;
}

if (!empty($db_host)) {
	$is_ok = Install::changeDB($db_host, $db_login, $db_password, $db_name, $error_message);
	$TmplContent->set('error_message', $error_message);
	if ($is_ok) {
		Install::updateMyConfig();
		$TmplContent->set('ok_message', 'Система успешно обновила конфигурационный файл. Теперь Вы можете перейти в <a href="/Admin/CMS/Modules/Delete/">админ панель</a>.');
		file_put_contents(SITE_ROOT.'install/.htaccess', "Order allow,deny\nDeny from all");
	}
}

 
// Вызываем шаблон
$TmplContent->set('db_host', $db_host);
$TmplContent->set('db_name', $db_name);
$TmplContent->set('db_login', $db_login);
$TmplContent->set('db_password', $db_password);
echo $TmplContent->display();


?>