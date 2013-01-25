<?php
/**
 * Параметры пользователя
 * @package Pilot
 * @subpackage Auth
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


$group_id = globalVar($_GET['group_id'], 0);
function cms_filter($row) {
	$row['name'] = "<a href='./?group_id=$row[id]'>$row[name]</a>";
	return $row;
}

// Группы
$query = "
	SELECT 
		id,
		uniq_name,
		name,
		priority
	FROM auth_user_group
	ORDER BY priority ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', '/Admin/User/Info/?');
$cmsTable->addColumn('name', '70%');   
$cmsTable->addColumn('uniq_name', '20%');
echo $cmsTable->display();
unset($cmsTable);


function cms_filter_params($row) {
	if(empty($row['is_display'])) $row['name'] = "<span style='color:#777;'>{$row['name']}</span>";
	
	return $row;
} 

$query = "
	SELECT
		id,
		case
			when data_type='devider' then concat('<b>', name, '</b>')
			when required=1 then concat(name, '<span class=asterix>*</span>')
			else name 
		end as name,
		uniq_name,
		data_type,
		is_display,
		priority
	FROM auth_user_group_param
	where 1 ".where_clause("group_id", $group_id)."
	order by priority
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_filter_params');
$cmsTable->setParam('show_parent_link', false);
$cmsTable->setParam('show_path', false); 
$cmsTable->addColumn('name', '30%');
$cmsTable->addColumn('uniq_name', '15%');
$cmsTable->addColumn('data_type', '15%');
echo $cmsTable->display();
unset($cmsTable);


?>
