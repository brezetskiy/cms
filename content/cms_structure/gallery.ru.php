<?php
/**
 * Фотогалерея. Для переключения режима работы фотогаллереи необходимо в таблице gallery_photo сменить родительское поле между
 * gallery_id и structure_id.
 * @package Gallery
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
$group_id = globalVar($_GET['group_id'], 0);

function cms_filter($row) {
	$row['name'] = "<a href='?group_id=$row[id]'>$row[name]</a>";
	return $row;
}

$query = "
	SELECT id, name_".LANGUAGE_CURRENT." AS name, priority
	FROM gallery_group
	WHERE group_id='$group_id'
	ORDER BY priority ASC
";
$cmsTable = new cmsShowView($DB, $query, 20);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('name', '70%');
$TmplContent->set('cms_table', $cmsTable->display());
unset($cmsTable);

/**
 * Class cmsShowCoolGallery
 */
$field_name = 'photo';
$TmplDesign->iterate('/onload/', null, array('function'=>"swf_upload_$field_name = new SWFUpload(gallery_create_swf_config('gallery_group', '$group_id'));"));

$query = "
	SELECT 
		id,
		photo,
		description_".LANGUAGE_CURRENT." AS description,
		priority
		FROM gallery_photo
	WHERE group_id='$group_id' and group_table_name='gallery_group'
	ORDER BY priority ASC
";
$cmsTable = new CoolGallery($DB, $query);
$cmsTable->setParam('image_field', $field_name);
$TmplContent->set('gallery', $cmsTable->display());

?>