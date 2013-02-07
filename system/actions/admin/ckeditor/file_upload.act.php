<?php
/**
 * Обработчик прикрепления файла
 * @package Pilot
 * @subpackage CKEditor
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$id = globalVar($_POST['id'], 0);
$temp_id = globalVar($_POST['temp_id'], '');
$name = globalVar($_POST['name'], '');
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$editor_name = globalVar($_POST['editor_name'], '');
$access = globalEnum($_POST['access'], array('all', 'registered', 'confirmed', 'checked'));


/**
 * Проверка прав редактирования таблицы пользователем
 */
if (!Auth::editContent($table_name, $id)) {
	echo "<script>alert('".cms_message('CMS', 'У Вас нет прав на редактирование данного раздела')."')</script>";
	exit;
}

/**
 * Определяем расширение файла
 */
$extension = Uploads::getFileExtension($_FILES['uploadFile']['name']);

// Права доступа к файлу
if ($access != 'all') {
	$extension = $access.'.'.$extension;
}

// Если пользователь явно не указал имя файла, то определяем его по
// имени закачанного файла
if (empty($name)) {
	$name = substr($_FILES['uploadFile']['name'], 0, strrpos($_FILES['uploadFile']['name'], '.'));
}

if (!empty($id)) {
	$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
} else {
	$temp_id = preg_replace('~[^a-z0-9]~i', '', $temp_id);
	if (empty($temp_id)) {
		UploadError(cms_message('editor', 'Для загрузки файлов к несохраненным объектам передайте TempId'));
	}
	$destination_root = TMP_ROOT."ckeditor/$temp_id/";
}

//$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
$destination_file = Filesystem::getMaxFileId($destination_root).'.'.$extension;

// Переименовываем закачанный файл
Uploads::moveUploadedFile($_FILES['uploadFile']['tmp_name'], $destination_file);

/**
 * Определяем HTML тег, который вставляется в редактор
 */
if (empty($id)) {
	$html = '<a href="/tools/cms/site/download.php?url='.Uploads::getURL($destination_file).'&name='.$name.'">';
} else {
	$html = '<a href="/tools/cms/site/download.php?url=/'.UPLOADS_DIR.substr($destination_file, strlen(UPLOADS_ROOT)).'&name='.$name.'">';
}



echo '<html>
<head>
	<title>File Upload</title>
</head>
<body>
<script language="JavaScript">

// get HTML from selection
function getSelectionHTML(selection)
{
   var range = (document.all ? selection.createRange() : selection.getRangeAt(selection.rangeCount - 1).cloneRange());

   if (document.all)
   {
      return range.htmlText;
   }
   else
   {
      var clonedSelection = range.cloneContents();
      var div = document.createElement(\'div\');
      div.appendChild(clonedSelection);
      return div.innerHTML;
   }
}

var CKEDITOR = window.parent.CKEDITOR;
var mySelection = CKEDITOR.instances["'.$editor_name.'"].getSelection();

selectedText = getSelectionHTML(mySelection.getNative())

CKEDITOR.instances["'.$editor_name.'"].insertHtml("'.str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html), '\"')).'"+selectedText+"</a>");

</script>
</body>
</html>
';
exit;