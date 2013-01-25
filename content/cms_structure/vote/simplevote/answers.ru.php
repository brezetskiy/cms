<?php
/**
* Варианты ответов для вопроса в голосовании
*
* @package Pilot
* @subpackage Vote
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$topic_id = globalVar($_GET['topic_id'], 0);

$query = "
	SELECT 
		id,
		concat('<div style=\"width:10px;height:10px;background-color:', color,';border:1px solid black;float:right;\"></div><a href=\"./Stat/?answer_id=', id,'\">', answer_".LANGUAGE_CURRENT.", '</a>') AS answer,
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
echo $cmsTable->display();
unset($cmsTable);

?>