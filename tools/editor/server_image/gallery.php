<?php
/**
* ����, � ������� ��������� ��� IFRAME, � ����� �� ������� ������������ �������� 
* ��������, � � ������ ������������� ��
*/


/**
* ���������� ��������� ��� ��������� �������������������
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

$TmplDesign = new Template(SITE_ROOT.'templates/editor/server_image/gallery');
$TmplDesign->set('field_name', globalVar($_GET['field_name'], ''));
$TmplDesign->set('table_name', globalVar($_GET['table_name'], ''));
$TmplDesign->set('id', globalVar($_GET['id'], ''));

echo $TmplDesign->display();
?>