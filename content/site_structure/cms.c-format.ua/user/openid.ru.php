<?php

/**
 * Настройки OpenID авторизации
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


/**
 * Текущий блок
 */
$current_block = (!empty($_SESSION['auth_config_current_block'])) ? $_SESSION['auth_config_current_block'] : 'oid';
$TmplContent->set('current_block', $current_block);

/**
 * Identity
 */
$identities = $DB->query("
	SELECT 
		tb_identity.id, 
		tb_identity.provider_id, 
		tb_identity.identity,
		tb_provider.name_".LANGUAGE_CURRENT." as provider,
		tb_provider.icon_context
	FROM auth_user_oid_identity as tb_identity
	INNER JOIN auth_user_oid_provider as tb_provider ON tb_provider.id = tb_identity.provider_id
	WHERE tb_identity.user_id = '".Auth::getUserId()."'
	ORDER BY tb_identity.provider_id ASC, tb_identity.identity ASC
");

$TmplContent->set('identity_count', $DB->rows);

reset($identities);
while(list($index, $row) = each($identities)){ 
	$row['class'] = ($index % 2 == 0) ? 'odd' : 'even';
	$row['icon'] = "/".UPLOADS_DIR."auth_user_oid_provider/icon_context/".Uploads::getIdFileDir($row['provider_id']).".".$row['icon_context'];
	$TmplContent->iterate('/identities/', null, $row);
}


?>