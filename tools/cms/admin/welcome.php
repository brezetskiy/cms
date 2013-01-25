<?php
/**
 * ������������� ������������ �� ��������� ������������� ��������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


/**
 * ���������� �������� ���������
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

/**
* ������������
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

// ������������ ���  ������ � ������������� ���������
new Auth(true);

$query = "select value from cms_user_settings where user_id='".Auth::getUserId()."' and name='last_visited_page'";
$location = $DB->result($query);
if ($DB->rows == 0) {
	$location = '/Admin/Site/Structure/';
}

header("Location: $location");
exit;


?>