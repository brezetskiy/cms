<?php
/**
 * Список рассылаемых сообщений
 * @package Maillist
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

function cms_filter($row) {
	global $DB;
	
	$row['message_size'] = Maillist::getMessageSize($row['id']);
	$row['message_size'] = ($row['message_size'] > MAILLIST_ATTACHMENT_MAX_SIZE) ? 
		'<img src="/img/maillist/warning.png" border=0 alt="Слишком большое письмо" align=absbottom> <font color=red>'.ceil($row['message_size']/1000).' Kb'.'</font>': 
		ceil($row['message_size']/1000).' Kb';
	/**
	 * Добавляем отправителя к теме сообщения
	 */
	$row['subject'] .= "<br><span class='comment'>".$row['reply_to']."</span>";
	$row['_attach'] = ($row['attach'] > 0) ?
		"<a href='./Attachment/?message_id=$row[id]'><img src=\"/img/maillist/attach_active.png\" border=0 alt=\"Вложения\"></a>":
		"<a href='./Attachment/?message_id=$row[id]'><img src=\"/img/maillist/attach.png\" border=0 alt=\"Вложения\"></a>";
	$row['_cron'] = ($row['cron'] > 0) ?
		"<a href='./Cron/?message_id=$row[id]'><img src=\"/img/maillist/clock_active.png\" border=0 alt=\"Расписание\"></a>":
		"<a href='./Cron/?message_id=$row[id]'><img src=\"/img/maillist/clock.png\" border=0 alt=\"Расписание\"></a>";
	return $row;
}

$query = "
	SELECT 
		tb_message.id,
		reply_to,
		length(tb_message.content_".LANGUAGE_SITE_DEFAULT.") as message_size,
		(select count(*) from maillist_task where message_id=tb_message.id) as cron,
		(select count(*) from maillist_attachment where message_id=tb_message.id) as attach,
		CONCAT(
			'<a href=\"./Stat/?message_id=', tb_message.id, '\"><img border=0 src=\"/img/maillist/stat.png\" align=absbottom></a> (',
			(SELECT COUNT(*) FROM maillist_queue WHERE message_id=tb_message.id AND delivery='wait'),
			'/<span class=\"green\">',
			(SELECT COUNT(*) FROM maillist_queue WHERE message_id=tb_message.id AND delivery='ok'),
			'</span>/<span class=\"red\">',
			(SELECT COUNT(*) FROM maillist_queue WHERE message_id=tb_message.id AND delivery='error'),
			'</span>)'
		) AS status,
		html_editor(tb_message.id, 'maillist_message', 'content_".LANGUAGE_SITE_DEFAULT."', tb_message.subject) AS subject,
		DATE_FORMAT(tb_message.create_dtime, '".LANGUAGE_DATETIME_SQL."') AS create_dtime
	FROM maillist_message AS tb_message
	ORDER BY tb_message.create_dtime DESC
";
$cmsTable = new cmsShowView($DB, $query, CMS_VIEW, 'maillist_message');
$cmsTable->setParam('prefilter', 'cms_filter');
$cmsTable->addEvent('test', '/action/admin/maillist/test', false, true, true, '/design/cms/img/event/maillist/test.gif', '/design/cms/img/event/maillist/test_over.gif', 'Отправить тестовое сообщение', '');
$cmsTable->addEvent('queue', '/action/admin/maillist/queue', false, true, true, '/design/cms/img/event/maillist/queue.gif', '/design/cms/img/event/maillist/queue_over.gif', 'Разослать сообщение', 'Произвести рассылку выбраных сообщений?');
$cmsTable->addColumn('subject', '50%', null, 'Заголовок');
$cmsTable->addColumn('_attach', '10%', 'center', 'Файл');
$cmsTable->addColumn('_cron', '10%', 'center', 'Расписание');
$cmsTable->addColumn('message_size', '10%', 'center', 'Объем');
$cmsTable->addColumn('status', '10%', 'left', 'Статус');
echo $cmsTable->display();
unset($cmsTable);
?>