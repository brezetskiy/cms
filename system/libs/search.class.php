<?php
/**
 * Класс поиска
 * 
 * Названия некоторых методов соответсвуют названиям таблиц, благодаря этому при добавлении и удалении данных 
 * в классах cmsEditAdd, cmsEditDel автоматически формируется поисковый индекс при обновлении таблиц. Для добавления
 * индекса по новой таблице достаточно создать в этом классе новый метод.
 * 
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class Search {
	
	/**
	 * Количество записей, которые будут обновляться за один раз, необходимо для того, 
	 * что б не переполнять память
	 */
	const limit = 500;
	
	/**
	 * Обновляет информацию в поисковом индексе
	 *
	 * @param string $table_name
	 * @param int $id
	 * @return mixed
	 */
	public static function update($table_name, $id = 0) {
		// Проверяем, существует ли поисковый индекс по заданной таблице
		if (!method_exists('Search', $table_name)) {
			return false;
		}
		
		// Обновляем поисковый индекс
		return self::$table_name($id);
	}
	
	/**
	 * Удаление данных из поискового индекса
	 *
	 * @param string $table_name
	 * @param mixed $id
	 * @return bool
	 */
	public static function delete($table_name, $id) {
		global $DB;
		
		// Проверяем, существует ли поисковый индекс по заданной таблице
		if (!method_exists('Search', $table_name)) {
			return false;
		}
		
		
		$query = "select field_id from cms_field_static where db_alias='default' and table_name='$table_name'";
		$fields = $DB->fetch_column($query);
		
		$languages = preg_split("/,/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
		
		$query = "delete from search_content where 1 ".where_clause('id', $id)." and language in ('".implode("','", $languages)."') and field_id in (0".implode(",", $fields).")";
		$DB->delete($query);
		return true;
	}
	
	
	/**
	 * Полное обновление поискового индекса
	 *
	 */
	public static function reload() {
		global $DB;
		
		$query = "truncate table search_content";
		$DB->delete($query);
		
		$methods =  get_class_methods('Search');
		reset($methods);
		while (list(,$method) = each($methods)) {
			if (strstr($method, '_') !== false) {
				echo "[i] Reload $method\n";
				Search::$method();
			}
		}
	}
	
	/**
	 * Формрование лога поиска по сайту
	 * @param string $keyword
	 * @param int $amount
	 * @return int $id
	 */
	
	public static function addToLog($keyword, $amount, $site_id) {
		global $DB;
		
		$query = "
			insert into search_log 
			set
				keyword = '".$DB->escape($keyword)."',
				site_id = '".$site_id."',
				amount = '".$amount."'
		";
		return $DB->insert($query);
		
	}
	
	
	
	
	
	
	/**
	 * Private методы
	 */
	
	
	
	
	/**
	 * Определяет id поля
	 *
	 * @param string $table_name
	 * @param string $field_name
	 * @return int
	 */
	private static function getFieldId($table_name, $field_name) {
		global $DB;
		
		$query = "select id from cms_field_static where table_name='$table_name' and full_name='$field_name'";
		return $DB->result($query);
		
	}
	
	/**
	 * Добавляет новую запись в таблицу
	 *
	 * @param int $id
	 * @param int $field_id
	 * @param string $language
	 * @param int $site_id
	 * @param string $url
	 * @param string $title
	 * @param string $content
	 * @param string $change_dtime
	 * @param decimal $page_priority
	 */
	private static function add($id, $field_id, $language, $site_id, $url, $title, $content, $change_dtime, $change_frequency, $page_priority) {
		global $DB;
		
		$content = self::clean($content);
		if (empty($content)) {
			return false;
		}
		
		$query = "
			replace into search_content set
				id = $id,
				field_id = $field_id,
				language = '$language',
				site_id = $site_id,
				url = '$url',
				title = '".substr(self::clean($title), 0, 250)."',
				content = '$content',
				change_dtime = '$change_dtime',
				change_frequency = '$change_frequency',
				page_priority = '$page_priority'
		";
		$DB->insert($query);
	}
	
	
	/**
	 * Очищает текст от лишних символов
	 *
	 * @param string $text
	 * @return string
	 */
	private static function clean($text) {
		// Удаляем &nbsp; &ndash; &#33;
		$text = preg_replace("/&[a-z#0-9]+;/", '', $text);
		return trim(preg_replace("/[\s\n\r\t]+/", ' ', preg_replace("/[^a-zA-Zа-яА-ЯіїєІЇЄ0-9\s]+/", " ", strip_tags($text))));
	}
	
	
	
	
	/**
	 * Методы для поиска, название метода должно соответсвовать названию таблицы
	 */
	
	
	
	
	
	
	/**
	 * Добавляет в индекс форум
	 *
	 * @param string $language
	 */
	private static function forum_message($id = 0) {
		global $DB;
		
		if (!is_module('Forum')) {
			return false;
		}
		
		$field_id = self::getFieldId('forum_message', "message");
		
		$start_id = 0;
		do {
			$query = "
				select
					tb_message.id,
					tb_forum.site_id,
					tb_message.message as content,
					concat('/Forum/', tb_thread.url, '.html') as url,
					tb_title.title as title, 
					tb_message.last_update as last_modified
				from forum_message as tb_message
				inner join forum_forum as tb_forum on tb_forum.id=tb_message.forum_id
				inner join forum_thread as tb_thread on tb_thread.forum_id=tb_forum.id
				inner join forum_message as tb_title on tb_title.id=tb_message.thread_id
				where 
					tb_forum.active=1 and
					tb_forum.invisible=0 and
					tb_forum.access_view='any' and
					tb_title.id=tb_thread.id and
					tb_message.id > '$start_id'
					".where_clause("tb_message.id", $id)."
				limit ".self::limit."
			";
			$data = $DB->query($query);
			reset($data);
			while (list(,$row) = each($data)) {
				self::add($row['id'], $field_id, LANGUAGE_SITE_DEFAULT, $row['site_id'], $row['url'], $row['title'], $row['content'], $row['last_modified'], 'weekly', 0);
				$start_id = $row['id'];
			}
		} while(!empty($data));
	}
	
	/**
	 * Добавляет в индекс FAQ
	 *
	 * @param string $language
	 */
	private static function faq_question($id = 0) {
		global $DB;
		
		if (!is_module('FAQ')) {
			return false;
		}
		
		$languages = preg_split("/,/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
		reset($languages);
		while (list(,$language) = each($languages)) {
			
			$field_id = self::getFieldId('faq_question', "content_$language");
			$start_id = 0;
			do {
				$query = "
					select
						tb_question.id,
						tb_group.site_id,
						tb_question.content_$language as content,
						concat('/FAQ/', tb_question.uniq_name, '.html') as url,  
						ifnull(tb_question.headline_$language, tb_question.name_$language) as title,
						tb_question.create_date as last_modified,
						tb_question.page_priority
					from faq_question as tb_question
					inner join faq_group as tb_group on tb_group.id=tb_question.group_id
					where 
						tb_group.active=1 and 
						tb_question.active=1 and
						tb_question.id > $start_id
						".where_clause("tb_question.id", $id)."
				";
				$data = $DB->query($query);
				reset($data);
				while (list(,$row) = each($data)) {
					self::add($row['id'], $field_id, $language, $row['site_id'], $row['url'], $row['title'], $row['content'], $row['last_modified'], 'monthly', $row['page_priority']);
					$start_id = $row['id'];
				}
			} while(!empty($data));
		}
	}
	
	/**
	 * Добавляет в индекс новости
	 *
	 * @param string $language
	 */
	private static function news_message($id = 0) {
		global $DB;
		
		if (!is_module('News')) {
			return false;
		}
		
		$languages = preg_split("/,/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
		reset($languages);
		while (list(,$language) = each($languages)) {
			
			$field_id = self::getFieldId('news_message', "content_$language");
			$start_id = 0;
			do {
				$query = "
					select 
						tb_message.id,
						tb_type.site_id,
						tb_message.content_$language as content,
						concat('/News/?id=', tb_message.id) as url,
						tb_message.headline_$language as title,
						tb_message.change_frequency,
						tb_message.dtime as last_modified,
						tb_message.page_priority
					from news_message as tb_message
					inner join news_type as tb_type on tb_type.id=tb_message.type_id
					where 
						tb_message.active=1 and 
						tb_message.id > $start_id and 
						tb_message.content_$language is not null
						".where_clause("tb_message.id", $id)."
				";
				$data = $DB->query($query);
				reset($data);
				while (list(,$row) = each($data)) {
					self::add($row['id'], $field_id, $language, $row['site_id'], $row['url'], $row['title'], $row['content'], $row['last_modified'], $row['change_frequency'], $row['page_priority']);
					$start_id = $row['id'];
				}
			} while(!empty($data));
		}
	}
	
	/**
	 * Добавляет в индекс контент сайта
	 *
	 * @param string $language
	 */
	private static function site_structure($id = 0) {
		global $DB;
		
		if (!is_module('Site')) {
			return false;
		}
		
		$languages = preg_split("/,/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
		reset($languages);
		while (list(,$language) = each($languages)) {
			
			$field_id = self::getFieldId('site_structure', "content_$language");
			
			$query = "select url, id from site_structure_site where url is not null";
			$site = $DB->fetch_column($query);
			
			$start_id = 0;
			do {
				$query = "
					select
						id,
						ifnull(content_$language, keywords_$language) as content,
						url,
						ifnull(title_$language, name_$language) as title,
						last_modified,
						change_frequency,
						page_priority
					from site_structure
					where 
						active=1 and
						id > $start_id
						".where_clause("id", $id)."
				";
				$data = $DB->query($query);
				reset($data);
				while (list(,$row) = each($data)) {
					$host = (strpos($row['url'], '/') > 0) ? substr($row['url'], 0, strpos($row['url'], '/')): $row['url'];
					if (!isset($site[$host])) continue;
					$site_id = $site[$host];
					self::add($row['id'], $field_id, $language, $site_id, substr($row['url'], strpos($row['url'], '/')).'/', $row['title'], $row['content'], $row['last_modified'], $row['change_frequency'], $row['page_priority']);
					$start_id = $row['id'];
				}
			} while(!empty($data));
		}
	}	
}

?>