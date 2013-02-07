<?php
/**
* Восстановление данных из CVS
* @package Pilot
* @subpackage CKEditor
* @version 5.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Типизируем переменные
*/
$id = globalVar($_GET['id'], 0);

$query = "SELECT * FROM cvs_log WHERE id='$id'";
$info = $DB->query_row($query);
if ($DB->rows == 0) {
	// Невозможно востановить указанную версию из за отсутствия данных.
	echo cms_message('CMS', 'Невозможно востановить указанную версию из за отсутствия данных.');
	exit;
}


/**
* Проверка прав редактирования таблицы пользователем
*/
if (!Auth::editContent($info['table_name'], $info['edit_id'])) {
	// У Вас нет прав на доступа к данным.
	echo cms_message('CMS', 'У Вас нет прав на доступа к данным.');
	exit;
}

/**
 * Делаем переблокировку в CVS, для того, чтоб когда пользователь нажмет на кнопку сохранить то
 * востановленная версия сохранилась как новый архив CVS, а не перезаписала последние изменения
 * которые делал пользователь
 */
CVS::lock($info['table_name'], $info['field_name'], $info['edit_id']);

/**
 * восстанавливаем файлы в нужной директории
 */
$source_uploads = SITE_ROOT.'cvs/'.Uploads::getIdFileDir($inserted_id).'/';
$destination_uploads = SITE_ROOT.'uploads/'.Uploads::getStorage($table_name, $field_name, $edit_id).'/';
if (is_dir($source_uploads)) {
	
	// только файлы, подкаталоги не копируем - это дочерние разделы, до которых нам нет дела
	$source_uploads_files = Filesystem::getDirContent($source_uploads, true, false, true);
	if (is_dir($destination_uploads)) {
		Filesystem::delete($destination_uploads);
	}
	mkdir($destination_uploads, 0750, true);
	reset($source_uploads_files); 
	while (list(,$row) = each($source_uploads_files)) { 
		link($row, $destination_uploads.basename($row));
	}
	
	// Заменяем в контенте все ссылки на uploads
	$info['content'] = str_replace(Uploads::getURL($source_uploads), Uploads::getURL($destination_uploads), $info['content']);
}

// Обновляем в БД информацию
$query = "update `$info[table_name]` set `$info[field_name]`='".addcslashes($info['content'], "'")."' where id='$info[edit_id]'";
$DB->update($query);


?>

<html>
<head>
	<title>CVS Restore</title>
</head>
<body>
<script language="JavaScript">
var CKEDITOR = window.parent.CKEDITOR;
CKEDITOR.instances.content.execCommand('SelectAll');
CKEDITOR.dialog.getCurrent().hide()
window.parent.location.reload()
</script>
</body>
</html>