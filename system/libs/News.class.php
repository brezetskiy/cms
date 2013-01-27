<?php
/**
 * Класс выводящий новости
 * @package Pilot
 * @subpackage News
 * @author Rudenko Ilya <rudenko@delta-x.ua>, Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class News {
	
	/**
	 * Количество запмсей
	 * @var int
	 */
	public $total = 0;
	
	
	/**
	 * Информация о рубрике
	 *
	 * @param string $uniq_name
	 * @return array
	 */
	public function getType($uniq_name) {
		global $DB;
		
		$query = "
			select 
				tb_type.id,
				tb_type.site_id,
				tb_type.uniq_name,
				tb_type.image,
				tb_type.name_".LANGUAGE_CURRENT." as name,
				count(tb_message.id) as messages_count,
				tb_type.title_".LANGUAGE_CURRENT." as title,
				tb_type.description_".LANGUAGE_CURRENT." as description,
				tb_type.keywords_".LANGUAGE_CURRENT." as keywords,
				tb_type.content_".LANGUAGE_CURRENT." as content
			from news_type as tb_type 
			inner join news_message as tb_message on tb_message.type_id=tb_type.id
			where tb_type.uniq_name='$uniq_name'
			limit 1
		";
		$info = $DB->query_row($query);
		return $this->parseType($info);
	}
	
	/**
	 * Возвращает полный перечень рубрик нововстей
	 * 
	 * @param array $site_id
	 * @param string $parent_type
	 * @return array
	 */
	public function getAllTypes($site_id, $parent_type = '') {
		global $DB;
		
		if (!empty($parent_type)) {
			$query =  "select id from news_type where uniq_name='$parent_type'";
			$parent_type_id = $DB->result($query);
		} else {
			$parent_type_id = 0;
		}
		
		$query = "
			select 
				tb_type.id,
				tb_type.uniq_name,
				tb_type.image,
				tb_type.name_".LANGUAGE_CURRENT." as name,
				count(tb_message.id) as messages_count,
				tb_type.title_".LANGUAGE_CURRENT." as title,
				tb_type.description_".LANGUAGE_CURRENT." as description,
				tb_type.keywords_".LANGUAGE_CURRENT." as keywords,
				tb_type.content_".LANGUAGE_CURRENT." as content
			from news_type as tb_type 
			inner join news_message as tb_message on tb_message.type_id=tb_type.id
			where 1 
				".where_clause("tb_type.site_id", $site_id)."
				".where_clause("tb_type.type_id", $parent_type_id)."
			group by tb_type.id
			order by tb_type.priority asc
		";
		$data = $DB->query($query, 'uniq_name');
		reset($data);
		while (list($index, $row) = each($data)) {
			$data[$index] = $this->parseType($row);
		}
		return $data;
	}
	
	/**
	 * Обрабатывает вывод рубрик
	 *
	 * @param array $row
	 * @return array
	 */
	private function parseType($row) {
		if (empty($row)) return array();
		
		// Определяем наличие пиктограммы для типа новости
		$file = Uploads::getFile('news_type', 'image', $row['id'], $row['image']);
		$row['image'] = (is_file($file)) ? substr($file, strlen(SITE_ROOT) - 1) : '';
		
		$headers = parse_headers($row['name'], $row['name'], $row['title'], $row['description']);
		return array_merge($row, $headers);
	}
	
	/**
	 * Получение заголовков новостей
	 *
	 * @param int $limit - количество новостей на страницу
	 * @param mixed $type - уникальное имя рубрики
	 * @param bool $recursive - показывать вложенные новости
	 * @param int $tstamp - при наличии этого параметра выводятся новости за указаную дату
	 * @return array
	 */
	public function getHeadlines($limit, $type, $recursive = false, $tstamp = 0, $order = "date DESC") {
		global $DB;
		$type_id = 0;
		$table_id = $DB->result("SELECT id from cms_table where name = 'news_message'");
		if ($recursive) {
			$query = "
				select tb_relation.id
				from news_type as tb_type
				inner join news_type_relation as tb_relation on tb_relation.parent=tb_type.id
				where 1 ".where_clause("tb_type.uniq_name", $type);
			$type_id = $DB->fetch_column($query);
			$type = ''; 
		}
		
		if (is_module('Comments')) {
			$comment_field = "if (tb_comment.id IS null, 0, count(tb_message.id)) as count_comments,";
			$comment_join = "left join comment tb_comment on tb_comment.object_id = tb_message.id and tb_comment.table_id = $table_id";
		} else {
			$comment_field = "0 as count_comments,";
			$comment_join = "";
		}
		
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				
				tb_message.id, 
				
				tb_type.uniq_name as type,
				tb_message.headline_".LANGUAGE_CURRENT." AS headline, 
				tb_message.announcement_".LANGUAGE_CURRENT." AS announcement,
				tb_message.content_".LANGUAGE_CURRENT." AS content,
				$comment_field
				SUBSTRING(
					tb_message.content_".LANGUAGE_CURRENT.", 1, 
					IF( 
						LOCATE('<HR>', tb_message.content_".LANGUAGE_CURRENT.")>0, 
						LOCATE ('<HR>', tb_message.content_".LANGUAGE_CURRENT.")-1, 
						LENGTH(tb_message.content_".LANGUAGE_CURRENT.")
					) 
				) AS subcontent,
				
				tb_message.keywords_".LANGUAGE_CURRENT." as keywords,
				tb_message.description_".LANGUAGE_CURRENT." as description,
				tb_message.path,
				tb_message.url,
				
				tb_message.dtime AS system_public_dtime,
				date_format(tb_message.date_to, '%e') AS day_to,
				date_format(tb_message.date_to, '%m') AS month_to,
				YEAR(tb_message.date_to) AS year_to,
				
				date_format(tb_message.date, '%e') AS day_from,
				date_format(tb_message.date, '%m') AS month_from,
				YEAR(tb_message.date) AS year_from,
				
				CASE MONTH(tb_message.date) ".LANGUAGE_MONTH_GEN_SQL." END AS month_text_from,
				CASE MONTH(tb_message.date_to) ".LANGUAGE_MONTH_GEN_SQL." END AS month_text_to,
				
				UNIX_TIMESTAMP(tb_message.date) as tstamp_from,
				UNIX_TIMESTAMP(tb_message.date_to) as tstamp_to,
				
				tb_message.image,
				tb_message.priority as priority,
				tb_type.id as type_id,
				tb_type.image as type_image,
				tb_type.name_".LANGUAGE_CURRENT." as category
			from news_message as tb_message 
			inner join news_type as tb_type on tb_message.type_id=tb_type.id
			$comment_join
			where 
				tb_message.active='1'
				".where_clause('tb_type.uniq_name', $type)."
				".where_clause('tb_type.id', $type_id)."
				".where_clause('tb_message.date', $tstamp, 'FROM_UNIXTIME')."
			group by tb_message.id
			order by $order
			".Misc::limit_mysql($limit)."
		";
		$data = $DB->query($query);
		
		$query = "select found_rows()";
		$this->total = $DB->result($query);
		
		return $this->parseHeaders($data);
	}
	

	/**
	 * Получение заголовков новостей по каждой из указанных категорий
	 *
	 * @param int $limit
	 * @param mixed $type
	 * @return array
	 */
	public function getTopHeadlines($limit, $type, $recursive = false) {
		global $DB;
		

		if ($recursive) {
			$query = "
				select tb_relation.id
				from news_type as tb_type
				inner join news_type_relation as tb_relation on tb_relation.parent=tb_type.id
				where 1 ".where_clause("tb_type.uniq_name", $type);
			$type_id = $DB->fetch_column($query);
			$type = '';
		}
		
		// id лент новостей
		$query = "
			create temporary table tmp_id
			select tb_type.id as type_id, max(tb_message.date) as date
			from news_type as tb_type
			inner join news_message as tb_message on tb_message.type_id=tb_type.id
			where 1 
				".where_clause("tb_type.uniq_name", $type)."
				".where_clause("tb_type.id", $type_id)."
			group by tb_type.id
		";
		$DB->query($query);
		
		$query = "
			SELECT SQL_CALC_FOUND_ROWS
				tb_message.id, 
				tb_type.uniq_name as type,
				tb_message.headline_".LANGUAGE_CURRENT." AS headline, 
				tb_message.announcement_".LANGUAGE_CURRENT." AS announcement,
				tb_message.content_".LANGUAGE_CURRENT." AS content,
				tb_message.url,
				
				date_format(tb_message.date_to, '%e') AS day_to,
				date_format(tb_message.date_to, '%m') AS month_to,
				YEAR(tb_message.date_to) AS year_to,
				
				date_format(tb_message.date, '%e') AS day_from,
				date_format(tb_message.date, '%m') AS month_from,
				YEAR(tb_message.date) AS year_from,
				
				CASE MONTH(tb_message.date) ".LANGUAGE_MONTH_GEN_SQL." END AS month_text_from,
				CASE MONTH(tb_message.date_to) ".LANGUAGE_MONTH_GEN_SQL." END AS month_text_to,
				
				UNIX_TIMESTAMP(tb_message.date) as tstamp_from,
				UNIX_TIMESTAMP(tb_message.date_to) as tstamp_to,
				
				tb_message.image,
				tb_type.id as type_id,
				tb_type.image as type_image
			from news_message as tb_message
			inner join news_type as tb_type on tb_message.type_id=tb_type.id
			inner join tmp_id as tb_id on tb_id.type_id=tb_type.id and tb_id.date=tb_message.date
			where 
				tb_message.active='1'
				".where_clause('tb_type.uniq_name', $type)."
				".where_clause('tb_type.id', $type_id)."
			group by tb_type.id
			order by tb_message.date desc
			".Misc::limit_mysql($limit)."
		";
		$data = $DB->query($query);
		return $this->parseHeaders($data);

	}
	
	/**
	 * Обработка заголовков
	 *
	 * @param array $data
	 * @return array
	 */
	private function parseHeaders($data) {
		
		$prev_year = 0;
		reset($data);
		while(list($index, $row) = each($data)) {
			// Название месяца указывается полностью
			$row['month_from'] = $row['month_text_from'];
			$row['month_to'] = $row['month_text_to'];
			$data[$index]['odd'] = ($index % 2) ? 'odd': 'even';
			
			// Определяем формат вывода даты
			if (!empty($row['month_to']) && $row['month_to'] != $row['month_from']) {
				$data[$index]['date'] = "$row[day_from] $row[month_from] - $row[day_to] $row[month_to]";
			} elseif (!empty($row['month_to']) && $row['month_to'] == $row['month_from'] && $row['day_from'] != $row['day_to']) {
				$data[$index]['date'] = "$row[day_from] - $row[day_to] $row[month_from]";
			} else {
				$data[$index]['date'] = "$row[day_from] $row[month_from]";
			}
			
			// Определяем путь к картинке
			$image_file = Uploads::getFile('news_message', 'image', $row['id'], $row['image']);
			if (file_exists($image_file) && is_readable($image_file)) {
				$data[$index]['image_src'] = Uploads::getURL($image_file);
			} else {
				$data[$index]['image_src'] = '';
			}
			
			// Определяем путь к картинке
			$image_file = Uploads::getFile('news_message', 'image', $row['id'], $row['image']);
			if (file_exists($image_file) && is_readable($image_file)) {
				$data[$index]['image_src'] = Uploads::getURL($image_file);
			} else {
				$data[$index]['image_src'] = '';
			}
			
			// Определяем наличие контента новости
			$data[$index]['has_content'] = strlen($row['content']) > 30;
			
			// Разделение новостей по годам
			if ($prev_year != $row['year_from']) {
				$data[$index]['subtitle_year'] = $row['year_from'];
				$prev_year = $row['year_from'];
			} else {
				$data[$index]['subtitle_year'] = '';
			}
			
			// Определяем наличие пиктограммы для типа новости
			$file = Uploads::getFile('news_type', 'image', $row['type_id'], $row['type_image']);
			$data[$index]['type_image'] = (is_file($file)) ? substr($file, strlen(SITE_ROOT) - 1) : '';
			
			// Автоматически формируется анонс
			$announcement = wordwrap(strip_tags($row['content']), 200, '<br>');
			$data[$index]['auto_announcement'] = substr($announcement, 0, strpos($announcement, '<br>'));
		}
		return $data;
	}
	
	/**
	 * Получаем новость. Новость должна быть опубликована в группе,
	 * принадлежащей текущему сайту
	 *
	 * @param int $id
	 */
	public function getMessage($id) {
		global $DB;
		
		if(!is_numeric($id)) {
			$name = $id;
			$id = 0; 
		} else{
			$name = '';
		}
		
		// Пустое значение передаётся на всех страницах вывода информации, поэтому делаем проверку без SQL запроса
		// сделано это для того, что б не делать двойную проверку на наличие текста новости и наличия empty(id)
		if (empty($id) && empty($name)) return array();
		
		$query = "
			SELECT 
				tb_message.id, 
				tb_message.type_id, 
				tb_message.change_frequency, 
				tb_message.page_priority, 
				tb_message.headline_".LANGUAGE_CURRENT." as headline, 
				tb_message.headline_".LANGUAGE_CURRENT." as title,
				tb_message.keywords_".LANGUAGE_CURRENT." as keywords,
				tb_message.content_".LANGUAGE_CURRENT." as content,
				tb_message.description_".LANGUAGE_CURRENT." as description,
				tb_message.url,
				DATE_FORMAT(tb_message.date, '".LANGUAGE_DATE_SQL."') AS date,
				tb_message.dtime AS dbformat_date,
				
				CASE MONTH(tb_message.date) ".LANGUAGE_MONTH_GEN_SQL." END AS month_from,
				date_format(tb_message.date, '%e') AS day_from,
				
				tb_message.`image`,
				tb_type.uniq_name as type_uniq_name,
				tb_type.name_".LANGUAGE_CURRENT." as type_name,
				tb_type.archive_name_".LANGUAGE_CURRENT." as archive_name,
				unix_timestamp(tb_message.dtime) as mtime
			FROM news_message as tb_message
			inner join news_type as tb_type on tb_type.id=tb_message.type_id
			WHERE 1
				".where_clause('tb_message.id', $id)."
				".where_clause('tb_message.path', $name)."
			group by tb_message.id
		";
		$data = $DB->query_row($query);
		
		if ($DB->rows == 0) {
			return array();
		}
		
		$query = "select found_rows()";
		$this->total = $DB->result($query);
		
		// Текст новости
		$data['mtime'] = date('Y-m-d H:i:s', $data['mtime']);
		$data['content'] = id2url($data['content']);
		
		// Картинка, которая идёт к новости
		$image_file = Uploads::getFile('news_message', 'image', $id, $data['image']);
		$data['image_src'] = (file_exists($image_file) && is_readable($image_file)) ? Uploads::getURL($image_file) : '';
		
		$data['type_link'] = '<a href="./?type='.$data['type_uniq_name'].'">'.$data['type_name'].'</a>';
		$data['type_list'] = $data['type_uniq_name'];

//		reset($data['type_uniq_name']);
//		while (list($index,) = each($data['type_uniq_name'])) {
//			$data['type_link'][] = '<a href="./?type='.$data['type_uniq_name'][$index].'">'.$data['type_name'][$index].'</a>';
//			$data['type_list'][] = $data['type_uniq_name'][$index];
//		}
		 
		$headers = parse_headers($data['headline'], $data['headline'], $data['title'], $data['description']);
		return array_merge($data, $headers);
	}
	
	/**
	 * Получаем предыдущую или последующую от текущей новость в зависимости от значения параметр $side = 0|1. 
	 * Новость должна быть опубликована в группе, принадлежащей текущему сайту.
	 *
	 * @param int $id
	 * @param string $date
	 * @param int $limit
	 * @param int $side
	 * @return array
	 */
	public function getNearbyMessages($id, $date, $type_id, $limit=null, $side=0){
		global $DB;
				
		// Пустое значение передаётся на всех страницах вывода информации, поэтому делаем проверку без SQL запроса
		// сделано это для того, что б не делать двойную проверку на наличие текста новости и наличия empty(id)
		if (empty($id)) {
			return array();
		}
		
		$order = ($side == 0) ? "DESC" : "ASC";
		$direction = ($side == 0) ? "<=" : ">=";
		
		$query = "  
			SELECT 
				tb_message.id as nearby_id, 
				tb_message.headline_".LANGUAGE_CURRENT." AS headline,
				tb_message.content_".LANGUAGE_CURRENT." AS content,
				tb_message.url,
				date_format(tb_message.date, '%e') AS day_from,
				tb_message.announcement_".LANGUAGE_CURRENT." AS announcement,
				CASE MONTH(tb_message.date) ".LANGUAGE_MONTH_GEN_SQL." END AS month_from
			FROM news_message AS tb_message
			WHERE   
				tb_message.id != '$id' AND 
			 	tb_message.dtime $direction '$date' AND
				tb_message.type_id='$type_id' AND 
				tb_message.active=1
		 	ORDER BY tb_message.date $order
			LIMIT $limit
		";
		$data = $DB->query($query);
		if (empty($data)) return array(); 
		if ($DB->rows < $limit) $limit = $DB->rows;
		
		$result = array();
		reset($data);
		while(list($index, $row) = each($data)) {
			$row['nearby_date'] = "$row[day_from] $row[month_from]";
			array_push($result, $row);
		}
		
		return $result;
	}
	
}
	
?>