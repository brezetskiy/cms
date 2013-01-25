<?php

/**
 * Инициализация виджета
 * Используется при необходимости совместной работы двух виджетов на одной странице
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */

if(empty($_SESSION['oid_widget']['reloaded'])){
	if(!empty($_SESSION['oid_clarify_auto'])) unset($_SESSION['oid_clarify_auto']);
	if(!empty($_SESSION['oid_clarify_manual'])) unset($_SESSION['oid_clarify_manual']);
}

unset($_SESSION['oid_widget']['reloaded']);

$_REQUEST['name'] = globalVar($_REQUEST['name'], uniqid('rand'));
$_REQUEST['template'] = globalVar($_REQUEST['template'], 'box');
 
$_RESULT['oid_widget__'.$_REQUEST['template'].'__content'] = TemplateUDF::oid_widget($_REQUEST);
$_RESULT['javascript'] = "$('#oid_widget__".$_REQUEST['name']."').fadeIn();";
  


?>