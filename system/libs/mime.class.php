<?php
/**
 * ����� ����������� MIME ���� ���������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

require_once(LIBS_ROOT.'mime/quotedprintable.class.php');
require_once(LIBS_ROOT.'mime/base64.class.php');

/**
 * ����� ����������� MIME ���� ���������
 * @package Maillist
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
class Mime {
	
	/**
	* ���������
	* @var array
	*/
	private $headers = array();
	
	/**
	 * �������� ����������� HTML ������, ����� ������ � Content-Transfer-Encoding ���������
	 * @var string
	 */
	protected $encoding = 'quoted-printable';
	
	/**
	* ���� ���� ������������� � ��������� ������ 
	* � ��������������� �� ����������
	* @var array
	*/
	private $bodies = array();
	
	/**
	* ���� ���������
	* @var string
	*/
	private $body = '';
	
	/**
	* ����������� ������ multipart MIME ���������
	* @var string
	*/
	private $boundary;
	
	/**
	* ������������ ���������� ������ MIME �����
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
	* �����������
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
	* ������ ����� ��������� � ������� HTML
	* @param $message string
	* @return void
	*/
	public function setHtml($message) {
		//$charset = Charset::detectCyrCharset($message);
		$charset = 'Windows-1251';
		/**
		 * M$ Outlook �� �������� ��������� CP1251, ������� ������ �� �� windows-1251
		 */
		/*if ($charset == 'CP1251') {
			$charset = 'windows-1251';
		}*/
		
		/**
		 * ��������� text/plain ������ ������
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
		 * ��������� Html ������ ������
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
	* ������ plain-text ������ ���������
	* @param $message string
	* @return void
	*/
	public function setPlainText($message) {
		//$charset = Charset::detectCyrCharset($message);
		$charset = 'Windows-1251';
		
		/**
		 * M$ Outlook �� �������� ��������� CP1251, ������� ������ �� �� windows-1251
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
	* �������� ������ ����������
	* @param $headers array ������ ����������
	* @return void
	*/
	public function addHeaders($headers) {
		$this->headers = array_merge($this->headers, $headers);
	}
	
	/**
	* ������ �������� ���������
	* @param $name string ��� ���������
	* @param $value string �������� ���������
	* @return void
	*/
	public function setHeader($name, $value) {
		$this->headers[$name] = $value;
	}
	
	/**
	* ���������� ������ ���������� � ������� ��� �������� ����
	* @return array ��������� � MIME �������
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
			
			//if (preg_match('/[�-��-�]/', $value)) {
			//	$value = $this->formatString($value);
			//}
			//$return[$key] = $value;
		}
		
		return $return;
	}
	
	/**
	* ���������� ���� ������ � ����������, ������� ��� ��������
	* @return string ���� ������ � MIME �������
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
	* ���������� HTML �������
	* @param string $filename string ��� ����� � ��������
	* @param string $name - ��� ��� ������� ����� ������ ���� � ������
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
	* ���������� ����
	* @param $file string ������ ��� ����� (� �����), ������� ����� ����������
	* @param $filename string ��� �����
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
	* �������� ������������� ���� ��� ������� � ���� ���������
	* @param array $headers ���������, ����������� � �������������� �������
	* @param string $body ���� ������� (��� ����������� � ������, ������� ��� ��������)
	* @return void
	*/
	private function addBody($headers, $body) {
		$this->bodies[] = array('headers' => $headers, 'body' => $body);
	}
	
	/**
	* ����������� ������ ��� ���������� ������� � ���������
	* @param $string string ������ ��� ��������������
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
	* �������, ������� ��������� ������ quoted-printable ������ ������
	* �������� ��������
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
	* ���������� MIME-��� �����, ����������� �� ���������� �����
	* @param $filename string ��� �����
	* @return string MIME-��� �����
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
	 * ��������� ����� multipart ��������� �� �����,
 	 * ���������� ��������� ������ (������������� base64, quoted-printable, ������� ������ � ��������� �����)
 	 * 
	 * ���������� ������ �� ��������� ����������:
	 * [<����� ����� ���������>] => Array (
	 * 		['body'] => ���� �����
	 * 		['headers'] => ��������� �����
	 * 		['meta'] => Array (
	 * 			['type'] => ��� ����� (���� ��: attachment, embed, text, multipart, undefined)
	 * 			['filename'] => ��� ����� (���������� ������ ��� ����� attachment � embed)
	 * 			['extension'] => ���������� ����� (���������� ������ ��� ����� attachment � embed)
	 * 			['cid'] => ������������� �������� (������ ��� ���� embed)
	 * 		)
 	 * )
	 * 		
	 * 
	 * @param string $message ����� ��������� � ������� MIME
	 * @param bool $text_to_html ������������� ��������� ����� � html
	 * @return array
	 */
	public static function decode($message, $text_to_html = false) {
		$message_parts = array();
	
		/**
		 * �������� ��������� ��������� �� ����
		 */
		$splitted = preg_split("/\r?\n\r?\n/", $message, 2);
		
		
		if (count($splitted) != 2) {
			/**
			 * ������������ ������ - �� ������� �������� ��������� �� ����
			 */
			self::debug('������������ ������ - �� ������� �������� ��������� �� ����');
			return false;
		}
		
		/**
		 * ������ ������ ���������� - �������������� ������ ���������� � ������
		 * ��� ����� ������� (����� ����������) - � ������ ��������
		 */
		$splitted[0] = array_change_key_case(iconv_mime_decode_headers(trim($splitted[0]), 0, LANGUAGE_CHARSET), CASE_LOWER);
		
		if (self::set_and_match('/multipart.*boundary=\"?([^\"]*)\"?/i', $splitted[0]['content-type'], $match)) {
			/**
			 * ������� ����� �������� � ���� ��� ���� multipart ��������� - ��������� ��� ��������
			 */
			$parts = preg_split('/--'.preg_quote($match[1], '/').'/', $message);
			
			self::debug("boundary: $match[1]");
			//x($parts);
			
			if (!is_array($parts)) {
				/**
				 * ������������ ������ ���������
				 */
				self::debug('�� ��������� ������ ���������� multipart ���������');
				return false;
			}
			
			self::debug('�������� multipart ���������');
			
			/**
			 * ������� top-level ���������, ����� ������������� ������������,
			 * �������������� �������� �� � ������ �������� ������������� �������
			 */
			$message_parts[0] = array('headers' => $splitted[0], 'meta' => array('type' => 'multipart'));
			unset($parts[0]);
			
			$splitted = array();
			reset($parts);
			while (list(,$row)=each($parts)) {
				$splitted = self::decode($row, $text_to_html);
				
				if (!is_array($splitted)) {
					self::debug('�� ������� ���������� ��������� ���������');
					continue;
				}
				
				reset($splitted);
				while (list(,$row2)=each($splitted)) {
					$message_parts[] = $row2;
				}
			}
		} else {
			/**
			 * ������� ����� - �� ��������� multipart ���������
			 */
			$splitted_part = preg_split("/\r?\n\r?\n/", $message, 2);
			
			if (count($splitted_part) == 2) {
				$this_part = array('headers' => array_change_key_case(iconv_mime_decode_headers(trim($splitted_part[0]), 0, LANGUAGE_CHARSET), CASE_LOWER), 'body' => trim($splitted_part[1]));
				
				/**
				 * ������������� quoted-printable ��� base64, ���� ����� ������������
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
				 * ����������� ����� � MS Word �������� - ������ �������� ���� �� ���� ������
				 */
				if (self::set_and_match('/text\/html/i', $this_part['headers']['content-type'])) { 
					$this_part['body'] = preg_replace('/\x96/', chr(45), $this_part['body']);
				}
				
				/**
				 * ������� � ��������� �����, ���� � ��������� ������� ��������� ���������
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
				 * ����������� ���� ����� - ��� �����, ���������� � HTML ���� ��� �����
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
				 * �������������� ������ � HTML
				 */
				if (self::set_and_match('/text\/plain/i', $this_part['headers']['content-type']) && $text_to_html) { 
					$this_part['body'] = self::text2html($this_part['body']);
				}
				
				$message_parts[] = $this_part;
			}
		}
		
		/**
		 * ���� �� ������� �� ����� ����� - ������ ��������� ����� ������������ ������ 
		 */
		if (count($message_parts) == 0) {
			self::debug('�� ������� �� ����� ����� - ��������� ����� ������������ ������');
			return false;
		} else {
			return $message_parts;	
		}
	}
	
	/**
	 * ���������, ���������� �� ���������� � ������������� �� ��� �������
	 * ��������������� �������. ������������ � Mime::decode()
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
	 * �������������� ������ � HTML
	 * ��������������� �������. ������������ � Mime::decode()
	 *
	 * @param string $text
	 * @return string
	 */
	private static function text2html($text) {
		/**
		 * 1. ����������� �������� ����� � <br>
		 */
		$result = preg_replace("/(\r\n|\r|\n)/", "<br />\n", $text);
		return $result;
	}
	
	/**
	 * ����� debug ���������
	 * @param string $message
	 */
	protected static function debug($message) {
		//echo iconv(LANGUAGE_CHARSET, 'UTF-8//IGNORE', "[D] $message\n");
	}
}
