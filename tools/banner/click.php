<?php
/**
* ���������� ������ �� ��������
*
* @package Pilot
* @subpackage Banner
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

/**
* ���������� ��������� ��� ��������� �������������������
* @ignore
*/
define('CMS_INTERFACE', 'SITE');

/**
* ���������������� ����
*/
require_once('../../system/config.inc.php');

$DB = DB::factory('default');

$banner_id = globalVar($_GET['id'], 0);

// ��������� ����������
$fp = fopen(LOGS_ROOT.'banner_click.log', 'a');
flock($fp, LOCK_EX);
fwrite($fp, date('Y-m-d H:i:s')."\t".HTTP_IP."\t".HTTP_LOCAL_IP."\t".$banner_id."\t".Auth::getUserId()."\n");
flock($fp, LOCK_UN);
fclose($fp);

// ���������� ������ � �������
$query = "SELECT link FROM banner_banner WHERE id = '$banner_id'";
header("Location: ".$DB->result($query));
exit;
?>