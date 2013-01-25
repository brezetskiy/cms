<?php
/**
 * Класс формирующий MIME тело сообщения
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

require_once(LIBS_ROOT.'mime/quotedprintable.class.php');
require_once(LIBS_ROOT.'mime/base64.class.php');

/**
 * Класс формирующий MIME тело сообщения
 * @package Maillist
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class Mime {
	
	/**
	* Заголовки
	* @var array
	*/
	private $headers = array();
	
	/**
	 * Алгоритм кодирования HTML текста, будет указан в Content-Transfer-Encoding заголовке
	 * @var string
	 */
	protected $encoding = 'quoted-printable';
	
	/**
	* Тела всех прикрепленных к сообщению файлов 
	* и соответствующих им заголовков
	* @var array
	*/
	private $bodies = array();
	
	/**
	* Тело сообщения
	* @var string
	*/
	private $body = '';
	
	/**
	* Разделитель частей multipart MIME сообщения
	* @var string
	*/
	private $boundary;
	
	/**
	* Соответствие расширений файлов MIME типам
	* @var array
	*/
	private $mime_types = array(
		'323' => 'text/h323',
		'acx' => 'application/internet-property-stream',
		'ai' => 'application/postscript',
		'aif' => 'audio/x-aiff',
		'aifc' => 'audio/x-aiff',
		'aiff' => 'audio/x-aiff',
		'asf' => 'video/x-ms-asf',
		'asr' => 'video/x-ms-asf',
		'asx' => 'video/x-ms-asf',
		'au' => 'audio/basic',
		'avi' => 'video/x-msvideo',
		'axs' => 'application/olescript',
		'bas' => 'text/plain',
		'bcpio' => 'application/x-bcpio',
		'bin' => 'application/octet-stream',
		'bmp' => 'image/bmp',
		'c' => 'text/plain',
		'cat' => 'application/vnd.ms-pkiseccat',
		'cdf' => 'application/x-cdf',
		'cer' => 'application/x-x509-ca-cert',
		'class' => 'application/octet-stream',
		'clp' => 'application/x-msclip',
		'cmx' => 'image/x-cmx',
		'cod' => 'image/cis-cod',
		'cpio' => 'application/x-cpio',
		'crd' => 'application/x-mscardfile',
		'crl' => 'application/pkix-crl',
		'crt' => 'application/x-x509-ca-cert',
		'csh' => 'application/x-csh',
		'css' => 'text/css',
		'dcr' => 'application/x-director',
		'der' => 'application/x-x509-ca-cert',
		'dir' => 'application/x-director',
		'dll' => 'application/x-msdownload',
		'dms' => 'application/octet-stream',
		'doc' => 'application/msword',
		'dot' => 'application/msword',
		'dvi' => 'application/x-dvi',
		'dxr' => 'application/x-director',
		'eps' => 'application/postscript',
		'etx' => 'text/x-setext',
		'evy' => 'application/envoy',
		'exe' => 'application/octet-stream',
		'fif' => 'application/fractals',
		'flr' => 'x-world/x-vrml',
		'gif' => 'image/gif',
		'gtar' => 'application/x-gtar',
		'gz' => 'application/x-gzip',
		'h' => 'text/plain',
		'hdf' => 'application/x-hdf',
		'hlp' => 'application/winhlp',
		'hqx' => 'application/mac-binhex40',
		'hta' => 'application/hta',
		'htc' => 'text/x-component',
		'htm' => 'text/html',
		'html' => 'text/html',
		'htt' => 'text/webviewhtml',
		'ico' => 'image/x-icon',
		'ief' => 'image/ief',
		'iii' => 'application/x-iphone',
		'ins' => 'application/x-internet-signup',
		'isp' => 'application/x-internet-signup',
		'jfif' => 'image/pipeg',
		'jpe' => 'image/jpeg',
		'jpeg' => 'image/jpeg',
		'jpg' => 'image/jpeg',
		'js' => 'application/x-javascript',
		'latex' => 'application/x-latex',
		'lha' => 'application/octet-stream',
		'lsf' => 'video/x-la-asf',
		'lsx' => 'video/x-la-asf',
		'lzh' => 'application/octet-stream',
		'm13' => 'application/x-msmediaview',
		'm14' => 'application/x-msmediaview',
		'm3u' => 'audio/x-mpegurl',
		'man' => 'application/x-troff-man',
		'mdb' => 'application/x-msaccess',
		'me' => 'application/x-troff-me',
		'mht' => 'message/rfc822',
		'mhtml' => 'message/rfc822',
		'mid' => 'audio/mid',
		'mny' => 'application/x-msmoney',
		'mov' => 'video/quicktime',
		'movie' => 'video/x-sgi-movie',
		'mp2' => 'video/mpeg',
		'mp3' => 'audio/mpeg',
		'mpa' => 'video/mpeg',
		'mpe' => 'video/mpeg',
		'mpeg' => 'video/mpeg',
		'mpg' => 'video/mpeg',
		'mpp' => 'application/vnd.ms-project',
		'mpv2' => 'video/mpeg',
		'ms' => 'application/x-troff-ms',
		'mvb' => 'application/x-msmediaview',
		'nws' => 'message/rfc822',
		'oda' => 'application/oda',
		'p10' => 'application/pkcs10',
		'p12' => 'application/x-pkcs12',
		'p7b' => 'application/x-pkcs7-certificates',
		'p7c' => 'application/x-pkcs7-mime',
		'p7m' => 'application/x-pkcs7-mime',
		'p7r' => 'application/x-pkcs7-certreqresp',
		'p7s' => 'application/x-pkcs7-signature',
		'pbm' => 'image/x-portable-bitmap',
		'pdf' => 'application/pdf',
		'pfx' => 'application/x-pkcs12',
		'pgm' => 'image/x-portable-graymap',
		'pko' => 'application/ynd.ms-pkipko',
		'pma' => 'application/x-perfmon',
		'pmc' => 'application/x-perfmon',
		'pml' => 'application/x-perfmon',
		'pmr' => 'application/x-perfmon',
		'pmw' => 'application/x-perfmon',
		'pnm' => 'image/x-portable-anymap',
		'pot,' => 'application/vnd.ms-powerpoint',
		'ppm' => 'image/x-portable-pixmap',
		'pps' => 'application/vnd.ms-powerpoint',
		'ppt' => 'application/vnd.ms-powerpoint',
		'prf' => 'application/pics-rules',
		'ps' => 'application/postscript',
		'pub' => 'application/x-mspublisher',
		'qt' => 'video/quicktime',
		'ra' => 'audio/x-pn-realaudio',
		'ram' => 'audio/x-pn-realaudio',
		'ras' => 'image/x-cmu-raster',
		'rgb' => 'image/x-rgb',
		'rmi' => 'audio/mid',
		'roff' => 'application/x-troff',
		'rtf' => 'application/rtf',
		'rtx' => 'text/richtext',
		'scd' => 'application/x-msschedule',
		'sct' => 'text/scriptlet',
		'setpay' => 'application/set-payment-initiation',
		'setreg' => 'application/set-registration-initiation',
		'sh' => 'application/x-sh',
		'shar' => 'application/x-shar',
		'sit' => 'application/x-stuffit',
		'snd' => 'audio/basic',
		'spc' => 'application/x-pkcs7-certificates',
		'spl' => 'application/futuresplash',
		'src' => 'application/x-wais-source',
		'sst' => 'application/vnd.ms-pkicertstore',
		'stl' => 'application/vnd.ms-pkistl',
		'stm' => 'text/html',
		'sv4cpio' => 'application/x-sv4cpio',
		'sv4crc' => 'application/x-sv4crc',
		't' => 'application/x-troff',
		'tar' => 'application/x-tar',
		'tcl' => 'application/x-tcl',
		'tex' => 'application/x-tex',
		'texi' => 'application/x-texinfo',
		'texinfo' => 'application/x-texinfo',
		'tgz' => 'application/x-compressed',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'tr' => 'application/x-troff',
		'trm' => 'application/x-msterminal',
		'tsv' => 'text/tab-separated-values',
		'txt' => 'text/plain',
		'uls' => 'text/iuls',
		'ustar' => 'application/x-ustar',
		'vcf' => 'text/x-vcard',
		'vrml' => 'x-world/x-vrml',
		'wav' => 'audio/x-wav',
		'wcm' => 'application/vnd.ms-works',
		'wdb' => 'application/vnd.ms-works',
		'wks' => 'application/vnd.ms-works',
		'wmf' => 'application/x-msmetafile',
		'wps' => 'application/vnd.ms-works',
		'wri' => 'application/x-mswrite',
		'wrl' => 'x-world/x-vrml',
		'wrz' => 'x-world/x-vrml',
		'xaf' => 'x-world/x-vrml',
		'xbm' => 'image/x-xbitmap',
		'xla' => 'application/vnd.ms-excel',
		'xlc' => 'application/vnd.ms-excel',
		'xlm' => 'application/vnd.ms-excel',
		'xls' => 'application/vnd.ms-excel',
		'xlt' => 'application/vnd.ms-excel',
		'xlw' => 'application/vnd.ms-excel',
		'xof' => 'x-world/x-vrml',
		'xpm' => 'image/x-xpixmap',
		'xwd' => 'image/x-xwindowdump',
		'z' => 'application/x-compress',
		'zip' => 'application/zip'
	);
	
	/**
	* Конструктор
	* @param string $from
	* @param string $to
	* @return object
	*/
	public function __construct($from, $to) {
		$this->boundary = uniqid("--PartSplitter--");
		
		$this->headers = array(
			'Date' => date('r'),
			'From' => $from,
			'To' => $to,
			'Content-Type' => "multipart/mixed; boundary=".$this->boundary,
			'MIME-Version' => '1.0'
		);
	}
	
	public function setEncodingBase64() {
		$this->encoding = 'base64';
	}
	
	public function setEncodingQuotedPrintable() {
		$this->encoding = 'quoted-printable';
	}
	
	public function startAlternative($boundary) {
		$this->addBody(array('boundary' => $boundary), 'start');
	}
	
	public function endAlternative($boundary) {
		$this->addBody(array('boundary' => $boundary), 'end');
	}
	
	/**
	* Задает текст сообщения в формате HTML
	* @param $message string
	* @return void
	*/
	public function setHtml($message) {
		//$charset = Charset::detectCyrCharset($message);
		$charset = 'Windows-1251';
		/**
		 * M$ Outlook не понимает кодировку CP1251, поэтому меняем ее на windows-1251
		 */
		/*if ($charset == 'CP1251') {
			$charset = 'windows-1251';
		}*/
		
		/**
		 * Добавляем text/plain версию письма
		 */
		$sub_splitter = uniqid("--SubSplitter--");
		$this->startAlternative($sub_splitter);
		
		/*
		$this->addBody(
			array(
				'Content-Type' => 'text/plain; charset='.$charset,
				'Content-Transfer-Encoding' => $this->encoding,
				'Content-Disposition' => 'inline'
			),
			$this->encode(strip_tags($message))
			// $this->quoted_printable($message)
			// chunk_split(base64_encode(($message)))
		);
		*/
		
		/**
		 * Добавляем Html версию письма
		 */
		$this->addBody(
			array(
				'Content-Type' => 'text/html; charset='.$charset,
				'Content-Transfer-Encoding' => $this->encoding,
				'Content-Disposition' => 'inline'
			),
			$this->encode($message)
			// $this->quoted_printable($message)
			// chunk_split(base64_encode(($message)))
		);
		
		$this->endAlternative($sub_splitter);
	}
	
	/**
	* Задает plain-text версию сообщения
	* @param $message string
	* @return void
	*/
	public function setPlainText($message) {
		//$charset = Charset::detectCyrCharset($message);
		$charset = 'Windows-1251';
		
		/**
		 * M$ Outlook не понимает кодировку CP1251, поэтому меняем ее на windows-1251
		 */
		/*if ($charset == 'CP1251') {
			$charset = 'windows-1251';
		}*/
		
		$this->addBody(
			array(
				'Content-Type' => 'text/plain; charset='.$charset,
				'Content-Transfer-Encoding' => $this->encoding,
				'Content-Disposition' => 'inline'
			),
			$this->encode($message)
		);
	}
	
	/**
	* Добавить массив заголовков
	* @param $headers array Массив заголовков
	* @return void
	*/
	public function addHeaders($headers) {
		$this->headers = array_merge($this->headers, $headers);
	}
	
	/**
	* Задать значение заголовка
	* @param $name string Имя заголовка
	* @param $value string Значение заголовка
	* @return void
	*/
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
	/**
	* Возвращает список заголовков в готовом для отправки виде
	* @return array Заголовки в MIME формате
	*/
	public function getHeaders() {
		$return = array();
		reset($this->headers);
		while(list($key, $value) = each($this->headers)) {
			switch (strtolower($key)) {
				case 'from':
				case 'to':
				case 'reply-to':
					if (preg_match('/(\<?[a-z0-9_\.\-]+@[a-z0-9_\.\-]+\.[a-z]{2,4}\>?)$/i', $value, $match) && trim($value) != trim($match[1])) {
						$value = $this->formatString(preg_replace('/'.addslashes($match[1]).'/', '', $value)). ' ' . $match[1];
					}
					break;
				case 'subject':
					$value = $this->formatString($value);
			}
			
			$return[$key] = $value;	
			
			//if (preg_match('/[а-яА-Я]/', $value)) {
			//	$value = $this->formatString($value);
			//}
			//$return[$key] = $value;
		}
		
		return $return;
	}
	
	/**
	* Возвращает тело письма с вложениями, готовое для отправки
	* @return string Тело письма в MIME формате
	*/
	public function getMessageBody() {
		$return = '';
		
		$boundary = $this->boundary;
		
		reset($this->bodies);
		while(list(, $body) = each($this->bodies)) {
			
			if (isset($body['headers']['boundary'])) {
				/**
				 * Sub-boundary
				 */
				if ($body['body'] == 'start') {
					// start sub-part
					$return .= "--".$boundary."\r\n";
					$boundary = $body['headers']['boundary'];
					$return .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n\r\n";
					
				} else {
					$return .= "--".$boundary."--\n\n";
					$boundary = $this->boundary;
				}
				continue;
			}
			$return .= "--".$boundary."\r\n";
			reset($body['headers']);
			while (list($key, $value) = each($body['headers'])) {
				$return .= $key.": ".$value."\r\n";
			}
			$return .= "\r\n".$body['body']."\r\n";
		}
		return $return."--".$boundary."--";
	}
	
	/**
	* Прикрепить HTML рисунок
	* @param string $filename string Имя файла с рисунком
	* @param string $name - имя под которым будет назван файл в письме
	* @return bool
	*/
	public function attachImage($filename, $name = '') {
		if (!is_file($filename) || !is_readable($filename)) {
			return false;
		}
		if (empty($name)) {
			$name = basename($filename);
		}
		$this->addBody(
			array(
				'Content-Type' => $this->getMimeType($filename).'; name="'.$name.'"',
				'Content-Transfer-Encoding' => 'base64',
				'Content-ID' => '<'.basename($filename).'>'
			),
			chunk_split(base64_encode(file_get_contents($filename)))
		);
		return true;
	}
	
	/**
	* Прикрепить файл
	* @param $file string Полное имя файла (с путем), который нужно прикрепить
	* @param $filename string Имя файла
	* @return bool
	*/
	public function attachFile($file, $filename) {
		if (!is_file($file) || !is_readable($file)) {
			return false;
		}
		$this->addBody(
			array(
				'Content-Type' => $this->getMimeType($file).'; name="'.$filename.'"',
				'Content-Transfer-Encoding' => 'base64',
				'Content-Disposition' => 'attachment; filename="'.$filename.'"'
			),
			chunk_split(base64_encode(file_get_contents($file)))
		);
		return true;
	}
	
	/**
	* Добавить прикрепленный файл или рисунок к телу сообщения
	* @param array $headers Заголовки, относящиеся к прикрепляемому объекту
	* @param string $body Тело объекта (УЖЕ приведенное в формат, готовый для отправки)
	* @return void
	*/
	private function addBody($headers, $body) {
		$this->bodies[] = array('headers' => $headers, 'body' => $body);
	}
	
	/**
	* Форматирует строку для корректной вставки в заголовок
	* @param $string string Строка для преобразования
	* @return string
	*/
	protected function formatString($string) {
		if ($this->encoding=='base64') {
			return MimeBase64::formatString($string);
		} else {
			return MimeQuotedPrintable::formatString($string);
		}
	}
	
	/**
	* Функция, которая правильно делает quoted-printable версию текста
	* работает медленно
	* @param $string string 
	* @return string
	*/
	protected function encode($string) {
		if ($this->encoding=='base64') {
			return MimeBase64::encode($string);
		} else {
			return MimeQuotedPrintable::encode($string);
		}
	}

	/**
	* Возвращает MIME-тип файла, основываясь на расширении файла
	* @param $filename string Имя файла
	* @return string MIME-тип файла
	*/
	private function getMimeType($filename) {
		$ext = substr($filename, strrpos($filename, '.') + 1);
		if(isset($this->mime_types[$ext])) {
			return $this->mime_types[$ext];
		} else {
			return "application/octet-stream";
		}
	}
	
	/**
	 * Разбивает текст multipart сообщения на части,
 	 * Производит обработку частей (декодирование base64, quoted-printable, перевод текста в кодировку сайта)
 	 * 
	 * Возвращает массив со следующей структурой:
	 * [<номер части сообщения>] => Array (
	 * 		['body'] => тело части
	 * 		['headers'] => заголовки части
	 * 		['meta'] => Array (
	 * 			['type'] => тип части (одно из: attachment, embed, text, multipart, undefined)
	 * 			['filename'] => имя файла (существует только для типов attachment и embed)
	 * 			['extension'] => расширение файла (существует только для типов attachment и embed)
	 * 			['cid'] => идентификатор контента (только для типа embed)
	 * 		)
 	 * )
	 * 		
	 * 
	 * @param string $message Текст сообщения в формате MIME
	 * @param bool $text_to_html Преобразовать текстовые части в html
	 * @return array
	 */
	public static function decode($message, $text_to_html = false) {
		$message_parts = array();
	
		/**
		 * Отделяем заголовки сообщения от тела
		 */
		$splitted = preg_split("/\r?\n\r?\n/", $message, 2);
		
		
		if (count($splitted) != 2) {
			/**
			 * Неправильный формат - не удалось отделить заголовки от тела
			 */
			self::debug('Неправильный формат - не удалось отделить заголовки от тела');
			return false;
		}
		
		/**
		 * Делаем разбор заголовков - преобразование списка заголовков в массив
		 * Все ключи массива (имена заголовков) - в нижнем регистре
		 */
		$splitted[0] = array_change_key_case(iconv_mime_decode_headers(trim($splitted[0]), 0, LANGUAGE_CHARSET), CASE_LOWER);
		
		if (self::set_and_match('/multipart.*boundary=\"?([^\"]*)\"?/i', $splitted[0]['content-type'], $match)) {
			/**
			 * Текущая часть содержит в себе еще одно multipart сообщение - разбираем его отдельно
			 */
			$parts = preg_split('/--'.preg_quote($match[1], '/').'/', $message);
			
			self::debug("boundary: $match[1]");
			//x($parts);
			
			if (!is_array($parts)) {
				/**
				 * Неправильный формат сообщения
				 */
				self::debug('Не распознан формат вложенного multipart сообщения');
				return false;
			}
			
			self::debug('Вложеное multipart сообщение');
			
			/**
			 * Убираем top-level заголовок, чтобы предотвратить зацикливание,
			 * предварительно сохранив их в первом элементе возвращаемого массива
			 */
			$message_parts[0] = array('headers' => $splitted[0], 'meta' => array('type' => 'multipart'));
			unset($parts[0]);
			
			$splitted = array();
			reset($parts);
			while (list(,$row)=each($parts)) {
				$splitted = self::decode($row, $text_to_html);
				
				if (!is_array($splitted)) {
					self::debug('не удалось распарсить вложенное сообщение');
					continue;
				}
				
				reset($splitted);
				while (list(,$row2)=each($splitted)) {
					$message_parts[] = $row2;
				}
			}
		} else {
			/**
			 * Текущая часть - не вложенное multipart сообщение
			 */
			$splitted_part = preg_split("/\r?\n\r?\n/", $message, 2);
			
			if (count($splitted_part) == 2) {
				$this_part = array('headers' => array_change_key_case(iconv_mime_decode_headers(trim($splitted_part[0]), 0, LANGUAGE_CHARSET), CASE_LOWER), 'body' => trim($splitted_part[1]));
				
				/**
				 * Декодирование quoted-printable или base64, если такое присутствует
				 */
				if (self::set_and_match('/(quoted-printable|base64)/i', $this_part['headers']['content-transfer-encoding'], $match)) {
					if ($match[1] == 'base64') {
						//echo "[i] == Base64 encoded file\n";
						$this_part['body'] = base64_decode($this_part['body']);
					} elseif ($match[1] == 'quoted-printable') {
						//echo "[i] == Quoted-printable text\n";
						$this_part['body'] = quoted_printable_decode($this_part['body']);
					}
				}
				
				/**
				 * Исправление глюка с MS Word текстами - замена длинного тире на знак минуса
				 */
				if (self::set_and_match('/text\/html/i', $this_part['headers']['content-type'])) { 
					$this_part['body'] = preg_replace('/\x96/', chr(45), $this_part['body']);
				}
				
				/**
				 * Перевод в кодировку сайта, если в заголовке указана кодировка сообщения
				 */
				if (self::set_and_match('/charset=\"?([^\s\";]+)/i', $this_part['headers']['content-type'], $match)) {
					//echo "[i] == Charset detected: ".$match[1]."\n";
					$this_part['body'] = iconv($match[1], LANGUAGE_CHARSET.'//IGNORE', $this_part['body']);
				} elseif (self::set_and_match('/text\//i', $this_part['headers']['content-type'])) { 
					$charset = Charset::detectCyrCharset($this_part['body']);
					//echo "[i] == Charset GUESSED: $charset\n";
					$this_part['body'] = iconv($charset, LANGUAGE_CHARSET.'//IGNORE', $this_part['body']);
				}
				
				/**
				 * Определение типа части - это аттач, встроенный в HTML файл или текст
				 */
				if (self::set_and_match('/attachment;\s+filename=\"(.*)\"/i', $this_part['headers']['content-disposition'], $match)) {
					$this_part['meta']['type'] = 'attachment';
					$this_part['meta']['filename'] = $match[1];
					$this_part['meta']['extension'] = Uploads::getFileExtension($this_part['meta']['filename']);
					
				} elseif (self::set_and_match('/\<(.*)\>/i', $this_part['headers']['content-id'], $match) && self::set_and_match('/name=\"(.*)\"/i', $this_part['headers']['content-type'], $match2)) {
					$this_part['meta']['type'] = 'embed';
					$this_part['meta']['filename'] = $match2[1];
					$this_part['meta']['extension'] = Uploads::getFileExtension($this_part['meta']['filename']);
					$this_part['meta']['cid'] = $match[1];
					
				} elseif (self::set_and_match('/text\//i', $this_part['headers']['content-type'])) { 
					$this_part['meta']['type'] = 'text';
					
				} else {
					$this_part['meta']['type'] = 'undefined';
				}
				
				/**
				 * Преобразование текста в HTML
				 */
				if (self::set_and_match('/text\/plain/i', $this_part['headers']['content-type']) && $text_to_html) { 
					$this_part['body'] = self::text2html($this_part['body']);
				}
				
				$message_parts[] = $this_part;
			}
		}
		
		/**
		 * Если не найдено ни одной части - значит сообщение имеет неправильный формат 
		 */
		if (count($message_parts) == 0) {
			self::debug('не найдено ни одной части - сообщение имеет неправильный формат');
			return false;
		} else {
			return $message_parts;	
		}
	}
	
	/**
	 * Проверяет, существует ли переменная и соответствует ли она шаблону
	 * Вспомогательная функция. Используется в Mime::decode()
	 *
	 * @param string $pattern
	 * @param string $subject
	 * @param array $match
	 * @return bool
	 */
	private static function set_and_match($pattern, &$subject, &$match = null) {
		if (isset($subject) && preg_match($pattern, $subject, $match)) {
			return true;
		}
		return false;
	}
	
	/**
	 * Преобразование текста в HTML
	 * Вспомогательная функция. Используется в Mime::decode()
	 *
	 * @param string $text
	 * @return string
	 */
	private static function text2html($text) {
		/**
		 * 1. Преобразуем переводы строк в <br>
		 */
		$result = preg_replace("/(\r\n|\r|\n)/", "<br />\n", $text);
		return $result;
	}
	
	/**
	 * Вывод debug сообщения
	 * @param string $message
	 */
	protected static function debug($message) {
		//echo iconv(LANGUAGE_CHARSET, 'UTF-8//IGNORE', "[D] $message\n");
	}
}
