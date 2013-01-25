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

if (isset($_SESSION['ActionError']['email'])) {
	$TmplContent->set($_SESSION['ActionError']);
}

// Перечень сайтов
$query = "
	select 
		id,
		concat(url, ' (', users, ')')
	from (
		select 
			tb_site.id,
			tb_site.url,
			(select count(*) from auth_user where site_id=tb_site.id) as users
		from site_structure_site tb_site
		order by tb_site.priority asc
	) as tb
	where users > 0
";
$data = $DB->fetch_column($query);
$TmplContent->set('site', $data);


/**
 * Построение дерева
 *
 * @param int $category_id
 */
function show_tree($category_id) {
	global $DB;
	
	$query = "
		SELECT
			tb_category.id,
			tb_category.name_".LANGUAGE_CURRENT." AS name,
			tb_category.description_".LANGUAGE_CURRENT." AS description,
			(SELECT COUNT(*) FROM maillist_category WHERE category_id=tb_category.id) AS counter
		FROM maillist_category AS tb_category
		WHERE tb_category.category_id='$category_id' AND (sql_query = '' OR sql_query IS NULL)
	";
	$category = $DB->query($query);
	reset($category);
	while (list(,$row) = each($category)) {
		$selected = (isset($_SESSION['ActionError']['category']) && in_array($row['id'], $_SESSION['ActionError']['category'])) ? 'checked' : '';
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
$TmplContent->set('category', ob_get_clean());
?>