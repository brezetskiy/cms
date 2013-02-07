<?php
/**
* Закачка картинок через редактор
* @package Pilot
* @subpackage CKEditor
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

$id = globalVar($_POST['id'], 0);
$temp_id = globalVar($_POST['temp_id'], '');
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$extension = Uploads::getFileExtension($_FILES['uploadFile']['name']);
$editor_name = globalVar($_POST['editor_name'], '');


/**
* Проверка прав редактирования таблицы пользователем
*/
if (!Auth::editContent($table_name, $id)) {
	echo "<script>alert('".cms_message('CMS', 'У Вас нет прав на редактирование данного раздела')."')</script>";
	exit;
}

if (!empty($id)) {
	$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
} else {
	$temp_id = preg_replace('~[^a-z0-9]~i', '', $temp_id);
	if (empty($temp_id)) {
		UploadError(cms_message('editor', 'Для загрузки изображений к несохраненным объектам передайте TempId'));
	}
	$destination_root = TMP_ROOT."ckeditor/$temp_id/";
}

//$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
$destination_file = Filesystem::getMaxFileId($destination_root).'.'.$extension;


/**
* Переименовываем закачанный файл
*/
Uploads::moveUploadedFile($_FILES['uploadFile']['tmp_name'], $destination_file);

/**
* Определяем HTML тег, который вставляется в редактор
*/
$html = Uploads::htmlImage($destination_file);

echo '<html>
<head>
	<title>File Upload</title>
</head>
<body>
<script language="JavaScript">
var CKEDITOR = window.parent.CKEDITOR;
CKEDITOR.instances["'.$editor_name.'"].insertHtml("'.str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html), '\"')).'");
</script>
</body>
</html>
';
exit;