<?php
/**
 * Удаление файлов, закачанных через swf upload
 * @package Pilot
 * @subpackage CMS
 * @version 6.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, 2008
 */
$id = globalVar($_POST['id'], 0);
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$extension = globalVar($_POST['extension'], '');

// Проверка прав редактирования таблицы пользователем
if (!Auth::updateTable($table_name)) {
	echo cms_message('CMS', 'У Вас нет прав на редактирование таблицы %s.', $table_name);
	exit;
}

$DB->delete("delete from `$table_name` where id = '$id'");

Uploads::deleteImage(UPLOADS_ROOT.$table_name.'/'.$field_name.'/'.Uploads::getIdFileDir($id).'.'.$extension);

$_RESULT['javascript'] = "var my_div = byId('il_$id'); if(my_div) my_div.parentNode.removeChild(my_div); galleryLayerOut();";

exit;
?>