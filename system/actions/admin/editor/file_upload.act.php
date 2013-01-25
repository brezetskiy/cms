<?php
/**
* ������� �������� ����� ��������
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

$id = globalVar($_POST['id'], 0);
$name = globalVar($_POST['name'], '');
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$access = globalEnum($_POST['access'], array('all', 'registered', 'confirmed', 'checked'));


/**
 * �������� ���� �������������� ������� �������������
 */
if (!Auth::editContent($table_name, $id)) {
	Action::setError(cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������'));
	Action::onError();
}

/**
 * ���������� ���������� �����
 */
$extension = Uploads::getFileExtension($_FILES['uploadFile']['name']);

// ����� ������� � �����
if ($access != 'all') {
	$extension = $access.'.'.$extension;
}

// ���� ������������ ���� �� ������ ��� �����, �� ���������� ��� ��
// ����� ����������� �����
if (empty($name)) {
	$name = substr($_FILES['uploadFile']['name'], 0, strrpos($_FILES['uploadFile']['name'], '.'));
}

$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
$destination_file = Filesystem::getMaxFileId($destination_root).'.'.$extension;

// ��������������� ���������� ����
Uploads::moveUploadedFile($_FILES['uploadFile']['tmp_name'], $destination_file);

/**
 * ���������� HTML ���, ������� ����������� � ��������
 */
$html = '<a href="/tools/cms/site/download.php?url=/'.UPLOADS_DIR.substr($destination_file, strlen(UPLOADS_ROOT)).'&name='.$name.'">';


echo '<html>
<head>
	<title>File Upload</title>
</head>
<body>
<script language="JavaScript">
window.opener.frames.EditFrame.focus()
var range = window.opener.frames.EditFrame.document.selection.createRange();
range.pasteHTML("'.str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html), '\"')).'" + range.htmlText + "</a>");
window.close();
</script>
</body>
</html>
';


exit;
?>