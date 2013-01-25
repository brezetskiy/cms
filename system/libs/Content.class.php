<?PHP
/**
 * ����� ��������� ���������� ��������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * ����� ��������� ���������� ��������
 * @package Pilot
 * @subpackage Libraries
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
Class Content {
	
	/**
	 * �������
	 *
	 * @var string
	 */
	private $table_name = '';
	
	/**
	 * �������
	 *
	 * @var string
	 */
	private $column_name = '';
	
	/**
	 * id ����, � ������� ������������� ������
	 *
	 * @var int
	 */
	private $edit_id = 0;
	
	/**
	* �����, ������� ������� ��� ����������
	* @var string
	*/
	public $content = '';
	
	/**
	* ��������, ������� ���� � ��������
	* @var array
	*/
	private $content_files = array();
	
	/**
	* ����������, � ������� ������������� �����
	* ���� site/ru/main, ��� ���������� � ��������� ������
	* @var string
	*/
	private $edit_dir = '';
	
	/**
	 * ���� ������� �� ������ ������ ���� ��������� ������ ��������
	 * ��� ��� ����� ������� �� ������������ HTML ����
	 * @var array
	 */
	private $remove_empty_tags = array('ADDRESS', 'B', 'BLOCKQUOTE', 'CAPTION', 'CENTER', 'CODE', 'DD', 'DL', 'EM', 'FONT', 'FORM', 'H1', 'H2', 'H3', 'H4', 'I', 'LI', 'MENU', 'OL', 'OPTION', 'PRE', 'SMALL', 'STRIKE', 'STRONG', 'SUB', 'SUP', 'TABLE', 'TR', 'TT', 'U', 'UL');
	
	/**
	* ����, ����������� �� ��, ���� �� �������� �������� � �������� �������,
	* ��� ����������� ��������� ������� ������ �� ��������. ������������, ���
	* ����, ���� ������ ������������ ��������� � ���, ��� ���� �������� ��������, ���
	* ����, ���� ������� ���������.
	* @public
	* @var bool
	*/
	public $remote_images = false;
	
	/**
	* ���������� ������� ������ �� ��������
	* @var int
	*/
	private $stat = array('thumb_size' => 0, 'image_size' => 0, 'attach_size' => 0, 'content_size' => 0, 'encoding' => CMS_CHARSET);
	
	
	/**
	 * ������ tidy
	 * ��������: http://tidy.sourceforge.net/docs/quickref.html
	 * @var array
	 */
	private $tidy_config = array(
		'bare' => 1,
//		'clean' => 1,
		'drop-proprietary-attributes' => 1,
		
		'drop-empty-paras' => 1,
		'drop-font-tags' => 0,
		'output-html' => 1,
		'join-classes' => 1,
		'join-styles' => 1,
		'logical-emphasis' => 1,
		'lower-literals' => 1,
		'merge-divs' => 0,
		'show-body-only' => 1,
		'uppercase-tags' => 0,
		'wrap' => 1000,
		'break-before-br' => 1,
		'indent' => 1,
		'force-output' => 1,
	);
	
	/**
	* ����������� ������
	* @param string $content
	* @param string $edit_dir - ���� site/ru/main, ��� ���������� � ��������� ������
	* @param bool $html_tidy
	* @param bool $html_auto_charset
	* @param void
	*/
	function __construct($content, $table_name, $column_name, $edit_id, $html_tidy, $html_auto_charset) {
		
		$this->content = $content;
		$this->table_name = $table_name;
		$this->column_name = $column_name;
		$this->edit_id = $edit_id;
		$this->edit_dir = Uploads::getStorage($table_name, $column_name, $edit_id);
		
		/**
		 * ��������������� ���������� ������ ��� ��������, ������� �� ��� ��������� http://___/ � ��������� �� /
		 */
		$this->content = str_replace(array('src="'.CMS_URL, 'href="'.CMS_URL), array('src="/', 'href="/'), $this->content);
		
		/**
		 * ���� � ������ �������� ��������� {#CMS_HOST} �� ��� ����� ��������
		 */
		$this->content = str_replace('/tools/editor/frame/%7B', '{', $this->content);
		
		/**
		 * ������� ������� ���� ������ ������� ������� ��� ������� ��� &nbsp; �� ����������� ������
		 */
		if ($html_tidy) {
			if (extension_loaded('tidy')) {
				$this->content = iconv('utf-8', CMS_CHARSET.'//IGNORE', tidy_repair_string(iconv(CMS_CHARSET, 'utf-8//IGNORE', $this->content), $this->tidy_config, 'utf8'));
			} else {
				$this->content = preg_replace("/<(".implode('|', $this->remove_empty_tags).")[^>]*>((?:\s|\n|\r|\t|\&nbsp\;)*)<\/\\1>/i", "\\2", $this->content);
				$this->content = preg_replace("/<a[^>]+href=[^>]+>((?:[\s\n\r\t]+|&nbsp;)*)<\/a>/ism", "\\1", $this->content);
			}
		}
		
		// ���� �� �������� ��������� ������� �� �����, �� � ����� ����� ����� ������������� URL ��������
		$this->content = preg_replace("~/tools/editor/frame/edit.php[^#]+#~", '#', $this->content);
		$this->stat['content_size'] = strlen($this->content);
	}
	
	/**
	* ������������ ������ ������ � ���������� ����� ������, ������� ������������ � �������� 
	* @param string $regexp
	* @param string $stat_name
	* @return void
	*/
	private function attachedFiles($regexp, $stat_name) {
		preg_match_all($regexp, $this->content, $matches);
		if (isset($matches[1]) && !empty($matches[1])) {
			
			// ��������� ������ �� �����
			$new_files = array_unique($matches[1]);
			$this->content_files = array_merge($this->content_files, $new_files);
			
			// ������� ������ ������
			reset($new_files);
			while (list(, $file) = each($new_files)) {
				if (strpos($file, '../') === false && is_file(SITE_ROOT . $file)) { // ��� ��������� ������ ���� href="../../Order/" ��������� ������
					$this->stat[$stat_name] += filesize(SITE_ROOT . $file);
				}
			}
		}
	}
	
	/**
	* ������� �������� �������� � �����
	* @param string $images_root
	* @return void
	*/
	public function rmImages() {
		// ���������� � ����������
		$images_root = UPLOADS_ROOT . $this->edit_dir . '/';
		
		// ����� ��������� ������
		$this->attachedFiles("~<img[^>]+src=\"(.+)\"~iU", 'thumb_size');
		$this->attachedFiles("~<a[^>]+href=\"(.+)\"~iU", 'image_size');
		$this->attachedFiles("~/download\.php\?url=([^\&\']+)&~i", 'attach_size');
		$this->attachedFiles("~<embed[^>]+src=\"(.+)\"~iU", 'image_size');

		// ���������� ����� �������� ��������� � ���������� �������� �������� �����
		if (!is_dir($images_root)) {
			makedir($images_root, 0777, true);
		}
		$ftp_images = Filesystem::getDirContent($images_root, true, false, true);
		$all_images = array();
		
		/**
		 * ���������� �������� ����� ��������
		 */
		if (is_array($this->content_files) && is_array($ftp_images)) {
			
			reset($ftp_images);
			while(list($index, $node) = each($ftp_images)) {
				$img_url = substr($ftp_images[$index], strlen(SITE_ROOT) - 1);
				$all_images[$img_url] = $ftp_images[$index];
			}
			unset($ftp_images);
			
			reset($this->content_files);
			while(list($index, $img) = each($this->content_files)) {
				if (isset($all_images[$img])) {
					unset($all_images[$img]);
				}
			}
		}
		
		/**
		 * ������� ����� ��������, ������� ��� � ����������� �����
		 */
		if (is_array($this->content_files) && is_array($all_images)) {
			reset($all_images);
			while(list(,$img_file) = each($all_images)) {
				if (is_file($img_file)) {
					unlink($img_file);
				}
			}
		}
	}
	
	/**
	 * ��������������� ��������� url ������ � ��� {$url_xxx}, ��� xxx- id �������, �� ������� ��������� ������
	 * @param void
	 * @return void
	 */
	public function url2id() {
		global $DB;
		
		// ���������� ������ ������, ������� ���� � �������
		$query = "select uniq_name from site_structure where structure_id=0";
		$site = $DB->fetch_column($query);
		
		// ������� ��� ��������� url ������
		preg_match_all('~href="([a-zA-Z0-9\-\_\:\/\.]+)"~iU', $this->content, $matches);
		$cms_url = parse_url(CMS_URL, PHP_URL_HOST);
		$url = array();
		reset($matches[1]);
		while (list(,$row) = each($matches[1])) {
			$parsed = parse_url($row);
			if (!isset($parsed['host'])) {
				// ����� ������ �����
				$url[] = substr($cms_url.$row, 0, -1);
				
			} elseif (in_array($parsed['host'], $site)) {
				// ����� �� �������� � �������
				$url[] = substr($row, strlen($parsed['scheme'])+3, -1);
			}
		}
		
		// ���������� id ���� ��������� url ������� ��� ����� ��������
		$query = "
			SELECT concat('href=\"{url:', id, '}') as id, replace(concat('href=\"http://', url, '/'), '".CMS_URL."', '/') as url2
			FROM site_structure
			WHERE url IN ('".implode("','", $url)."')
			ORDER BY length(url2) desc /* ������ ������� - ���������� �� �������! ��� ��� ����� ������� �������� /About/, � ����� �� ������ /About/Test/ */
		";
		$url2id = $DB->fetch_column($query);
		
		$this->content = str_replace($url2id, array_keys($url2id), $this->content);
	}
	
	/**
	* ��������� ������� � �����
	* @param string $file ������ ���� � ��� ����� � �����������
	* @return void
	*/
	public function save() {
		global $DB;
		
		$query = "select id from cms_field_static where table_name='$this->table_name' and full_name='$this->column_name'";
		$DB->query($query);
		if ($DB->rows == 0) {
			Action::setError(cms_message('CMS', '���������� ��������� ������ � ������� %s.%s.', $this->table_name, $this->column_name));
		}
		
		$query = "update `$this->table_name` set `".$this->column_name."`='".str_replace("'", "\'", $this->content)."' where id='$this->edit_id'";
		$DB->update($query);
		
		if (is_module('Search')) {
			Search::update($this->table_name, $this->edit_id);
		}
	}
	
	/**
	* ���������� �������� � ������� �������� � � ������ �������
	* �����, ���������� ���� ���� �� �������� ���� �����-�� ��������
	* @param string $img_root
	* @return bool
	*/
	public function uploadImages() {
		
		if (!ini_get('allow_url_fopen')) {
			// ��� ������ ������� ���������� ���� allow_url_fopen = true
			trigger_error(cms_message('CMS', '��� ������ ������� ���������� ���� allow_url_fopen = true'), E_USER_ERROR);
		}
		
		$this->content = preg_replace_callback('/<img[^>]+src=["\']?([^>"\']+)["\']?[^>]+>/i', array($this, 'uploadImagesCallback'), $this->content);
	}
	
	/**
	* ���������� regexp'a �� ������ � ������� ��������
	* @param array
	* @return string
	*/
	private function uploadImagesCallback($matches) {
		
		$extension = Uploads::getFileExtension($matches[1]);
		if (empty($extension)) {
			$extension = 'jpg';
		}
		
		$destination_file = Filesystem::getMaxFileId(UPLOADS_ROOT . $this->edit_dir . '/').'.'.$extension;
		
		if (!file_exists(dirname($destination_file))) {
			makedir(dirname($destination_file), 0777, true);
		}
		
		if (
			!preg_match("/^\/".addcslashes(UPLOADS_DIR.$this->edit_dir, '/')."/", $matches[1])
			&& !preg_match("/^\/".addcslashes(CMS_URI.UPLOADS_DIR.$this->edit_dir, '/')."/", $matches[1])
		) {
			// ���������� �������� � ������� ��������
			
			if (preg_match("/icq\.com/", $matches[1])) {
				return $matches[0];
			} elseif (
				preg_match("/^\//", $matches[1])
				|| preg_match("/^".addcslashes(CMS_URL, "/")."/", $matches[1])
			) {
				// ��������� ������ ���������� � / ��� � ������ ����� �����
				$upload_result = $this->uploadLocalImage($matches[1], $destination_file);
			} elseif (preg_match("/^http:\/\//", $matches[1])) {
				// ��������, ������� ��������� �� ������� �������
				$upload_result = $this->uploadRemoteImage($matches[1], $destination_file);
			} else {
				/**
				* ��������� ���� ������ - �� �������� �������� ��� ��� ���� ������ ../../../config.ini, 
				* �� � ����� ������� ���������� � �������� ���� � ��������
				*/
				$upload_result = false;
			}
			
			if ($upload_result === true) {
				// �������� ������� �������� ��� ������� ������� ������
				// ������ ���� src
				$destination_url = substr($destination_file, strlen(SITE_ROOT) - 1);
				$this->remote_images = true;
				return preg_replace("/".addcslashes($matches[1], "/")."/", $destination_url, $matches[0]);
			} else {
				return $matches[0];
			}
			
		} else {
			return $matches[0];
		}
	}
	
	/**
	* ������� ������� ������ �� ��������� ��������
	* @param string $img_url
	* @return string
	*/
	private function uploadLocalImage($img_url, $destination_file) {
		if (is_file(substr(SITE_ROOT, 0, -1) . $img_url)) {
			link(substr(SITE_ROOT, 0, -1) . $img_url, $destination_file);
			return true;
		} else {
			return false;
		}
	}
	
	/**
	* ���������� �������� � ������� ��������
	* @param string $img_url
	* @return string
	*/
	private function uploadRemoteImage($img_url, $destination_file) {
		$img = file_get_contents($img_url);
		file_put_contents($destination_file, $img);
		if (filesize($destination_file) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * ����������� ����� ��� ����, ��� � �� � ���������� ��� ���� ��������� diff
	 * @param void
	 * @return void
	 */
	public function prepare4diff() {
		$this->content = preg_replace("/(<(?:p|br|ul|li|ol|div)[^>]*>)/i", "\n\\1", $this->content);
		$this->content = preg_replace("/(<\/(?:p|br|ul|li|ol|div)[^>]*>)/i", "\\1\n", $this->content);
	}
	
	/**
	* ���������� �����, ������� ����� ������� � ���������
	* @param void
	* @return string
	*/
	public function statistic() {
		global $_RESULT;
		$_RESULT['content_size'] = ceil($this->stat['content_size'] / 1000);
		$_RESULT['thumb_size'] = ceil($this->stat['thumb_size'] / 1000);
		$_RESULT['attach_size'] = ceil($this->stat['attach_size'] / 1000);
		$_RESULT['encoding'] = $this->stat['encoding'];
	}
}
?>