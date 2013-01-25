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

$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$id = globalVar($_GET['id'], '');

$field_name = globalVar($_GET['field_name'], '');
$current_dir = globalVar($_GET['current_dir'], '');
$open_dir = globalVar($_GET['open_dir'], '');

if (empty($open_dir)) {
	$open_dir = Uploads::getStorage($table_name, $field_name, $id).'/';
}

$TmplDesign = new Template(SITE_ROOT.'templates/editor/server_image/iframe');
$TmplDesign->set('field_name', $field_name);

// Открываем картинки, которые связаны с текущей страницей
if (!empty($open_dir)) {
	$TmplDesign->iterate('/img_list/', null, array('open_dir' => $open_dir));
}

if ($current_dir == '') {
	$TmplDesign->iterate('/ul_root/');
} else {
	$TmplDesign->iterate('/ul_hidden/', null, array('id' => fileinode(UPLOADS_ROOT.$current_dir)));
}

$data = Filesystem::getDirContent(UPLOADS_ROOT.$current_dir, false, true, false);

reset($data);
while (list(, $dir) = each($data)) {
	$row = array(
		'id' => fileinode(UPLOADS_ROOT.$current_dir.$dir),
		'name' => substr($dir, 0, -1),
		'current_dir' => $current_dir.$dir,
		'url' => $current_dir.$dir,
		'open_dir' => $open_dir,
		'open' => (preg_match("%".$current_dir.$dir."%", $open_dir)) ? 'src="/tools/editor/server_image/iframe.php?current_dir='.$current_dir.$dir.'&open_dir='.$open_dir.'"' : '',
		'sub_parent' => (count(Filesystem::getDirContent(UPLOADS_ROOT.$current_dir.$dir, false, true, false)))
	);
	$row['url'] = addcslashes($row['url'], "'");
	$node_id = $TmplDesign->iterate('/node/');
	if ($row['sub_parent'] == 0) {
		$TmplDesign->iterate('/node/no_childs/', $node_id, $row);
	} else {
		$TmplDesign->iterate('/node/with_childs/', $node_id, $row);
	}
}

if ($current_dir != '') {
	$TmplDesign->iterate('/script/', null, array('id' => fileinode(UPLOADS_ROOT.$current_dir)));
}

echo $TmplDesign->display();
?>