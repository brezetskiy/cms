<?php
/** 
 * ����� ������� ������ ������ �� ������� maillist_stoplist
 * @package Pilot 
 * @subpackage Billing 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
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

if (!Auth::selectTable('maillist_stoplist')) {
	echo "��� ���� �������";
	exit;
}

$id = globalVar($_GET['id'], 0);

$query = "select * from maillist_stoplist where id='$id'";
$info = $DB->query_row($query);

if(empty($info)){
	echo "<center>�� ������� ���������</center>";
} else {
	echo "<div style=\"width:500px;\"><pre>".$info['message']."</pre></div>";
}



?>
