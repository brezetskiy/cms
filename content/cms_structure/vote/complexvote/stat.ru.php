<?php
/**
 * Статистика голосовавших
 * @package Pilot
 * @subpackage Vote
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */
$topic_id  = globalVar($_GET['topic_id'], 0);
$answer_id = globalVar($_GET['answer_id'], 0);

if(empty($topic_id) && empty($answer_id)) header("Location: /Admin/Vote/ComplexVote/");

/**
 * Определяем группу
 */
if(!empty($topic_id)){
	$group = $DB->result("SELECT `group` FROM vote_topic WHERE id = '$topic_id'");
} elseif(!empty($answer_id)) {
	$group = $DB->result("SELECT tb_topic.group FROM vote_topic as tb_topic INNER JOIN vote_answer as tb_answer ON tb_answer.topic_id = tb_topic.id WHERE tb_answer.id = '$answer_id'");
}

/**
 * Дополнительные поля для нестандартных групп
 */
$additional_fields = '';
if($group == 'evaluative') $additional_fields = "value_int, ";
if($group == 'comment'){
	$additional_fields = "value_text, ";
	$parent_id = $DB->result("SELECT topic_id FROM vote_topic WHERE id = '$topic_id'");
}


$query = "
	SELECT 
		ip, 
		local_ip, 
		$additional_fields
		date_format(tstamp, '".LANGUAGE_DATE_SQL." %H:%i:%s') as tstamp
	FROM vote_stat
	WHERE 1
		".where_clause('topic_id', $topic_id)."
		".where_clause('answer_id', $answer_id)."
	ORDER BY tstamp desc
";
$cmsTable = new cmsShowView($DB, $query);
if($group == 'comment'){
	$cmsTable->setParam('show_parent_link', true);
	$cmsTable->setParam('parent_link', '../?topic_id='.$parent_id);
}
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);


if($group == 'evaluative') $cmsTable->addColumn('value_int', '10%', 'right', 'Оценка');
if($group == 'comment') $cmsTable->addColumn('value_text', '60%', 'left', 'Комментарий от пользователя');
$cmsTable->addColumn('ip', '10%', 'center');
$cmsTable->addColumn('local_ip', '10%', 'center');
$cmsTable->addColumn('tstamp', '10%', 'center');
echo $cmsTable->display();
unset($cmsTable);

?>