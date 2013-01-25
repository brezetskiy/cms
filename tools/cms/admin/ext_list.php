<?php
/**
 * Выбор разделов через подгружаемое меню
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2005
 * @version 6.0
 */

/**
 * Определяем языковой интерфейс
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурация
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

// Аунтификация
new Auth(true);

$fk_table_id = globalVar($_GET['table_id'], 0);
$open_id = globalVar($_GET['open_id'], 0);
$input_id = globalVar($_GET['field_name'], '');
$fk_table = cmsTable::getInfoById($fk_table_id);

define("FIELD_NAME", $input_id);

$select = array();
$table = cmsTable::getInfoById($fk_table_id);
$fields = cmsTable::getFields($fk_table_id);


$TmplDesign = new Template(SITE_ROOT.'templates/cms/admin/ext_list');
$TmplDesign->set('title', $table['title']);
$TmplDesign->set('fk_table_id', $fk_table_id);
$TmplDesign->set('fkey_reference', Misc::cmsFKeyReference($fk_table_id, 0, array('id' => $open_id)));
echo $TmplDesign->display();
?>