<?php

/**
 * Список дизайнов писем
 * 
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */

$group_id = globalVar($_GET['group_id'], 0);
$group = $DB->result("select name from site_template_group where id='$group_id'");
$files = Filesystem::getDirContent(SITE_ROOT.'design/'.$group.'/mail/', false, false, true);

$insert = array();
reset($files); 
while (list(,$file) = each($files)) { 
	 if (substr($file, -4) == 'tmpl') {
	 	$filename = substr($file, 0, strlen($file) - 8);
	 	$insert[] = "('$filename', '$filename', '$group_id')";
	 }
}

if (!empty($insert)) {
	$query = "insert ignore into cms_mail_design (name, title, group_id) values ".implode(",", $insert);
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
	
	if (!is_file(SITE_ROOT."design/$group/mail/{$row['name']}.".LANGUAGE_SITE_DEFAULT.".tmpl")) $row['name'] = '<font color="gray">'.$row['name'].'</font>';
	return $row;
}
 
 
$query = "
	SELECT
		id,
		name,
		title as title,
		(select count(*) from cms_mail_template where design_id=tb_template.id) as `usage`,
		priority
	FROM cms_mail_design as tb_template
	where tb_template.group_id='$group_id'
	ORDER BY priority ASC
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('title', '50%');
$cmsTable->addColumn('usage', '5%', 'right', 'Использование');
echo $cmsTable->display();

?>