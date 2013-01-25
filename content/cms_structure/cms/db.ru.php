<?php
/**
 * ����� ������ ��
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

function cms_prefilter($row) {
	$row['name'] = db_config_constant("name", $row['alias']);
	$row['host'] = db_config_constant("host", $row['alias']);
	$row['login'] = db_config_constant("login", $row['alias']);
	//$row['password'] = db_config_constant("password", $row['alias']);
	$row['type'] = db_config_constant("type", $row['alias']);
	
	return $row;
}


$query = "
	SELECT id, alias, concat('<a href=\"./Tables/?db_alias=', alias, '\">', UPPER(alias), '</a>') as alias_link
	FROM cms_db
	ORDER BY alias ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('prefilter', 'cms_prefilter');

$cmsTable->addColumn('id', '5%', 'center', 'ID');
$cmsTable->addColumn('alias_link', '15%', 'center', '�����');
$cmsTable->addColumn('name', '15%', 'left', '��� ����');
$cmsTable->addColumn('host', '15%', 'left', '����');
$cmsTable->addColumn('login', '15%', 'left', '�����');
//$cmsTable->addColumn('password', '15%', 'left', '������');
$cmsTable->addColumn('type', '10%', 'center', '��� ����������');

echo $cmsTable->display();
unset($cmsTable);


?>

<div class="context_help">
	<b>������������ ��� ������ �������� � ��������� ���������������� ����:</b> ../www/system/config.inc.php
	<br/>������������ � ���� ��������: DB_x_NAME, DB_x_HOST, DB_x_LOGIN, DB_x_PASSWORD, DB_x_TYPE, ��� x - ����� ��������������� ���� ������
</div>