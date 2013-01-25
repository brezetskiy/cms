<?php
/**
 * Список шаблонов с дизайном страниц
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */
$group_id = globalVar($_GET['group_id'], 0);

$query = "select name from site_template_group where id='$group_id'";
$group = $DB->result($query);

$files = Filesystem::getDirContent(SITE_ROOT.'design/'.$group.'/', false, false, true);
$insert = array();
reset($files); 
while (list(,$file) = each($files)) { 
	 if (substr($file, -4) == 'tmpl') {
	 	$filename = substr($file, 0, strlen($file) - 8);
	 	$insert[] = "('$filename', '$filename', '$group_id')";
	 }
}
if (!empty($insert)) {
	$query = "insert ignore into site_template (name, title, group_id) values ".implode(",", $insert);
	$DB->insert($query);
}


/**
 * Фильтр предварительной обработки значений в таблице
 * @ignore
 * @param array $row
 * @return array
 */
function cms_prefilter($row) {
	global $group;
	
	if (!is_file(SITE_ROOT."design/$group/$row[name].".LANGUAGE_SITE_DEFAULT.".tmpl")) {
		$row['name'] = '<font color="gray">'.$row['name'].'</font>';
	}
	return $row;
}
//concat('<a href=\"javascript:void(0);\" onclick=\"CenterWindow(\'/tools/cms/admin/show_design.php?id=', id, '\', \'template\', 1024, 768, 1, 0);\">', title, '</a>') as title,
$query = "
	SELECT
		id,
		name,
		title as title,
		(select count(*) from site_structure where template_id=tb_template.id) as `usage`,
		priority
	FROM site_template as tb_template
	where tb_template.group_id='$group_id'
	ORDER BY priority ASC";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('title', '50%');
$cmsTable->addColumn('usage', '5%', 'right', 'Использование');
echo $cmsTable->display();
?>