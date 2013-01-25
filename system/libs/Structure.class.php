<?php
/**
 * Класс, который выполняет обработку изменений в структуре сайта и админ. интерфейса
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */

class Structure {
	/**
	 * Таблица, над которой ведётся работа
	 *
	 * @var string
	 */
	private $table_name = '';
	
	/**
	 * Путь к контентк
	 *
	 * @var string
	 */
	private $content_root = '';
	
	/**
	 * Путь к закачанным файлам
	 *
	 * @var string
	 */
	private $uploads_root = '';
	
	/**
	 * URL ссылок в контенте
	 *
	 * @var string
	 */
	private $link_url = '';
	
	/**
	 * Языкм
	 *
	 * @var array
	 */
	private $languages = array();
	
	
	/**
	 * Конструктор
	 *
	 * @param string $table_name
	 */
	public function __construct($table_name) {
		$this->table_name = $table_name;
		$this->content_root = CONTENT_ROOT.strtolower($this->table_name).'/';
		$this->uploads_root = UPLOADS_ROOT.strtolower($this->table_name).'/';
		$this->link_url = '/'.UPLOADS_DIR.strtolower($this->table_name).'/';
		$this->languages = preg_split('/,/', LANGUAGE_ADMIN_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
	}
	
	/**
	 * Получает URL страницы по её id
	 *
	 * @param int $id
	 * @return string
	 */
	private function getURL($id) {
		global $DB;
		return $DB->result("SELECT LOWER(url) FROM `".$this->table_name."` WHERE id='$id'");
	}
	
	/**
	 * Удаляет файлы, привязанные к структуре
	 *
	 * @param int $id
	 * @return bool
	 */
	public function delete($url) {
		$url = strtolower($url);
		reset($this->languages);
		while (list(, $language_current) = each($this->languages)) {
			Filesystem::delete($this->content_root."$url.$language_current.php");
			Filesystem::delete($this->content_root."$url.$language_current.tmpl");
			Filesystem::delete($this->content_root."$url/");
		}
	}
	
	/**
	 * Переносит раздел
	 *
	 * @param string $src_url
	 * @param string $dst_url
	 * @return bool
	 */
	public function move($src_url, $dst_url) {
		$src_url = strtolower($src_url);
		$dst_url = strtolower($dst_url);
		if ($src_url == $dst_url || empty($dst_url)) return true;
		
		Filesystem::rename($this->content_root.$src_url, $this->content_root.$dst_url, true);
		
		reset($this->languages);
		while (list(, $language_current) = each($this->languages)) {
			Filesystem::rename($this->content_root.$src_url.".$language_current.php", $this->content_root.$dst_url.".$language_current.php", true);
			Filesystem::rename($this->content_root.$src_url.".$language_current.tmpl", $this->content_root.$dst_url.".$language_current.tmpl", true);
		}
	}
	
	/**
	 * Устанавливает значение поля url=''
	 *
	 * @param int $id
	 */
	public function cleanURL($id) {
		global $DB;
		
		$start = 0;
		do {
			$data = $DB->fetch_column("SELECT id FROM `{$this->table_name}_relation` WHERE parent='$id' GROUP BY id ASC LIMIT $start, 200");
			$DB->update("UPDATE `$this->table_name` SET url='' WHERE id IN (0".implode(",", $data).")");
			$start += 200;
		} while (!empty($data));
	}
	
	/**
	 * Определяет URL для полей, которые не заполнены
	 *
	 */
	public function updateURL() {
		global $DB;
		
		$query = "
			SELECT
				tb_relation.id,
				GROUP_CONCAT(tb_structure.uniq_name ORDER BY tb_relation.priority SEPARATOR '/') AS url
			FROM `{$this->table_name}_relation` AS tb_relation
			INNER JOIN `$this->table_name` AS tb_structure ON tb_structure.id=tb_relation.parent
			INNER JOIN `$this->table_name` as tb_empty on tb_empty.id=tb_relation.id
			WHERE tb_empty.url=''
			GROUP BY tb_relation.id
		";
		$data = $DB->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$DB->update("UPDATE `$this->table_name` SET url='$row[url]' WHERE id='$row[id]'");
		}
	}
	
	/**
	 * Добавление сайта
	 *
	 * @param int $id
	 * @param string $url
	 * @param int $template_id
	 */
	public static function createSite($structure_id, $url, $template_id = 0) {
		global $DB;
		
		$priority = $DB->result("select priority from site_structure where id = '$structure_id'", 0);
		$DB->insert("insert into site_structure_site set id='$structure_id', error_template_id='$template_id', default_template_id='$template_id', url='$url', priority='$priority'");
		if ($template_id > 0) {
			$DB->delete("delete from site_structure_site_template where site_id = '$structure_id'");
			$DB->insert("insert into site_structure_site_template set site_id = '$structure_id', template_id = '$template_id'");
		}
		self::rebuildSiteAliases();
	}
	
	/**
	 * Удаляет сайт
	 * @param int $id
	 */
	public static function deleteSite($id) {
		global $DB;
		
		$DB->delete("delete from site_structure_site where id = '$id'");
		$DB->delete("delete from site_structure_site_template where site_id = '$id'");
		$DB->delete("delete from site_structure_site_alias where site_id = '$id'");
	}
	
	/**
	 * Обновляет сайт
	 *
	 * @param int $id
	 * @param string $url
	 */
	public static function updateSite($id, $url) {
		global $DB;
		
		$priority = $DB->result("select priority from site_structure where id = '$id'", 0);
		$DB->insert("update site_structure_site set url = '$url', priority = '$priority' where id = '$id'");
		self::rebuildSiteAliases();
	}
	
	/**
	 * Обновляет список алиасов сайта и перестраивает
	 * таблицу кросс-доменной авторизации
	 */
	public static function rebuildSiteAliases() {
		global $DB;

		/**
		 * Обновляем URLы сайтов
		 */
		$DB->update("update site_structure_site set url=(select uniq_name from site_structure where id = site_structure_site.id)");
		
		/**
		 * Определяем алиасы, по которым доступны сайты системы
		 */
		$sites = $DB->query("select * from site_structure_site", 'id');
	
		/**
		 * Строим полный список алиасов в виде host => (site_id, auth_group_id)
		 */
		$alias_site = array();
		$alias_auth_group = array();
		reset($sites); 
		while (list(,$row) = each($sites)) { 
			
			$alias_site[$row['url']] = $row['id'];
			$alias_auth_group[$row['url']] = $row['auth_group_id'];
			
			$site_aliases = preg_split("~[\r\n\s\t,]+~", $row['aliases'], -1, PREG_SPLIT_NO_EMPTY);
			reset($site_aliases); 
			while (list(,$alias) = each($site_aliases)) { 
				$alias_site[$alias] = $row['id'];
				$alias_auth_group[$alias] = $row['auth_group_id'];
			}
		}
		
		/**
		 * Определяем, для каких алиасов нужно делать spread авторизации
		 * (не нужно делать для поддоменов)
		 * Для поддоменов авторизация включается, если для основного домена она отключена
		 */
		$spread_auth = $alias_site;

		foreach ($alias_site as $alias_host => $site_id) {
			foreach ($alias_site as $alias_host2 => $site_id2) {
				if ($alias_auth_group[$alias_host] == $alias_auth_group[$alias_host2] && $alias_auth_group[$alias_host] > 0 && preg_match('~\.'.preg_quote($alias_host2).'$~', $alias_host)) {
					$spread_auth[$alias_host] = 0;
					break;
				}
			}
		}
				
		/**
		 * Заменяем старые алиасы на новые
		 */
		$insert_values = array();
		reset($alias_site); 
		while (list($alias,$site_id) = each($alias_site)) { 
			$insert_values[] = "('$site_id', '".$DB->escape($alias)."', '".($spread_auth[$alias] == 0 ? 0 : 1)."', '{$alias_auth_group[$alias]}')";
		}
		
		$DB->query("lock table site_structure_site_alias write");
		$DB->delete("delete from site_structure_site_alias");
		$DB->insert("insert into site_structure_site_alias(site_id, url, spread_auth, auth_group_id) values".implode(',', $insert_values));
		$DB->query("unlock tables");
	}
	
	/**
	 * Создает файл в структуре админки
	 *
	 * @param int $id
	 */
	public function touch($id) {
	    global $DB;
	
	    $url = $DB->result("select url from cms_structure where id='$id'");
	    $file = CONTENT_ROOT.$this->table_name.'/'.strtolower($url).'.'.LANGUAGE_CURRENT.'.php';
	    if (!is_dir(dirname($file))) mkdir(dirname($file), 0777, true);
	    touch($file);
	}

}


?>