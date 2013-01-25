<?php

/**
 * ������� ������� �������
 * @package Pilot
 * @subpackage Vote
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */
class VoteComplex {
	
	
	/**
	 * ID ������
	 * @var int
	 */
	private $id = 0;
	
	
	/**
	 * ������ ������ ������ ������
	 * @var object
	 */ 
	private $template;
	
	
	/**
	 * ������ ������ ����������� 
	 * @var object
	 */ 
	private $results;
	
	
	/**
	 * ������ ������ ��� ���������� ������
	 * @var array
	 */
	private $relation = array();
	
	
	/**
	 * ������� ������
	 * @var int
	 */
	private $level = 0;
	
	
	/**
	 * ���������� ������� ������ ������
	 * @param array $topics
	 * @param array &$tree
	 * @return array
	 */
	private function getTree($children, &$tree){
				
		reset($children);
		while(list($topic_id, $child) = each($children)){
			if($child['group'] == 'complex' && !empty($this->relation[$topic_id])) self::getTree($this->relation[$topic_id], $child);
			$tree['children'][$topic_id] = $child;
		}
		
		return $tree;
	}
	
	
	/**
	 * ���������� ������� ������ ������
	 * @param array $topics
	 */
	private function displayTree($topics){
		$this->level++;
				
		reset($topics);
		while(list($topic_id, $topic) = each($topics)){
			
			$TmplTreeElement = new Template(SITE_ROOT.'templates/vote/complex_tree');
			
			/**
			 * ����� �������
			 */
			$TmplTreeElement->set('id', $topic['id']);
			$TmplTreeElement->set('question', $topic['question']);
			$TmplTreeElement->set('comment', $topic['comment']);
			$TmplTreeElement->set('group', $topic['group']);
			$TmplTreeElement->set('type', $topic['type']);
			$TmplTreeElement->set('access_level', $topic['access_level']);
			$TmplTreeElement->set('lvl', $this->level);
			
			/**
			 * ��������� ������� ����������
			 */
			if($topic['group'] == 'complex' && !empty($this->relation[$topic_id])){
				$this->template .= " " . $TmplTreeElement->display() . " "; 
				self::displayTree($this->relation[$topic_id]);
				continue; 
			 
			/**
			 * ��������� ������� ����������
			 */
			} elseif($topic['group'] == 'simple' && !empty($topic['answers'])) {
				$row['is_error_checked'] = false;
				
				reset($topic['answers']);
				while(list($index, $row) = each($topic['answers'])){
					
					if($topic['type'] == 'multiple' && !empty($_SESSION['ActionError']['topic'][$row['topic_id']][$row['id']])){
						$row['is_error_checked'] = true;
					} elseif(!empty($_SESSION['ActionError']['topic'][$row['topic_id']]) && $_SESSION['ActionError']['topic'][$row['topic_id']] == $row['id']){
						$row['is_error_checked'] = true;
					}
					 
					$TmplTreeElement->iterate('/answers/', null, $row);
				}
			
			/**
			 * ��������� ��������� ����������
			 */
			} elseif($topic['group'] == 'evaluative' && !empty($topic['answers'])) {
				
				reset($topic['answers']);
				while(list($index, $row) = each($topic['answers'])){
					$row['is_error_value'] = null;
					if(!empty($_SESSION['ActionError']['topic'][$row['topic_id']][$row['id']])){
						$row['is_error_value'] = $_SESSION['ActionError']['topic'][$row['topic_id']][$row['id']];
					}
					 
					$TmplTreeElement->iterate('/answers/', null, $row);
				}
			
			/**
			 * ��������� ������������� ����������
			 */	 
			} elseif($topic['group'] == 'comment' && !empty($_SESSION['ActionError']['topic'][$topic['id']])) {
				$TmplTreeElement->set('is_error_text', array($topic['id'] => $_SESSION['ActionError']['topic'][$topic['id']]));
			}
			
			/**
			 * ������������ �������
			 */
			$this->template .= " " . $TmplTreeElement->display() . " ";  
		}
		
		$this->level--;
	}
	
	 
	/**
	 * ���������� ������� � ���� ��� ���� ���������� ������
	 * @param array $tree
	 * @return bool
	 */
	private function addVoteThroughTree($tree, $answers, &$update, &$insert){
		
		reset($tree);
		while(list($index, $topic) = each($tree)){
			
			/**
			 * ��������� �������� ���������
			 */
			if($topic['group'] == 'complex' && !empty($topic['children'])){
				$insert[] = "('{$topic['id']}', NULL, NULL, NULL, '".HTTP_LOCAL_IP."', '".HTTP_IP."')";
				$this->addVoteThroughTree($topic['children'], $answers, $update, $insert);
				continue;
				
			/**
			 * ��������� �������� ���������
			 */
			} elseif($topic['group'] == 'simple' && !empty($answers[$topic['id']])){
				if($topic['type'] == 'single'){
					$update[] = "('{$answers[$topic['id']]}', '{$topic['id']}', '1')";
					$insert[] = "('{$topic['id']}', '{$answers[$topic['id']]}', NULL, NULL, '".HTTP_LOCAL_IP."', '".HTTP_IP."')";
				
				} elseif($topic['type'] == 'multiple'){
					reset($answers[$topic['id']]);
					while(list($answer_id, ) = each($answers[$topic['id']])){
						$update[] = "('$answer_id', '{$topic['id']}', '1')";
						$insert[] = "('{$topic['id']}', '$answer_id', NULL, NULL, '".HTTP_LOCAL_IP."', '".HTTP_IP."')";
					}
				}
			
			/**
			 * ��������� ���������� ���������
			 */
			} elseif($topic['group'] == 'evaluative' && !empty($answers[$topic['id']])){
				reset($answers[$topic['id']]);
				while(list($answer_id, $value) = each($answers[$topic['id']])){
					$update[] = "('$answer_id', '{$topic['id']}', '1')";
					$insert[] = "('{$topic['id']}', '$answer_id', '$value', NULL, '".HTTP_LOCAL_IP."', '".HTTP_IP."')";
				}	
			
			/**
			 * ��������� �������������� ��������� 
			 */
			} elseif($topic['group'] == 'comment'){
				$comment = addslashes(trim(substr($answers[$topic['id']], 0, 64000)));
				if(empty($comment)) continue;
				
				$insert[] = "('{$topic['id']}', NULL, NULL, '$comment', '".HTTP_LOCAL_IP."', '".HTTP_IP."')";
			}
		}
	}
	
	
	/**
	 * ����� ����������� ��� ���� ���������� ������
	 * @param array $tree
	 * @return void
	 */
	private function displayResultsTree($tree, $stat){
		$this->level++;
		
		if(!IS_DEVELOPER) {
			$this->results = '� �������� ����������';
			return;		
		}
	
		reset($tree);
		while(list($topic_id, $topic) = each($tree)){
			
			$TmplTreeElement = new Template(SITE_ROOT.'templates/vote/complex_tree_results');
			 
			/**
			 * ����� �������
			 */
			$TmplTreeElement->set('id', $topic['id']);
			$TmplTreeElement->set('question', $topic['question']);
			$TmplTreeElement->set('comment', $topic['comment']);
			$TmplTreeElement->set('group', $topic['group']);
			$TmplTreeElement->set('votes_total', $topic['votes_total']);
			$TmplTreeElement->set('votes_max', $topic['votes_max']);
			$TmplTreeElement->set('lvl', $this->level);
			
			/**
			 * ��������� ������� ����������
			 */
			if($topic['group'] == 'complex' && !empty($topic['children'])){
				$this->results .= " " . $TmplTreeElement->display() . " "; 
				$this->displayResultsTree($topic['children'], $stat);
				continue; 
			 
			/**
			 * ��������� ������� ����������
			 */
			} elseif($topic['group'] == 'simple' && !empty($topic['answers'])) {
				reset($topic['answers']);
				while(list($index, $row) = each($topic['answers'])){
					$row['percent'] = (!empty($row['votes'])) ? round($row['votes'] / $topic['votes_total'] * 100, 2) : 0;
					$row['width'] = (!empty($row['votes'])) ? round($row['votes'] * 300 / $topic['votes_max'], 2) : 1;
					$TmplTreeElement->iterate('/answers/', null, $row);
				}
			   
			/**
			 * ��������� ��������� ����������
			 */
			} elseif($topic['group'] == 'evaluative' && !empty($topic['answers'])) {
				reset($topic['answers']);
				while(list($index, $row) = each($topic['answers'])){
					$values = (!empty($stat[$topic['id']][$row['id']])) ? $stat[$topic['id']][$row['id']] : array();
					$row['average_point'] = (!empty($values)) ? round($values['points'] / $values['votes'], 2) : 0;
					$row['width'] = (!empty($values)) ? round($row['average_point'] * 200 / $values['points'], 2) : 1; 
					
					$row['average_point'] = number_format($row['average_point'], 2, '.', '');
					$TmplTreeElement->iterate('/answers/', null, $row);
				}
			
			/**
			 * ��������� ������������� ����������
			 */	 
			} elseif($topic['group'] == 'comment') {
				if(empty($stat[$topic['id']])){
					$TmplTreeElement->set('comment_rows', array($topic['id'] => 0));
				} else {
					$TmplTreeElement->set('comment_rows', array($topic['id'] => count($stat[$topic['id']])));
					reset($stat[$topic['id']]);
					while(list($index, $row) = each($stat[$topic['id']])){
						$TmplTreeElement->iterate('/answers/', null, $row);
					}
				}
			}
			
			/**
			 * ������������ �������
			 */
			$this->results .= " " . $TmplTreeElement->display() . " ";  
		}
		
		$this->level--;
	}
	
	
	/**
	 * ����������� ������
	 * @param int $id
	 */
	function __construct($id){
		$this->id = $id;
		$this->template = '';
		$this->results = '';
	}
	
	
	/**
	 * ���������� ������ �� �������� ������
	 *
	 * @param int $topic_id
	 * @return bool
	 */
	public function addVote($answers, &$error) {
		global $DB;     
		
		/**
		 * ���������� ���������� ����������� 
		 */
		if (Vote::isBlocked($this->id)){
			$error = '��������, �� �� ��� ������ ������ �����. ������� �� ��, ��� ��������� ������ ��� ������ �����.';
			return false;
		}
		if (empty($answers)){
			$error = '����������, ����� ����� ���� �� �� ���� ������.';
			return false;
		}
		
		/**
		 * ��������� ������
		 */
		$update = array();
		$insert = array();
		
		$topic_tree = $this->display(true);
		if(empty($topic_tree['children'])){
			$error = '������: ������� �� �������. ����������, ���������� � ���. ���������.';
			return false;
		}
		  
		$insert[] = "('{$topic_tree['id']}', NULL, NULL, NULL, '".HTTP_LOCAL_IP."', '".HTTP_IP."')";
		$this->addVoteThroughTree($topic_tree['children'], $answers, $update, $insert); 
		
		/**
		 * ���������� ���-�� ������� �� ��������������� ������
		 */
		if(!empty($update)) $DB->insert("INSERT INTO vote_answer (id, topic_id, votes) VALUES ".implode(',', $update)." ON DUPLICATE KEY UPDATE votes = votes + VALUES(votes)");
		
		/**
		 * ��������� �������� �������
		 */
		if(!empty($insert)) $DB->insert("
			INSERT INTO vote_stat (topic_id, answer_id, value_int, value_text, local_ip, ip) 
			VALUES ".implode(',', $insert)." 
		");
		
		/**
		 * ������������� ����� ���� �� 2 ������
		 */
		$cookie = globalVar($_COOKIE['vote'], '').','.$this->id; 
		$cookie = preg_split("/,/", $_COOKIE['vote'], -1, PREG_SPLIT_NO_EMPTY);
		setcookie('vote', implode(",", array_unique($cookie)), time() + 86400 * 60, '/', CMS_HOST, false);
		 
		return true;
	}
	
	
	/**
	 * ���������� ������� html ������ ��� ������ ������ ������
	 * @param $id
	 * @return mixed
	 */
	public function display($return_array = false){
		global $DB;
		 
		/**
		 * ���������� ��� ����������
		 */
		$family = $DB->result("
			SELECT GROUP_CONCAT(tb_relation.id SEPARATOR ',') as family
			FROM vote_topic as tb_topic   
			INNER JOIN vote_topic_relation as tb_relation ON (tb_relation.parent = tb_topic.id)
			WHERE tb_topic.id = '{$this->id}'
		");
		
		if(empty($family)) return false;
		
		/**
		 * ������ ������ ������� �� ��������
		 */
		$answers_tmp = $DB->query("
			SELECT 
				tb_answer.id, 
				tb_answer.topic_id, 
				tb_answer.answer_".LANGUAGE_CURRENT." as answer,
				tb_answer.color,
				tb_answer.votes 
			FROM vote_answer as tb_answer  
			WHERE tb_answer.topic_id IN (0$family)
			ORDER BY tb_answer.priority ASC
		", "id");
		
		$answers = array();
		
		reset($answers_tmp);
		while(list(, $answer) = each($answers_tmp)){
			$answers[$answer['topic_id']][$answer['id']] = $answer;
		}
		
		/**
		 * ������ ������ ����������� �� ��������
		 */
		$topics = $DB->query("
			SELECT 
				tb_topic.id,
				tb_topic.topic_id as parent_id,
				tb_topic.question_".LANGUAGE_CURRENT." as question,
				tb_topic.comment_".LANGUAGE_CURRENT." as comment,
				tb_topic.group,
				tb_topic.type,
				tb_topic.access_level,
				SUM(tb_answer.votes) as votes_total,
				MAX(tb_answer.votes) AS votes_max
			FROM vote_topic as tb_topic
			LEFT JOIN vote_answer as tb_answer ON tb_answer.topic_id = tb_topic.id
			WHERE tb_topic.id IN (0$family)
			GROUP BY tb_topic.id
			ORDER BY tb_topic.topic_id, tb_topic.priority ASC
		", 'id'); 
		
		reset($topics);
		while(list(, $topic) = each($topics)){
			$this->relation[$topic['parent_id']][$topic['id']] = $topic;
			if(!empty($answers[$topic['id']])) $this->relation[$topic['parent_id']][$topic['id']]['answers'] = $answers[$topic['id']];
		}
		
		/**
		 * ���������� ������ ������ � ���� �������
		 */
		if($return_array){
			if(empty($topics[$this->id])) return false;
			$tree = $topics[$this->id];   
			
			self::getTree($this->relation[$this->id], $tree);
			return $tree;
		}
		
		/**
		 * ����� ������� ������
		 */
		$TmplVote = new Template(SITE_ROOT.'templates/vote/complex');
		$TmplVote->setGlobal($topics[$this->id]);
		 
		$this->displayTree($this->relation[$this->id]); 
		$TmplVote->set('tree', $this->template);  
		 
		return $TmplVote->display();
	}
	  
	
	/**
	 * ����� �����������
	 * @return string
	 */
	public function displayResults(){
		global $DB;
		
		/**
		 * ���������� ��� ���������� 
		 */
		$family = $DB->result("
			SELECT GROUP_CONCAT(tb_relation.id SEPARATOR ',') as family
			FROM vote_topic as tb_topic   
			INNER JOIN vote_topic_relation as tb_relation ON (tb_relation.parent = tb_topic.id)
			WHERE tb_topic.id = '{$this->id}'
		");
		
		if(empty($family)) return false;
		
		/**
		 * ������ ����� ������� � �������� �������
		 */
		$stat = array();
		$stat_list = $DB->query("
			SELECT 
				tb_stat.topic_id,
				tb_stat.answer_id,
				tb_topic.group,
				CASE 
   					WHEN tb_topic.group = 'comment' THEN tb_stat.value_text
   					WHEN tb_topic.group = 'evaluative' THEN tb_stat.value_int
   				END as value,
   				tb_stat.ip,
   				date_format(tb_stat.tstamp, '".LANGUAGE_DATE_SQL." %H:%i:%s') as tstamp
			FROM vote_stat as tb_stat 
			INNER JOIN vote_topic as tb_topic ON tb_topic.id = tb_stat.topic_id
			WHERE tb_stat.topic_id IN (0$family)
		");
		
		if(empty($stat_list)) return false;
		
		reset($stat_list);
		while(list($index, $row) = each($stat_list)){
			if($row['group'] == 'complex') {
				$stat[$row['topic_id']]['votes'] = (!empty($stat[$row['topic_id']]['votes'])) ? $stat[$row['topic_id']]['votes'] + 1 : 1;
			} elseif($row['group'] == 'comment'){
				$stat[$row['topic_id']][] = array('value' => $row['value'], 'ip' => $row['ip'], 'tstamp' => $row['tstamp']);
			} elseif($row['group'] == 'simple') {
				$stat[$row['topic_id']][$row['answer_id']]['votes'] = (!empty($stat[$row['topic_id']][$row['answer_id']]['votes'])) ? $stat[$row['topic_id']][$row['answer_id']]['votes'] + 1 : 1;
			} elseif($row['group'] == 'evaluative'){
				$stat[$row['topic_id']][$row['answer_id']]['votes'] = (!empty($stat[$row['topic_id']][$row['answer_id']]['votes'])) ? $stat[$row['topic_id']][$row['answer_id']]['votes'] + 1 : 1;
				$stat[$row['topic_id']][$row['answer_id']]['points'] = (!empty($stat[$row['topic_id']][$row['answer_id']]['points'])) ? $stat[$row['topic_id']][$row['answer_id']]['points'] + $row['value'] : $row['value'];
			}
		}
		
		/**
		 * ������ ����������
		 */
		$topic_tree = $this->display(true);
		if(empty($topic_tree['question'])) return false;
		if(empty($topic_tree['children'])) return false;
		
		/**
		 * ����� �������
		 */
		$TmplResults = new Template(SITE_ROOT.'templates/vote/complex_results');
		$TmplResults->setGlobal('question', $topic_tree['question']);
		$TmplResults->setGlobal('comment', (!empty($topic_tree['comment'])) ? $topic_tree['comment'] : '');  
		$TmplResults->setGlobal('votes_total', (!empty($stat[$topic_tree['id']])) ? $stat[$topic_tree['id']]['votes'] : 0);
 
		$this->displayResultsTree($topic_tree['children'], $stat); 
		$TmplResults->set('tree_content', $this->results);  
		
		/**
		 * ������� ������ ������ ���� �����������
		 */
		$votes = $DB->query("SELECT id, question_".LANGUAGE_CURRENT." AS question FROM vote_topic WHERE topic_id = '0' AND show_result=1 ORDER BY priority ASC");
		$first = true;
		
		reset($votes);
		while (list(, $row) = each($votes)) {
			if ($row['id'] == $this->id) {
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