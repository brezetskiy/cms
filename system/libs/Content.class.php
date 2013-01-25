<?PHP
/**
 * Класс обработки текстового контента
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Класс обработки текстового контента
 * @package Pilot
 * @subpackage Libraries
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 */
Class Content {
	
	/**
	 * Таблица
	 *
	 * @var string
	 */
	private $table_name = '';
	
	/**
	 * Колонка
	 *
	 * @var string
	 */
	private $column_name = '';
	
	/**
	 * id ряда, в котором редактируются данные
	 *
	 * @var int
	 */
	private $edit_id = 0;
	
	/**
	* Текст, который передан для сохранения
	* @var string
	*/
	public $content = '';
	
	/**
	* Картинки, которые есть в контенте
	* @var array
	*/
	private $content_files = array();
	
	/**
	* Директория, в которой редактируются файлы
	* путь site/ru/main, без начального и конечного слешей
	* @var string
	*/
	private $edit_dir = '';
	
	/**
	 * Теги которые не должны внутри себя содержать пустых значений
	 * Все они будут удалены из сохраняемого HTML кода
	 * @var array
	 */
	private $remove_empty_tags = array('ADDRESS', 'B', 'BLOCKQUOTE', 'CAPTION', 'CENTER', 'CODE', 'DD', 'DL', 'EM', 'FONT', 'FORM', 'H1', 'H2', 'H3', 'H4', 'I', 'LI', 'MENU', 'OL', 'OPTION', 'PRE', 'SMALL', 'STRIKE', 'STRONG', 'SUB', 'SUP', 'TABLE', 'TR', 'TT', 'U', 'UL');
	
	/**
	* Флаг, указывающий на то, были ли закачаны картинки с внешнего сервера,
	* или установлены локальные жесткие ссылки на картинки. Используется, для
	* того, чтоб выдать пользователю сообщение о том, что надо обновить редактор, для
	* того, чтоб увидеть изменения.
	* @public
	* @var bool
	*/
	public $remote_images = false;
	
	/**
	* Статистика размера файлов на странице
	* @var int
	*/
	private $stat = array('thumb_size' => 0, 'image_size' => 0, 'attach_size' => 0, 'content_size' => 0, 'encoding' => CMS_CHARSET);
	
	
	/**
	 * Конфиг tidy
	 * Описание: http://tidy.sourceforge.net/docs/quickref.html
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
	* Конструктор класса
	* @param string $content
	* @param string $edit_dir - путь site/ru/main, без начального и конечного слешей
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
		 * Преобразовываем внутренние ссылки для картинок, убираем от них приставку http://___/ и заменяяем на /
		 */
		$this->content = str_replace(array('src="'.CMS_URL, 'href="'.CMS_URL), array('src="/', 'href="/'), $this->content);
		
		/**
		 * Если в ссылку добавить константу {#CMS_HOST} то она будет изменена
		 */
		$this->content = str_replace('/tools/editor/frame/%7B', '{', $this->content);
		
		/**
		 * Убираем мертвые теги внутри которых пробелы или пустота или &nbsp; за исключением якорей
		 */
		if ($html_tidy) {
			if (extension_loaded('tidy')) {
				$this->content = iconv('utf-8', CMS_CHARSET.'//IGNORE', tidy_repair_string(iconv(CMS_CHARSET, 'utf-8//IGNORE', $this->content), $this->tidy_config, 'utf8'));
			} else {
				$this->content = preg_replace("/<(".implode('|', $this->remove_empty_tags).")[^>]*>((?:\s|\n|\r|\t|\&nbsp\;)*)<\/\\1>/i", "\\2", $this->content);
				$this->content = preg_replace("/<a[^>]+href=[^>]+>((?:[\s\n\r\t]+|&nbsp;)*)<\/a>/ism", "\\1", $this->content);
			}
		}
		
		// Если на странице поставить переход по якорю, то к этому якорю будет привязываться URL страницы
		$this->content = preg_replace("~/tools/editor/frame/edit.php[^#]+#~", '#', $this->content);
		$this->stat['content_size'] = strlen($this->content);
	}
	
	/**
	* Просчитывает размер файлов и определяет имена файлов, которые присоеденены к странице 
	* @param string $regexp
	* @param string $stat_name
	* @return void
	*/
	private function attachedFiles($regexp, $stat_name) {
		preg_match_all($regexp, $this->content, $matches);
		if (isset($matches[1]) && !empty($matches[1])) {
			
			// Добавляем ссылки на файлы
			$new_files = array_unique($matches[1]);
			$this->content_files = array_merge($this->content_files, $new_files);
			
			// Считаем размер файлов
			reset($new_files);
			while (list(, $file) = each($new_files)) {
				if (strpos($file, '../') === false && is_file(SITE_ROOT . $file)) { // при обработке ссылок типа href="../../Order/" возникает ошибка
					$this->stat[$stat_name] += filesize(SITE_ROOT . $file);
				}
			}
		}
	}
	
	/**
	* Удаляем ненужные картинки и файлы
	* @param string $images_root
	* @return void
	*/
	public function rmImages() {
		// Директория с картинками
		$images_root = UPLOADS_ROOT . $this->edit_dir . '/';
		
		// имена вложенных файлов
		$this->attachedFiles("~<img[^>]+src=\"(.+)\"~iU", 'thumb_size');
		$this->attachedFiles("~<a[^>]+href=\"(.+)\"~iU", 'image_size');
		$this->attachedFiles("~/download\.php\?url=([^\&\']+)&~i", 'attach_size');
		$this->attachedFiles("~<embed[^>]+src=\"(.+)\"~iU", 'image_size');

		// Определяем какие картинки находятся в директории картинок текущего файла
		if (!is_dir($images_root)) {
			makedir($images_root, 0777, true);
		}
		$ftp_images = Filesystem::getDirContent($images_root, true, false, true);
		$all_images = array();
		
		/**
		 * Определяем ненужные файлы картинок
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
		 * Удаляем файлы картинок, которых нет в сохраненном файле
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
	 * Преобразовываем локальные url адреса в вид {$url_xxx}, где xxx- id раздела, на который указывает ссылка
	 * @param void
	 * @return void
	 */
	public function url2id() {
		global $DB;
		
		// Определяем адреса сайтов, которые есть в системе
		$query = "select uniq_name from site_structure where structure_id=0";
		$site = $DB->fetch_column($query);
		
		// Находим все локальные url адреса
		preg_match_all('~href="([a-zA-Z0-9\-\_\:\/\.]+)"~iU', $this->content, $matches);
		$cms_url = parse_url(CMS_URL, PHP_URL_HOST);
		$url = array();
		reset($matches[1]);
		while (list(,$row) = each($matches[1])) {
			$parsed = parse_url($row);
			if (!isset($parsed['host'])) {
				// Адрес внутри сайта
				$url[] = substr($cms_url.$row, 0, -1);
				
			} elseif (in_array($parsed['host'], $site)) {
				// Адрес на страницу в системе
				$url[] = substr($row, strlen($parsed['scheme'])+3, -1);
			}
		}
		
		// Определяем id всех найденных url адресов без учета регистра
		$query = "
			SELECT concat('href=\"{url:', id, '}') as id, replace(concat('href=\"http://', url, '/'), '".CMS_URL."', '/') as url2
			FROM site_structure
			WHERE url IN ('".implode("','", $url)."')
			ORDER BY length(url2) desc /* важное условие - сортировка не удалять! так как будет сначала заменять /About/, а потом не найдет /About/Test/ */
		";
		$url2id = $DB->fetch_column($query);
		
		$this->content = str_replace($url2id, array_keys($url2id), $this->content);
	}
	
	/**
	* Сохраняет контент в файле
	* @param string $file полный путь и имя файла с расширением
	* @return void
	*/
	public function save() {
		global $DB;
		
		$query = "select id from cms_field_static where table_name='$this->table_name' and full_name='$this->column_name'";
		$DB->query($query);
		if ($DB->rows == 0) {
			Action::setError(cms_message('CMS', 'Невозможно сохранить данные в таблице %s.%s.', $this->table_name, $this->column_name));
		}
		
		$query = "update `$this->table_name` set `".$this->column_name."`='".str_replace("'", "\'", $this->content)."' where id='$this->edit_id'";
		$DB->update($query);
		
		if (is_module('Search')) {
			Search::update($this->table_name, $this->edit_id);
		}
	}
	
	/**
	* Закачивает картинки с внешних серверов и с других страниц
	* сайта, возвращает флаг были ли закачаны хоть какие-то картинки
	* @param string $img_root
	* @return bool
	*/
	public function uploadImages() {
		
		if (!ini_get('allow_url_fopen')) {
			// Для работы скрипта установите флаг allow_url_fopen = true
			trigger_error(cms_message('CMS', 'Для работы скрипта установите флаг allow_url_fopen = true'), E_USER_ERROR);
		}
		
		$this->content = preg_replace_callback('/<img[^>]+src=["\']?([^>"\']+)["\']?[^>]+>/i', array($this, 'uploadImagesCallback'), $this->content);
	}
	
	/**
	* Обработчик regexp'a по поиску и закачке картинок
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
			// Игнорируем картинки с текущей страницы
			
			if (preg_match("/icq\.com/", $matches[1])) {
				return $matches[0];
			} elseif (
				preg_match("/^\//", $matches[1])
				|| preg_match("/^".addcslashes(CMS_URL, "/")."/", $matches[1])
			) {
				// Локальная ссылка начинается с / или с адреса этого сайта
				$upload_result = $this->uploadLocalImage($matches[1], $destination_file);
			} elseif (preg_match("/^http:\/\//", $matches[1])) {
				// Картинка, которая находится на внешнем сервере
				$upload_result = $this->uploadRemoteImage($matches[1], $destination_file);
			} else {
				/**
				* Остальные типы файлов - не пытаемся закачать так как если ввести ../../../config.ini, 
				* то в итоге получим закачанный в картинку файл с паролями
				*/
				$upload_result = false;
			}
			
			if ($upload_result === true) {
				// Картинка успешно закачана или успешно сделана ссылка
				// меняем поле src
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
	* Создает жесткие ссылки на локальные картинки
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
	* Закачивает картинки с внешних серверов
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
	 * Форматирует текст для того, что б он в дальнейшем мог быть обработан diff
	 * @param void
	 * @return void
	 */
	public function prepare4diff() {
		$this->content = preg_replace("/(<(?:p|br|ul|li|ol|div)[^>]*>)/i", "\n\\1", $this->content);
		$this->content = preg_replace("/(<\/(?:p|br|ul|li|ol|div)[^>]*>)/i", "\\1\n", $this->content);
	}
	
	/**
	* Возвращает ответ, который будет показан в редакторе
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