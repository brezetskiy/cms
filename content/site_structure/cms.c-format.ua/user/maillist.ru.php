<?php
/**
 * Подписка на рассылку новостей
 * 
 * Если пользователь залогинился, то выдаём просто список групп, если нет,
 * то предлагаем ему зарегистрироватся, при этом если его e-mail адрес есть 
 * в базе, то говорим, что он уже зарегистрирован и высылаем ему пароль
 * 
 * @package Maillist
 * @subpackage Content_Site
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
$user_id = Auth::isLoggedIn();
$email = globalVar($_POST['email'], '');

/**
 * Построение дерева групп рассылок
 * @ignore 
 * @param int $group_id
 * @return void
 */
function show_tree($category_id) {
	global $DB, $subscribed, $TmplContent;
	
	$query = "
		SELECT
			tb_category.id,
			tb_category.name_".LANGUAGE_CURRENT." AS name,
			tb_category.description_".LANGUAGE_CURRENT." AS description,
			(SELECT COUNT(*) FROM maillist_category WHERE category_id=tb_category.id) AS counter
		FROM maillist_category AS tb_category
		WHERE 
			tb_category.category_id='$category_id'
			AND (
				sql_query is null OR
				TRIM(sql_query) = ''
			)
			AND (
				tb_category.private='false'
				OR (
					tb_category.private='true'
					AND tb_category.id IN (0".implode(",", $subscribed).")
					)
				)
	";
	$category = $DB->query($query);
	
	/**
	 * Создается новый массив Relations[$category_id] для хранения
	 * групп, зависимых от этой
	 */
	$tmpl_category = 0;
	if (!empty($category_id)) {
		$tmpl_category = $TmplContent->iterate('/category/', null, array('id' => $category_id));
	}
		
	$counter = 0;
	reset($category);
	while (list(,$row) = each($category)) {
		$selected = (in_array($row['id'], $subscribed)) ? 'checked' : '';
		$row['category_id'] = $category_id;
		$row['i'] = $counter;
 		if (!empty($tmpl_category)) {
			$TmplContent->iterate('/category/relations/', $tmpl_category, $row);
		}
		
		echo '
			<input onclick="check('.$row['id'].', this.checked)" '.$selected.' type="checkbox" name="category[]" value="'.$row['id'].'" id="category_'.$row['id'].'">
			<label for="category_'.$row['id'].'">'.$row['name'].'</label>
			<br>
		';
		
		if (!empty($row['description'])) {
			echo '<span class="comment">'.$row['description'].'</span><br>';
		}
		
		if ($row['counter'] > 0) {
			echo '<div style="margin:0; margin-left:20px;" id="maillist_group_'.$row['id'].'">';
			show_tree($row['id']);
			echo '</div>';
		}
		$counter++;
	}
}


if (isset($_SESSION['ActionError']['email'])) {
	$TmplContent->set('error', $_SESSION['ActionError']);
} else {
	$TmplContent->set('error', array('email' => $email));
}



// Определяем информацию о подписанных группах

if (isset($_SESSION['ActionError']['category'])) {
	$subscribed = $_SESSION['ActionError']['category'];
} elseif (Auth::isLoggedIn()) {
	$query = "SELECT category_id FROM maillist_user_category WHERE user_id='$user_id'";
	$subscribed = $DB->fetch_column($query, 'category_id', 'category_id');
} else {
	$subscribed = array();
}



ob_start();
show_tree(0);
$TmplContent->set('category', ob_get_clean());



?>