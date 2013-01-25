<?php
/** 
 * Выдает куки для междоменной авторизации 
 * @package Pilot 
 * @subpackage User 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 

/**
 * Определяем интерфейс для поддержки интернационализации
 * @ignore 
 */
define('CMS_INTERFACE', 'SITE');

/**
 * Конфигурационный файл
 */
require_once('../../system/config.inc.php');

$key = base64_decode(globalVar($_GET['key'], ''));
$key_tokens = str_split($key, 16);
$decrypted = '';

$AES = new AES();
$cipher_key = $AES->makeKey(AUTH_CROSS_DOMAIN_AUTH_KEY);

reset($key_tokens); 
while (list(,$row) = each($key_tokens)) { 
	$decrypted .= $AES->blockDecrypt($row, $cipher_key); 
}
$decrypted = trim($decrypted);

if (preg_match('~^auth-([0-9]+)-([0-9\.]+)-([a-z0-9]+)$~i', $decrypted, $match)) {
	if ($match[2] == HTTP_IP) {
		// Продлеваем время жизни кук
		setcookie('auth_id', $match[1], time() + 30 * 86400, '/', CMS_HOST);
		setcookie('auth_code', $match[3], time() + 30 * 86400, '/', CMS_HOST);
//		$DB = DB::factory('default');
//		$query = "replace into auth_online (user_id, ip, local_ip, cookie_code, cms_host) values ('$match[1]', '".HTTP_IP."', '".HTTP_LOCAL_IP."', '$match[3]', '".CMS_HOST."')";
//		$DB->insert($query);
	}
}

?>