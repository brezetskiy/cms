<?php
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



?>