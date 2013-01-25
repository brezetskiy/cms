<?php 
/**
* Добавляет голос в голосовании
*
* @package Pilot
* @subpackage Vote
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$structure_id = globalVar($_REQUEST['structure_id'], 0);
$template = globalVar($_REQUEST['template'], 'vote/result');
$answer_id = globalVar($_REQUEST['vote'], 0);
$answer_list = globalVar($_REQUEST['answer_list'], array());
$form_name    = globalVar($_REQUEST['form_name'], '');

$Vote = new Vote($structure_id);
$info = $Vote->getInfo();

if($info['access_level'] == 'registered' && (!isset($_SESSION['auth']))){
	echo cms_message('Vote', "Только зарегистрорванный пользователь может проголосовать - зарегистрируйтесь на сайте или авторизируйтесь");	
	exit;
}
if($info['access_level'] == 'confirmed' && (!isset($_SESSION['auth']['confirmed']) || $_SESSION['auth']['confirmed'] == 0)){
	echo cms_message('Vote', "Только пользователь, которые подтвердили свой e-mail, может проголосовать");	
	exit;
}

if($info['access_level'] == 'checked' && (!isset($_SESSION['auth']['checked']) || $_SESSION['auth']['checked'] == 0)){
	echo cms_message('Vote', "Только пользователь, которого подтвердил администратор системы, может проголосовать");	
	exit;
}

if (empty($answer_id) && empty($answer_list)) {
	echo cms_message('Vote', "Выберите вариант ответа");
	exit;
}

$topic_id = (empty($answer_list)) ? Vote::getTopicByAnswer($answer_id) : Vote::getTopicByAnswer($answer_list);
Vote::addVote($topic_id, $answer_id, $answer_list);


/*$_RESULT['vote'] = cms_message('Vote', 'Спасибо, Ваш голос учтен.');*/

$Template = new Template($template);
$Template->set($info);
$Template->set('structure_id', $structure_id);
$Template->set('template', $template);
$Template->set('ajax', true);
$answers = $Vote->getAnswers();
$Template->iterateArray('/answer/', null, $answers);
$Template->set('total', $answers[0]['total']);
$_RESULT['vote'] = $Template->display();

//Спасибо за Ваш голос
$_RESULT[$form_name] = cms_message('Vote', 'Спасибо, Ваш голос учтен.');

/*$Template = new Template($template);

if($info['show_result'] == 1){ 
	$_RESULT['javascript'] = "document.location.href='/Vote/?id=$info[id]'";
	exit;
} 

if ($info['id'] == $topic_id) {
	$_RESULT['vote'] = cms_message('Vote', 'Спасибо, Ваш голос учтен.');
} else {
	$answers = $Vote->getAnswers();
	$Template->set($info);
	$Template->set('structure_id', $structure_id);
	$Template->set('template', $template);
	$Template->set('ajax', true);
	$Template->iterateArray('/answer/', null, $answers);
	$_RESULT['vote'] = $Template->display();
}
*/

exit;
?>