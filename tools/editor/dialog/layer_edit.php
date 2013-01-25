<?php 

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/dialog/layer_edit');

$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$id = globalVar($_GET['id'], 0);

$TmplDesign->set('table_name', $table_name);
$TmplDesign->set('field_name', $field_name);
$TmplDesign->set('id', $id);



/**
* Выводим таблицы стилей, которые определяются пользователем
*/
require_once(INC_ROOT.'editor/editor.inc.php');
$style = parse_css(SITE_ROOT.'css/site/content.css');
reset($style);
while(list(,$row) = each($style)) {
	if (strtoupper($row['element']) != 'DIV') continue;
	$TmplDesign->iterate('/style/', null, array('title' => $row['title'], 'class' => $row['class']));
}
unset($style);

echo $TmplDesign->display();
?>