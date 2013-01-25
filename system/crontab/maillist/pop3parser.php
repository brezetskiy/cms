<?php
/**
* ������ ��������� � ���������, ����������� �� �����
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@id.com.ua>
* @copyright Copyright 2005, Delta-X ltd.
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

/**
 * ���������� ������������ ������� �������
 */
Shell::collision_catcher();

$query = "select * from cms_mail_account where id='".MAILLIST_PARSER_MAIL_ID."'";
$mailserver = $DB->query_row($query);

// ����������� � pop3 ��������, ������� � ��������� TRANSACTION
$POP3 = new POP3($mailserver['pop3_host'], $mailserver['pop3_port'], $mailserver['pop3_login'], $mailserver['pop3_password']);

$message_count = count($POP3->stat);

echo '[i] '.$message_count." message(s) in mailbox\n";

reset($POP3->stat);
while(list($message_id, $size) = each($POP3->stat)) {
	
	echo "[i] Parsing message ".$message_id." of ".$message_count."\n";
	
	if ($size > MAILLIST_PARSER_MAX_SIZE) {
		$text = $POP3->top($message_id, 50);
	} else {
		$text = $POP3->retr($message_id);
	}
//	$POP3->dele($message_id);
	
	/**
	 * ��������� ������ � ����
	 */
	if (!is_dir(LOGS_ROOT.'pop3parser/')) {
		mkdir(LOGS_ROOT.'pop3parser/', 0755, true);
	}
	file_put_contents(LOGS_ROOT.'pop3parser/'.date('Y-m-d-H-i-s-').$message_id, $text);
	
	if(!$message_parts = split_multipart($text)) {
		echo "[e] Message parsing error\n";
		continue;
	}
	
	$top_headers = $message_parts[0]['headers'];
	
	/**
	 * �������� ���� From
	 */
	if (!set_and_match("/([a-z0-9_\.\-]+@[a-z0-9_\.\-]+\.[a-z]{2,4})/i", $top_headers['from'], $match)) {
		echo "[e] == From header not found. Message skipped.\n";
		continue;
	} else {
		$message_from = $match[1];
	}
	
	/**
	 * ��������� �� ������� ���������, ��������� � ����� ������
	 */
	$allow_from = preg_split("/[\s\t,]/", MAILLIST_PARSER_ALLOW_FROM, -1, PREG_SPLIT_NO_EMPTY);
	
	if (MAILLIST_PARSER_ALLOW_FROM != '' && !in_array($message_from, $allow_from)) { 
		$Template = new Template(SITE_ROOT.'templates/maillist/parser_access_denied');
		if (isset($top_headers['subject'])) {
			$Template->set('subject', $top_headers['subject']);
		} else {
			$Template->set('subject', '��� ����');
		}
		$Sendmail = new Sendmail(CMS_MAIL_ID, 'Access denied', $Template->display());
		$Sendmail->send($message_from);
		unset($Template);
		echo "[w] Message from $message_from : access denied\n";
		continue;	
	}
	
	/**
	 * ���� ������ ��������� ��������� ����� - ������������ ��������� �
	 * ������� ����������� ������
	 */
	if ($size > MAILLIST_PARSER_MAX_SIZE) {
		$Template = new TemplateDB('cms_mail_template', 'maillist', 'parser_max_size'); 
		if (isset($top_headers['subject'])) {
			$Template->set('subject', $top_headers['subject']);
		} else {
			$Template->set('subject', '��� ����');
		}
		
		$Sendmail = new Sendmail(CMS_MAIL_ID, 'Max message size exceeded', $Template->display());
		$Sendmail->send($message_from);
		unset($Template);
		echo "[w] Maximum message size exceeded in message from $message_from\n";
		continue;
	}
	
	/**
	 * ����������� ��������� ���������
	 */
	if (isset($top_headers['subject'])) {
		echo "[i] == Subject: ".iconv(LANGUAGE_CHARSET, CMS_SHELL_CHARSET.'//IGNORE', $top_headers['subject'])."\n";
	} else {
		echo "[e] Subject header not found\n";
//		x($text);
		continue;
	}
	
	$query = "
		INSERT INTO maillist_message
		SET
			subject = '".$top_headers['subject']."',
			editable = 'true',
			reply_to = '$message_from'
	";
	$db_message_id = $DB->insert($query);
	
	/**
	 * ������ ��������� �� �������
	 */
	if (isset($top_headers['subject']) && preg_match("/^{([^\}]+)}/", trim($top_headers['subject']), $matches)) {
		$top_headers['subject'] = trim(substr($top_headers['subject'], strpos($top_headers['subject'], '}') + 1));
		$category = preg_split("/,/", strtolower($matches[1]), -1, PREG_SPLIT_NO_EMPTY);
		$query = "
			INSERT INTO maillist_message_category (message_id, category_id)
			SELECT '$db_message_id' AS message_id, id AS category_id
			FROM maillist_category 
			WHERE LOWER(uniq_name) IN ('".implode("','", $category)."')
		";
		$DB->insert($query);
		
		// ��������� ���������
		$query = "UPDATE maillist_message SET subject='".$top_headers['subject']."' WHERE id='$db_message_id'";
		$DB->update($query);
		
	} else {
		// ���� ������������ �� ������ ������ �� ������� ���������� ���������
		// �� ��������� ������ �� ��� ������
		$query = "
			INSERT INTO maillist_message_category (message_id, category_id)
			SELECT '$db_message_id' AS message_id, id AS category_id
			FROM maillist_category 
		";
		$DB->insert($query);

	}
	unset($matches);
	
	
	/**
	 * ��������� ������ ���������
	 */
	$embed_counter = 0;
	$message_text = '';
	$replacements = array();
	reset($message_parts);
	while (list($index, $row)=each($message_parts)) {
		if ($row['meta']['type'] == 'attachment') {
			/**
			 * ��� ����� ��������� - �����
			 */
			echo "[i] == Attachment found\n";
			$query = "
				INSERT INTO maillist_attachment
				SET
					message_id = '$db_message_id',
					name = '".basename($row['meta']['filename'], '.'.$row['meta']['extension'])."',
					file = '".$row['meta']['extension']."'
			";
			$attach_id = $DB->insert($query);
			
			$filename = Uploads::getFile('maillist_attachment', 'file', $attach_id, $row['meta']['extension']);
			if (!is_dir(dirname($filename))) {
				mkdir(dirname($filename), 0755, true);
			}
			file_put_contents($filename, $row['body']);
		} elseif ($row['meta']['type'] == 'embed') {
			/**
			 * ��� ���������� � HTML ���� (�������, ����, etc)
			 */
			echo "[i] == Embeded file found\n";
			$file = 'maillist_message/'.LANGUAGE_CURRENT.'/'.Uploads::getIdFileDir($db_message_id).'/'.sprintf('%02d', $embed_counter).'.'.$row['meta']['extension'];
			$filename = UPLOADS_ROOT.$file;
			
			if (!is_dir(dirname($filename))) {
				mkdir(dirname($filename), 0755, true);
			}
			file_put_contents($filename, $row['body']); 
			$replacements[ 'cid:'.$row['meta']['cid'] ] = '/'.UPLOADS_DIR.$file;
			$embed_counter++;
		} elseif ($row['meta']['type'] == 'text') {
			$message_text = $row['body'];
		}
	}
	
	/**
	 * ���������� � ���� ����� ��������� (����� ������ � ��� CID'�� �� ����� ������)
	 */
	$message_text = str_replace(array_keys($replacements), array_values($replacements), $message_text);
	
	// ��� ���������� ������ ���� ���������, ��� ����������
	$message_text = preg_replace("~^.+</HEAD>.+<BODY[^>]*>~ismU", '', $message_text);
	$message_text = preg_replace("/<\/body>.*$/i", '', $message_text);

	$query = "update maillist_message set content='".addcslashes($message_text, "'")."' where id='$db_message_id'";
	$DB->update($query);
}

// ��������� � ��������� UPDATE
unset($POP3);
















/**
 * ���������, ���������� �� ���������� � ������������� �� ��� �������
 *
 * @param string $pattern
 * @param string $subject
 * @param array $match
 * @return bool
 */
function set_and_match($pattern, &$subject, &$match = null) {
	if (isset($subject) && preg_match($pattern, $subject, $match)) {
		return true;
	}
	return false;
}

/**
 * ��������� �������� ���������� � HTML �������, ���������� ���������� �����
 * � HTML, ����� �� �������� ��� � ������
 *
 * @param string $text
 * @return string
 */
function text2html($text) {
	/**
	 * 1. ����������� �������� ����� � <br>
	 */
	$result = preg_replace("/(\r\n|\r|\n)/", "<br />\n", $text);
	return $result;
}

/**
 * ��������� ����� multipart ��������� �� �����,
 * ���������� ��������� ������ (������������� base64, quoted-printable, ������� ������ � ��������� �����)
 *
 * @param string $text
 * @return array
 */
function split_multipart($text) {
	$message_parts = array();
	
	$splitted = preg_split("/\r\n\r\n/", $text, 2);
	
	/**
	 * �������� ��������� ��������� �� ����
	 */
	if (count($splitted) != 2) {
		echo "[e] Can't detect headers delimiter!\n";
//		x($text);
		return false;
	}
	
	$splitted[0] = array_change_key_case(iconv_mime_decode_headers(trim($splitted[0]), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, LANGUAGE_CHARSET), CASE_LOWER);
	
	if (set_and_match('/multipart.*boundary=\"(.*)\"/iU', $splitted[0]['content-type'], $match)) {
		// ��� ����� multipart ��������� - ������� ��� �� �����
		$parts = preg_split('/--'.preg_quote($match[1], '/').'/', $text);
		echo "[i] == Multipart message. Boundary = ".$match[1]."\n";
		
		if (!is_array($parts)) {
			echo "BOUNDARY: $match[1]";
			exit;
		}
		
		/**
		 * ������� top-level ���������, ����� ������������� ������������,
		 * �������������� �������� �� � ������ �������� ������������� �������
		 */
		$message_parts[0] = array('headers' => $splitted[0], 'meta' => array('type' => 'multipart'));
		unset($parts[0]);
		
		$splitted = array();
		reset($parts);
		while (list(,$row)=each($parts)) {
			$splitted = split_multipart($row);
			
			if (!is_array($splitted)) {
				echo "[e] Can't split multipart message!\n";
//				x($text);
//				x($splitted);
				return false;
			}
			
			reset($splitted);
			while (list(,$row2)=each($splitted)) {
				$message_parts[] = $row2;
			}
		}
	} else {
		// ��� ��������� � ���������� �����
		$splitted_part = preg_split("/\r\n\r\n/", $text, 2);
		if (count($splitted_part) == 2) {
			$this_part = array('headers' => array_change_key_case(iconv_mime_decode_headers(trim($splitted_part[0]), ICONV_MIME_DECODE_CONTINUE_ON_ERROR, LANGUAGE_CHARSET), CASE_LOWER), 'body' => trim($splitted_part[1]));
			
			/**
			 * ������������� quoted-printable ��� base64, ���� ����� ������������
			 */
			if (set_and_match('/(quoted-printable|base64)/i', $this_part['headers']['content-transfer-encoding'], $match)) {
				if ($match[1] == 'base64') {
					echo "[i] == Base64 encoded file\n";
					$this_part['body'] = base64_decode($this_part['body']);
				} elseif ($match[1] == 'quoted-printable') {
					echo "[i] == Quoted-printable text\n";
					$this_part['body'] = quoted_printable_decode($this_part['body']);
				}
			}
			
			/**
			 * ����������� ����� � MS Word �������� - ������ �������� ���� �� ���� ������
			 */
			if (set_and_match('/text\/html/i', $this_part['headers']['content-type'])) { 
				$this_part['body'] = preg_replace('/\x96/', chr(45), $this_part['body']);
			}
			
			/**
			 * ������� � ��������� �����, ���� � ��������� ������� ��������� ���������
			 */
			if (set_and_match('/charset=\"?([^\s\"]+)/i', $this_part['headers']['content-type'], $match)) {
				echo "[i] == Charset detected: ".$match[1]."\n";
				$this_part['body'] = iconv($match[1], LANGUAGE_CHARSET.'//IGNORE', $this_part['body']);
				
				// � �������� ����� ���� ��������� <meta http-equiv=Content-Type content="text/html; charset=koi8-r">, � ���� ���� �������� ���������� ���������
				$this_part['body'] = preg_replace("/".preg_quote($match[1], '/')."/", LANGUAGE_CHARSET, $this_part['body']);
			} elseif (set_and_match('/text\//i', $this_part['headers']['content-type'])) { 
				$charset = Charset::detectCyrCharset($this_part['body']);
				echo "[i] == Charset GUESSED: $charset\n";
				$this_part['body'] = iconv($charset, LANGUAGE_CHARSET.'//IGNORE', $this_part['body']);
			}
			
			/**
			 * ����������� ���� ����� - ��� �����, ���������� � HTML ���� ��� �����
			 */
			if (set_and_match('/attachment;\s+filename=\"(.*)\"/i', $this_part['headers']['content-disposition'], $match)) {
				$this_part['meta']['type'] = 'attachment';
				$this_part['meta']['filename'] = $match[1];
				$this_part['meta']['extension'] = Uploads::getFileExtension($this_part['meta']['filename']);
				
			} elseif (set_and_match('/\<(.*)\>/i', $this_part['headers']['content-id'], $match) && set_and_match('/name=\"(.*)\"/i', $this_part['headers']['content-type'], $match2)) {
				$this_part['meta']['type'] = 'embed';
				$this_part['meta']['filename'] = $match2[1];
				$this_part['meta']['extension'] = Uploads::getFileExtension($this_part['meta']['filename']);
				$this_part['meta']['cid'] = $match[1];
				
			} elseif (set_and_match('/text\//i', $this_part['headers']['content-type'])) { 
				/**
				 * ����������� ����� MS Word
				 */
				$this_part['meta']['type'] = 'text';
				
			} else {
				$this_part['meta']['type'] = 'undefined';
			}
			
			/**
			 * �������������� ������ � HTML
			 */
			if (set_and_match('/text\/plain/i', $this_part['headers']['content-type'])) { 
				$this_part['body'] = text2html($this_part['body']);
			}
			
			$message_parts[] = $this_part;
		}
	}
	
	
	if (count($message_parts) == 0) {
		return false;
	} else {
		return $message_parts;	
	}
}
?>