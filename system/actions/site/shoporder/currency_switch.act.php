<?php
/** 
 * ƒобавление товара в список сравнени€
 * @package Pilot 
 * @subpackage ShopOrder 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$id = globalVar($_REQUEST['id'], 0);

if(empty($id)){
	exit;
}

$_SESSION['current_currency'] = $id;	 

?>  