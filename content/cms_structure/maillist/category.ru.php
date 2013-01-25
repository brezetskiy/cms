<?php
/**
 * Список групп почтовой рассылки
 * @package Maillist
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$category_id = globalVar($_GET['category_id'], 0);

function cms_filter($row) {
	$row['name'] = "<a href='?category_id=$row[id]'>$row[name]</a><br><span class='comment'>$row[description]</span>";
	$row['_var'] = "<a href='./Vars/?category_id=$row[id]'><img src=\"/img/maillist/combo_box.png\" border=0 alt=\"Переменные\"></a>";
	return $row;
}

$query = "
	SELECT 
		id, 
		priority,
		name_".LANGUAGE_CURRENT." as name,
		description_".LANGUAGE_CURRENT." as description,
		private,
		test,
		if(
			sql_query is null or sql_query='',
			'<input type=checkbox disabled>',
			'<input type=checkbox checked disabled>'
		) as sql_query
	FROM maillist_category
	WHERE category_id='$category_id'
	ORDER BY priority ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addColumn('name', '40%');
$cmsTable->addColumn('test', '10%');
$cmsTable->setColumnParam('test', 'editable', true);
$cmsTable->addColumn('private', '10%', null, 'Скрытая');
$cmsTable->setColumnParam('private', 'editable', true);
$cmsTable->addColumn('sql_query', '10%', 'center', 'SQL');
$cmsTable->addColumn('_var', '10%', 'center', 'Переменные');
echo $cmsTable->display();
unset($cmsTable);
?>