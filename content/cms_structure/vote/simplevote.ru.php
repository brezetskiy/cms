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


function cms_filter($row) {
	$row['question'] = "<a href='./?topic_id=$row[id]'>$row[question]</a>";
	return $row;
}
 

/**
 * Вывод опросов
 */
if(empty($topic_id)) {

$query = "
	SELECT 
		id,
		question_".LANGUAGE_CURRENT." AS question,
		priority,
		active, 
		CONCAT(DATE_FORMAT(start_date, '".LANGUAGE_DATE_SQL."'), ' - ', DATE_FORMAT(end_date, '".LANGUAGE_DATE_SQL."')) AS period
	FROM vote_topic
	WHERE topic_id = '$topic_id' AND `group` = 'simple'
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


/**
 * Вывод вариантов ответов текущего опроса
 */
} elseif(!empty($topic_id)){
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
	$cmsTable->setParam('parent_link', './?');
	$cmsTable->addColumn('answer', '60%');
	$cmsTable->addColumn('color', '20%', 'center');
	$cmsTable->setColumnParam('color', 'editable', true);
	$cmsTable->addColumn('votes', '20%', 'center');
	$cmsTable->setColumnParam('votes', 'editable', true);
	$TmplContent->set('cms_answers', $cmsTable->display());
	unset($cmsTable);
}

?>