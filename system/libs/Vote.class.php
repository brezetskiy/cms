<?php
/**
 * Система голосования и опросов
 * @package Pilot
 * @subpackage Vote
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2008
 */
class Vote {
	
	
	/**
	 * Информация о текущем голосовании
	 *
	 * @var array
	 */
	private $info = array();
	
	
	/**
	 * Конструктор
	 *
	 * @param int $structure_id
	 */
	public function __construct($structure_id) {
		global $DB;
		
		// Определяем информацию об активном голосовании
		$query = "
			SELECT 
				tb_topic.id,
				tb_topic.type,
				tb_topic.question_".LANGUAGE_CURRENT." AS question,
				tb_topic.show_result,
				tb_topic.access_level,
				(select sum(votes) from vote_answer where topic_id=tb_topic.id) as votes
			FROM vote_topic as tb_topic
			INNER JOIN vote_topic_structure as tb_relation on tb_relation.topic_id=tb_topic.id
			WHERE tb_topic.active=1
				and (tb_topic.start_date<=CURRENT_DATE() OR tb_topic.start_date IS NULL) 
				and (tb_topic.end_date>=CURRENT_DATE() OR tb_topic.end_date IS NULL)
				and tb_relation.structure_id='$structure_id'
			ORDER BY tb_topic.priority ASC
			LIMIT 1
		";
		$this->info = $DB->query_row($query);
	}
	
	
	/**
	 * Возвращает информацию о голосовании
	 *
	 * @return array
	 */
	public function getInfo() {
		return $this->info;
	}
	
	
	/**
	 * Список вопросов
	 *
	 * @return array
	 */
	public function getAnswers() {
		global $DB;
		
		if (empty($this->info)) return array();
		
		$query = "
			SELECT
				id,
				answer_".LANGUAGE_CURRENT." AS answer,
				'{$this->info['type']}' as type,
				votes,
				color
			FROM vote_answer
			WHERE topic_id = '{$this->info['id']}'
			ORDER BY priority ASC
		";
		$data = $DB->query($query);
		$total = 0;
		
		reset($data);
		while (list(,$row) = each($data)) {
			$total += $row['votes'];
		}
		
		reset($data);
		while (list($index,$row) = each($data)) {
			$data[$index]['total'] = $total;
			$data[$index]['percent'] = ($row['votes'] == 0) ? 0 : round($row['votes'] / $total * 100, 2);
			$data[$index]['percent_int'] = ($row['votes'] == 0) ? 1 : ceil($row['votes'] / $total * 100);
		}
		
		return $data;
	}
	
	
	static function getTopicByAnswer($answer) {
		global $DB;
		
		$query = "select topic_id from vote_answer where 1 ".where_clause('id', $answer)." limit 1";
		return  $DB->result($query);
	}
	
	
	/**
	 * Проверяет, активно ли голосование или нет
	 *
	 * @param int $topic_id
	 * @return bool
	 */
	static public function addVote($topic_id, $answer_id, $answer_list) {
		global $DB;
		
		$query = "
			select type
			from vote_topic 
			where
				active=1
				and (start_date<=CURRENT_DATE() OR start_date IS NULL) 
				and (end_date>=CURRENT_DATE() OR end_date IS NULL)
				and id='$topic_id'
		";
		$type = $DB->result($query);
		
		// голосование уже закрыто
		if ($DB->rows == 0) return false;
		
		// Блокировка повторного голосования
		if (self::isBlocked($topic_id)) return false;
		if (empty($answer_id) && empty($answer_list)) return false;
		
		$answer = ($type == 'single') ? array($answer_id) : $answer_list;
		
		reset($answer);
		while (list(,$row)=each($answer)) {
			// Фиксируем количество проголосовавших
			$query = "UPDATE vote_answer SET votes = votes + 1 WHERE id='$row' AND topic_id='$topic_id'";
			$DB->insert($query);
			
			// Заносим юзера в список проголосовавших
			$query = "insert into vote_stat set topic_id='$topic_id', answer_id='$row', local_ip = '".HTTP_LOCAL_IP."', ip = '".HTTP_IP."'";
			$DB->insert($query);
		}
		
		// Устанавливаем юзеру куку на 2 месяца
		$cookie = globalVar($_COOKIE['vote'], '').','.$topic_id;
		$cookie = preg_split("/,/", $_COOKIE['vote'], -1, PREG_SPLIT_NO_EMPTY);
		setcookie('vote', implode(",", array_unique($cookie)), time() + 86400 * 60, '/', CMS_HOST, false);
		
		return true;
	}
	
	
	/**
	 * Проверяет, может ли пользователь голосовать или нет
	 *
	 * @param int $toppic_id
	 * @return bool
	 */
	public static function isBlocked($topic_id) {
		global $DB;
		
		$cookie = globalVar($_COOKIE['vote'], '').','.$topic_id;
		$cookie = preg_split("/,/", $_COOKIE['vote'], -1, PREG_SPLIT_NO_EMPTY);
		if (in_array($topic_id, $cookie)) return true;
		
		// Проверка на повторное голосование по IP
		$query = "
			SELECT topic_id
			FROM vote_stat
			WHERE topic_id = '$topic_id'
				AND local_ip = '".HTTP_LOCAL_IP."'
				AND ip = '".HTTP_IP."'
				AND UNIX_TIMESTAMP(tstamp) + 3600 > UNIX_TIMESTAMP()
			LIMIT 1
		";
		$DB->result($query);
		
		if ($DB->rows > 0) return true;
		return false;
	}
	
	
	/**
	 * Вывод результатов
	 * 
	 * @param int $topic_id
	 * @return mixed
	 */
	public static function displayResults($topic_id){
		global $DB;
		
		$TmplResults = new Template(SITE_ROOT.'templates/vote/results');

		$vote = $DB->query_row("
			SELECT 
				question_".LANGUAGE_CURRENT." AS question, 
				comment_".LANGUAGE_CURRENT." AS comment,
				DATE_FORMAT(start_date, '".LANGUAGE_DATE_SQL."') AS start,
				DATE_FORMAT(end_date, '".LANGUAGE_DATE_SQL."') AS end
			FROM vote_topic
			WHERE id='$topic_id' 
		");
		
		if ($DB->rows == 0) return false;
		$TmplResults->set($vote);
		
		/**
		 * Определяем общее и максимальное количество голосов
		 */
		$count = $DB->query_row("
			SELECT MAX(tb_answer.votes) AS max, SUM(tb_answer.votes) AS total 
			FROM vote_topic As tb_topic
			INNER JOIN vote_answer AS tb_answer ON tb_answer.topic_id = tb_topic.id
			WHERE tb_topic.id='$topic_id'
		");
		$TmplResults->set($count);
		
		$result = $DB->query("
			SELECT 
				answer_".LANGUAGE_CURRENT." AS answer, 
				ROUND(votes / ".$count['total']." * 100, 2) AS percent,
				ROUND(votes * 300 / ".$count['max'].") AS width,
				votes
			FROM vote_answer AS tb_answer
			WHERE topic_id = '".$topic_id."'
			ORDER BY priority ASC
		");
		
		reset($result);
		while (list(, $row) = each($result)) {
			$row['width'] = ($row['width']) ? $row['width'] : 1;
			$row['percent'] = number_format($row['percent'], 2, ',', ' ');
			$TmplResults->iterate('/answer/', null, $row);
		}
		
		/**
		 * Выводим полный список всех голосований
		 */
		$votes = $DB->query("SELECT id, question_".LANGUAGE_CURRENT." AS question FROM vote_topic WHERE topic_id = '0' AND show_result=1 ORDER BY priority ASC");
		$first = true;
		
		reset($votes);
		while (list(, $row) = each($votes)) {
			if ($row['id'] == $topic_id) {
				$row['style'] = 'font-weight: bold;';
				$first = false;
			}
			$TmplResults->iterate('/vote/', null, $row);
		}
		
		unset($votes);
		return $TmplResults->display();
	}

}


?>