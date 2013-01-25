<?php 
/**
* ��������� ����� � �����������
*
* @package Pilot
* @subpackage Vote
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$topic_id = globalVar($_REQUEST['topic_id'], 0);
$answers  = globalVar($_REQUEST['topic'], array());
$topic_access_level = $DB->result("SELECT access_level FROM vote_topic WHERE id = '$topic_id'");

if($topic_access_level == 'registered' && (!isset($_SESSION['auth']))){
	Action::onError("������ ����������������� ������������ ����� ������������� - ����������������� �� ����� ��� ���������������");	
	
} elseif($topic_access_level == 'confirmed' && (!isset($_SESSION['auth']['confirmed']) || $_SESSION['auth']['confirmed'] == 0)){
	Action::onError("������ ������������, ������� ����������� ���� e-mail, ����� �������������");	

} elseif($topic_access_level == 'checked' && (!isset($_SESSION['auth']['checked']) || $_SESSION['auth']['checked'] == 0)){
	Action::onError("������ ������������, �������� ���������� ������������� �������, ����� �������������");	
}

$error = '';

$VoteComplex = new VoteComplex($topic_id);
if(!$VoteComplex->addVote($answers, $error)) Action::onError($error);



?>