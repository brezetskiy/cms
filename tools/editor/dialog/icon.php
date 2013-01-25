<?php 
/**
* Создание пользовательских таблиц стилей
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/dialog/icon');

$images = Filesystem::getAllSubdirsContent(SITE_ROOT.'img/shared/ico/', true);

$tmpl_row = $TmplDesign->iterate('/row/', null, array());
$counter = -1;

reset($images);
while(list(,$row) = each($images)) {
	$counter++;
	if ($counter >= 5) {
		$tmpl_row = $TmplDesign->iterate('/row/', null, array());
		$counter = 0;
	}
	$size = getimagesize($row);
	$TmplDesign->iterate('/row/td/', $tmpl_row, array('img'=>Uploads::getURL($row), 'width' => $size[0], 'height' => $size[1]));
}

echo $TmplDesign->display();

?>