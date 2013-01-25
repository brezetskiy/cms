<?php
/**
 * Вывод списка соробщений, которые принадлежат модулю
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */


define('MODULE_ID', globalVar($_GET['module_id'], 0));

$query = "
	SELECT 
		tb_message.id, 
		tb_message.message_".LANGUAGE_CURRENT." AS message,
		tb_interface.name AS interface
	FROM cms_message AS tb_message
	INNER JOIN cms_interface AS tb_interface ON tb_interface.id=tb_message.interface_id
	WHERE module_id='".MODULE_ID."'
	ORDER BY tb_interface.name ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('subtitle', 'interface');
$cmsTable->addColumn('id', '5%', 'center', 'id');
$cmsTable->addColumn('message', '80%');
echo $cmsTable->display();
unset($cmsTable);
?>