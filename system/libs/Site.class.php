<?php
/**
 * Обработка запросов и вывод структуры сайта
 * @package Pilot
 * @subpackage Site
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class Site {
	
	/**
	 * Раздел, в котором находится пользователь
	 *
	 * @var int
	 */
	public $structure_id = 0;
	
	/**
	 * Сайт, на котором находится пользователь
	 *
	 * @var int
	 */
	public $site_id = 0;
	
	/**
	 * Адрес сайта, на котором находится пользователь
	 *
	 * @var string
	 */
	public $site_url = '';
	
	/**
	 * Родительские разделы
	 *
	 * @var array
	 */
	public $parents = array();
	
	/**
	 * Путь к странице
	 *
	 * @var string
	 */
	public $url;
	
	/**
	 * Название таблицы, которая обрабатывается
	 *
	 * @var string
	 */
	public $table_name;
	
	/**
	 * Имя файла с контентом
	 *
	 * @var string
	 */
	public $filename = '';
	
	/**
	 * Название 404 шаблона
	 *
	 * @var string
	 */
	public $error_template_name = '';
	
	/**
	 * Шаблон по умолчанию
	 *
	 * @var string
	 */
	public $default_template_name = '';
	
	/**
	 * id группы авторизации
	 *
	 * @var int
	 */
	public $auth_group_id = 0;
	
	

	/**
	 * Конструктор класса
	 *
	 * @param string $url
	 * @param DB $DB
	 * @param string $table_name
	 */
	function __construct($url, $table_name) {
		global $DB;
		
		$this->table_name = $table_name;
	 
		if (substr($url, -3) == '.js' || substr($url, -4) == '.css') {
			trigger_error("Impossible to serve url which ends on .js and .css as html request. URL: $url", E_USER_ERROR);
			exit;
		}
	
		$cms_host_no_www = (strtolower(substr(CMS_HOST, 0, 4)) == 'www.') ? substr(CMS_HOST, 4): CMS_HOST;
		
		// Информация о сайте
		$query = "
			(
				select 
					-1 as priority,
					tb_site.url,
					tb_site.id,
					tb_site.auth_group_id,
					tb_site.force_https,
					concat(tb_error_template_group.name, '/', tb_error_template.name) as error_template_name,
					concat(tb_default_template_group.name, '/', tb_default_template.name) as default_template_name
				from site_structure_site as tb_site
				inner join site_structure_site_alias as tb_alias on tb_site.id = tb_alias.site_id
				inner join site_template as tb_error_template on tb_site.error_template_id = tb_error_template.id
				inner join site_template_group as tb_error_template_group on tb_error_template.group_id = tb_error_template_group.id
				inner join site_template as tb_default_template on tb_site.default_template_id = tb_default_template.id
				inner join site_template_group as tb_default_template_group on tb_default_template.group_id = tb_default_template_group.id
				where tb_alias.url in ('".CMS_HOST."', '$cms_host_no_www') and tb_site.active=1
			) UNION (
				select 
					tb_site.priority,
					tb_site.url,
					tb_site.id,
					tb_site.auth_group_id,
					tb_site.force_https,
					concat(tb_error_template_group.name, '/', tb_error_template.name) as error_template_name,
					concat(tb_default_template_group.name, '/', tb_default_template.name) as default_template_name
				from site_structure_site as tb_site
				inner join site_template as tb_error_template on tb_site.error_template_id = tb_error_template.id
				inner join site_template_group as tb_error_template_group on tb_error_template.group_id = tb_error_template_group.id
				inner join site_template as tb_default_template on tb_site.default_template_id = tb_default_template.id
				inner join site_template_group as tb_default_template_group on tb_default_template.group_id = tb_default_template_group.id
				where tb_site.active=1
			) order by priority asc limit 1
		";
		$info = $DB->query_row($query);
	
		if ($DB->rows > 0) {
			$this->auth_group_id = $info['auth_group_id'];
			$this->site_url = $info['url'];
			$this->site_id = $info['id'];
			$this->error_template_name = $info['error_template_name'];
			$this->default_template_name = $info['default_template_name'];
			
			/**
			 * Форсирование использования https, если указано в настройках системы
			 */
			if ($info['force_https'] && $_SERVER['REQUEST_METHOD'] == 'GET' && HTTP_SCHEME == 'http') {
				header('Location: https://'.CMS_HOST.$_SERVER['REQUEST_URI']);
				exit;
			}
			
		} else {
			$query = "
				select concat(tb_group.name, '/', tb_template.name)
				from site_template as tb_template
				inner join site_template_group as tb_group on tb_group.id=tb_template.group_id
				where tb_template.id='".CMS_DEFAULT_404_TEMPLATE."'
			";
			$this->error_template_name = $DB->result($query, 'default');
			// Тут не должно быть выхода. так как это не даст возможность запустить админ часть нового сайта, который не прописан в алиасах
			// return;
		}
		
		// удаляем язык
		$url = trim(strtolower($url), '/');
		if (substr($url, 0, 2) == LANGUAGE_CURRENT) {
			$url = substr($url, 3);
		}
		
		// Добавляем путь к сайту
		if ($this->table_name == 'site_structure') {
			$url = (empty($url)) ? $this->site_url : $this->site_url.'/'.$url;
		}
		
		$query = "
			SELECT id, url, LOWER(tb_structure.url) AS filename
			FROM `$this->table_name` AS tb_structure
			WHERE 
				tb_structure.url='".addcslashes($url, "'")."'
				AND tb_structure.active='true'
		";
		$info = $DB->query_row($query);
		if ($DB->rows == 0) { 
			
			// 301 Redirect: если страница была перемещена 
			$query = "
				SELECT
					tb_structure.id,
					tb_structure.url_old,
					tb_structure.url_new
				FROM site_structure_redirect AS tb_structure
				WHERE tb_structure.url_old='".addcslashes($url, "'")."'
			";
			$redirect = $DB->query_row($query);
			
			if($DB->rows > 0){
				header( "HTTP/1.1 301 Moved Permanently" );
				header( "Location: http://$redirect[url_new]/" );
				exit;
			}
			
			$this->log404(); 
			return;
		}
		
		$this->structure_id = $info['id'];
		$this->url = $info['url'];
		$this->filename = $info['filename'];
		
		$query = "
			SELECT parent, priority 
			FROM `{$this->table_name}_relation`
			WHERE id='$this->structure_id' 
			ORDER BY priority ASC
		";
		$this->parents = $DB->fetch_column($query, 'priority', 'parent');
	}

	/**
	 * Возвращает id сайта по имени хоста
	 * @param string $hostname
	 * @return int
	 */
	static function getSiteId($hostname) {
		global $DB;
		
		$query = "
			select tb_site.id
			from site_structure_site as tb_site
			inner join site_structure_site_alias as tb_alias on tb_site.id = tb_alias.site_id
			where tb_alias.url = '$hostname'
		";
		return $DB->result($query, 0);
	}
	
	/**
	 * Информация о текущей странице сайта
	 *
	 * @return array
	 */
	public function getInfo() {
		global $DB;
		
		if ($this->table_name != 'site_structure') {
			return array();
		}
		
		$info = $DB->query_row("
			SELECT
				tb_structure.*,
				unix_timestamp(tb_structure.last_modified) as last_modified,
				tb_structure.content_".LANGUAGE_CURRENT." as content,
				ifnull(concat(tb_design_group.name, '/', tb_design.name), '$this->default_template_name') AS template_design
			FROM site_structure AS tb_structure
			LEFT JOIN site_template AS tb_design ON tb_structure.template_id = tb_design.id
			LEFT JOIN site_template_group AS tb_design_group ON tb_design.group_id = tb_design_group.id
			WHERE tb_structure.id='$this->structure_id'
		");
		
		reset($info);
		while (list($key,$val) = each($info)) {
			$language = substr($key, -3);
			$no_language = substr($key, 0, -3);
			$default_language = $no_language."_".LANGUAGE_SITE_DEFAULT;
			
			if ($language == "_".LANGUAGE_CURRENT) {
				$info[$no_language] = (empty($val) && isset($info[$default_language])) ? $info[$default_language] : $val;
				unset($info[$key]);
			}
		}
		 
		if ($DB->rows == 0) {
			$info = array(
				'template_design' => $this->error_template_name, 
				'cache' => 'false', 
				'name' => '', 
				'headline' => '', 
				'title' => '', 
				'keywords' => '', 
				'description' => '', 
				'access_level' => 'any',
				'substitute_url' => '',
				'last_modified' => time(),
				'content' => '',
			);
			header("HTTP/1.0 404 Not Found"); 
			header("HTTP/1.1 404 Not Found"); 
			header("Status: 404 Not Found"); 
		}
		
		$info = array_merge($info, parse_headers($info['name'], $info['headline'], $info['title'], $info['description']));	
		
		// Определяем обработчик шаблона
		$info['template_parser'] = SITE_ROOT.'design/'.$info['template_design'].'.inc.php';
		
		if ($info['access_level'] != 'any') {
			$query = "SELECT group_id FROM site_group_relation WHERE structure_id = '$this->structure_id'";
			$info['access_groups'] = $DB->fetch_column($query);
		} else {
			$info['access_groups'] = array();
		}
		
		return $info;
	}
	
	/**
	 * Функция возвращает путое значение, если доступ к системе есть, в случае, если доступа нет, то выводится окно ввода логина
	 *
	 * @param unknown_type $access_level
	 * @param unknown_type $access_groups
	 * @return unknown
	 */
	public function checkAccess($access_level, $access_groups) {
		if ($access_level == 'any') {
			return '';
		}
		
		if (!Auth::isLoggedIn()) {
			
			// Страница предназначена только для зарегистрированных пользователей, а пользователь даже не зашёл в систему, выводим форму для входа
			return Auth::displayLoginForm();
		}
		
		$user = array_merge(array('group_id' => 0, 'confirmed' => false, 'checked' => false), $_SESSION['auth']);
		
		if (!empty($access_groups) && !in_array($user['group_id'], $access_groups)) {
			
			// Страница доступна только для пользователей из определенных групп, а текущий пользователь не принадлежит ни одной из этих групп
			$TmplContent = new Template(SITE_ROOT.'templates/user/error_badgroup');
			return $TmplContent->display();
			
		} elseif ($access_level == 'confirmed' && !$user['confirmed']) {
			
			// Страница предназначена только для пользователей, которые подтвердили свой e-mail
			$TmplContent = new Template(SITE_ROOT.'templates/user/error_confirmed');
			return $TmplContent->display();
			
		} elseif ($access_level == 'checked' && !$user['checked']) {
			
			// Страница предназначена только для пользователей, которых подтвердил администратор системы
			$TmplContent = new Template(SITE_ROOT.'templates/user/error_checked');
			return $TmplContent->display();
			
		}
		
		return '';
	}
	
	

	/**
	 * Возвращает перечень пунктов главного меню
	 *
	 * @return array
	 */
	public function getTopMenu() {
		global $DB;
		
		$query = "
			SELECT
				tb_structure.id,
				LOWER(tb_structure.uniq_name) as uniq_name,
				tb_structure.name_".LANGUAGE_CURRENT." AS name,
				CONCAT(tb_structure.url, '/') AS url
			FROM site_structure AS tb_structure
			WHERE 
				tb_structure.structure_id='{$this->site_id}'
				AND FIND_IN_SET('top_menu', tb_structure.show_menu) > 0
				AND tb_structure.active='true'
			ORDER BY priority ASC
		";
		$data = $DB->query($query);
		reset($data);
		while (list($index, $row) = each($data)) {
			$row['class'] = (in_array($row['id'], $this->parents)) ? 'selected' : 'node';
			$row['url'] = '/'.LANGUAGE_URL.substr($row['url'], strpos($row['url'], '/', 1) + 1);
			$data[$index] = $row;
		}
		return $data;
	}
	
	/**
	 * Возвращает путь к текущей странице
	 * 
	 * @param string $main_name - название главной страницы
	 * @param string $url - URL главной страницы
	 * @return array
	 */
	public function getPath($main_name = '', $url = '/') {
		global $DB;
		$return = array();
		if (!empty($main_name)) {
			$return[] = array('id' => 0, 'name' => cms_message('CMS', $main_name), 'url' => $url);
		}
		
		$query = "
			SELECT
				tb_structure.id,
				tb_structure.name_".LANGUAGE_CURRENT." AS name,
				CONCAT(tb_structure.url, '/') AS url
			FROM site_structure AS tb_structure
			INNER JOIN site_structure_relation AS tb_relation ON tb_relation.parent = tb_structure.id
			WHERE tb_relation.id = '$this->structure_id'
			ORDER BY tb_relation.priority ASC
			limit 1, 100 /* отсекаем 1-й элемент - имя сайта */
		";
		$data = $DB->query($query);
		reset($data);
		while (list($index, $row) = each($data)) {
			$row['url'] = '/'.LANGUAGE_URL.substr($row['url'], strpos($row['url'], '/', 1) + 1);
			$return[] = $row;
		}
		
		// В последнем пунке не должно быть url, чтоб ссылка на текущий раздел не выводилась
		$return[count($return) - 1]['url'] = '';
		return $return;
	}
	
	
	/**
	 * Определяем разделы, которые находятся в вертикальном меню
	 * 
	 * @return array
	 */
	public function getLeftMenu($structure_id = -1, $menu_type = 'left_menu') {
		global $DB;
		
		if ($structure_id < 0) $structure_id = $this->structure_id;
		
		$data = $DB->query("
			SELECT
				tb_structure.id,
				tb_structure.name_".LANGUAGE_CURRENT." AS name,
				CONCAT(tb_structure.url, '/') AS url,
				IF(tb_structure.id='$structure_id', 'selected', 'node') AS class
			FROM site_structure AS tb_structure
			WHERE tb_structure.structure_id = '$structure_id'
				AND FIND_IN_SET('$menu_type', tb_structure.show_menu) > 0
				AND tb_structure.active='true'
			ORDER BY priority ASC
		");
		
		// У текущего раздела нет подразделов, выводим разделы его родителя если id родителя != 0
		if ($DB->rows == 0 && count($this->parents) > 1) {
			$parent = (isset($this->parents[count($this->parents) - 1])) ? $this->parents[count($this->parents) - 1] : 0;
			$data = $DB->query("
				SELECT
					tb_structure.id,
					tb_structure.name_".LANGUAGE_CURRENT." AS name,
					CONCAT(tb_structure.url, '/') AS url,
					IF(tb_structure.id='$parent', 0, priority) AS priority,
					IF(tb_structure.id='$structure_id', 'selected', 'node') AS class
				FROM site_structure AS tb_structure
				WHERE tb_structure.structure_id='$parent'
					AND tb_structure.active='true'
					AND FIND_IN_SET('left_menu', tb_structure.show_menu) > 0
				ORDER BY priority ASC
			");
		}
		
		reset($data);
		while (list($index, $row) = each($data)) {
			$data[$index]['url'] = '/'.LANGUAGE_URL.substr($row['url'], strpos($row['url'], '/', 1) + 1);
		}
		
		return $data;
	}

	
	public function get404Info(){
		$info = array(
			'template_design' => $this->error_template_name, 
			'cache' => 'false', 
			'name' => '', 
			'headline' => '', 
			'title' => '', 
			'keywords' => '', 
			'description' => '', 
			'access_level' => 'any',
			'substitute_url' => '',
			'last_modified' => time(),
			'content' => '',
		);
		
		header("HTTP/1.0 404 Not Found"); 
		header("HTTP/1.1 404 Not Found"); 
		header("Status: 404 Not Found"); 
		
		$info = array_merge($info, parse_headers($info['name'], $info['headline'], $info['title'], $info['description']));	
		
		// Определяем обработчик шаблона
		$info['template_parser'] = SITE_ROOT.'design/'.$info['template_design'].'.inc.php';
		
		if ($info['access_level'] != 'any') {
			$info['access_groups'] = $DB->fetch_column("SELECT group_id FROM site_group_relation WHERE structure_id = '$this->structure_id'");
		} else {
			$info['access_groups'] = array();
		}
		
		return $info;
	}
	
	
	/**
	* Обработчик неудачных запросов страниц
	* @return void
	*/
	public function log404() {
		global $DB;
		
		if (!is_file(LOGS_ROOT.'404.log')) {
			touch(LOGS_ROOT.'404.log');
		}
		
		$_SERVER['HTTP_REFERER'] = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : "unknown";
		$_SERVER['HTTP_USER_AGENT'] = (!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : "unknown";
			
		if (is_writable(LOGS_ROOT.'404.log') && filesize(LOGS_ROOT.'404.log')/(1000 * 1000) < 100) {
			$fp = fopen(LOGS_ROOT . '404.log', 'a');
			
			fwrite($fp, "\n
				[BEGIN]".str_repeat('-', 50)."
				Date: ".date('Y-m-d H:i:s')."
				URL: ".(defined('CURRENT_URL_FORM') ? "http://".CMS_HOST.CURRENT_URL_FORM : 'shell')."
				IP: ".HTTP_IP." (".HTTP_LOCAL_IP.")
				Refferer: ".$_SERVER['HTTP_REFERER']." 
				UserAgent: ".$_SERVER['HTTP_USER_AGENT']."   
				[END]".str_repeat("-", 50)."\n");
			fclose($fp);
		}  
	}
	
	
	public function checkPageUrlCase(){
		return (strtolower($_SERVER['REQUEST_URI']) == $_SERVER['REQUEST_URI']) ? true : false;
	}
}

?>