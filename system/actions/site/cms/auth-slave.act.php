<?php
/**
 * Отдача авторизационных данных сайту, который требует авторизацию
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$continue = globalVar($_GET['continue'], '');

if (!empty($continue)) {
	$query = "select url from site_structure_site_alias";
	$sites = $DB->fetch_column($query);
	
	$continue_parsed = @parse_url($continue);
	if ($continue_parsed !== false && in_array($continue_parsed['host'], $sites)) {
		if (Auth::isLoggedIn()) {
			
			$AES = new AES();
			$AES->key = $AES->makeKey(AUTH_CROSS_DOMAIN_AUTH_KEY);
			$AES->rnd = md5(microtime().HTTP_IP.rand(0,1000));
			$AES->crypted = '';
			
			$cross_domain_auth_str = 'auth-'.$_SESSION['auth']['id'].'-'.HTTP_IP.'-'.$_SESSION['auth']['cookie_code'];
			$cross_domain_auth_str_tokens = str_split($cross_domain_auth_str, 16);
			
			reset($cross_domain_auth_str_tokens); 
			while (list(,$row) = each($cross_domain_auth_str_tokens)) {
				$row = str_pad($row, 16, ' ', STR_PAD_RIGHT);
				$AES->crypted .= $AES->blockEncrypt($row, $AES->key); 
			}
			
			$key = urlencode(base64_encode($AES->crypted));
			
			header("Location: http://$continue_parsed[host]/action/cms/auth-returned/?auth=$key&r={$AES->rnd}&continue=".urlencode($continue));
			exit;
		} else {
			/**
			 * Пользователь не авторизован на главном сайте
			 */
			header("Location: http://$continue_parsed[host]/action/cms/auth-returned/?auth=no&continue=".urlencode($continue));
			exit;
		}
	} else {
		echo "[e] Bad continue: $continue_host , $continue";
	}
} else {
	echo "[e] Empty continue";
}