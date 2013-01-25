<?php
/**
 * Редактирование групп рассылок на которые подписан пользователь сайта
 * 
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$user_id = globalVar($_GET['user_id'], 0);
$TmplContent->set('user_id', $user_id);

// Определяем информацию о пользователе
$query = "
	SELECT 
		CONCAT('(', login, ') <', email, '>') AS email, 
		name 
	FROM auth_user 
	WHERE id='$user_id'
";
$user = $DB->query_row($query);
if ($DB->rows == 0) {
	Action::setError(cms_message('Maillist', 'Не найден пользователь №%s', USER_ID));
	header('Location: /Admin/Maillist/Subscribers/');
	exit;
}
$TmplContent->set('user', $user);


// Определяем информацию о подписанных группах
$query = "
	SELECT category_id 
	FROM maillist_user_category
	WHERE user_id='$user_id'
";
$subscribed = $DB->fetch_column($query, 'category_id', 'category_id');


/**
 * Построение дерева
 * @param int $category_id
 */
function show_tree($category_id) {
	global $DB, $subscribed;
	
	$query = "
		SELECT
			tb_category.id,
			tb_category.name_".LANGUAGE_CURRENT." AS name,
			tb_category.description_".LANGUAGE_CURRENT." AS description,
			(SELECT COUNT(*) FROM maillist_category WHERE category_id=tb_category.id) AS counter
		FROM maillist_category AS tb_category
		WHERE 
			tb_category.category_id='$category_id' 
		AND (sql_query='' OR sql_query IS NULL)
	";
	$categories = $DB->query($query);
	reset($categories);
	while (list(,$row) = each($categories)) {
		$selected = (isset($subscribed[ $row['id'] ])) ? 'checked' : '';
		echo '
			<input '.$selected.' type="checkbox" name="category[]" value="'.$row['id'].'" id="category_'.$row['id'].'">
			<label for="category_'.$row['id'].'">'.$row['name'].'</label>
			<br>
		';
		
		if (!empty($row['description'])) {
			echo '<span class="comment">'.$row['description'].'</span><br>'; 
		}
		
		if ($row['counter'] > 0) {
			echo '<ul>';
			show_tree($row['id']);
			echo '</ul>';
		}
	}
}

ob_start();
show_tree(0);
$TmplContent->set('categories', ob_get_clean());

?>