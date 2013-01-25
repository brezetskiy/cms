<?php
/** 
 * Класс вывода постов модуля FAQ 
 * @package Pilot
 * @subpackage FAQ
 * @author Markovskiy Dima <dima@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class FAQ {
		
		
	/**
	 * Метод формирует список всех вопросов со всех категорий вызывается если нет id записм
	 * 
	 * @param int $group_id
	 * @param string $search_request
	 * @return array;
	 */
	public  function getList($group_id = 0, $search_request = '') {
		global $DB, $Site;
		
		/**
		 * Формируем строку фильтрации записей по текстовому запросу
		 */
		$where_search_request = '';
		$where_text_filter = array();
		
		if(!empty($search_request) && strlen($search_request) > 2){
			$search_request_words = preg_split("/[\s,]+/", $search_request);
			
			reset($search_request_words);
			while(list(, $word) = each($search_request_words)){
				$where_text_filter[] = "tb_question.name_".LANGUAGE_CURRENT." LIKE '%$word%' OR tb_question.content_".LANGUAGE_CURRENT." LIKE '%$word%'";	
			}

			if(!empty($where_text_filter)) $where_search_request = " AND (".implode(' OR ', $where_text_filter).") ";
		}
		
		/**
		 * Массив данных
		 */
		$data = $DB->query("
			SELECT 
				tb_group.id AS group_id,
				tb_group.name_".LANGUAGE_CURRENT." AS group_name,
				tb_question.id AS question_id,
				tb_question.create_date as date,
				tb_question.is_hot,
				tb_question.uniq_name,
				tb_question.name_".LANGUAGE_CURRENT." AS question
			FROM faq_group AS tb_group
			INNER JOIN faq_question AS tb_question ON tb_question.group_id = tb_group.id
			WHERE tb_group.site_id = '{$Site->site_id}' 
				AND tb_group.active=1
				AND tb_question.active=1
				".where_clause("tb_group.id", $group_id)." 
				$where_search_request
			ORDER BY tb_group.priority, tb_question.priority
		");
		
		$last_group = null;
		
		reset($data); 
		while (list($index,$row) = each($data)) { 
			$row['group_name'] = htmlspecialchars($row['group_name'], ENT_QUOTES);
			$row['question'] = htmlspecialchars($row['question'], ENT_QUOTES);
			
			$flag = (time() - strtotime($row['date']))/(3600*24);
			$data[$index]['new'] = ($flag < 30)? 'true':'false';
				
			if ($last_group != $row['group_id']) {
				$data[$index]['sub_group_name'] = $row['group_name'];
				$last_group = $row['group_id'];
			} else {
				$data[$index]['sub_group_name'] = '';
			}
		}
			
		return $data;	
	}
	
	
	/**
	 * Возвращает  список активных групп
	 *
	 * @return array
	 */
	public function getGroups(){
		global $DB,$Site;
		
		return $DB->query("
			SELECT 
				tb_group.id,
				tb_group.name_".LANGUAGE_CURRENT." AS name,
				'node' as class
			FROM faq_group AS tb_group
			INNER JOIN faq_question AS tb_question ON tb_question.group_id = tb_group.id
			WHERE tb_group.site_id = '{$Site->site_id}' 
				AND tb_group.active=1
				AND tb_question.active=1
			GROUP BY tb_group.id
			ORDER BY tb_group.priority, tb_question.priority
		", 'id');
	}
	
	
	/**
	 * Метод возврашает имя 
	 * текущего вопроса 
	 *
	 * @param int $id
	 * @return array
	 */
	public function getQuestion($id) {
		global $DB;
		
		$query = "
			SELECT 
				name_".LANGUAGE_CURRENT." as name,
				title_".LANGUAGE_CURRENT." as title,
				headline_".LANGUAGE_CURRENT." as headline,
				keywords_".LANGUAGE_CURRENT." as keywords,
				description_".LANGUAGE_CURRENT." as description,
				content_".LANGUAGE_CURRENT." as content,
				uniq_name
			FROM faq_question
			WHERE id='$id' and active=1
		";
		$question = $DB->query_row($query);
		if ($DB->rows == 0) {
			return array();
		}
		$question['name'] = htmlspecialchars($question['name'], ENT_QUOTES);
		$question['answer'] = id2url($question['content']);
		$question = array_merge($question, parse_headers($question['name'], $question['title'], $question['headline'], $question['description']));
		return $question;
	}
		
	
	/**
	 * Метод возврашает имя 
	 * текущего вопроса 
	 *
	 * @param string $url
	 * @return array
	 */
	public function getQuestionByUrl($url) {
		global $DB;
		
		$query = "
			SELECT 
				id,
				group_id,
				name_".LANGUAGE_CURRENT." as name,
				title_".LANGUAGE_CURRENT." as title,
				headline_".LANGUAGE_CURRENT." as headline,
				keywords_".LANGUAGE_CURRENT." as keywords,
				description_".LANGUAGE_CURRENT." as description,
				content_".LANGUAGE_CURRENT." as content,
				uniq_name
			FROM faq_question
			WHERE uniq_name='$url' and active=1
		";
		$question = $DB->query_row($query);
		if ($DB->rows == 0) {
			return array();
		}
		$question['name'] = htmlspecialchars($question['name'], ENT_QUOTES);
		$question['answer'] = id2url($question['content']);
		$question = array_merge($question, parse_headers($question['name'], $question['title'], $question['headline'], $question['description']));
		return $question;
	}
	
	/**
	 * Метод возврашает список похожих вопросов текушей группы
	 *
	 * @param int $question_id
	 */
	public function getSimilarQuestions($question_id) {
		global $DB;
		
		$query = "select group_id from faq_question where id='$question_id'";
		$group_id = $DB->result($query);
		
		$query = "
			SELECT 
				id AS question_id,
				create_date as date,
				is_hot,
				name_".LANGUAGE_CURRENT." AS question,
				if(id='$question_id', 'selected', '') as class
			FROM faq_question
			WHERE group_id='$group_id' AND active=1
			ORDER BY priority
		";
		return $DB->query($query);
	}
	
		
	/**
	 * Метод возврашает список похожих вопросов текушей группы
	 *
	 * @param string $url
	 */
	public function getSimilarQuestionsByUrl($url) {
		global $DB;
		
		$query = "select group_id from faq_question where uniq_name='$url'";
		$group_id = $DB->result($query);

		$query = "
			SELECT 
				id AS question_id,
				create_date as date,
				is_hot,
				uniq_name,
				name_".LANGUAGE_CURRENT." AS question,
				if(uniq_name='$url', 'selected', '') as class
			FROM faq_question
			WHERE group_id='$group_id' AND active=1
			ORDER BY priority
		";
		return $DB->query($query);
	}
	
	public function hit($question_id){
		global $DB;
		
		$query = "INSERT INTO faq_hit SET question_id = '$question_id', ip = '".HTTP_IP."', local_ip = '".HTTP_LOCAL_IP."'";
		$DB->insert($query);
	}	
}
?>