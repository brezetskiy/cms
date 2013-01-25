<?php
/**
* ������� �������� ����� ��������
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
$editor_name = globalVar($_POST['editor_name'], '');

$alt = globalVar($_POST['alt'], '');
$alt_text = globalVar($_POST['alt_text'], '');
$border = globalVar($_POST['border'], 0);
$imgAlign = globalVar($_POST['imgAlign'], '');
$hspace = globalVar($_POST['hspace'], 0);
$vspace = globalVar($_POST['vspace'], 0);
$thumb_height = globalVar($_POST['thumb_height'], 0);
$thumb_width = globalVar($_POST['thumb_width'], 0);
$watermark = globalVar($_POST['watermark'], 'false');

/**
 * �������� ���� �������������� ������� �������������
 */
if (!Auth::editContent($table_name, $id)) {
	echo "<script>alert('".cms_message('CMS', '� ��� ��� ���� �� �������������� ������� �������')."')</script>";
	exit;
}

/**
* ��������� � ����� ��������� ������������� ������ �����������, 
* � ����� ��������� ��������� ������ �������� �����������
*/
setcookie('editor_thumb_width', $thumb_width, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_thumb_height', $thumb_height, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_img_border', $border, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);

setcookie('editor_hspace', $hspace, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_vspace', $vspace, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);
setcookie('editor_watermark', $watermark, time() + 60 * 60 * 24 * 10, '/', CMS_HOST);

/**
 * ���������, �� �������� �� ������ ��� ������� �����
 */
if (!in_array($_FILES['normalImage']['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
	echo "<script>alert('".Uploads::check($_FILES['normalImage']['error'])."')</script>";
	exit;
}

if (!empty($id)) {
	$root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
} else {
	$temp_id = preg_replace('~[^a-z0-9]~i', '', $temp_id);
	if (empty($temp_id)) {
		UploadError(cms_message('editor', '��� �������� ����������� � ������������� �������� ��������� TempId'));
	}
	$root = TMP_ROOT."ckeditor/$temp_id/";
}

$extension = Uploads::getFileExtension($_FILES['normalImage']['name']);
//$root = UPLOADS_ROOT.Uploads::getStorage($table_name, $field_name, $id).'/';
$image = Filesystem::getMaxFileId($root).'.'.strtolower($extension);
$thumb = Uploads::getThumb($image);
if (is_file($thumb)) unlink($thumb);

// ��������������� ���������� ����
Uploads::moveUploadedFile($_FILES['normalImage']['tmp_name'], $image);

$Image = new Image($image);
$Image->thumb($thumb, $thumb_width, $thumb_height);

// ������������� �� ������������ �������� ������� �����
if ($watermark == 'true') {
	$query = "SELECT * FROM cms_watermark WHERE use_in_editor='true'";
	$watermarks = $DB->query($query);
	reset($watermarks);
	while (list(,$row) = each($watermarks)) {
		$file = UPLOADS_ROOT.'cms_watermark/file/'.Uploads::getIdFileDir($row['id']).'.'.$row['file'];
		$Image->watermark($file, $row['pos_x'], $row['pos_y'], $row['pad_x'], $row['pad_y'], $row['transparency']);
	}
}

/**
 * ���������� HTML ���, ������� ����������� � ��������
 */
//$attrib = ' '.$imgAlign.' alt="'.$alt.'" '; 
$attrib = ($border == '1') ? ' border="1"' : ' border="0"';
$attrib .= ($hspace != '') ? ' hspace="'.$hspace.'"' : '';
$attrib .= ($vspace != '') ? ' vspace="'.$vspace.'"' : '';


if (!empty($alt_text)) {
	// �������� ����������� ��� ����� �� ������
	$html_img = '<a rel="lightbox-content" href="'.Uploads::getURL($image).'">'.$alt_text.'</a>';
} elseif (empty($image)) {
	// ��� �������� ��� �����������
	$html_img = Uploads::htmlImage($thumb, $attrib);
} else {
	// ������� HTML ��� �������� � ������������
	$html_img = Uploads::lightboxImage($image, $alt, 'content', $attrib);
}

$Image->save();
unset($Image);



/**
 * ������ ������ � ������� cms_image � ��������� ����������� � ��������, ������� ����� �������
 * � ��������� ����������� ��������. ���������� ����������� ���� ���� ����������� ����� ��������
 * � ���� ����������� � ���.
 */
if (!empty($alt) && !empty($image)) {
	$query = "REPLACE INTO cms_image (url, title) VALUES ('".substr($image, strlen(UPLOADS_ROOT), -1 * strlen($extension) - 1)."', '$alt')";
	$DB->insert($query);
} elseif (!empty($image)) {
	$query = "DELETE FROM cms_image WHERE url='".substr($image, strlen(UPLOADS_ROOT), -1 * strlen($extension) - 1)."'";
	$DB->delete($query);
}

echo '<html>
<head>
	<title>File Upload</title>
</head>
<body>
<script language="JavaScript">
var CKEDITOR = window.parent.CKEDITOR;
CKEDITOR.instances["'.$editor_name.'"].insertHtml("'.str_replace(array("\n", "\r"), '', addcslashes(stripslashes($html_img), '\"')).'");
</script>
</body>
</html>
';
exit;