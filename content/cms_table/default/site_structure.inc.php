<?php
/** 
 * Редактирование структуры сайта 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

$structure_id = globalVar($_REQUEST['structure_id'], 0);

if (empty($id) && !empty($structure_id)) {
	// при создании страницы - подраздела - показываем шаблоны для родительского раздела
	$site_id = $DB->result("select parent from site_structure_relation where id = '$structure_id' order by priority limit 1", 0);
} elseif (!empty($id)) {
	// при редактировании страницы - показываем только шаблоны дизайна ее сайта
	$site_id = $DB->result("select parent from site_structure_relation where id = '$id' order by priority limit 1", 0);
} else {
	// при создании страницы в корне (нового сайта) - показываем все шаблоны
	$site_id = 0;
}
$cmsShowEdit = new cmsShowEdit($table_id, $id, $copy);

$query = "select template_id from site_structure_site_template where site_id='$site_id'";
$template_list = $DB->fetch_column($query);

$query = "select group_id from site_template where id in (0".implode(",", $template_list).")";
$group_list = $DB->fetch_column($query);

$query = "
	(
		select
			concat('site_template', id) as id,
			id as real_id,
			priority as sort,
			concat('site_template_group', group_id) as parent,
			title as name
		from site_template
		where id in (0".implode(",", $template_list).")
	) union (
		select
			concat('site_template_group', id) as id,
			0 as real_id,
			0 as sort,
			0 as parent,
			title as name
		from site_template_group
		where id in (0".implode(",", $group_list).")
	)
";
$data = $DB->query($query, 'id');
if ($DB->rows > 0) {
	$cmsShowEdit->setFKeyData('template_id', $data);
}
$cmsShowEdit->parseFields();
echo $cmsShowEdit->show();

?>