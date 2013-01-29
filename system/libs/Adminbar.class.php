<?php
/**
 * Панель администратора, которая выводится на сайте
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */


class Adminbar {
	
	/**
	 * id версии, которую необходимо отобразить на странице
	 *
	 * @var int
	 */
	private $cvs_id = 0;
	
	/**
	 * История изменения файла
	 *
	 * @var array
	 */
	private $cvs_versions = array();
	
	/**
	 * Шаблон с тулбариной
	 *
	 * @var object
	 */
	private $Template;
	
	/**
	 * Режим отображения административной панели
	 *
	 * @var string
	 */
	private $mode = 'visible';
	
	/**
	 * Список кнопок
	 *
	 * @var array
	 */
	public $buttons = array();
	
	
	/**
	 * Конструктор класса
	 *
	 */
	public function __construct($short = false) {
		$this->Template = new Template(SITE_ROOT.'templates/cms/site/adminbar', 'ru');
		$this->Template->setGlobal("short", $short);
		$this->mode   = globalEnum($_COOKIE['adminbar_mode'], array('visible', 'hidden'));
		$this->cvs_id = globalVar($_GET['cvs_version'], 0);
	}
	
	
	/**
	 * Добавляет кнопку в административную панель
	 *
	 * @param char $type enum(cms_edit, editor)
	 * @param string $table_name
	 * @param int $id
	 */
	public function addButton($type, $table_name, $id, $name, $img, $param = '') {
		$this->buttons[] = array('name' => $name, 'id' => $id, 'table_name' => $table_name, 'type' => $type, 'img' => $img, 'param' => $param);
	}
	
	public function addLink($url, $name, $img) {
		$this->buttons[] = array('name' => $name, 'url' => $url, 'type' => 'link', 'img' => $img);
	}
	
	/**
	 * Вывод истории изменения файла
	 *
	 * @param string $table_name
	 */
	public function cvs($table_name, $id) {
		global $DB;
		
		// проверка прав доступа
		if (!Auth::editContent($table_name, $id)) {
			return false;
		}
		
		$query = "
			SELECT
				tb_log.id,
				length(tb_log.content) as len,
				tb_user.login,
				DATE_FORMAT(tb_log.dtime, '".LANGUAGE_DATE_SQL." %H:%i:%s') AS dtime
			FROM cvs_log AS tb_log
			LEFT JOIN auth_user AS tb_user ON tb_user.id=tb_log.admin_id
			WHERE
				tb_log.table_name='$table_name'
				AND tb_log.field_name='content_".LANGUAGE_CURRENT."'
				AND tb_log.edit_id='$id'
			ORDER BY tb_log.dtime DESC
		";
		$data = $DB->query($query);
		$counter = 0;
		reset($data);
		while(list(,$row) = each($data)) {
			$counter++;
			$row['size'] = number_format($row['len'], 0, '.', ' ');
			$this->cvs_versions[$row['id']] = "№$row[id]: $row[login], $row[dtime] ($row[size] байт)";
		}
	}
	
	/**
	 * Загружает CVS версию сайта
	 *
	 * @return string
	 */
	public function loadCVS() {
		global $DB;
		
		if (empty($this->cvs_id) || !Auth::isAdmin()) {
			$this->cvs_id = 0;
			return '';
		}
		
		$query = "SELECT content, table_name, edit_id FROM cvs_log WHERE id='$this->cvs_id'";
		$info = $DB->query_row($query);
		if (empty($info)) {
			$this->cvs_id = 0;
			return '';
		}
		
		if(!Auth::editContent($info['table_name'], $info['edit_id'])) {
			$this->cvs_id = 0;
			return '';
		}
		
		return $info['content'];
	}
	
	
	/**
	 * Добавляет в HTML код код административной тулбарины
	 *
	 * @param string $content
	 * @return string
	 */
	public function display($content) {
		$this->Template->set('cvs_version', $this->cvs_id);
		$this->Template->set('cvs_versions', $this->cvs_versions);
		$this->Template->set('adminbar_mode', $this->mode);
		$this->Template->iterateArray('/button/', null, $this->buttons);
		$adminbar = $this->Template->display();
		
		preg_match("~</(?:body|html)>~ismU", $content, $matches, PREG_OFFSET_CAPTURE);
		$pos = (!empty($matches[0][1])) ? $matches[0][1] : 0;
		$content = substr($content, 0, $pos).$adminbar.substr($content, $pos);
		
		preg_match("~</head>~ismU", $content, $matches, PREG_OFFSET_CAPTURE);
		$pos = (!empty($matches[0][1]))? $matches[0][1] : 0;
		$content = substr($content, 0, $pos).'<link href="/css/cms/adminbar.css" rel="stylesheet" type="text/css" />'.substr($content, $pos);
		
		// вставляем adminbar в конце html и стили в head
		return $content;
	}
	
}


?>