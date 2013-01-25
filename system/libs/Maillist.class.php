<?php
/**
 * Почтовая рассылка
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


class Maillist {
	
	public $message_id = 0;
	public $subject = '';
	public $from = '';
	public $reply_to = '';
	public $content = '';
	
	public function __construct($message_id) {
		global $DB;
		
		$query = "
			SELECT 
				id, 
				subject,
				reply_to,
				content_".LANGUAGE_SITE_DEFAULT." as content
			FROM maillist_message
			WHERE id = '$message_id'
		";
		$info = $DB->query_row($query);
		if ($DB->rows == 0) {
			trigger_error('Unable to get mailist with id='.$message_id, E_USER_ERROR);
			exit;
		}
		$query = "select sender_name, sender_email from cms_mail_account where id='".CMS_MAIL_ID."'";
		$sender = $DB->query_row($query);
		
		$this->message_id = $info['id'];
		$this->subject = $info['subject'];
		$this->content = $info['content'];
		$this->from = "$sender[sender_name] <$sender[sender_email]>";
		$this->reply_to = (empty($info['reply_to'])) ? $this->from : $info['reply_to'];
	}
	
	public function getAttachments() {
		global $DB;
		
		$return = array();
		$query = "SELECT id, name, file FROM maillist_attachment WHERE message_id='$this->message_id'";
		$attachments = $DB->query($query);
		reset($attachments);
		while (list(, $row) = each($attachments)) {
			$file = Uploads::getFile('maillist_attachment', 'file', $row['id'], $row['file']);
			$return[] = array('file' => $file, 'name' => $row['name'].'.'.$row['file']);
		}
		return $return;
	}
	
	
	public function getQueue() {
		global $DB;
	
		$query = "
			SELECT 
				tb_queue.message_id,
				tb_queue.email,
				tb_queue.param,
				tb_user.name AS user_name
			FROM maillist_queue AS tb_queue
			LEFT JOIN auth_user AS tb_user ON tb_queue.email = tb_user.email
			WHERE 
				tb_queue.delivery = 'wait' 
				AND tb_queue.message_id = '$this->message_id'
		";
		return $DB->query($query);
	}

	
	/**
	 * Определяет размер письма
	 *
	 * @param int $this->message_id
	 * @return int
	 */
	static function getMessageSize($message_id) {
		global $DB;
		
		$query = "select length(content_".LANGUAGE_CURRENT.") from maillist_message where id='$message_id'";
		$message_size = $DB->result($query);
		
		// Размер вставленных картинок
		$images = Filesystem::getDirContent(UPLOADS_ROOT.'maillist_message/content_'.LANGUAGE_CURRENT.'/'.Uploads::getIdFileDir($message_id).'/', true, false, true);
		reset($images);
		while (list(,$row2)=each($images)) {
			if (is_file($row2)) {
				$message_size += filesize($row2);
			}
		}
	
		// Размер вложений
		$query = "SELECT id, file FROM maillist_attachment WHERE message_id = '$message_id'";
		$attachments = $DB->query($query);
		reset($attachments);
		while (list(,$row2)=each($attachments)) {
			$file = Uploads::getFile('maillist_attachment', 'file', $row2['id'], $row2['file']);
			if (is_file($file)) {
				$message_size += filesize($file);
			}
		}
		
		return $message_size;
	}
	
	
	/**
	 * Добавляет в очередь сообщения
	 *
	 * @param int $message_id
	 * @param bool $test
	 * @return int
	 */
	static public function queue($message_id, $test) {
		global $DB;
		$total_count = 0;
		
		if (self::getMessageSize($message_id) > MAILLIST_ATTACHMENT_MAX_SIZE) {
			// Письмо превышает допустимы размер
			return false;
		}

		// Удаляем старые записи из очереди рассылки
		$query = "DELETE FROM maillist_queue WHERE expire_dtime < NOW() and message_id='$message_id'";
		$DB->delete($query);
		
		// Черный список клиентов
		$query = "select email from maillist_stoplist";
		$stoplist = $DB->fetch_column($query);
		
		// Категории, которым принадлежит письмо
		if ($test) {
			$query = "
				select 
					tb_category.name_ru,
					tb_category.id as category_id,
					tb_category.access_level,
					tb_category.sql_query,
					10 as expire_period
				from maillist_category as tb_category
				where tb_category.test='1'
			";
		} else {
			$query = "
				select 
					tb_relation.category_id,
					tb_category.access_level,
					tb_category.sql_query,
					tb_category.expire_period
				from maillist_message_category as tb_relation
				inner join maillist_category as tb_category on tb_category.id=tb_relation.category_id
				where tb_relation.message_id='$message_id'
				order by tb_category.expire_period asc
			";
		}
		$data = $DB->query($query);
		$insert = array();
		reset($data); 
		while (list(,$row) = each($data)) {
			
			// Получаем данные о подписчиках
			if (empty($row['sql_query'])) {
				// Пользователи, которые подписаны на простые категории
				switch ($row['access_level']) {
					case 'confirmed':
						$where = " and tb_user.confirmed=1 ";
						break;
					case 'checked':
						$where = " and tb_user.checked=1 ";
						break;
					default:
						$where = "";
						break;
				}
				$query = "
					select tb_user.*, tb_user.id as user_id
					from auth_user as tb_user
					inner join maillist_user_category as tb_relation on tb_relation.user_id=tb_user.id
					where tb_relation.category_id='$row[category_id]' $where
					group by tb_user.id
				";
				$data = $DB->query($query);
			} else {
				// SQL рассылка
				$data = $DB->query($row['sql_query']);
			}
			reset($data);
			while (list(,$rcpt) = each($data)) {
				if (in_array($rcpt['email'], $stoplist)) {
					// Пользователь находится в стоплисте
					continue;
				}
				if (isset($rcpt['user_id']) && !empty($rcpt['user_id'])) {
					// Получаем информацию о пользователе
					$rcpt2 = Auth::getUserData($rcpt['user_id']);
					$rcpt = array_merge($rcpt2, $rcpt);
				}
				$insert[] = "($message_id, '$rcpt[email]', '".date('Y-m-d', time() + $row['expire_period'])."', '".addcslashes(serialize($rcpt), "'")."')";
				if (count($insert) > 500) {
					// Переносим данные в основню таблицу
					$query = "insert ignore into maillist_queue (message_id, email, expire_dtime, param) values ".implode(",", $insert)."";
					$DB->insert($query);
					$total_count += $DB->affected_rows;
					$insert = array();
				}
			}
			if (!empty($insert)) {
				// Переносим данные в основню таблицу
				$query = "insert ignore into maillist_queue (message_id, email, expire_dtime, param) values ".implode(",", $insert)."";
				$DB->insert($query);
				$total_count += $DB->affected_rows;
				$insert = array();
			}
		}
	
		$query = "update maillist_message set send_dtime=now() where id='$message_id'";
		$DB->update($query);
		
		return $total_count;
	}
	
	

//	public function getImages() {
//		
//		$return = array();
//		
//		// Определяем файлы, которые не надо прикреплять к письму
//		preg_match_all('~href="[^"]+/([^"]+)"~', $this->content, $matches);
//		$skip_attachment = $matches[1];
//		unset($matches);
//		
//		// Добавляем к письму картинки
//		$img_root = UPLOADS_ROOT.'maillist_message/content_'.LANGUAGE_SITE_DEFAULT.'/'.Uploads::getIdFileDir($this->message_id).'/';
//		if (is_dir($img_root)) {
//			$images = Filesystem::getDirContent($img_root, true, false, true);
//			if (is_array($images)) {
//				reset($images);
//				while(list($index, $file) = each($images)) {
//					if (in_array(basename($file), $skip_attachment)) {
//						// Пропускаем аттачменты, которые добавлены в редактор
//						continue;
//					}
//					$return[] = $file;
//				}
//			}
//		}
//		return $return;
//	}

	/**
	 * MIME кодирование заголовка
	 *
	 * @param string $string
	 * @return string
	 */
//	public static function mimeEncodeString($string) {
//		if (preg_match("/^[a-zA-Z0-9@\.,_<> ]+$/", $string)) return $string;
//		$charset = 'Windows-1251';
//		if (strlen(base64_encode($string)) > 40) {
//			$strings = explode("\r\n", substr(chunk_split(base64_encode($string), 40, "\r\n"), 0, -2));
//		} else {
//			$strings = array(base64_encode($string));
//		}
//		return "=?$charset?B?".implode("?=\r\n        =?$charset?B?", $strings)."?=";
//	}
	
	/**
	 * Добавление cid к картинкам
	 */
//	public static function cidCallback($matches) {
//		/**
//		 * НЕ СТАВИТЬ ЗАКРЫВАЮЩУЮ КАВЫЧКУ И ПРОБЕЛ ПОСЛЕ ПАРАМЕТРА SRC !!!
//		 * Все, что стоит за именем файла в параметре src, в т.ч. и закрывающая кавычка 
//		 * находится в параметре $matches[3]
//		 */
//		if (!preg_match('~^cid:~i', $matches[2])) {
//			return '<img '.$matches[1].' src="cid:'.basename($matches[2]).$matches[3];
//		} else {
//			return '<img '.$matches[1].' src="'.basename($matches[2]).$matches[3];
//		}
//	}

	
	/**
	 * Рассылки к которым пользователь имеет доступ
	 *
	 * @param int $user_id
	 * @return array
	 */
//	static function allowableCategories($user_id) {
//		global $DB;
//		$query = "
//			(select category_id from maillist_user_category where user_id='$user_id')
//			union
//			(select id from maillist_category where private='false' and test=0)
//		";
//		return $DB->fetch_column($query);
//	}


	// todo: не обрабатывает картинки, которые находятся в самом письме
	// todo: не обрабатывает нужный шаблон из базы, а берет обычный
	// Добавляем к письму рисунки, на которые есть ссылки в шаблоне письма
//	public function getEmbededImages() {
//		$return = array();
//		$template_content = file_get_contents(TEMPLATE_ROOT.'maillist/message.'.LANGUAGE_CURRENT.'.tmpl');
//		preg_match_all('/\<img\s+[^\>]*src=[\"\']?([^\>\'\"\s]+)/i', $template_content, $matches);
//		reset($matches[1]);
//		while (list(,$file)=each($matches[1])) {
//			if (preg_match('~^/img/~i', $file) && strpos($file, '..') === false) {
//				$return[] = SITE_ROOT.substr($file, 1);
//			}
//		}
//		return $return;
//	}
	
}
?>