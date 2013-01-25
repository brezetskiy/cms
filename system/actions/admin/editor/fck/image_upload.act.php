<?php
/**
 * ���������� �������� �������� ����� FCK Editor
 * @package Pilot
 * @subpackage Editor
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$id = globalVar($_POST['id'], 0);
$temp_id = globalVar($_POST['temp_id'], '');
$table_name = globalVar($_POST['table_name'], '');
$field_name = globalVar($_POST['field_name'], '');

$alt = globalVar($_POST['alt'], '');
$border = globalVar($_POST['border'], 0);
$imgAlign = globalVar($_POST['imgAlign'], '');
$hspace = globalVar($_POST['hspace'], 0);
$vspace = globalVar($_POST['vspace'], 0);

$thumb = globalEnum($_POST['thumb'], array('make', 'none', 'upload'));
$thumb_height = globalVar($_POST['thumb_height'], 0);
$thumb_width = globalVar($_POST['thumb_width'], 0);

$watermark = globalVar($_POST['watermark'], 0);

function UploadError($message) {
	echo $message;
	exit;
}

/**
* �������� ���� �������������� ������� �������������
*/
if (!Auth::editContent($table_name, $id)) {
	UploadError(cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������'));
}

/**
* ���������, �� �������� �� ������ ��� ������� �����
*/
if (!in_array($_FILES['normalImage']['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
	UploadError(Uploads::check($_FILES['normalImage']['error']));
}

/**
* ��������� � ����� ��������� ������������� ������ �����������, 
* � ����� ��������� ��������� ������ �������� �����������
*/
if ($thumb_width > 0 && $thumb_height > 0) {
	setcookie('editor_thumb_width', $thumb_width, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
	setcookie('editor_thumb_height', $thumb_height, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
}
setcookie('editor_image_border', $border, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_thumb', $thumb, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);

setcookie('editor_image_hspace', $hspace, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_image_vspace', $vspace, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_image_watermark', $watermark, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);

/**
* ���������� ���������� ���������� ��������
*/
$extension_normal = Uploads::getFileExtension($_FILES['normalImage']['name']);

if (!empty($id)) {
	$destination_root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
} else {
	$temp_id = preg_replace('~[^a-z0-9]~i', '', $temp_id);
	if (empty($temp_id)) {
		UploadError(cms_message('editor', '��� �������� ����������� � ������������� �������� ��������� TempId'));
	}
	$destination_root = TMP_ROOT."fck-editor/$temp_id/";
}

/**
* ���������� MAX(id) ����� � ���� ����������
*/
$destination_root = Filesystem::getMaxFileId($destination_root);
$destination_thumb = $destination_root . '_thumb.jpg';
$destination_normal = $destination_root . '.' . strtolower($extension_normal);


/**
* ��������������� ���������� ����
*/
Uploads::moveUploadedFile($_FILES['normalImage']['tmp_name'], $destination_normal);

/**
* ������������ �������� �������� � ������� CMYK � RGB
* ������ ��� JPEG ������
*/
$Image = new Image($destination_normal);


/**
* ����������� ��� ��������
*/
if ($thumb == 'upload' && !empty($_FILES['thumbImage']['tmp_name'])) {

	/**
	* ������������ ������� �����������
	*/
	Uploads::moveUploadedFile($_FILES['thumbImage']['tmp_name'], $destination_thumb);
	
} elseif (
	$thumb == 'make'
	&& $thumb_width > 10
	&& $thumb_height > 10
	&& ($Image->width > $thumb_width || $Image->height > $thumb_height)
) {
	
	/**
	* ������������ ����� ��� ���� ������� �����������, 
	* �������� �������� ������ ��� �����������
	*/
	$Image->thumb($destination_thumb, $thumb_width, $thumb_height);
	
} else {
	
	/**
	* ������������ ������ ��� ����� �������� ��������, �� �� ������ ����� 
	* ��� ������, ��� �������� �� ���� ���������, � ������ ���� ��������� ��������
	*/
	$destination_thumb = $destination_normal;
	$destination_normal = '';
	
}

/**
* ���������� HTML ���, ������� ����������� � ��������
*/
$attrib = ' align="'.$imgAlign.'" alt="'.$alt.'" '; 
$attrib .= ($border == '1') ? ' border="1"' : ' border="0"';
$attrib .= ($hspace != '') ? ' hspace="'.$hspace.'"' : '';
$attrib .= ($vspace != '') ? ' vspace="'.$vspace.'"' : '';

if (empty($destination_normal)) {
	// ��� �������� ��� �����������
	$html_img = Uploads::htmlImage($destination_thumb, $attrib);
} else {
	// ������� HTML ��� �������� � ������������
	$html_img = Uploads::htmlImage($destination_normal, $attrib);
}

/**
 * ������������� �� ������������ �������� ������� �����
 */
if ($watermark == 'true') {
	$query = "SELECT * FROM cms_watermark WHERE use_in_editor='true'";
	$watermarks = $DB->query($query);
	reset($watermarks);
	while (list(,$row) = each($watermarks)) {
		$file = UPLOADS_ROOT.'cms_watermark/file/'.Uploads::getIdFileDir($row['id']).'.'.$row['file'];
		if (is_file($file)) {
			$info = getimagesize($file);
			if ($info[0] < $Image->width && $info[1] < $Image->height) {
				$Image->watermark($file, $row['pos_x'], $row['pos_y'], $row['pad_x'], $row['pos_y'], $row['transparency']);
			}
		}
	}
}
$Image->save();
unset($Image);



/**
 * ������ ������ � ������� cms_image � ��������� ����������� � ��������, ������� ����� �������
 * � ��������� ����������� ��������. ���������� ����������� ���� ���� ����������� ����� ��������
 * � ���� ����������� � ���.
 */
if (!empty($alt) && !empty($destination_normal)) {
	$query = "
		REPLACE INTO cms_image (url, title) 
		VALUES
			(
				'".substr($destination_normal, strlen(UPLOADS_ROOT), -1 * strlen($extension_normal) - 1)."',
				'$alt'
			)
	";
	$DB->insert($query);
} elseif (!empty($destination_normal)) {
	$query = "DELETE FROM cms_image WHERE url='".substr($destination_normal, strlen(UPLOADS_ROOT), -1 * strlen($extension_normal) - 1)."'";
	$DB->delete($query);
}

?>
<script type="text/javascript">
window.parent.FCK.InsertHtml('<?php echo str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html_img), "\'")); ?>')
window.parent.CloseDialog()
</script>
<?php
exit;
?>