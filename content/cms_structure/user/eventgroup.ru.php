<?php
/**
 * Группы событий
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudneko@delta-x.com.ua>
 * @copyright Delta-X ltd, 2005
 */

/**
 * Фильтр предварительной обработки скриптов
 * @ignore
 * @param array $row
 * @return array
 */
function cms_prefilter($row) {
	global $DB;
	
	$path = ACTIONS_ROOT . 'admin/' . strtolower($row['name']) . '/';
	
	$row['total'] = 0;
	$row['delete'] = 0;
	$row['new'] = 0;
	if (is_dir($path)) {
		$query = "select name from cms_event where module_id='$row[id]'";
		$data = $DB->fetch_column($query, 'name', 'name');
		$row['total'] = $DB->rows;
		
		$files = Filesystem::getDirContent($path, false, false, true);
		
		reset($files); 
		while (list(,$filename) = each($files)) {
			$filename = substr($filename, 0, -8);
			if (!isset($data[$filename])) {
				$row['new']++;
			} else {
				unset($data[$filename]);
			}
		}
		$row['delete'] = count($data);
	}
	
	$row['name'] = "<a href='./EventFile/?module_id=$row[id]'>$row[name]</a>";
	
	if ($row['total'] == 0) $row['total'] = '-';
	if ($row['new'] == 0) $row['new'] = '-';
	if ($row['delete'] == 0) $row['delete'] = '-';
	return $row;
}

$query = "
	SELECT
		id,
		name,
		description_".LANGUAGE_CURRENT." AS description
	FROM cms_module 
	ORDER BY name ASC
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('description', '50%');
$cmsTable->addColumn('total', '10%', 'right', 'Всего');
$cmsTable->addColumn('new', '10%', 'right', 'Новых');
$cmsTable->addColumn('delete', '10%', 'right', 'Удалить');
$table_info = $cmsTable->getTableInfo();
echo $cmsTable->display();


?>