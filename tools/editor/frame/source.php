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


$TmplDesign = new Template(SITE_ROOT.'templates/editor/frame/source');
echo $TmplDesign->display();

?>