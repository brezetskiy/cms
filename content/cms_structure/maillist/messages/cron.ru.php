<?php
/**
 * –асписание отправки сообщений почтовой рассылки
 * @package Pilot
 * @subpackage Maillist
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
$message_id = globalVar($_REQUEST['message_id'], 0);

$query = "
	SELECT
		id,
		date_format(dtime, '".LANGUAGE_DATE_SQL." %H:%i') as dtime,
		date_format(date_to, '".LANGUAGE_DATE_SQL."') as date_to,
		date_format(_next, '".LANGUAGE_DATE_SQL." %H:%i') as _next,
		case
			when `repeat`='none' then 'ќднократно'
			when `repeat`='daily' then '≈жедневно'
			when `repeat`='weekly' then '≈женедельно'
			when `repeat`='monthly' then '≈жемес€чно'
			when `repeat`='yearly' then '≈жегодно'
		end as `repeat`
	FROM maillist_task
	WHERE message_id='$message_id'
	ORDER BY dtime
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('dtime', '10%');
$cmsTable->addColumn('date_to', '10%');
$cmsTable->addColumn('repeat', '10%');
$cmsTable->addColumn('_next', '10%');
echo $cmsTable->display();


$query = "
	SELECT
		date_format(tb_log.tstamp, '".LANGUAGE_DATE_SQL." %H:%i') as tstamp,
		tb_log.amount
	FROM maillist_task_log as tb_log
	INNER JOIN maillist_task as tb_task on tb_task.id=tb_log.task_id
	WHERE tb_task.message_id='$message_id'
	ORDER BY tb_log.tstamp DESC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('show_parent_link', false);
$cmsTable->setParam('show_path', false);
$cmsTable->setParam('excel', false);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);
$cmsTable->addColumn('tstamp', '10%', 'left');
$cmsTable->addColumn('amount', '10%');
echo $cmsTable->display();



?>