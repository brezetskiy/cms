<?php
/**
* Фрейм с контентом, который отображается в SiteWerk
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
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

new Auth('admin');

/**
* Типизируем переменные
*/
$id = globalVar($_GET['id'], 0);
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');

/**
* Проверка прав редактирования таблицы пользователем
*/
if (!Auth::editContent($table_name, $id)) {
	echo 'У Вас нет прав на редактирование этой страницы';
	exit;
}

$TmplDesign = new Template(SITE_ROOT.'templates/editor/cvs/list');
$TmplDesign->setGlobal('table_name', $table_name);
$TmplDesign->setGlobal('field_name', $field_name);

//$TmplDesign->setGlobal('safe_mode', ini_get('safe_mode'));

$query = "
	SELECT
		tb_log.id,
		tb_user.login,
		DATE_FORMAT(tb_log.dtime, '".LANGUAGE_DATE_SQL." %H:%i:%s') AS dtime,
		length(tb_log.content) as size
	FROM cvs_log AS tb_log
	LEFT JOIN auth_user AS tb_user ON tb_user.id=tb_log.admin_id
	WHERE
		tb_log.table_name='$table_name'
		AND tb_log.field_name='$field_name'
		AND tb_log.edit_id='$id'
	ORDER BY dtime DESC
";
$data = $DB->query($query);
$counter = 0;
reset($data);
while(list($index, $row) = each($data)) {
	if ($index == 0) {
		$TmplDesign->iterate('/current/', null, $row);
	} else {
		$TmplDesign->iterate('/row/', null, $row);
	}
}

echo $TmplDesign->display();
?>