<?php
/**
* Сохранение структуры сайта
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Типизируем переменные
*/
$id = globalVar($_POST['id'], 0);
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$content = stripslashes(globalVar($_POST['content'], ''));
$html_tidy = globalVar($_POST['html_tidy'], 0);
$html_auto_charset = globalVar($_POST['html_auto_charset'], 0);

/**
 * Проверка прав редактирования таблицы пользователем
 */
if (!Auth::editContent($table_name, $id)) {
	Action::setError(cms_message('CMS', 'У Вас нет прав на редактирование данного раздела'));
	Action::onError();
}

/**
 * Проверка блокировки ряда в таблице
 */
$owner = CVS::isOwner($table_name, $field_name, $id);
if ($owner !== true) {
	Action::setError(cms_message('CMS', 'Страница заблокирована %s пользователем %s', $owner['datetime'], $owner['login']));
	Action::onError();
}


/**
 * Загружаем триггер
 */
if (is_file(ACTIONS_ROOT.'admin/html_editor/'.$table_name.'.inc.php')) {
	require_once(ACTIONS_ROOT.'admin/html_editor/'.$table_name.'.inc.php');
}

/**
* Определяем директорию в которой находится файл с контентом
*/
$Content = new Content($content, $table_name, $field_name, $id, $html_tidy, $html_auto_charset);
$Content->uploadImages();
$Content->rmImages();
$Content->url2id();
$Content->prepare4diff();

/**
 * Фиксируем изменения в CVS
 */
CVS::log($table_name, $field_name, $id, $Content->content);

$Content->save();
$Content->statistic();

/**
 * Флаг, который даёт редактору команду перегрузить страницу с контентом
 */
if ($Content->remote_images === true) {
	$_RESULT['source_update'] = true;
}


/**
 * Если закончилось время сессии пользователя, то необходимо после ввода логина и пароля
 * закрыть окно.
 */
if (isset($_REQUEST['login']) && isset($_REQUEST['passwd'])) {
	echo '<script language="JavaScript">window.close();</script>';
}

unset($Content);

/**
 * При вызове сохранения без использования Ajax - нормальный переход на _return_path
 */
if (AJAX) {
	exit;
}

?>