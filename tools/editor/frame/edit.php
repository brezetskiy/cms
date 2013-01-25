<?php
/**
* Фрейм с контентом, который отображается в SiteWerk
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
* Принимает параметры методом GET 
* @param string $table_name
* @param string $field_name
* @param int $id
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


// Аунтификация при  работе с запароленными разделами
new Auth(true);

/**
* Типизируем переменные
*/
$id = globalVar($_GET['id'], 0);
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$css = globalVar($_GET['css'], '');


/**
* Проверяем, не заблокирован ли файл другим пользователем
*/
$owner = CVS::isOwner($table_name, $field_name, $id);
if ($owner !== true) {
	// Вывдим сообщение о том, что страница - заблокирована
	echo '
		Страница, которую вы хотите редактировать открыта<br>
		пользователем <b>'.$owner['login'].'</b>.<br><br>
		Время открытия: <b>'.$owner['datetime'].'</b>.<br><br>
		Одновременное изменение информации на странице <br>двумя пользователями - невозможно.
	';
	exit;
}
unset($owner);


/**
* Проверка прав редактирования таблицы пользователем
*/
if (!Auth::editContent($table_name, $id)) {
	echo 'У Вас нет прав на редактирование этой страницы';
	exit;
}

$TmplDesign = new Template(SITE_ROOT.'templates/editor/frame/edit');
// Добавляем таблицы стилей
$TmplDesign->set('css', $css);

// Выводим контент
$query = "select `$field_name` from `$table_name` where id='$id'";
$content = $DB->result($query);
$TmplDesign->set('content', id2url($content, true));
echo $TmplDesign->display();

?>