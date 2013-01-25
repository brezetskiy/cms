<?php

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурация
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');

$id = globalVar($_GET['id'], 0);
$action = globalVar($_GET['action'], '');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/dialog/local_link');
$TmplDesign->setGlobal('action', $action);

if ($id == 0) {
	$TmplDesign->iterate('/ul_root/');
} else {
	$TmplDesign->iterate('/ul_hidden/', null, array('id' => $id));
}

$query = "
	SELECT 
		tb_structure.id,
		tb_structure.name_".LANGUAGE_SITE_DEFAULT." AS name,
		COUNT(tb_parent.id) AS sub_parent,
		CONCAT('http://', tb_structure.url, '/') AS url
	FROM site_structure AS tb_structure
	LEFT JOIN site_structure AS tb_parent ON tb_structure.id = tb_parent.structure_id
	WHERE tb_structure.structure_id='".$id."'
	GROUP BY tb_structure.id
	ORDER BY tb_structure.priority ASC
";
$data = $DB->query($query);

reset($data);
while (list(, $row) = each($data)) {
	$row['url'] = addcslashes($row['url'], "'");
	$node_id = $TmplDesign->iterate('/node/');
	if ($row['sub_parent'] == 0) {
		$TmplDesign->iterate('/node/no_childs/', $node_id, $row);
	} else {
		$TmplDesign->iterate('/node/with_childs/', $node_id, $row);
	}
}

if ($id != 0) {
	$TmplDesign->iterate('/script/', null, array('id' => $id));
}

echo $TmplDesign->display();
?>