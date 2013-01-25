<?php 

/**
 * Сохранение в сессии полей, что нужно отображать для конкретного фильтра    
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */


$instance_number = globalVar($_REQUEST['instance_number'], 0);
$structure_id = globalVar($_REQUEST['structure_id'], 0); 
$fields = globalVar($_REQUEST['fields'], array());   

$_SESSION['cms_filter'][$structure_id][$instance_number]['checked'] = array();

reset($fields);
while(list(, $field) = each($fields)){
	$_SESSION['cms_filter'][$structure_id][$instance_number]['checked'][] = $field['id'];
}
 
 
$_RESULT['javascript'] = "cms_filter_rows_repaint($instance_number, ".json_encode($fields).");";


?>