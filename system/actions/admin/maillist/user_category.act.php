<?php
/**
 * Изменение подписанных категорий для пользователя
* @package Pilot
* @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$user_id = globalVar($_POST['id'], 0);
$category = globalVar($_POST['category'], array());

$query = "SELECT id FROM auth_user WHERE id='$user_id'";
$DB->query($query);
if ($DB->rows == 0) {
	// не найден пользователь №...
	Action::onError(cms_message('Maillist', 'Не найден пользователь №%s', $user_id));
}

$query = "DELETE FROM maillist_user_category WHERE user_id='$user_id'";
$DB->delete($query);

if (count($category) > 0) {
	$query = "
		INSERT INTO maillist_user_category (user_id, category_id) 
		VALUES ('$user_id', '".implode("'), ('$user_id', '", $category)."')";
	$DB->insert($query);
}

$query = "UNLOCK TABLES";
$DB->query($query);



?>