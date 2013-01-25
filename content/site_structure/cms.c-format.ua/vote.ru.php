<?php
/**
* ���������� �������
*
* @package Pilot
* @subpackage Vote
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

$id = globalVar($_GET['id'], 0);


/**
 * ������� ���������� �� �������� �����������
 */
if (empty($id)) {
	$id = $DB->result("
		SELECT id FROM vote_topic 
		WHERE active=1
			and topic_id = '0'
			and (start_date>=current_date() or start_date is null)
			and (end_date<=current_date() or end_date is null)
		ORDER BY priority ASC LIMIT 0,1
	");
	
	/**
	 * ���� ������ ��� ������� �������� �����������, �� ������� ���������� �� ���������� �����������
	 */
	$id = $DB->result("SELECT id FROM vote_topic WHERE topic_id = 0 AND id IN (SELECT DISTINCT topic_id FROM vote_stat) ORDER BY id DESC LIMIT 0,1");
}


/**
 * ���������� ������ ������
 */
$group = $DB->result("SELECT `group` FROM vote_topic WHERE id = '$id'");


/**
 * ��������� ������� �������
 */
if($group == 'simple'){
	$TmplContent->set('results', Vote::displayResults($id));	
	
/**
 * ��������� ������� �������
 */
} elseif($group == 'complex'){
	$VoteComplex = new VoteComplex($id); 
	$TmplContent->set('results', $VoteComplex->displayResults());	
} 



?>