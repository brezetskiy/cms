<?php
/**
* �������������� ������ �� CVS
* @package Pilot
* @subpackage Editor
* @version 5.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X, 2004
*/

/**
* ���������� ����������
*/
$id = globalVar($_GET['id'], 0);

$query = "SELECT * FROM cvs_log WHERE id='$id'";
$info = $DB->query_row($query);
if ($DB->rows == 0) {
	// ���������� ����������� ��������� ������ �� �� ���������� ������.
	echo cms_message('CMS', '���������� ����������� ��������� ������ �� �� ���������� ������.');
	exit;
}


/**
* �������� ���� �������������� ������� �������������
*/
if (!Auth::editContent($info['table_name'], $info['edit_id'])) {
	// � ��� ��� ���� �� ������� � ������.
	echo cms_message('CMS', '� ��� ��� ���� �� ������� � ������.');
	exit;
}

/**
 * ������ �������������� � CVS, ��� ����, ���� ����� ������������ ������ �� ������ ��������� ��
 * �������������� ������ ����������� ��� ����� ����� CVS, � �� ������������ ��������� ���������
 * ������� ����� ������������
 */
CVS::lock($info['table_name'], $info['field_name'], $info['edit_id']);

/**
 * ��������������� ����� � ������ ����������
 */
$source_uploads = SITE_ROOT.'cvs/'.Uploads::getIdFileDir($inserted_id).'/';
$destination_uploads = SITE_ROOT.'uploads/'.Uploads::getStorage($table_name, $field_name, $edit_id).'/';
if (is_dir($source_uploads)) {
	
	// ������ �����, ����������� �� �������� - ��� �������� �������, �� ������� ��� ��� ����
	$source_uploads_files = Filesystem::getDirContent($source_uploads, true, false, true);
	if (is_dir($destination_uploads)) {
		Filesystem::delete($destination_uploads);
	}
	mkdir($destination_uploads, 0750, true);
	reset($source_uploads_files); 
	while (list(,$row) = each($source_uploads_files)) { 
		link($row, $destination_uploads.basename($row));
	}
	
	// �������� � �������� ��� ������ �� uploads
	$info['content'] = str_replace(Uploads::getURL($source_uploads), Uploads::getURL($destination_uploads), $info['content']);
}

// ��������� � �� ����������
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