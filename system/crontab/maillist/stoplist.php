<?php 
/**
* ��������� �����, ������� �� ���������� ���������
* 
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2006
* @cron 33 ~/4 * * *
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

/**
* ���������� ������������ ������� �������
*/
Shell::collision_catcher();

/**
 * ������������� ������ ���������
 */
if (rand(0, 100) > 70) {
	$query = "DELETE FROM maillist_stoplist WHERE dtime < NOW() - INTERVAL ".MAILLIST_STOPLIST_DURATION." SECOND";
	$DB->delete($query);
}

/**
 * ���������� �����, ������� ������ ����������� � ������ ��� ����, ��� � �������
 * ������������ ������ �������� �� � ���� ����. ��� ������ �� �������, �������
 * �� ����������� � ���� ������ ���� � ��������������
 */
$accept_words = preg_split("/\s*,\s*/", MAILLIST_STOPLIST_ACCEPT_WORDS, -1, PREG_SPLIT_NO_EMPTY);


$query = "select * from cms_mail_account where id='".MAILLIST_MAIL_ID."'";
$mailserver = $DB->query_row($query);

/**
 * ����������� � POP3 ��������
 */
$POP3 = new POP3($mailserver['pop3_host'], $mailserver['pop3_port'], $mailserver['pop3_login'], $mailserver['pop3_password']);
echo '[i] '.count($POP3->stat)." message(s) in mailbox\n";
reset($POP3->stat);
while(list($message_id, $size) = each($POP3->stat)) {
	echo "\n[i] Parsing message ".$message_id." of ".count($POP3->stat)."\n";
	if ($size > 1000000) {
		echo "[i] Message size $size, skip\n";
//		$POP3->dele($message_id);
		continue;
	}
	
	$text = $POP3->retr($message_id);
		
	// �������� ��������� �� ���������� � ��������� ���������, ��� ���
	// ��� ����� ��������� ������ � ���������� From, Reply-To etc.
	$text = message_cleanup($text);
	
	// ��������� �����, ������� ���������� � �������, ��������������� ��������� ��������
	$accepted = false;
	reset($accept_words);
	while(list(,$word) = each($accept_words)) {
		if (stristr($text, $word)) {
			$accepted = true;
			continue;
		}
	}
	if (!$accepted && !empty($accept_words)) {
		echo "[i] not accepted\n";
		$POP3->dele($message_id);
		continue;
	}

	
	if (!preg_match_all("/[a-z0-9_\.\-]+@[a-z0-9_\.\-]+\.[a-z]{2,4}/i", $text, $matches)) {
		echo "[i] skipped\n";
		$POP3->dele($message_id);
		continue;
	}
	
	echo "[i] ".count($matches[0])." e-mail found ... \n";
	// ������ � �������� ���������, ������� ������� ������
	if (count($matches[0]) > 0) {
		$insert = array();
		reset($matches[0]);
		while (list(,$email) = each($matches[0])) {
			$insert[] = "('$email', '".$DB->escape($text)."')";
		}
		$query = "INSERT INTO maillist_stoplist (email, message) VALUES ".implode(",", $insert);
		$DB->insert($query);
		echo "[i] ".$DB->affected_rows . " e-mail added to stoplist\n";
	}
	
	/**
	 * ������� ������������ ���������
	 */
	$POP3->dele($message_id);
}
unset($POP3);






/**
 * ������� ������ ������ �� ���������� � ��������, � ��� ����� 
 * ��������� ���������
 *
 * @param string $message_text
 * @return string
 */
function message_cleanup($message_text) {
	$parts = preg_split("/\r\n\r\n/", $message_text, 2);
	
	/**
	 * ��� �� �������� ��������� - ���������� ������������
	 */
	if (count($parts) != 2) {
		return $message_text;
	}
	
	$parts[0] = array_change_key_case(iconv_mime_decode_headers(trim($parts[0]), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, LANGUAGE_CHARSET), CASE_LOWER);
	if (isset($parts[0]['content-type']) && preg_match('/multipart.*boundary=\"(.*)\"/i', $parts[0]['content-type'], $match)) {
		$result = '';
		$parts = preg_split('/--'.preg_quote($match[1], '/').'/', $message_text);
		echo "[i] == Multipart message. Boundary = ".$match[1]."\n";
		
		reset($parts);
		while (list(,$row)=each($parts)) {
			/**
			 * �������� ��������� �� ����, ���� �������� - ���������� ��� �����
			 */
			$splitted = preg_split("/\r\n\r\n/", $row, 2);
			if (count($splitted) != 2) {
				continue;
			}
			
			/**
			 * ���� ��� ����� - text/*, �� ��������� ���������� ����� � ����������
			 * � ������ ������ (�����, �������� ���������) - ����������
			 */
			$this_part = array('headers' => array_change_key_case(iconv_mime_decode_headers(trim($splitted[0]), 0, LANGUAGE_CHARSET), CASE_LOWER), 'body' => trim($splitted[1]));
			if (isset($this_part['headers']['content-type']) && preg_match('/text\//i', $this_part['headers']['content-type'])) {
				$result .= $this_part['body']."\n";
			}
		}
		
		return $result;
		
	} else {
		/**
		 * ��� �� multipart ��������� - ���������� ��� ����� (��� ����������)
		 */
		return $parts[1];
	}
}
?>