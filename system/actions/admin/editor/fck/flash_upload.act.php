<?php
/**
 * Вставка Flash в FCK Editor на страницах сайта
 * @package Pilot
 * @subpackage Editor
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$id = globalVar($_POST['id'], 0);
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$extension = Uploads::getFileExtension($_FILES['uploadFile']['name']);

function UploadError($message) {
	echo $message;
	exit;
}

/**
* Проверка прав редактирования таблицы пользователем
*/
if (!Auth::editContent($table_name, $id)) {
	UploadError(cms_message('CMS', 'У Вас нет прав на редактирование данного раздела'));
}

if (!empty($id)) {
	$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
} else {
	$temp_id = preg_replace('~[^a-z0-9]~i', '', $temp_id);
	if (empty($temp_id)) {
		UploadError(cms_message('editor', 'Для загрузки изображений к несохраненным объектам передайте TempId'));
	}
	$destination_root = TMP_ROOT."fck-editor/$temp_id/";
}

$destination_file = Filesystem::getMaxFileId($destination_root).'.'.$extension;


/**
* Переименовываем закачанный файл
*/
Uploads::moveUploadedFile($_FILES['uploadFile']['tmp_name'], $destination_file);

/**
* Определяем HTML тег, который вставляется в редактор
*/
$html = Uploads::htmlImage($destination_file);
?>
<script type="text/javascript">
window.parent.FCK.InsertHtml('<?php echo str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html), "\'")); ?>')
window.parent.CloseDialog()
</script>
<?php
exit;
?>