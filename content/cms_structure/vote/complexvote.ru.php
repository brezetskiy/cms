<?php
/**
* Список голосований
*
* @package Pilot
* @subpackage Vote
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$topic_id = globalVar($_GET['topic_id'], 0); 
$TmplContent->set('topic_id', $topic_id);

$group_titles = array('simple' => "простой", 'evaluative' => "оценочный", 'comment' => "комментарий", 'complex' => "сложный"); 


/**
 * Вытягиваем формат текущего опроса
 */
$current_topic = array('topic_id' => 0, 'group' => 'complex');
if(!empty($topic_id)) $current_topic = $DB->query_row("SELECT topic_id, `group` FROM vote_topic WHERE id = '$topic_id'");


/**
 * Фильтр опросов
 *
 * @param unknown_type $row
 * @return unknown
 */
function cms_topic_filter($row) {
	global $group_titles;
	
	$row['question'] = ($row['group'] == 'comment') ? "<a href='./Stat/?topic_id=$row[id]'>$row[question]</a>" : "<a href='./?topic_id=$row[id]'>$row[question]</a>";
	$row['question'] = $row['question']."<br/><span style='color:#777; font-size:10px;'>формат: {$group_titles[$row['group']]}</span>";
	return $row;
}
	

/**
 * Начальный список
 */
if(empty($topic_id)){
	
	$query = "
		SELECT id, `group`, question_".LANGUAGE_CURRENT." AS question, priority, active
		FROM vote_topic
		WHERE `group` = 'complex' AND topic_id = '0'
		ORDER BY priority ASC
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('prefilter', 'cms_topic_filter');
	$cmsTable->addColumn('question', '50%');
	$cmsTable->addColumn('active', '10%');
	$cmsTable->setColumnParam('active', 'editable', true);
	$TmplContent->set('cms_topics', $cmsTable->display());
	unset($cmsTable);

	
/**
 * Обработка сложных опросов
 */
} elseif(!empty($topic_id) && $current_topic['group'] == 'complex'){
	 
	$query = "
		SELECT 
			id,
			question_".LANGUAGE_CURRENT." AS question,
			`group`,
			priority,
			active, 
			CONCAT(DATE_FORMAT(start_date, '".LANGUAGE_DATE_SQL."'), ' - ', DATE_FORMAT(end_date, '".LANGUAGE_DATE_SQL."')) AS period
		FROM vote_topic
		WHERE topic_id = '$topic_id'
		ORDER BY priority
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('prefilter', 'cms_topic_filter');
	$cmsTable->addColumn('question', '50%');
	$cmsTable->addColumn('period', '20%', 'center', 'Дата проведения');
	$cmsTable->addColumn('active', '10%');
	$cmsTable->setColumnParam('active', 'editable', true);
	$TmplContent->set('cms_topics', $cmsTable->display());
	unset($cmsTable);

	
/**
 * Обработка простых опросов
 */
} elseif(!empty($topic_id) && $current_topic['group'] == 'simple'){
	$query = "
		SELECT 
			id,
			concat('<a href=\"./Stat/?answer_id=', id,'\">', answer_".LANGUAGE_CURRENT.", '</a>') AS answer,
			votes,
			priority
		FROM vote_answer 
		WHERE topic_id = '$topic_id'
		ORDER BY priority
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('parent_link', './?topic_id='.$current_topic['topic_id']);
	$cmsTable->addColumn('answer', '80%');
	$cmsTable->addColumn('votes', '10%', 'center');
	$cmsTable->setColumnParam('votes', 'editable', true);
	$TmplContent->set('cms_answers', $cmsTable->display());
	unset($cmsTable);

	
/**
 * Обработка оценочных опросов
 */
} elseif(!empty($topic_id) && $current_topic['group'] == 'evaluative'){
	$query = "
		SELECT 
			id,
			concat('<a href=\"./Stat/?answer_id=', id,'\">', answer_".LANGUAGE_CURRENT.", '</a>') AS answer,
			votes,
			IF(votes > 0, ROUND(SUM(tb_stat.value_int)/votes, 2), 0) as average,
			priority
		FROM vote_answer as tb_answer
		INNER JOIN vote_stat as tb_stat ON tb_stat.answer_id = tb_answer.id
		WHERE tb_answer.topic_id = '$topic_id'
		GROUP BY tb_answer.id
		ORDER BY tb_answer.priority
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->setParam('parent_link', './?topic_id='.$current_topic['topic_id']);
	$cmsTable->addColumn('answer', '70%'); 
	$cmsTable->addColumn('votes', '10%', 'center'); 
	$cmsTable->setColumnParam('votes', 'editable', true);
	$cmsTable->addColumn('average', '10%', 'center', 'Средняя оценка');
	$TmplContent->set('cms_answers', $cmsTable->display());
	unset($cmsTable);
}


?>