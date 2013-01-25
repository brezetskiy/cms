<?php
/**
 * ������������ ������� ������ ��������
 * @package Pilot
 * @subpackage Banner
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 * @cron 12 0 * * *
 */

/**
 * ���������� ���������
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');

// ������������� ���������� ������� ����������
chdir(dirname(__FILE__));

/**
* ���������������� ����
*/
require_once('../../config.inc.php');


// ���������� ������������ ������� �������     
Shell::collision_catcher();


$DB = DB::factory('default');

Banner::buldCache(5);

?>