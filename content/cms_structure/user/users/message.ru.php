<?php
/**
 * Список Сообшений пользователя  
 * @package Pilot
 * @subpackage PM
 * @author Markovskiy Dima<dima@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$user_id  = globalVar($_GET['user_id'], 0);

$query = "
	select 
		tb_message.*,
		tb_type.name as type,
		tb_category.name as category	
	from pm_message as tb_message
	inner join pm_type as tb_type on tb_type.id = tb_message.type_id
	inner join pm_category as tb_category on tb_category.id = tb_message.category_id
	inner join auth_user_admin_view as tb_view on tb_view.id = tb_message.creator_id 
	where tb_message.user_id  = '".$user_id."' 
";

$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', "/Admin/User/Users/?");
$cmsTable->addColumn('type', '40%', 'left', 'Тип');
$cmsTable->addColumn('message', '40%', 'left', 'Сообщение');
$cmsTable->addColumn('active', '10%', 'center', 'Активный');
$cmsTable->setColumnParam('active', 'editable', true);

echo $cmsTable->display();
unset($cmsTable);

?>