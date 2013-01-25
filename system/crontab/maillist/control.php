<?php 
/**
* ���������� ��������� �� e-mail
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
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

// ���������� ������������ ������� �������
Shell::collision_catcher();

/**
 * ��������� �����������, ������������� ��� �������� ������
 */
$auth_email = '';
$auth_passwd = '';

$query = "select * from cms_mail_account where id='".MAILLIST_CONTROL_MAIL_ID."'";
$mailserver = $DB->query_row($query);

// ����������� � pop3 ��������, ������� � ��������� TRANSACTION
$POP3 = new POP3($mailserver['pop3_host'], $mailserver['pop3_port'], $mailserver['pop3_login'], $mailserver['pop3_passwd']);

$message_count = count($POP3->stat);

echo '[i] '.$message_count." message(s) in mailbox\n";

reset($POP3->stat);
while(list($message_id, $size) = each($POP3->stat)) {
	echo "[i] Parsing message ".$message_id." of ".$message_count."\n";
	
	if ($size > MAILLIST_CONTROL_MAX_SIZE) {
		//$text = $POP3->top($message_id, 50);
		echo "[i] Message size is greater than ".MAILLIST_CONTROL_MAX_SIZE." byte - skipped\n";
		continue;
	} else {
		$text = $POP3->retr($message_id);
	}
	$POP3->dele($message_id);
	
	$message = Mime::decode($text);
	
	/**
	 * �� ������� ���������� ������ ���������
	 */
	if (!is_array($message)) {
		echo "[e] Message format unrecognized - skipped\n";
		continue;
	}
	
	/**
	 * ���� � ��������� ������ ����� �����, ������ ��� ���� ��������
	 * (����� ��� ������ ���������)
	 */
	if (count($message) != 1) {
		echo "[i] Message has embedded multipart content - skipped\n";
		continue;
	}
	
	if (!isset($message[0]['body'])) {
		echo "[e] Message without body - skipped\n";
		continue;
	}
	
	if (!isset($message[0]['headers']['from']) || !preg_match("/([a-z0-9_\.\-]+@[a-z0-9_\.\-]+\.[a-z]{2,4})/i", $message[0]['headers']['from'], $match)) {
		echo "[e] Message without from header - skipped\n";
		continue;
	} else {
		$message_from = $match[1];
	}
	
	$auth_email = '';
	$auth_passwd = '';
	$commands = preg_split("~[\n\r]+~", $message[0]['body'], -1, PREG_SPLIT_NO_EMPTY);
	
	$TmplReport = new TemplateDB('cms_mail_template', 'maillist', 'control_report');
	reset($commands);
	while (list(,$row)=each($commands)) {
		execute_command($row, $message_from);
	}
	
	/**
	 * �������� ������������ ����� � ���������� ������
	 */
	$Sendmail = new Sendmail(MAILLIST_CONTROL_MAIL_ID, '����� �� ���������� ������ �������', nl2br($TmplReport->display()));
	
	$random_seed = Misc::randomKey(32);
	file_put_contents("/tmp/$random_seed.eml", $text);
	$Sendmail->attach("/tmp/$random_seed.eml", 'your-request.eml');
	unlink("/tmp/$random_seed.eml");
	$Sendmail->send($message_from, true);
	echo "[i] Report sent to $message_from\n";
}
unset($POP3);





/**
 * ������� ����������� ��� ������ ������ ��������� ������
 * @param string $command
 * @param string $message_from
 */
function execute_command($command, $message_from) {
	global $DB, $auth_email, $auth_passwd;
	
	echo "[cmd] $command\n";
	
	$command = trim($command);
	
	if (strtolower($command) == 'help') {
		/**
		 * ������ ������ ������, �������������� ��������
		 */
		$Template = new TemplateDB('cms_mail_template', 'maillist', 'control_help'); 
		$Sendmail = new Sendmail(MAILLIST_CONTROL_MAIL_ID, '������ �� ������������� �������� �� '.CMS_HOST, nl2br($Template->display()));
		$Sendmail->send($message_from, true);
		echo "[cmd] Help message sent to $message_from\n";
		iterate_report($command, 'OK');
		
	} elseif(strtolower($command) == 'remind_password') {
		/**
		 * ����������� ������
		 */
		$user = $DB->query_row("SELECT * FROM auth_user WHERE email = '$message_from'");
		if ($DB->rows == 1) {
			
			$Template = new TemplateDB('cms_mail_template', 'cms', 'amnesia'); 
			$Template->set($user);
			$Sendmail = new Sendmail(MAILLIST_CONTROL_MAIL_ID, '����������� ������ �� '.CMS_HOST, nl2br($Template->display()));
			$Sendmail->send($message_from, true);
			echo "[cmd] Reminder message sent to $message_from\n";
			iterate_report($command, 'OK');
		}
		
	} elseif(strtolower($command) == 'list') {
		/**
		 * ������ ���� ��������� ��������
		 */
		$query = "SELECT * FROM maillist_category WHERE private != 'true'";
		$lists = $DB->query($query);
		
		$Template = new TemplateDB('cms_mail_template', 'maillist', 'control_list'); 
		reset($lists);
		while (list(,$row)=each($lists)) {
			$Template->iterate('/list/', null, $row);
		}
		$Sendmail = new Sendmail(MAILLIST_CONTROL_MAIL_ID, '������ �������� �� '.CMS_HOST, nl2br($Template->display()));
		$Sendmail->send($message_from, true);
		iterate_report($command, 'OK');
		
	} elseif(strtolower($command) == 'subscribed_list') {
		/**
		 * ��������� ������ ��������, �� ������� �������� ������������. ������� �����������
		 */
		if (!check_auth($error, $user)) {
			return iterate_report($command, "������ �����������: $error");
		}
		
		$query = "
			SELECT tb_category.*
			FROM maillist_user_category AS tb_rel
			INNER JOIN maillist_category AS tb_category
				ON tb_rel.category_id = tb_category.id
			WHERE tb_rel.user_id = '$user[id]'
		";
		$lists = $DB->query($query);
		
		$Template = new TemplateDB('cms_mail_template', 'maillist', 'control_subscribed_list');
		reset($lists);
		while (list(,$row)=each($lists)) {
			$Template->iterate('/list/', null, $row);
		}
		$Sendmail = new Sendmail(MAILLIST_CONTROL_MAIL_ID, '������ �������� �� '.CMS_HOST.', �� ������� �� ���������', nl2br($Template->display()));
		$Sendmail->send($message_from, true);
		iterate_report($command, 'OK');
		
	} elseif (preg_match("~^pause\s+([0-9]+)$~i", $command, $match)) {
		/**
		 * ���������� � ����-���� �� N ����. ������� �����������
		 */
		if (!check_auth($error, $user)) {
			return iterate_report($command, "������ �����������: $error");
		}
		
		/**
		 * ������ � ����-���� ���, ����� ������������ ��������������� ����� N ����
		 */
		$query = "REPLACE INTO maillist_stoplist SET id = '$user[id]', `date` = NOW() + INTERVAL ".intval($match[1] * 86400 - MAILLIST_STOPLIST_DURATION)." SECOND";
		$DB->insert($query);
		iterate_report($command, "OK. ������ �� ��� ����� �� ����� ��������� � ������� $match[1] ����");
		
	} elseif (preg_match("~^subscribe\s+(.+)$~i", $command, $match)) { 
		/**
		 * �������� �� ��������. ������� �����������
		 */
		if (!check_auth($error, $user)) {
			return iterate_report($command, "������ �����������: $error");
		}
		
		$query = "SELECT * FROM maillist_category WHERE uniq_name = '".$DB->escape($match[1])."'";
		$list = $DB->query_row($query);
		if ($DB->rows == 0) {
			iterate_report($command, "�������� � ����� $match[1] �� �������");
		} else {
			$DB->insert("INSERT IGNORE INTO maillist_user_category SET user_id = '$user[id]', category_id = '$list[id]'");
			iterate_report($command, "OK. �� ��������� �� �������� '$list[name_ru]'");
		}
		
	} elseif (preg_match("~^unsubscribe\s+(.+)$~i", $command, $match)) {
		/**
		 * ���������� �� ��������. ������� �����������
		 */
		if (!check_auth($error, $user)) {
			return iterate_report($command, "������ �����������: $error");
		}
		
		$query = "SELECT * FROM maillist_category WHERE uniq_name = '".$DB->escape($match[1])."'";
		$list = $DB->query_row($query);
		if ($DB->rows == 0) {
			iterate_report($command, "�������� � ����� $match[1] �� �������");
		} else {
			$DB->insert("DELETE FROM maillist_user_category WHERE user_id = '$user[id]' AND category_id = '$list[id]'");
			iterate_report($command, "OK. �� �������� �� �������� '$list[name_ru]'");
		}
		
	} elseif (preg_match("/^email:\s+([a-z0-9\-\_\.]+@[a-z0-9\-\_\.]+\.[a-z]{1,4})$/i", $command, $match)) {
		/**
		 * ������������ ��������� ���� e-mail ��� �������� ��� ������� �� ��������
		 */
		$auth_email = $match[1];
		iterate_report($command, 'OK');
		
	} elseif (preg_match("/^passwd:\s+([a-zA-Z0-9_+!@#$%^&*~\(\)\-]{1,20})$/i", $command, $match)) {
		/**
		 * ������������ ��������� ���� ������ ��� �������� ��� ������� �� ��������
		 */
		$auth_passwd = $match[1];
		iterate_report($command, 'OK');
		
	} else {
		echo "[cmd] unknown command\n";
		iterate_report($command, '����������� ������� ��� ������ ����������');
	}
	
}

/**
 * ����� � ���������� �������
 *
 * @param string $command
 * @param string $result
 */
function iterate_report($command, $result) {
	global $TmplReport;
	
	if ($TmplReport instanceof Template) {
		$TmplReport->iterate('/command/', null, array('command' => $command, 'result' => $result));
	}
}

/**
 * �������� ����������� ��� ������, ������� ����� �������
 * @param string $error
 * @return bool
 */
function check_auth(&$error, &$user) {
	global $DB, $auth_email, $auth_passwd;
	
	if (empty($auth_email)) {
		$error = '�� ������ e-mail. ����������� ������� email: your@mail.box ��� help ��� ��������� �������';
		return false;
	} elseif (empty($auth_passwd)) {
		$error = '�� ������ ������. ����������� ������� passwd: ���_������ ��� help ��� ��������� �������';
		return false;
	} else {
		$query = "SELECT id, passwd FROM auth_user WHERE email = '$auth_email'";
		$user = $DB->query_row($query);
		if ($DB->rows == 0) {
			$error = "������������ � ������� $auth_email �� ����������";
			return false;
		} elseif ($auth_passwd != $user['passwd']) {
			$error = "����������� ������ ������";
			return false;
		}
	}
	
	return true;
}

?>