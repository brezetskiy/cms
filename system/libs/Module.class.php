<?php
/**
 * ����� ������ � �������� �������
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class Module {
	
	/**
	 * id ������
	 *
	 * @var int
	 */
	public $id = 0;
	
	/**
	 * �������� ������ ���������� �������
	 *
	 * @var string
	 */
	public $name = '';
	
	/**
	 * �����, ������� ���� � �������
	 *
	 * @var array
	 */
	private $languages = array();
	
	/**
	 * ����������, ������� ���� � �������
	 *
	 * @var array
	 */
	private $interfaces = array();
	
	/**
	 * �������, ������� ����������� ������
	 *
	 * @var array
	 */
	public $tables = array();
	
	/**
	 * �������
	 *
	 * @var array
	 */
	public $templates = array();
	
	/**
	 * ������ ���������, ������� ��������� � ������
	 *
	 * @var array
	 */
	public $libraries = array();
	
	/**
	 * Include
	 *
	 * @var array
	 */
	public $includes = array();
	public $includes_dir = '';
	
	/**
	 * Tools
	 *
	 * @var array
	 */
	public $tools = array();
	public $tools_dir = '';
	
	/**
	 * Crontab
	 *
	 * @var array
	 */
	public $crontab = array();
	public $crontab_dir = '';
	
	/**
	 * ��������
	 *
	 * @var array
	 */
	public $img = array();
	public $img_dir = '';
	
	/**
	 * ������� ������
	 *
	 * @var array
	 */
	public $css = array();
	public $css_dir = '';
	
	/**
	 * JavaScript
	 *
	 * @var array
	 */
	public $js = array();
	public $js_dir = '';
	
	/**
	 * ����� � ������� ��������
	 *
	 * @var array
	 */
	public $relations = array();
	
	/**
	 * ������� ��������� �����
	 *
	 * @var array
	 */
	public $site_content = array();
	
	/**
	 * �������, ����������� � ��������� �����
	 *
	 * @var array
	 */
	public $site_template = array();
	
	/**
	 * ������� ����������� � �������
	 *
	 * @var array
	 */
	public $admin_content = array();
	
	/**
	 * �������, ����������� � �������
	 *
	 * @var array
	 */
	public $admin_template = array();
	
	/**
	 * �������
	 *
	 * @var array
	 */
	public $events = array();
	
	/**
	 * ������ ������, ������� ��������� � ������� �������
	 *
	 * @var array
	 */
	private $cms_files = array(
		'cache/.htaccess',
		'uploads/.htaccess',
		'content/.htaccess',
		'content/cms_table/edit.inc.php',
		'design/_default/',
		'extras/',
		'img/1x1.gif',
		'img/shared/',
		'js/shared/',
		'install/',
		'system/.htaccess',
		'system/config.inc.php',
		'system/fonts/',
		'system/pear/',
		'.htaccess',
		'actions_admin.php',
		'actions_site.php',
		'crossdomain.xml',
		'favicon.ico',
		'index_admin.php',
		'index_admin_edit.php',
		'index_admin_login.php',
		'index_site.php',
		'robots.txt',
		'sitemap.php',
	);
	
	/**
	 * ����������� ������
	 *
	 * @param mixed $name_or_id
	 */
	public function __construct($name_or_id) {
		global $DB;
		
		if (is_numeric($name_or_id)) {
			$query = "select lower(name) from cms_module where id='$name_or_id'";
			$this->name = $DB->result($query);
			$this->id = $name_or_id;
		} else {
			$query = "select id from cms_module where name='$name_or_id'";
			$this->id = $DB->result($query);
			$this->name = $name_or_id;
		}
		
		if (empty($this->name)) {
			// ���� � ���� $this->name  ������ ��������, �� ��� ����� �������� � �������� ����� �����
			trigger_error(cms_message('SDK', '�� ��������� ������� ������, ������� ��� ������'), E_USER_ERROR);
			exit;
		}
		
		// ���������� �� �����������
		$query = "SELECT id, LOWER(name) AS name FROM cms_interface";
		$this->interfaces = $DB->fetch_column($query, 'id', 'name');
		
		// ���������� ��� ���� ������ � �������
		$this->languages = preg_split("/,/", LANGUAGE_AVAILABLE);
		
		// �������
		$this->tables = $this->getTables();
		
		
		// �������
		if (is_dir(SITE_ROOT.'templates/'.$this->name.'/')) {
			$this->templates = Filesystem::getAllSubdirsContent(SITE_ROOT.'templates/'.$this->name.'/', true);
			$this->templates_dir = SITE_ROOT.'templates/'.$this->name.'/';
		}
		
		// Crontab
		if (is_dir(SITE_ROOT.'system/crontab/'.$this->name)) {
			$this->crontab = Filesystem::getAllSubdirsContent(SITE_ROOT.'system/crontab/'.$this->name, true);
			$this->crontab_dir = SITE_ROOT.'system/crontab/'.$this->name.'/';
		}
		
		// Include
		if (is_dir(INC_ROOT.$this->name.'/')) {
			$this->includes = Filesystem::getAllSubdirsContent(INC_ROOT.$this->name.'/', true);
			$this->includes_dir = INC_ROOT.$this->name.'/';
		}
		
		// Tools
		if (is_dir(SITE_ROOT.'tools/'.$this->name.'/')) {
			$this->tools = Filesystem::getAllSubdirsContent(SITE_ROOT.'tools/'.$this->name.'/', true);
			$this->tools_dir = SITE_ROOT.'tools/'.$this->name.'/';
		}

		// ������� ������
		if (is_dir(SITE_ROOT.'css/'.$this->name.'/')) {
			$this->css = Filesystem::getAllSubdirsContent(SITE_ROOT.'css/'.$this->name.'/', true);
			$this->css_dir = SITE_ROOT.'css/'.$this->name.'/';
		}
		
		// ��������
		if (is_dir(SITE_ROOT.'img/'.$this->name.'/')) {
			$this->img = Filesystem::getAllSubdirsContent(SITE_ROOT.'img/'.$this->name.'/', true);
			$this->img_dir = SITE_ROOT.'img/'.$this->name.'/';
		}
		
		// JavaScript
		if (is_dir(SITE_ROOT.'js/'.$this->name.'/')) {
			$this->js = Filesystem::getAllSubdirsContent(SITE_ROOT.'js/'.$this->name.'/', true);
			$this->js_dir = SITE_ROOT.'js/'.$this->name.'/';
		}
		
		// ����� � ������� ��������
		$query = "
			select 
				tb_table.name as table_name, 
				tb_field.name as field_name,
				fk_table.name as fk_table_name,
				ifnull(tb_field_module.name, tb_module.name) as fk_module_name
			from cms_table as tb_table
			inner join cms_field as tb_field on tb_field.table_id = tb_table.id
			inner join cms_table as fk_table on tb_field.fk_table_id = fk_table.id
			inner join cms_module as tb_module on fk_table.module_id = tb_module.id
			left join cms_module as tb_field_module on tb_field.module_id = tb_field_module.id
			where 
				tb_table.module_id = '$this->id'
				and fk_table.module_id != '$this->id'
		";
		$this->relations = $DB->query($query);
		
		// ������� � �������
		$this->loadSite();
		$this->loadAdmin();
		
		// ������� 
		$this->events = $this->getEvents();
		
		// ����������
		$this->libraries = $this->getLibraries();
	}
	
	
	/**
	 * ���������� � ��������, ������� ���� � ������
	 *
	 * @return array
	 */
	private function getTables() {
		global $DB;
		
		// $content_dirs = Filesystem::getDirContent(CONTENT_ROOT, false, true, false);
		$uploads_dirs = Filesystem::getDirContent(UPLOADS_ROOT, false, true, false);
		
		$return = $DB->query("
			SELECT 
				tb_table.id,
				tb_table.id as table_id,
				tb_table.name,
				tb_table.name as table_name,
				upper(tb_static.table_type) as table_type,
				tb_db.alias AS db_alias
			FROM cms_table AS tb_table
			INNER JOIN cms_table_static AS tb_static on tb_static.id=tb_table.id
			INNER JOIN cms_db AS tb_db ON tb_db.id=tb_table.db_id
			WHERE tb_table.module_id='$this->id'
		");
		
		// ������, ������� ��������� � ��������
		if (strtolower($this->name) == 'shop') {
			$shop = $DB->fetch_column("
				select table_name
				from information_schema.tables 
				where table_schema='$DB->db_name' and table_name like 'shop\\_x\\_%'
			");
			 
			reset($shop);
			while (list(,$table_name) = each($shop)) {
				$return[] = array('id' => 0, 'name' => $table_name, 'table_name' => $table_name, 'db_alias' => 'default', 'table_type' => 'BASE TABLE');
			}
		}
		
		reset($return);
		while (list($index, $row) = each($return)) {
			$row['db_name'] = db_config_constant("name", $row['db_alias']);
		
			// ��������
			$row['triggers'] = (is_dir(TRIGGERS_ROOT.$row['db_alias'].'/'.$row['name'])) ? Filesystem::getDirContent(TRIGGERS_ROOT.$row['db_alias'].'/'.$row['name'], true, false, true) : array();
			$row['triggers_dir'] = TRIGGERS_ROOT.$row['db_alias'].'/'.$row['name'];
			
			// �������
			$row['content'] = array();
//			reset($content_dirs);
//			while (list(,$dirname) = each($content_dirs)) {
//				if (substr($row['name'], 0, strlen($dirname) + 1) == substr($dirname, 0, -1).'.' || substr($dirname, 0, -1) == $row['name']) {
//					$row['content'][] = CONTENT_ROOT.$dirname;
//				}
//			}
			
			// Uploads
			$row['uploads'] = array();
			reset($uploads_dirs);
			while (list(,$dirname) = each($uploads_dirs)) {
				if (substr($dirname, 0, strlen($row['name']) + 1) == $row['name'].'.' || substr($dirname, 0, -1) == $row['name']) {
					$row['uploads'][] = UPLOADS_ROOT.$dirname;
				}
			}
			
			$return[$index] = $row;
		}
		
		return $return;
	}
	
	/**
	 * ������ �������
	 *
	 * @return array
	 */
	private function getEvents() {
		$return = array();
		reset($this->interfaces);
		while(list(,$interface) = each($this->interfaces)) {
			if (!is_dir(ACTIONS_ROOT.$interface.'/'.$this->name.'/')) {
				continue;
			}
			$files = Filesystem::getAllSubdirsContent(ACTIONS_ROOT.$interface.'/'.$this->name.'/', true);
			reset($files);
			while(list(,$file) = each($files)) {
				$return[] = $file;
			}
		}
		return $return;
	}
	
	/**
	 * �������� �����
	 * 
	 * @return array
	 */
	private function loadSite() {
		global $DB;
		$return = array();
		$query = "
			select
				tb_structure.url,
				lower(tb_structure.url) as url_lower,
				tb_structure.name_".LANGUAGE_SITE_DEFAULT." as name
			from site_structure as tb_structure
			inner join cms_module_site_structure as tb_relation ON tb_relation.structure_id=tb_structure.id
			where tb_relation.module_id='$this->id'
			order by url
		";
		$data = $DB->query($query);
		reset($data); 
		while (list($index, $row) = each($data)) { 
			reset($this->languages); 
			while (list(,$language_current) = each($this->languages)) {
				if (is_file(CONTENT_ROOT."site_structure/$row[url_lower].$language_current.php")) {
					$this->site_content[] = CONTENT_ROOT."site_structure/$row[url_lower].$language_current.php";
				}
				if (is_file(CONTENT_ROOT."site_structure/$row[url_lower].$language_current.tmpl")) {
					$this->site_template[] = CONTENT_ROOT."site_structure/$row[url_lower].$language_current.tmpl";
				}
			}
		}
		return $return;
	}
		
		
	/**
	 * �������� ���������������� ����������
	 *
	 * @return array
	 */
	private function loadAdmin() {
		global $DB;
		$return = array();
		$query = "
			select
				tb_structure.url,
				lower(tb_structure.url) as url_lower,
				tb_structure.name_".LANGUAGE_SITE_DEFAULT." as name
			from cms_structure as tb_structure
			where tb_structure.module_id='$this->id'
			order by url
		";
		$data = $DB->query($query);
		reset($data); 
		while (list(,$row) = each($data)) { 
			reset($this->languages); 
			while (list(,$language_current) = each($this->languages)) { 
				if (is_file(CONTENT_ROOT."cms_structure/$row[url_lower].$language_current.php")) {
					$this->admin_content[] = CONTENT_ROOT."cms_structure/$row[url_lower].$language_current.php";
				}
				if (is_file(CONTENT_ROOT."cms_structure/$row[url_lower].$language_current.tmpl")) {
					$this->admin_template[] = CONTENT_ROOT."cms_structure/$row[url_lower].$language_current.tmpl";
				}
			}
		}
		return $return;
	}
	
	/**
	 * ����� � ������� ��������
	 *
	 * @return array
	 */
	private function getDependencies() {
		global $DB;
		
		$query = "
			select 
				tb_module.name as dependency,
				tb_dependency.name as module
			from cms_module_dependency as tb_relation
			inner join cms_module as tb_module on tb_module.id=tb_relation.module_id
			inner join cms_module as tb_dependency on tb_dependency.id=tb_relation.dependency_id
			where 
				tb_dependency.id='$this->id'
				and tb_module.id!='$this->id'
		";
		return $DB->query($query);
	}
	
	/**
	 * �������� ������
	 *
	 * @return bool
	 */
	public function delete() {
		global $DB;
		
		// ��������� ����������� ��������� �������, ���� ���� ������, ������� ������� �� �������, �� �� ������� ���
		$dependencies = $this->getDependencies();
		if (!empty($dependencies)) {
			return false;
		}
		
		reset($this->tables); 
		while (list(,$row) = each($this->tables)) {
			cmsTable::delete($row['db_alias'], $row['table_name'], $row['table_type']);
		}
		
		// ������� �������, ������� ��������� � ������� � ������ �������
		$query = "
			select tb_table.name as table_name, tb_field.name as column_name, tb_db.alias as db_alias
			from cms_field as tb_field
			inner join cms_table as tb_table on tb_table.id=tb_field.table_id
			inner join cms_db as tb_db on tb_db.id=tb_table.db_id
			where tb_field.module_id='$this->id' and tb_field._is_real=1
		";
		$data = $DB->query($query);
		reset($data);
		while (list(,$row) = each($data)) {
			$query = "alter table `$row[table_name]` drop column `$row[column_name]`";
			$DBServer = DB::factory($row['db_alias']);
			$DBServer->delete($query);
		}
		
		// ������� �������� �������, ������� ��������� � ������� � ������ �������
		$query = "delete from cms_field where module_id='$this->id'";
		$DB->delete($query);
		
		// ������� �� �� ������� ��� ������
		$query = "delete from cms_event where module_id='$this->id'";
		$DB->delete($query);
	
		// ������� �� �� g��������� ��� ������
		$query = "delete from auth_action where module_id='$this->id'";
		$DB->delete($query);
		
		// ������� ������ �� ������� cms_structure
		$query = "delete from cms_structure where module_id='$this->id'";
		$DB->delete($query);
		
		// ������� ��������� ������� ��������� � �������
		$query = "delete from cms_settings where module_id='$this->id'";
		$DB->delete($query);
		
		// ������� ������ �� ������� site_structure
		$query = "delete from site_structure where id in (select structure_id from cms_module_site_structure where module_id='$this->id')";
		$DB->delete($query);
				
		// ������� ������������ ���������
		$query = "delete from cms_message where module_id='$this->id'";
		$DB->delete($query);
		
		// ������� ������ �� ������ �������
		$query = "delete from cms_module where id='$this->id'";
		$DB->delete($query);
		
		// ������� �����
		$files = $this->getAllFiles();
		reset($files);
		while (list(,$row) = each($files)) {
			Filesystem::delete($row);
		}
		
		return true;
	}
	
	
	/**
	 * ���������� ������ ������, ������� ����������� �������
	 */
	public static function getTemplateFiles($name) {
		return Filesystem::getAllSubdirsContent(SITE_ROOT.'design/'.strtolower($name).'/', true);
	}
	
	/**
	 * ��������� ������ ���� ������ � �����, ������� ����������� ������
	 *
	 * @return array
	 */
	public function getAllFiles() {
		$return = array_merge($this->libraries, $this->admin_content, $this->admin_template, $this->site_content, $this->site_template, array($this->crontab_dir), array($this->js_dir), array($this->css_dir), array($this->img_dir), $this->events, array($this->includes_dir), $this->templates, array($this->tools_dir));
		reset($this->tables);
		while (list(,$row) = each($this->tables)) {
			$return = array_merge($row['uploads'], $row['content'], $row['triggers'], $return);
		}
		
		reset($return);
		while (list($index,$row) = each($return)) {
			if (empty($row)) {
				unset($return[$index]);
			}
		}
		
		if (strtolower($this->name) == 'cms') {
			reset($this->cms_files); 
			while (list(,$row) = each($this->cms_files)) { 
				$return[] = SITE_ROOT.$row; 
			}
		}
		
		return $return;
	}
	
	/**
	 * ���������� ������ ���� ���������
	 *
	 * @return array
	 */
	private function getLibraries() {
		$return = array();
		$files = Filesystem::getAllSubdirsContent(LIBS_ROOT, true);
		reset($files);
		while (list(,$file) = each($files)) {
			$content = file_get_contents($file);
			if (!preg_match("~/\*\*.+\*/~ismU", $content, $matches)) {
				continue;
			}
			$content = $matches[0];
			if (!preg_match("/@subpackage\s+(\w+)/is", $content, $matches)) {
				continue;
			}
			if (!isset($matches[1]) || strtolower($matches[1]) != strtolower($this->name)) {
				continue;
			}
			$return[] = $file;
		}
		return $return;
	}
	
	/**
	 * ��������� ������ ��� ��������� �������
	 *
	 * @param int $project_id
	 */
	static public function distrib($project_id) {
		global $DB;

		$query = "select * from sdk_project where id='$project_id'";
		$project = $DB->query_row($query);
		
		$query = "
			select tb_structure.uniq_name
			from sdk_project_site as tb_relation
			inner join site_structure as tb_structure on tb_structure.id=tb_relation.site_id
			where tb_relation.project_id='$project_id'
		";
		$sites = $DB->fetch_column($query);
		
		$query = "
			select tb_module.name
			from sdk_project_module as tb_relation
			inner join cms_module as tb_module on tb_module.id=tb_relation.module_id
			where tb_relation.project_id='$project_id'
		";
		$modules = $DB->fetch_column($query);
		
		return "/usr/local/bin/php ".SITE_ROOT."system/crontab/sdk/distrib.php ".implode(",", $modules)." ".implode(",", $sites)." $project[db_host] $project[db_name] $project[db_login] $project[db_password]";
		
	}
	
}


?>