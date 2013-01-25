<?php
/**
 * Список шаблонов с дизайном страниц
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */

// Новые группы шаблонов
$dir_content = Filesystem::getDirContent(SITE_ROOT.'design/', false, true, false);
reset($dir_content); 
while (list($index,$row) = each($dir_content)) { 
	 $dir_content[$index] = substr($row, 0, strlen($row) - 1);
}

$query = "insert ignore into site_template_group (name) values ('".implode("'),('", $dir_content)."')";
$DB->insert($query);


/**
 * Фильтр предварительной обработки значений в таблице
 * @ignore
 * @param array $row
 * @return array
 */
function cms_prefilter($row) {
	$row['name'] = (!is_dir(SITE_ROOT."design/$row[name]/")) ?
		'<a style="color:gray;" href="./Templates/?group_id='.$row['id'].'">'.$row['name'].'</a>':
		'<a href="./Templates/?group_id='.$row['id'].'">'.$row['name'].'</a>';
	return $row;
}

$query = "SELECT * FROM site_template_group	ORDER BY name ASC";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->addColumn('name', '30%');
$cmsTable->addColumn('title', '50%');
echo $cmsTable->display();


?>