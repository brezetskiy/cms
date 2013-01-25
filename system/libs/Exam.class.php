<?php
/**
 * Клас для построения тестов 
 * @package Pilot
 * @subpackage Vote
 * @author Markovskiy Dima <dima@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

class Exam {
		
	/**
	 * Метод формирует вопрос для вывода по 
	 * id вопроса
	 *
	 * @param int $id
	 * @return array
	 */
	
	// TODO описать свойства класса
	public $test_id = '';

	public $uniq_name = '';
	
	public $session_id = '';
	
	
	/**
	 * конструктор класа
	 *
	 * @param string $uniq_test_name
	 */
	public function __construct($uniq_test_name) {
		global $DB;
		
		$query = "
			SELECT 
				tb_test.id
			FROM exam_test as tb_test	
			WHERE tb_test.uniq_name='$uniq_test_name'   	
		";				
		$this->test_id = $DB->result($query);
		$this->uniq_name = $uniq_test_name;
	}
	
	
	
	/**
	 * Функция возвращает вопрос по его id
	 *
	 * @param ineger $id
	 * @return array
	 */
	
	public function getQuestion($id) {
		global $DB;

		$query = "
			select 
				tb_quest.id,
				tb_quest.test_id,
				tb_quest.name_".LANGUAGE_CURRENT." as name,
				tb_quest.priority
			from exam_question as tb_quest
			where tb_quest.id = '$id' and tb_quest.active = 1
			order by tb_quest.priority asc
			
		";
		return $DB->query_row($query);
	}
	
	/**
	 * Следующий неотвеченный вопрос
	 *
	 * @return array
	 */
	public function getPriorityQuestion() {
		global $DB;
		
		$query = "
			select 
			    tb_quest.id,
			    tb_quest.test_id,
			    tb_quest.name_".LANGUAGE_CURRENT." as name,
			    tb_quest.priority
			from exam_question as tb_quest
			where 
				tb_quest.test_id='".$this->test_id."' 
				and tb_quest.active=1 
				and tb_quest.id not in (select question_id from exam_way where session_id='".$this->session_id."') 
			order by priority
			limit 1		 
		";
		return $DB->query_row($query);
	}
	
	
	/**
	 * Метод возвращает список вопросов для 
	 * указаного теста
	 *
	 * @return array
	 */
	public function getQuestionList() {
		global $DB;
		
		$query = "
			select 
				tb_quest.id,
				tb_quest.test_id,
				tb_quest.name_".LANGUAGE_CURRENT." as name,
				tb_quest.priority
			from exam_question as tb_quest
			where tb_quest.test_id = '$this->test_id' and tb_quest.active=1
			order by tb_quest.priority asc
		";
		return $DB->query($query);
	}
	
	
	static function getUniqName($id) {
		global $DB;
		
		$query = "
			SELECT tb_test.uniq_name
			FROM exam_test as tb_test
			inner join exam_session as tb_session on tb_session.test_id = tb_test.id	
			WHERE tb_session.id='$id'   	
		";
		return $DB->result($query);
	}
		
	/**
	 * Метод возвращает масив вариантов 
	 * ответов на вопросс
	 *
	 * @param int $question_id
	 * @return array
	 */
	public function getAnswer($question_id) {
		global $DB;
		
		$query = "
			select sum(id)
			from exam_answer
			where
				question_id='$question_id'  
				and corect='1'
		";
		$numbercorect = $DB->result($query);
		$questtype = ($numbercorect > 1) ? 'multiply': 'single';
		
		$query = "
			select 
				tb_answer.id,
				tb_answer.name_".LANGUAGE_CURRENT." AS name,
				tb_answer.question_id,
				'$questtype' as questtype
			from exam_answer as tb_answer
			inner join exam_question as tb_quest on tb_quest.id = tb_answer.question_id 
			where tb_answer.question_id='$question_id' and tb_answer.active = '1'
			order by tb_answer.priority asc
		";
		return $DB->query($query);
	}
	
	/**
	 * Метод возврашает масив правильных 
	 * ответов на вопросс
	 *
	 * @param int $question_id
	 * @return array
	 */
	private function getCorrectAnswer($question_id) {
		global $DB;
		
		$query = "
			select 
				tb_answer.id,
				tb_answer.question_id 
			from exam_answer as tb_answer
			where tb_answer.question_id='$question_id' and tb_answer.corect=1 
			order by tb_answer.priority asc
		";
		return $DB->fetch_column($query);
	}
	
	
	/**
	 * метод провиряет правильномть результатов
	 *
	 * @param int $quest_id
	 * @param mixed $answer
	 * @return bool
	 */
	public function checkAnswer($quest_id, $answer) {
		global $DB;
		$diff = array();
		
		$data = $this->getCorrectAnswer($quest_id);
		
		if (!is_array($answer)) $answer = array($answer);
		
		$insert = array();
		reset($answer);
		while (list(,$answer_id) = each($answer)) {
			$insert[] = "('$this->session_id', '$quest_id', '$answer_id')";
		}
		$query = "INSERT INTO exam_history_answer (session_id, question_id, answer_id) VALUES ".implode(",", $insert);
		$DB->insert($query);
		asort($answer);
		asort($data);
		
		return (implode(',', $answer) == implode(',', $data)) ? true : false;
	}
	
	/**
	 * Метод формирует количество 
	 * правильных ответов  
	 *
	 * @return array
	 */
	public function getTestResult() {
		global $DB;
		
		$query = "
			select 
			   COUNT(tb_quest.id) as count_quest,
			   SUM(tb_way.answer = 1) as corect_quest
			from exam_question as tb_quest
			inner join exam_way as tb_way on tb_quest.id = tb_way.question_id
			inner join exam_session as tb_session on tb_way.session_id = tb_session.id
			where tb_session.id = '$this->session_id'
			order by tb_quest.priority asc
		";
		return $DB->query_row($query); 
	}
	
	
	
	private function countTestQuest() {
		global $DB;
		
		$query = "
			select 
				count(tb_quest.id) as answer
			from exam_question as tb_quest
			where tb_quest.test_id = '$this->test_id' 		
		";
		return $DB->result($query);
	}
	
	
	/**
	 * Метод проверяет не начинал ли раньше 
	 * проходить тест этот user
	 *
	 * @return bool
	 */
	public function checkContinue() {
		global $DB;
		
		$query = "
			select tb_session.id
			from exam_session as tb_session
			where 
				tb_session.test_id = '$this->test_id'
				and tb_session.user_id = '".Auth::getUserId()."'
				and ((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(tb_session.dtime)) < ".EXAM_DURATION.") 
		";
		$result = $DB->result($query);
		
		if($DB->rows > 0) {
			$this->session_id = $DB->result("SELECT id from exam_session WHERE test_id = '$this->test_id' and user_id = '".Auth::getUserId()."' AND ((UNIX_TIMESTAMP() - UNIX_TIMESTAMP(dtime)) < ".EXAM_DURATION.")");
			return true;
		} else {
			$this->session_id = $DB->insert("INSERT INTO exam_session SET user_id = '".Auth::getUserId()."', test_id = '$this->test_id', dtime = NOW()");
			return false;
		}
		
	}
	
	/**
	 * Метод удаляет предыдушие ответы
	 *
	 * @param void
	 */
	public function delAnswer() {
		global $DB;
		$query = "delete tb_way.* from exam_way as tb_way where tb_way.session_id='$this->session_id'";
		$DB->delete($query);
		
		$query = "delete tb_histanswer.* from exam_history_answer as tb_histanswer where tb_histanswer.session_id='$this->session_id'";
		$DB->delete($query);
	}
}

?>
