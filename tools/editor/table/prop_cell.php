<?php
/**
* Фрейм который сохраняет контент
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');


$TmplDesign = new Template(SITE_ROOT.'templates/editor/table/prop_cell');

/**
 * Выводим таблицы стилей, которые определяются пользователем
 */
require_once(INC_ROOT.'editor/editor.inc.php');
$style = parse_css(SITE_ROOT.'css/site/content.css');
reset($style);
while(list(,$row) = each($style)) {
	if ($row['element'] != 'TD') continue;
	$TmplDesign->iterate('/style/', null, array('title' => $row['title'], 'class' => $row['class']));
}
unset($style);

echo $TmplDesign->display();

?>