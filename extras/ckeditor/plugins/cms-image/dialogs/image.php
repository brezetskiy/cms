<?php 
/**
* ������� ������ ��������, ������� �� �����������
*/

/**
* ���������� ��������� ��� ��������� �������������������
* @ignore
*/
define('CMS_INTERFACE', 'ADMIN');

require_once('../../../../../system/config.inc.php');

$TmplDesign = new Template(dirname(__FILE__).'/image');
echo $TmplDesign->display();
?>