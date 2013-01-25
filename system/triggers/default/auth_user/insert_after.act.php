<?php

/**
 * ѕарсим список IP адресов, с которых ограничен доступ 
 * дл€ группы в таблицу auth_user_allow_ip
 */

$values = array();
$query = "DELETE FROM auth_user_allow_ip WHERE user_id = '".$this->NEW['id']."'";
$DB->delete($query);
reset($allow_ip); 
while (list(,$row) = each($allow_ip)) { 
	if (preg_match("~^([0-9]{1,3}\.){3}[0-9]{1,3}$~", $row)) {
		// просто IP
		$values[] = "('".$this->NEW['id']."', INET_ATON('$row'), INET_ATON('$row'))";
	} elseif (preg_match("~^(([0-9]{1,3}\.){3}[0-9]{1,3})\/([0-9]{1,2})$~", $row, $match)) {
		// CIDR
		$ip = $match[1];
		$cidr = $match[3];
		$mask = ip2long('255.255.255.255') << (32 - $cidr);
		$first_ip = long2ip((ip2long($ip) & $mask));
		$last_ip = long2ip((ip2long($ip) & $mask) + pow(2, 32-$cidr) - 1);
		$values[] = "('".$this->NEW['id']."', INET_ATON('$first_ip'), INET_ATON('$last_ip'))";
	} elseif (preg_match("~^(([0-9]{1,3}\.){3}[0-9]{1,3})\-(([0-9]{1,3}\.){3}[0-9]{1,3})$~", $row, $match)) {
		// диапазон адресов
		$values[] = "('".$this->NEW['id']."', INET_ATON('$match[1]'), INET_ATON('$match[3]'))";
	} else {
		Action::setWarning("Ќе удалось распознать адрес '$row'");
	}
}

if (count($values) > 0) {
	$query = "INSERT IGNORE INTO auth_user_allow_ip (user_id, ip_from, ip_to) VALUES ".implode(", ", $values);
	$DB->insert($query);
}

?>