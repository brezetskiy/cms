<?php
/**
 * �������� �������� ���������
 * @package Pilot
 * @subpackage CMS
 * @author Morkovsky Dima dima@delta-x.ua
 * @copyright Delta-X, ltd. 2010
 */

class Sendmail {
	
	/**
	 * ���� ���������
	 * @var string
	 */
	protected $content = '';
	
	/**
	 * ���� ������
	 *
	 * @var string
	 */
	protected $subject = '';
	
	/**
	 * �������, ����� �������� ���������� �������� �����
	 * @var unknown_type
	 */
	protected $account = array();
	
	/**
	 * ���������, ������� ���������� �������� � ������
	 *
	 * @var array
	 */
	protected $headers = array();
	
	/**
	 * ��������, ������� ���������� �������� � ������
	 *
	 * @var array
	 */
	protected $attachment = array();
	
	/**
	 * �����������
	 *
	 * @param int $account_id
	 * @param string $subject
	 * @param string $content
	 * @param bool $plain_text
	 */
	public function __construct($account_id, $subject, $content) {
		global $DB;
		
		$this->content = $content;
		$this->subject = $subject;
		
		// ������� � �������� ������������ �����
		$query = "select * from cms_mail_account where id='$account_id'";
		$this->account = $DB->query_row($query);
		
		
	}
	
	/**
	 * ��������� ���������
	 *
	 * @param array $extra_headers
	 */
	public function addHeaders($headers) {
		$this->headers =  array_merge($this->headers, $headers);
	}
	
	/**
	 * ��������� �����
	 * 
	 * @param string $file - ����
	 * @param string $name - ��� �������� � ����������� ��� ��� ����
	 * @return bool
	 */
	public function attach($file, $name = '') {
		if (empty($name)) {
			$name = basename($file);
		} elseif (!empty($name) && strpos($name, '.') === false) {
			$name = $name.'.'.Uploads::getFileExtension($file);
		}
		if (!is_file($file) || !is_readable($file)) {
			return false;
		}
 		$this->attachment[] = array('name' => $name, 'file' => $file);
 		return true;
	}
	
	/**
	 * ��������� ���� ��������� ��� ��������
	 *
	 * @param string $recipient
	 * @return object
	 */
	private function mime($recipient) {
		
		$Mime = new Mime($this->account['sender_name'].' <'.$this->account['sender_email'].'>', $recipient);
		$Mime->setHeader('From', $this->account['sender_name'].' <'.$this->account['sender_email'].'>');
		$Mime->setHeader('Subject', $this->subject);
		
		/**
		 * ��������������� �������� � ������ ����������� ��� ������
		 */
		$mime_content = id2url($this->content);
		
 		preg_match_all('/\<img\s+[^\>]*src=[\"\']?([^\>\'\"\s]+)/i', $mime_content, $matches);
 		$mime_content = preg_replace_callback('/\<img\s+([^\>]*)src=[\"\']?([^\>\'\"\s]+)([^>]*)/i', array(&$this, 'sendMailCidCallback'), $this->content);
 		
		// �������� ���������� ������ �� ���� �� ������������� ������
		$mime_content = str_ireplace(CMS_URL.'/uploads/maillist_message/', '/uploads/maillist_message/', $mime_content);
		
		$mime_content = preg_replace('~<a([^>])href=("|\')?\/~i', '<a $1 href=$2'.CMS_URL, $mime_content);
		
		// �������� ������ � ������������ ������ �� ������, ������� ����� ����� �� ����
		$mime_content = preg_replace(
			'~href="/tools/cms/site/download\.php\?url=/uploads/maillist_message/([^"&]+)(&[^"]+)?~i',
			'href="'.CMS_URL."uploads/maillist_message/$1",
			$mime_content
		);
		
 		
 		if (preg_match("/<(a|p|br|td|table|span|tr|font)[^>]*>/", $this->content)) {
 			$Mime->setHtml($mime_content);
 		} else {
			$Mime->setPlainText($mime_content);
 		}

		// ��������� � ������ �������, �� ������� ���� ������ � ���� ������
		reset($matches[1]);
		while (list(,$file) = each($matches[1])) {
			if (preg_match('~^/(img|uploads)/~', $file) && strpos($file, '..') === false) {
				$Mime->attachImage(SITE_ROOT.substr($file, 1));
			}
		}
		
		// ��������� ���������
 		$Mime->addHeaders($this->headers);
 		
 		// ��������� ��������
 		reset($this->attachment);
 		while (list(,$row) = each($this->attachment)) {
	 		$Mime->attachFile($row['file'], $row['name']); 
 		}
 		
 		return $Mime;
	}
	
	/**
	 * ���������� �������� ���������
	 * 
	 * @param mixed $recipient e-mail ����� ����������
	 * @param bool $immidiatley - ��������� ��������� ����� ��
	 * @return int ���������� id ������� ��� ��� ���������� �������� ��������� �������� ���������
	 */
	public function send($recipient, $immidiatley = false) {
		$Mime = $this->mime($recipient);
		$recipient = preg_split("/[\s,]+/", $recipient, -1, PREG_SPLIT_NO_EMPTY);
		reset($recipient);
		while (list(,$email) = each($recipient)) {
			$queue_id = $this->queue($Mime->getHeaders(), $Mime->getMessageBody(), $email);
			if ($immidiatley == true) {
				$queue_id = $this->delivery($queue_id, $this->account);
			}
		}
		return $queue_id;
	}
	
	
	/**
	 * Callback ��� ������ ������� �������� � ������������ �������
	 * @param array $matches
	 * @return string
	 */
	private function sendMailCidCallback($matches) {
		if (!preg_match('~^cid:~i', $matches[2])) {
			return '<img '.$matches[1].' src="cid:'.basename($matches[2]).$matches[3];
		} else {
			return '<img '.$matches[1].' src="'.basename($matches[2]).$matches[3];
		}
	}
	
	/**
	 * ��������� ������ � ������� �� ��������
	 *
	 * @return int
	 */
	private function queue($headers, $body, $recipient) {
		global $DB;
		
		// account_id ����� ���� ������ ����
		$extension = '';
		if(empty($this->account['id'])){
			$extension = ",
				sender_email  = '".$this->account['sender_email']."', 
				smtp_host	  = '".$this->account['smtp_host']."',
				smtp_port	  = '".$this->account['smtp_port']."',
				smtp_login	  = '".$this->account['smtp_login']."',
				smtp_password = '".$this->account['smtp_password']."',
				smtp_auth	  = '".$this->account['smtp_auth']."'
			";
		}
		  
		// ������ ������ � �������
		$query = "
			INSERT INTO cms_mail_queue SET
				account_id='".$this->account['id']."',
				recipient='".$DB->escape($recipient)."',
				create_dtime=NOW(),
				message='".$DB->escape($this->content)."',
				headers='".$DB->escape(serialize($headers))."',
				body='".$DB->escape($body)."'
				$extension
		";
		return $DB->insert($query);
	}
	
	

	/**
	 * �������� ��������� ������ �� ������� � ���������� ���
	 * @param DB $DB
	 * @return array
	 */
	public static function delivery($queue_id = null, $mailserver = null) {
		global $DB;
		
		$DB->query("LOCK TABLES cms_mail_queue WRITE");
		
		/**
		 * � ��������� ������������ ������ ������� �����, �������� ������� ���� ������,
		 * �� �� �����-�� �������� �� ��������� � ������� 2-� �����. ���� ����� ��������� 
		 * � ������� ��� - fallback �� ������������ ������ ��������. ��������� ������� 
		 * ��������� ����� ���� �������� �������, ��� �������� � ��������� ������, �����
		 * �� ��������� ������� ������� �������� �������
		 */
		if (rand(0, 100) > 90) {
			$DB->update("UPDATE cms_mail_queue SET delivery='wait' WHERE delivery='sending' AND send_dtime < NOW() - INTERVAL 2 HOUR");
		}
		
		$message = $DB->query_row("SELECT * FROM cms_mail_queue WHERE delivery = 'wait' ".where_clause('id', $queue_id)." LIMIT 1");
		if ($DB->rows == 0) {
			$DB->query("UNLOCK TABLES");
			return false;
		}
		
		$DB->update("UPDATE cms_mail_queue SET delivery='sending', send_dtime=NOW() WHERE id='$message[id]'");
		$DB->query("UNLOCK TABLES");
		
		/**
		 * ��� ��������� �������� ��������� � ����������� ����������, �� ������������ � cms_mail_account
		 */
		if (empty($mailserver) && !empty($message['account_id'])) {
			$mailserver = $DB->query_row("select * from cms_mail_account where id='$message[account_id]'");
		} elseif(empty($mailserver) && empty($message['account_id'])){
			$mailserver = array(
				'sender_email' 	=> $message['sender_email'],  
				'smtp_host' 	=> $message['smtp_host'],
				'smtp_port' 	=> $message['smtp_port'],
				'smtp_login' 	=> $message['smtp_login'],
				'smtp_password' => $message['smtp_password'],
				'smtp_auth' 	=> $message['smtp_auth']
			);
		}
		
		$return = array('sender' => $mailserver['sender_email'], 'recipient' => $message['recipient']);
		
		$SMTP = new SMTP($mailserver['smtp_host'], $mailserver['smtp_port'], $mailserver['smtp_login'], $mailserver['smtp_password'], $mailserver['smtp_auth']);
		if ($SMTP->send($mailserver['sender_email'], $message['recipient'], unserialize($message['headers']), $message['body'])) {
			$return['delivery'] = 'ok';
			$return['status_message'] = '';
			$subquery = "headers=null, body=null,"; // ������� ��������� � ���� ������� ������������� ������, ��� ��� ��� ������� ����� ����� � ����
		} else {
			// x($SMTP);
			$return['delivery'] = 'error';
			$return['status_message'] = 'SMTP error: '.$SMTP->getLastMessage();
			$subquery = '';
		}
		 
		$query = "
			UPDATE cms_mail_queue SET 
				$subquery
				delivery='$return[delivery]',
				status_message='".$DB->escape($return['status_message'])."'
			WHERE id='$message[id]'
		";
		$DB->update($query);
		return $return;
	}
	
	
	
}

?>