<?php
/**
* ¬осстановление данных из CVS
* @package Pilot
* @subpackage Editor
* @version 5.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

/**
* “ипизируем переменные
*/
$id = globalVar($_GET['id'], 0);

$query = "SELECT * FROM cvs_log WHERE id='$id'";
$info = $DB->query_row($query);
if ($DB->rows == 0) {
	// Ќевозможно востановить указанную версию из за отсутстви€ данных.
	echo cms_message('CMS', 'Ќевозможно востановить указанную версию из за отсутстви€ данных.');
	exit;
}


/**
* ѕроверка прав редактировани€ таблицы пользователем
*/
if (!Auth::editContent($info['table_name'], $info['edit_id'])) {
	// ” ¬ас нет прав на доступа к данным.
	echo cms_message('CMS', '” ¬ас нет прав на доступа к данным.');
	exit;
}

/**
 * ƒелаем переблокировку в CVS, дл€ того, чтоб когда пользователь нажмет на кнопку сохранить то
 * востановленна€ верси€ сохранилась как новый архив CVS, а не перезаписала последние изменени€
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
	
	// «амен€ем в контенте все ссылки на uploads
	$info['content'] = str_replace(Uploads::getURL($source_uploads), Uploads::getURL($destination_uploads), $info['content']);
}

// ќбновл€ем в Ѕƒ информацию
$query = "update `$info[table_name]` set `$info[field_name]`='".addcslashes($info['content'], "'")."' where id='$info[edit_id]'";
$DB->update($query);


?>
<script language="JavaScript">
window.opener.frames.EditFrame.document.body.innerHTML = '';
window.opener.frames.EditFrame.focus()
var range = window.opener.frames.EditFrame.document.selection.createRange();
range.pasteHTML('<?php echo str_replace(array("\n", "\r"), '', addcslashes(stripslashes($info['content']), "\'")); ?>');
window.close();
</script>
<?PHP
exit;
?>