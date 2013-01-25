<?php
/**
* Изменение групп, на которые подписан подписчик
* @package Pilot
* @subpackage Maillist
* @version 5.0
* @author Rudenko Ilya <rudenko@ukraine.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
*/

$user_id = Auth::isLoggedIn();
$category = globalVar($_POST['category'], array());

if (empty($user_id)) {
	// необходимо войти или зарегистрироваться
	Action::onError(cms_message('Maillist', 'Чтобы подписаться на рассылку, необходимо войти в систему. Если у Вас еще нет аккаунта - зарегистрируйтесь, это не отнимет много времени.'));
}
	
//$query = "LOCK TABLES
//				maillist_user_category WRITE, 
//				maillist_category READ, 
//				maillist_user_categorys AS tb_relation WRITE,
//				maillist_category AS tb_category READ
//		";
//$DB->query($query);

/**
 * Определяем засекреченные группы, на которые был подписан пользователь
 * и от которых он не отписался
 */
$query = "
	SELECT tb_category.id
	FROM maillist_user_category AS tb_relation
	INNER JOIN maillist_category AS tb_category ON tb_category.id=tb_relation.category_id
	WHERE 
		tb_relation.user_id='$user_id'
		AND tb_category.private='true'
		AND id IN (0".implode(", ", $category).")
";
$private = $DB->fetch_column($query);

// Удаляем старые группы
$query = "DELETE FROM maillist_user_category WHERE user_id='".$user_id."'";
$DB->delete($query);

// Добавляем новые группы
$query = "
	INSERT IGNORE INTO maillist_user_category (user_id, category_id)
	SELECT
		'$user_id' AS user_id,
		id AS category_id
	FROM maillist_category
	WHERE 
		private='false'
		AND ( sql_query is null OR trim(sql_query) = '' )
		AND id IN (0".implode(", ", $category).")
	";
$DB->insert($query);

// Добавляем засекреченные группы, объеденение при помощи UNION 
// с предыдущим запросом вызовет ошибку
$query = "
	INSERT IGNORE INTO maillist_user_category (user_id, category_id)
	SELECT
		'".$user_id."' AS user_id,
		id AS category_id
	FROM maillist_category
	WHERE id IN (0".implode(", ", $private).")
	";
$DB->insert($query);

$query = "UNLOCK TABLES";
$DB->query($query);

Action::setSuccess(cms_message('Maillist', 'Изменения успешно сохранены'));	
Action::finish();

?>