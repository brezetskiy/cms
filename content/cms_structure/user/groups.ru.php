<?php
/**
 * Группы администраторов
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

function cms_filter($row) {
	global $DB;
	
	$row['priv'] = "<a href='./Priv/?group_id=$row[id]'>Настроить</a>";
	$row['site'] = "<a href='./Site/?group_id=$row[id]'>Настроить</a>";
	$row['is_admin'] = ($row['is_admin']) ? "<input type=checkbox disabled checked>":"<input type=checkbox disabled>";
	 
	$cmsFilterLink = new cmsFilterLink($DB->db_alias, '/Admin/User/Users/', 1);
	$cmsFilterLink->addCondition('=', 'auth_user', 'group_id', 1);
	$link = $cmsFilterLink->getLink();
	$row['user_count'] = "<a href=\"$link\">".$row['user_count']."</a>";
	
	return $row;
}



$query = "
	SELECT 
		tb_group.id, 
		tb_group.name,
		(select count(*) from auth_user where group_id=tb_group.id) AS user_count,
		is_admin
	FROM auth_group AS tb_group
	GROUP BY tb_group.id
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('show_filter', false);
$cmsTable->addColumn('name', '40%');
$cmsTable->addColumn('priv', '15%', 'center', 'Привилегии');
$cmsTable->addColumn('site', '15%', 'center', 'Редактирование');
$cmsTable->addColumn('user_count', '15%', 'right', 'Пользователей');
$cmsTable->addColumn('is_admin', '5%', 'center', 'Админ');
echo $cmsTable->display();
unset($cmsTable);

?>
