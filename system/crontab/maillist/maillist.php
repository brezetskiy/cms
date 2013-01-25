<?php 

/**
 * Рассылка почты
 * 
 * @package Pilot
 * @subpackage Maillist
 * @version 4.0
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2004
 * 
 * @cron ~/15 * * * *
 */

define('CMS_INTERFACE', 'ADMIN');
chdir(dirname(__FILE__));

require_once('../../config.inc.php');
$DB = DB::factory('default');
ini_set('memory_limit', '512M');

// Блокировка паралельного запуска скрипта
Shell::collision_catcher();

 
/**
 * Ставим в очередь письма, которые выполняются по расписанию
 */ 
$task = $DB->query("
	select id, message_id, `repeat` from maillist_task 
	where _next < now() and (date_to > current_date() or date_to is null)
");

reset($task);
while (list(,$row) = each($task)) {
	$amount = Maillist::queue($row['message_id'], false);
	
	/**
	 * Сохраняем информацию в логе
	 */ 
	$DB->insert("insert into maillist_task_log (task_id, amount) values ('$row[id]', '$amount')");
	
	/**
	 * Определяем следующую дату выполнения задачи
	 */ 
	$next = "NULL";
	if ($row['repeat'] == 'hourly') {
		$next = "_next + INTERVAL 1 HOUR";
	} elseif ($row['repeat'] == 'daily') {
		$next = "_next + INTERVAL 1 DAY";
	} elseif ($row['repeat'] == 'weekly') {
		$next = "_next + INTERVAL 1 WEEK";
	} elseif ($row['repeat'] == 'monthly') {
		$next = "_next + INTERVAL 1 MONTH";
	} elseif ($row['repeat'] == 'yearly') {
		$next = "_next + INTERVAL 1 YEAR";
	}
	
	$DB->update("update maillist_task set _next=$next where id='$row[id]'");
}


if (rand(0,100) > 95) {
	$DB->delete("delete from maillist_task_log where tstamp < now() - interval 1 month");
}


$mailserver = $DB->query_row("select * from cms_mail_account where id='".MAILLIST_MAIL_ID."'");
$stoplist = $DB->result("select sender_email from cms_mail_account where id='".MAILLIST_STOPLIST_MAIL_ID."'");

$SMTP = new SMTP($mailserver['smtp_host'], $mailserver['smtp_port'], $mailserver['smtp_login'], $mailserver['smtp_password'], $mailserver['smtp_auth']);

echo "[i] Starting ".date('Y-m-d H:i:s')."\n";
$messages = $DB->query("SELECT message_id FROM maillist_queue WHERE delivery='wait'", 'message_id');

reset($messages); 
while(list($message_id, ) = each($messages)) {
	
	if (CVS::isLocked('maillist_message', $message_id)) {
		echo "[e] Message #$message_id locked by another user.\n";
		continue;
	}
	
	$Maillist = new Maillist($message_id);
	if (strlen($Maillist->content) < 100) {
		echo "[e] Please create message\n";
		continue;
	}
	
	/** 
	 * Обрамляем текст сообщения шаблоном
	 */
	$Template = new TemplateDB('cms_mail_template', 'maillist', 'message');
	
	/**
	 * Отправка сообщений
	 */ 
	$emails = $Maillist->getQueue();
	$total_rows = count($emails);
	 
	reset($emails);
	while (list($counter, $rcpt) = each($emails)) {
		echo "[i][$counter/$total_rows] Sending mail to $rcpt[email] ... ";
		
		$TmplContent = new TemplateString($Maillist->content);
		if (!empty($rcpt['param'])) {
			$param = unserialize($rcpt['param']);
			$TmplContent->set($param);
		} 
		
		$Template->set('content', $TmplContent->display());
		
		$Sendmail = new Sendmail(MAILLIST_MAIL_ID, $Maillist->subject, $Template->display());
		$Sendmail->addHeaders(array(
			'From' 		 => $Maillist->from, 
			'Errors-To'  => $stoplist, 
			'Reply-To'   => $Maillist->reply_to,
			'Precedence' => 'bulk'
		));
		
		$attach = $Maillist->getAttachments();
		
		reset($attach);
		while (list(,$row) = each($attach)) {
			$Sendmail->attach($row['file'], $row['name']);
		}
		
		$result = $Sendmail->send($rcpt['email'], true);
		echo "$result[delivery]\n";
		
		if (!in_array($result['delivery'], array('error', 'wait', 'ok'))) {
			$result['delivery'] = 'error';
		}
		  
		$DB->update("
			UPDATE maillist_queue SET delivery='$result[delivery]' 
			WHERE message_id='$rcpt[message_id]' AND email='$rcpt[email]'
		");
	}
}


echo "[i] Finish ".date('Y-m-d H:i:s')."\n";


