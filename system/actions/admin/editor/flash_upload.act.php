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
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');
$extension = Uploads::getFileExtension($_FILES['uploadFile']['name']);


/**
* �������� ���� �������������� ������� �������������
*/
if (!Auth::editContent($table_name, $id)) {
	Action::setError(cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������'));
	Action::onError();
}

$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
$destination_file = Filesystem::getMaxFileId($destination_root).'.'.$extension;


/**
* ��������������� ���������� ����
*/
Uploads::moveUploadedFile($_FILES['uploadFile']['tmp_name'], $destination_file);

/**
* ���������� HTML ���, ������� ����������� � ��������
*/
$html = Uploads::htmlImage($destination_file);
?>
<script language="JavaScript">
window.opener.frames.EditFrame.focus()
var range = window.opener.frames.EditFrame.document.selection.createRange();
range.pasteHTML('<?php echo str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html), "\'")); ?>' + range.htmlText + '</a>');
window.close();
</script>
<?PHP
exit;
?>