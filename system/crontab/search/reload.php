<?php 
/**
 * ������� ������ ��� ������
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 * @cron none
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

$DB = DB::factory('default');

// ���������� ������������ ������� �������
Shell::collision_catcher();

// ���������� ���������� �������
Search::reload();

?>