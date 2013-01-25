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
$field = globalVar($_POST['field'], '');
$file_name = globalVar($_POST['file_name'], '');
$tmp_dir = globalVar($_POST['tmp_dir'], '');

// Проверка прав редактирования таблицы пользователем
if (!Auth::updateTable($table_name)) {
	echo cms_message('CMS', 'У Вас нет прав на редактирование таблицы %s.', $table_name);
	exit;
}

$file = (!empty($id)) ? 
	UPLOADS_ROOT.Uploads::getStorage($table_name, $field, $id).'/'.$file_name:
	TMP_ROOT.$tmp_dir."$field/$file_name";
	
if (CMS_CHARSET != CMS_CHARSET_FS) {
	$file = iconv('cp1251', 'utf-8', $file);
}

if (is_file($file) && is_writable($file)) {
	unlink($file);
}

$_RESULT['javascript'] = "var my_div = byId('file_".$table_name."_".$field."_".$file_name."');if(my_div) my_div.parentNode.removeChild(my_div);";

exit;
?>