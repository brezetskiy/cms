<?php
/**
* Групповое изменение параметров подписки
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/


/**
 * Принимаем переменные
 */
$mode = globalEnum($_POST['mode'], array('ignore', 'replace', 'add'));
$mode2 = globalEnum($_POST['mode2'], array('ignore', 'replace', 'add'));
$subscribers = globalVar($_POST['subscribers'], '');
$category = globalVar($_POST['category'], array());
$all_subscribers = globalVar($_POST['all_subscribers'], 0);
$site_id = globalVar($_REQUEST['site_id'], 0);

if ($all_subscribers) {
	$mode = $mode2;
}
unset($mode2);


if ($all_subscribers) {
	
	if ($mode == 'replace') {
		$query = "select id from auth_user where 1 ".where_clause('site_id', $site_id);
		$user = $DB->fetch_column($query);
		
		$query = "DELETE FROM maillist_user_category where user_id in (0".implode(",", $user).")";
		$DB->delete($query);
		
		unset($user);
	}
	
	
	$query = "
		INSERT IGNORE INTO maillist_user_category (user_id, category_id)
		SELECT
			tb_user.id AS user_id,
			tb_category.id AS category_id
		FROM auth_user AS tb_user
		CROSS JOIN maillist_category AS tb_category
		WHERE 
			tb_category.id IN (0".implode(",", $category).")
			".where_clause('tb_user.site_id', $site_id)."
	";
	$DB->insert($query);
	
	Action::setSuccess(cms_message('Maillist', 'На рассылку подписаны %d пользователей', $DB->affected_rows));
	
} else {
	$users = array();
	
	// Добавляем пользователей
	preg_match_all('/([a-z0-9_\.\-]+@[a-z0-9_\.\-]+\.[a-z]{2,4})/i', $subscribers, $matches);
	reset($matches[1]);
	while (list(,$row) = each($matches[1])) {
		
		// Добавляем пользователя
		$query = "INSERT IGNORE INTO auth_user (email, login) VALUES ('$row', '$row')";
		$DB->insert($query);
		if ($DB->affected_rows == 0 && $mode == 'ignore') {
			continue;
		}
		
		$query = "SELECT id FROM auth_user WHERE email = '$row' OR login = '$row'";
		$users[] = $DB->result($query);
	}
	
	if ($mode == 'replace') {
		$query = "DELETE FROM maillist_user_category WHERE user_id IN (0".implode(",", $users).")";
		$DB->delete($query);
	}
	
	// Добавляем группы
	$query = "
		INSERT IGNORE INTO maillist_user_category (user_id, category_id)
		SELECT
			tb_user.id AS user_id,
			tb_category.id AS category_id
		FROM auth_user AS tb_user
		CROSS JOIN maillist_category AS tb_category
		WHERE
			tb_user.id IN (0".implode(",", $users).")
			AND tb_category.id IN (0".implode(",", $category).")
	";
	$DB->insert($query);
	
	Action::setSuccess(cms_message('Maillist', 'Изменены категории для %d подписчиков', count($users)));
}





?>