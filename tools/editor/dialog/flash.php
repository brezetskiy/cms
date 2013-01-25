<?php


/**
 * Îןנוהוכÿול טםעפנויס
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/dialog/flash');
$TmplDesign->set('id', globalVar($_GET['id'], 0));
$TmplDesign->set('table_name', globalVar($_GET['table_name'], ''));
$TmplDesign->set('field_name', globalVar($_GET['field_name'], ''));


echo $TmplDesign->display();
?>