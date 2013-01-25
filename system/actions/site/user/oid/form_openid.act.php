<?php

/**
 * Открывает форму для ввода openid идентификатора
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2012
 */

$widget_name = globalVar($_REQUEST['widget_name'], '');
$widget_type = globalVar($_REQUEST['widget_type'], '');
$provider_id = globalVar($_REQUEST['provider_id'], 0);

 
$provider_name = $DB->result("SELECT name_".LANGUAGE_CURRENT." as name FROM auth_user_oid_provider WHERE id = '$provider_id'");
$_RESULT['javascript'] = 'delta_action("oid_widget__form_openid_send(\''.$widget_name.'\', \''.$provider_name.'\', '.$provider_id.')", "'.AuthOID::displayOpenIDForm($widget_name, $widget_type, $provider_id).'", "message_close()"); ';


?>