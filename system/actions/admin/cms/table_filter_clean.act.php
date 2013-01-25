<?php
/**
 * Очистка фильтра для таблицы
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

$structure_id = globalVar($_REQUEST['structure_id'], 0);
$instance_number = globalVar($_REQUEST['instance_number'], 0);
$admin_id = Auth::getUserId();

$query = "
	DELETE FROM cms_filter 
	WHERE 
			admin_id='$admin_id' 
		AND structure_id='$structure_id'
		AND instance_number='$instance_number'
";
$DB->delete($query);


?>