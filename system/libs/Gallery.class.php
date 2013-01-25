<?php
/**
 * Вывод фотогалереи
 * @package Pilot
 * @subpackage Gallery
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class Gallery {
	
	/**
	 * Таблица, которая содержит информацию о группах
	 *
	 * @var string
	 */
	private $group_table = '';
	
	/**
	 * id текущей группы
	 *
	 * @var int
	 */
	public $group_id = 0;
	
	/**
	 * Количество фотографий в группе
	 *
	 * @var int
	 */
	public $total = 0;
	
	/**
	 * Информация о таблице, в которой содержится описание групп
	 *
	 * @var array
	 */
	private $table_info = array();
	
	/**
	 * Конструктор класса
	 *
	 * @param string $group_table
	 * @param string $url
	 * @param int $group_id
	 */
	public function __construct($group_table, $group_id) {
		global $DB;
		
		$this->group_table = $group_table;
		$this->group_id = $group_id;
		
		// Определяем название родительского поля
		$this->table_info = cmsTable::getInfoByAlias($DB->db_alias, $group_table);
	}
	
	
	/**
	 * Информация о текущем разделе галереи и путь к нему
	 * 
	 * @return array
	 */
	public function getPath() {
		global $DB;
		$query = "
			SELECT 
				tb_group.name_".LANGUAGE_CURRENT." AS name,
				tb_group.url
			FROM {$this->group_table}_relation  AS tb_relation
			INNER JOIN {$this->group_table} AS tb_group ON tb_relation.parent = tb_group.id
			WHERE tb_relation.id = '$this->group_id'
			ORDER BY tb_relation.priority
		";
		return $DB->query($query);
	}
	
	
	public function getGroupInfo($group_id = 0) {
		global $DB;
		
		$group_id = (empty($group_id)) ? $this->group_id : $group_id;
		$query = "
			select
				id,
				group_id,
				uniq_name,
				name_".LANGUAGE_CURRENT." as name,
				title_".LANGUAGE_CURRENT." as title,
				headline_".LANGUAGE_CURRENT." as headline,
				keywords_".LANGUAGE_CURRENT." as keywords,
				description_".LANGUAGE_CURRENT." as description
			from `$this->group_table`
			where id='$group_id'
		";
		$info = $DB->query_row($query);
		if (is_null($info['title'])) $info['title'] = $info['name'];
		if (is_null($info['headline'])) $info['headline'] = $info['title'];
		if (is_null($info['description'])) $info['description'] = $info['headline'];
		if (is_null($info['keywords'])) $info['keywords'] = $info['description'];
		return $info;
	}
	
	/**
	 * Список групп
	 * 
	 * @param int $parent_id
	 * @return array
	 */
	public function getGroups($parent_id = -1) {
		global $DB;
		
		$parent_id = ($parent_id == -1) ? $this->group_id : $parent_id;
		
		$query = "
			SELECT 
				id,
				name_".LANGUAGE_CURRENT." AS name,
				photo,
				date_format(date, '".LANGUAGE_DATE_SQL."') as `date`,
				url
			FROM `$this->group_table`
			WHERE `{$this->table_info['parent_field_name']}`='$parent_id'
			ORDER BY priority ASC
		";
		$groups = $DB->query($query);
		reset($groups);
		while (list($index, $row)=each($groups)) {
			$group_image = Uploads::getFile($this->group_table, 'photo', $row['id'], $row['photo']);
			$row['photo'] = (file_exists($group_image) && is_readable($group_image)) ? Uploads::htmlImage($group_image) : '';
			$row['class'] = ($index % 2 == 0) ? 'odd': 'even';
			$row['class'] .= ($this->group_id == $row['id']) ? ' selected ': ' node ';
			$row['index'] = $index;
			$groups[$index] = $row;
		}
		return $groups;
	}
	
	
	/**
	 * Фотографии
	 * 
	 * @param int $per_page - количество на страницу
	 * @param int $offset - с какой фотографии по порядку начинать вывод
	 */
	public function getPhotos($per_page, $offset) {
		global $DB;
		$return = array();
		$query = "
			SELECT SQL_CALC_FOUND_ROWS *, description_".LANGUAGE_CURRENT." AS comment
			FROM gallery_photo
			WHERE `{$this->table_info['parent_field_name']}`='$this->group_id' 
			ORDER BY priority ASC
			".Misc::limit_mysql($per_page, 'gallery', $offset)."
		";
		$photos = $DB->query($query);
		$this->total = $DB->result("SELECT FOUND_ROWS()");
		reset($photos);
		while (list($index, $row) = each($photos)) {
			$extension_length = strlen($row['photo']) + 1;
			$row['photo'] = Uploads::getFile('gallery_photo', 'photo', $row['id'], $row['photo']);
			if (!file_exists($row['photo'])) continue;
			$row['photo'] = Uploads::getImageURL($row['photo']);
			$row['index'] = $index;
			$return[] = $row;
		}
		//x($return,true);
		return $return;
	}
	
	/**
	 * Удаление группы фотографий
	 *
	 * @param int $group_id
	 * @return int
	 */
	public function deleteGroup($group_id = -1) {
		global $DB;
		
		$group_id = ($group_id == -1) ? $this->group_id : $group_id;
		
		$query = "select * from gallery_photo where group_id='$group_id' and group_table_name='{$this->table_info['table_name']}'";
		$photos = $DB->query($query);
		reset($photos);
		while (list(,$row) = each($photos)) {
			$this->deletePhoto($row['id']);
		}
		
		$DB->delete("delete from {$this->group_table} where id='$group_id'");
		return $DB->affected_rows;
	}
	
	/**
	 * Удаление фотографии
	 *
	 * @param int $photo_id
	 * @return int
	 */
	public function deletePhoto($photo_id) {
		global $DB;
		
		$photo = $DB->query_row("select * from gallery_photo where id='$photo_id'");
		if ($DB->rows == 0) {
			return 0;
		}
		
		$image = Uploads::getFile('gallery_photo', 'photo', $photo_id, $photo['photo']);
		if (is_file($image)) unlink($image);
		
		$DB->delete("delete from gallery_photo where id='$photo_id'");
		return $DB->affected_rows;
	}
	
	/**
	 * Получаем id групи 
	 * @param string $uniq_name
	 * @return int $id
	 */
	public static function getGroupId($uniq_name) {
		global $DB;
		return $DB->result("
			select id
			from gallery_group
			where 1 ".where_clause('uniq_name', $uniq_name)."
		");
	}
	
	/**
	 * формирем id групи по пути 
	 * которий формируется
	 * @param string $path
	 * @return int
	 */
	public static function getGroupIdByPath($path) {
		global $DB;
		if($path{strlen($path) - 1} == '/') $path = substr($path, 0, strlen($path) -1);
		if($path{0} == '/') $path = substr($path, 1, strlen($path));
		return $DB->result("select id from gallery_group where 1 ".where_clause('url', $path)." limit 1");
	}
	
	
	
}

?>