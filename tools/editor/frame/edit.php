<?php
/**
* ����� � ���������, ������� ������������ � SiteWerk
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
* ��������� ��������� ������� GET 
* @param string $table_name
* @param string $field_name
* @param int $id
*/


/**
* ���������� ��������� ��� ��������� �������������������
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* ���������������� ����
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');


// ������������ ���  ������ � ������������� ���������
new Auth(true);

/**
* ���������� ����������
*/
$id = globalVar($_GET['id'], 0);
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$css = globalVar($_GET['css'], '');


/**
* ���������, �� ������������ �� ���� ������ �������������
*/
$owner = CVS::isOwner($table_name, $field_name, $id);
if ($owner !== true) {
	// ������ ��������� � ���, ��� �������� - �������������
	echo '
		��������, ������� �� ������ ������������� �������<br>
		������������� <b>'.$owner['login'].'</b>.<br><br>
		����� ��������: <b>'.$owner['datetime'].'</b>.<br><br>
		������������� ��������� ���������� �� �������� <br>����� �������������� - ����������.
	';
	exit;
}
unset($owner);


/**
* �������� ���� �������������� ������� �������������
*/
if (!Auth::editContent($table_name, $id)) {
	echo '� ��� ��� ���� �� �������������� ���� ��������';
	exit;
}

$TmplDesign = new Template(SITE_ROOT.'templates/editor/frame/edit');
// ��������� ������� ������
$TmplDesign->set('css', $css);

// ������� �������
$query = "select `$field_name` from `$table_name` where id='$id'";
$content = $DB->result($query);
$TmplDesign->set('content', id2url($content, true));
echo $TmplDesign->display();

?>