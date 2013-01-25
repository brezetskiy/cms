<?php
/**
 * Отправка почтовых сообщений
 * @package Pilot
 * @subpackage CMS
 * @author Morkovsky Dima dima@delta-x.ua
 * @copyright Delta-X, ltd. 2010
 */

class Sendmail {
	
	/**
	 * Тело сообщения
	 * @var string
	 */
	protected $content = '';
	
	/**
	 * Тема письма
	 *
	 * @var string
	 */
	protected $subject = '';
	
	/**
	 * Аккаунт, через котрорый происходит отправка почты
	 * @var unknown_type
	 */
	protected $account = array();
	
	/**
	 * Заголовки, которые необходимо добавить к письму
	 *
	 * @var array
	 */
	protected $headers = array();
	
	/**
	 * Вложения, которые необходимо добавить к письму
	 *
	 * @var array
	 */
	protected $attachment = array();
	
	/**
	 * Конструктор
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
		
		// Аккаунт с которого отправляется почта
		$query = "select * from cms_mail_account where id='$account_id'";
		$this->account = $DB->query_row($query);
		
		
	}
	
	/**
	 * Добавляет заголовки
	 *
	 * @param array $extra_headers
	 */
	public function addHeaders($headers) {
		$this->headers =  array_merge($this->headers, $headers);
	}
	
	/**
	 * Добавляет аттач
	 * 
	 * @param string $file - файл
	 * @param string $name - имя вложения с расширением или без него
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
	 * Формирует тело сообщения для отправки
	 *
	 * @param string $recipient
	 * @return object
	 */
	private function mime($recipient) {
		
		$Mime = new Mime($this->account['sender_name'].' <'.$this->account['sender_email'].'>', $recipient);
		$Mime->setHeader('From', $this->account['sender_name'].' <'.$this->account['sender_email'].'>');
		$Mime->setHeader('Subject', $this->subject);
		
		/**
		 * Преобразовываем документ в формат необходимый для письма
		 */
		$mime_content = id2url($this->content);
		
 		preg_match_all('/\<img\s+[^\>]*src=[\"\']?([^\>\'\"\s]+)/i', $mime_content, $matches);
 		$mime_content = preg_replace_callback('/\<img\s+([^\>]*)src=[\"\']?([^\>\'\"\s]+)([^>]*)/i', array(&$this, 'sendMailCidCallback'), $this->content);
 		
		// Заменяем абсолютные ссылки на сайт на относительные ссылки
		$mime_content = str_ireplace(CMS_URL.'/uploads/maillist_message/', '/uploads/maillist_message/', $mime_content);
		
		$mime_content = preg_replace('~<a([^>])href=("|\')?\/~i', '<a $1 href=$2'.CMS_URL, $mime_content);
		
		// Изменяем ссылки к прикреплённым файлам на ссылки, которые будут вести на сайт
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

		// Добавляем к письму рисунки, на которые есть ссылки в теле письма
		reset($matches[1]);
		while (list(,$file) = each($matches[1])) {
			if (preg_match('~^/(img|uploads)/~', $file) && strpos($file, '..') === false) {
				$Mime->attachImage(SITE_ROOT.substr($file, 1));
			}
		}
		
		// Добавляем заголовки
 		$Mime->addHeaders($this->headers);
 		
 		// Добавляем вложения
 		reset($this->attachment);
 		while (list(,$row) = each($this->attachment)) {
	 		$Mime->attachFile($row['file'], $row['name']); 
 		}
 		
 		return $Mime;
	}
	
	/**
	 * Производит отправку сообщения
	 * 
	 * @param mixed $recipient e-mail адрес получателя
	 * @param bool $immidiatley - отправить сообщение сразу же
	 * @return int возвращает id очереди или при мгновенной доставке результат доставки сообщения
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
	 * Callback для замены адресов картинок в отправляемых письмах
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
	 * Добавляет письмо в очередь на отправку
	 *
	 * @return int
	 */
	private function queue($headers, $body, $recipient) {
		global $DB;
		
		// account_id может быть равным нулю
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
		  
		// Ставим письмо в очередь
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
	 * Выбирает очередное письмо из очереди и отправляет его
	 * @param DB $DB
	 * @return array
	 */
	public static function delivery($queue_id = null, $mailserver = null) {
		global $DB;
		
		$DB->query("LOCK TABLES cms_mail_queue WRITE");
		
		/**
		 * С небольшой вероятностью делаем выборку писем, отправка которых была начата,
		 * но по каким-то причинам не завершена в течении 2-х часов. Если таких сообщений 
		 * в очереди нет - fallback до стандартного метода рассылки. Поскольку очередь 
		 * сообщений может быть довольно большой, это вынесено в отдельный запрос, чтобы
		 * не усложнять условие выборки главного запроса
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
		 * Для поддержки отправки сообщений с настройками соединения, не прописанными в cms_mail_account
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
			$subquery = "headers=null, body=null,"; // удаляем заголовки и тело успешно отправленного письма, так как они занмают много места в базе
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