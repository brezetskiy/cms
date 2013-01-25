<?php
/**
 * Статистика голосовавших
 * @package Pilot
 * @subpackage Vote
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$answer_id = globalVar($_GET['answer_id'], 0);


$query = "
	SELECT 
		ip, 
		local_ip, 
		date_format(tstamp, '".LANGUAGE_DATE_SQL." %H:%i:%s') as tstamp
	FROM vote_stat
	WHERE answer_id = '$answer_id'
	ORDER BY tstamp desc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->addColumn('ip', '30%');
$cmsTable->addColumn('local_ip', '30%');
$cmsTable->addColumn('tstamp', '30%');
echo $cmsTable->display();
unset($cmsTable);

?>