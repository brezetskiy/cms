<?php
/** 
 * Показывает переводчику шаблон 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
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

// Аунтификация при  работе с запароленными разделами
new Auth(true);

$template_id = globalVar($_GET['template_id'], 0);

$query = "
	select group_concat(tb_template.filename order by tb_relation.priority asc separator '/')
	from cms_language_template_relation as tb_relation
	inner join cms_language_template as tb_template on tb_template.id=tb_relation.parent
	where tb_relation.id='$template_id'
";
$file = $DB->result($query);
$file = TEMPLATE_ROOT . $file .'.ru.tmpl';


echo preg_replace("/{[^}]+}/", "", file_get_contents($file));
?>