<?php
/** 
 * �������� �����, ������� � ������� 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 * @cron ~/5 * * * * 
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

$message = $DB->fetch_column("
	SELECT id, recipient 
	FROM cms_mail_queue 
	WHERE delivery = 'wait' AND DATE(create_dtime) = current_date()
	ORDER BY id DESC
", 'id', 'recipient');

$counter = 0;
echo "[i] Start Mailq. ".count($message)." new messages found. \n"; 

reset($message);
while(list($message_id, $recipient) = each($message)){
	$result = @Sendmail::delivery($message_id);
	$counter++; 
	
	if(empty($result)){
		echo iconv(CMS_CHARSET, CMS_SHELL_CHARSET.'//IGNORE', "[i] $counter\t Message (ID:$message_id) to $recipient : failed!!! \n");
	} else {
		echo iconv(CMS_CHARSET, CMS_SHELL_CHARSET.'//IGNORE', "[i] $counter\t Message (ID:$message_id) to $result[recipient] : $result[delivery] ($result[status_message])\n");
	} 
} 


?>