<?php
/**
 * ������� �������� ���������� ������ 
 * @package Pilot
 * @subpackage CMS
 * @version 6.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, 2008
 */
$id = globalVar($_POST['id'], 0);
$table_name = globalVar($_POST['table_name'], '');
$field = globalVar($_POST['field'], '');
$tmp_dir = globalVar($_POST['tmp_dir'], '');

// �������� ���� �������������� ������� �������������
if (!Auth::updateTable($table_name)) {
	echo cms_message('CMS', '� ��� ��� ���� �� �������������� ������� %s.', $table_name);
	exit;
}

// ���������, �� �������� �� ������ ��� ������� �����
if (!in_array($_FILES['Filedata']['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
	echo Uploads::check($_FILES['Filedata']['error']);
	exit;
}

$file = (!empty($id)) ? 
	UPLOADS_ROOT.Uploads::getStorage($table_name, $field, $id).'/'.$_FILES['Filedata']['name']:
	TMP_ROOT.$tmp_dir."$field/".$_FILES['Filedata']['name'];
$extension = strtolower(Uploads::getFileExtension($_FILES['Filedata']['name']));
$basename = substr($file, 0, strlen($file) - strlen($extension) - 1);
if (is_file($file)) {
	$number = 0;
	do {
		$number++;
		$file = sprintf("%s(%02d).%s", $basename, $number, $extension);
	} while(is_file($file));
}

// ���������� ����
Uploads::moveUploadedFile($_FILES['Filedata']['tmp_name'], $file);

// ������� ���������� � �����
$available = Filesystem::getDirContent(SITE_ROOT.'img/shared/ico/', false, false, true);
$icon = (in_array($extension.'.gif', $available)) ? $extension : 'file';

$filename = basename($file);
$url = substr($file, strlen(SITE_ROOT) - 1);
echo <<<EOD
	<a href="javascript:void(0);" onclick="cms_swf_upload_delete('$id', '$table_name', '$field', '$filename', '$tmp_dir');"><img src="/design/cms/img/icons/swf_del.png" border="0" align="absmiddle"></a>
	<img src="/img/shared/ico/$icon.gif" border="0" align="absmiddle">
	<a target="_blank" href="$url">$filename</a>
EOD;
exit;
?>