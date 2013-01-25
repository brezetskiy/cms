<?php
/** 
 * Закачка файлов в галерею 
 * @package Pilot 
 * @subpackage Gallery 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 
$group_table_name = globalVar($_POST['group_table_name'], '');
$current_url = globalVar($_POST['current_url'], '');
$parent_id = globalVar($_POST['parent_id'], 0);
$extension = strtolower(Uploads::getFileExtension($_FILES['Filedata']['name']));

// Проверяем, не возникла ли ошибка при закачке файла
if (!in_array($_FILES['Filedata']['error'], array(UPLOAD_ERR_OK, UPLOAD_ERR_NO_FILE))) {
	echo Uploads::check($_FILES['Filedata']['error']);
	exit;
}

$query = "select id from cms_table where name='gallery_photo'";
$table_id = $DB->result($query);

// Добавляем картинку в БД
$query = "select ifnull(max(priority),0)+1 from gallery_photo where `group_id`='$parent_id'";
$priority = $DB->result($query, 1);

$query = "insert into gallery_photo (group_id, group_table_name, photo, priority) values ('$parent_id', '$group_table_name', '".$DB->escape($extension)."', '$priority')";
$id = $DB->insert($query);

// Переносим файл с временной директории
$file = UPLOADS_ROOT."gallery_photo/photo/".Uploads::getIdFileDir($id).'.'.$extension;
Uploads::moveUploadedFile($_FILES['Filedata']['tmp_name'], $file);
$image_url = Uploads::getURL($file);

// Определяем размер пиктограммы
$query = "select width, height from cms_image_size where uniq_name='cms_gallery'";
$size = $DB->query_row($query);

// Выводим информацию о файле
$link = (in_array($extension, array('flv', 'mp3', 'mp4'))) ? 
	"<a href=\"javascript:void(0);\" onclick=\"show_video('mediaspace', '$image_url');\"><img border=0 src=\"/design/cms/img/icons/zoom.gif\"></a>":
	"<a target=\"_blank\" href=\"{$image_url}\"><img border=\"0\" src=\"/design/cms/img/icons/zoom.gif\"></a>";
	
echo iconv(CMS_CHARSET, 'UTF-8//IGNORE', "
	<div style='width: ".intval($size['width'] + 40)."px; height: ".intval($size['height'] + 40)."px' onmouseover=\"return galleryLayerOver(this, '$size[width]', '$size[height]', '')\" class=\"gallery_image_layer\" id=\"il_{$id}\">
		<div class=\"gallery_image_layer_toolbar\">
			<div style=\"float:left\"><i>нет описания</i></div>
			$link
			<a href='#' title='Редактировать' onclick=\"EditWindow('$id', $table_id, '', '$current_url', '".LANGUAGE_CURRENT."', '');return false;\"><img border='0' src='/design/cms/img/icons/change.gif'></a>
			<a href='#' title='Удалить' onclick=\"gallery_swf_upload_delete('{$id}','gallery_photo', 'photo', '{$extension}'); return false;\"><img border='0' src='/design/cms/img/icons/del.gif'></a>
		</div>
		<div rel='image_holder' class='image_holder' style=\"text-align: center;\">
			<img src=\"/i/cms_gallery/".substr($file, strlen(UPLOADS_ROOT))."\">
		</div>
	</div>
");

exit;

?>