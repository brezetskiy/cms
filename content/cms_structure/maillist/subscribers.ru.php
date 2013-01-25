<?php
/**
 * Список подписчиков
 * @package Maillist
 * @subpackage Content_Admin
 * @author Rudenko Ilua <rudenko@id.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

function cms_filter($row) {
	$row['login'] = "<a href='./Category/?user_id=$row[id]'>$row[login]</a>";
	return $row;
}

/**
 * Вывод данных
 */
$query = "
	SELECT
		tb_user.id, 
		tb_user.login,
		tb_user.name,
		IF(
			tb_stoplist.email IS NOT NULL, 
			CONCAT('<font color=silver><b>', tb_user.email ,'</b></font>'), 
			tb_user.email
		) AS email,
		IFNULL(
			CONCAT('<span class=\"comment\">', GROUP_CONCAT(DISTINCT tb_category.name_".LANGUAGE_CURRENT." ORDER BY tb_category.name_".LANGUAGE_CURRENT." SEPARATOR ', '), '</span>'),
			'<span style=\"color:blue;\">не подписан</span>'
		) AS category,
		IF (COUNT(tb_category.id)>0, 1, -1) AS tb_category_filter
	FROM auth_user AS tb_user
	LEFT JOIN maillist_stoplist AS tb_stoplist ON tb_stoplist.email = tb_user.email
	LEFT JOIN maillist_user_category AS tb_relation ON tb_relation.user_id=tb_user.id
	LEFT JOIN maillist_category AS tb_category ON tb_category.id=tb_relation.category_id
	GROUP BY tb_user.id
	ORDER BY tb_user.email ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('title', 'Подписчики');
$cmsTable->addColumn('login', '20%');
$cmsTable->addColumn('email', '20%');
$cmsTable->addColumn('category', '40%', null, 'Категории рассылок');
$cms_view = $cmsTable->display();
unset($cmsTable);

$TmplContent->set('cms_view', $cms_view);
?>