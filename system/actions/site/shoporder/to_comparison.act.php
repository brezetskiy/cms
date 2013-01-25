<?php
/** 
 * Добавление товара в список сравнения
 * @package Pilot 
 * @subpackage ShopOrder 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$id = globalVar($_REQUEST['id'], 0);
$_RESULT['javascript'] = "";

if(empty($id)){
	exit;
}

if(isset($_SESSION['comparison_box']) && count($_SESSION['comparison_box']) >=4){
	$_RESULT['javascript'] .= "displayAlert();";	
	Action::finish();
}

$_SESSION['comparison_box'][$id] = $id;

if(count($_SESSION['comparison_box']) == 2){
	$_RESULT['javascript'] .= "activateComparisonLink();";
}

$_RESULT['javascript'] .= "$(\"#to_comparison_$id\").html(\"<img src=\'/design/220volt/img/bg-compare.gif\' border=\'0\' align=\'bottom\'> Перейти к <a href=\'/Comparison/\' >сравнению</a>\"); $(\"#comparison_total\").html($(\"#comparison_total\").html()*1+1)";    
	 



?>  