<?php
/**
* ����� ������� ��������� �������
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
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

new Auth('admin');


$TmplDesign = new Template(SITE_ROOT.'templates/editor/dialog/anchor');
echo $TmplDesign->display();

?>