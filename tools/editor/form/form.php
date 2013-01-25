<?php
/**
* ��������� �� ���� ����� ��� �������� ������
* @package Pilot
* @subpackage Editor
* @version 5.3
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2006
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

$TmplDesign = new Template(SITE_ROOT.'templates/editor/form/form');
echo $TmplDesign->display();

?>