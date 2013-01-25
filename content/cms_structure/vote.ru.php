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

$topic_id     = globalVar($_GET['topic_id'], 0);
$TmplContent->set('topic_id', $topic_id);

$group_titles = array(
	'simple' 	=> "простой", 
	'evaluative' => "оценочный", 
	'comment' 	=> "комментарий", 
	'complex' 	=> "сложный"
);


/**
 * Вытягиваем формат текущего опроса
 */
$topic_group  = "";
if(!empty($topic_id)) $topic_group = $DB->result("SELECT `group` FROM vote_topic WHERE id = '$topic_id'");


/**
 * Вывод опросов текущего опроса
 */
if(empty($topic_group) || $topic_group == 'complex'){
	
	function cms_filter($row) {
		global $group_titles;
		
		if(!in_array($row['group'], array('comment'))) $row['question'] = "<a href='./?topic_id=$row[id]'>$row[question]</a>";
		$row['question'] = $row['question']."<br/><span style='color:#777; font-size:10px;'>формат: {$group_titles[$row['group']]}</span>";
		return $row;
	}
	 
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
	$cmsTable->setParam('prefilter', 'cms_filter');
	$cmsTable->addColumn('question', '50%');
	$cmsTable->addColumn('period', '20%', 'center', 'Дата проведения');
	$cmsTable->addColumn('active', '10%');
	$cmsTable->setColumnParam('active', 'editable', true);
	$TmplContent->set('cms_topics', $cmsTable->display());
	unset($cmsTable);
}


/**
 * Вывод вариантов ответов текущего опроса
 */
if(!empty($topic_id) && !in_array($topic_group, array('comment', 'complex'))){
	$query = "
		SELECT 
			id,
			concat('
				<div style=\"width:10px;height:10px;background-color:', color,';border:1px solid black;float:right;\"></div><a href=\"./Stat/?answer_id=', id,'\">', 
				answer_".LANGUAGE_CURRENT.", 
				'</a>'
			) AS answer,
			votes,
			color,
			priority
		FROM vote_answer
		WHERE topic_id = '$topic_id'
		ORDER BY priority
	";
	$cmsTable = new cmsShowView($DB, $query);
	$cmsTable->addColumn('answer', '60%');
	$cmsTable->addColumn('color', '20%', 'center');
	$cmsTable->setColumnParam('color', 'editable', true);
	$cmsTable->addColumn('votes', '20%', 'center');
	$cmsTable->setColumnParam('votes', 'editable', true);
	$TmplContent->set('cms_answers', $cmsTable->display());
	unset($cmsTable);
}

?>