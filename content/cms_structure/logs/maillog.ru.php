<?php
/**
 * Почтовый лог
 * @package Pilot
 * @subpackage CMS
 * @author Markovskiy Dima <dima@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

if(rand(0, 100) > 90) {
	$query = "delete from cms_mail_queue where send_dtime < now() - interval 30 day";
	$DB->delete($query); 
}

function setdate($row) {
	$row['message'] = strip_tags(preg_replace("~<style[^>]*>.+<\/style>~ismU", '', $row['message']));
	if(strlen($row['message']) > 200) {
		$row['message'] = substr($row['message'], 0, strpos($row['message'], ' ', 200))."...";
	}
	$row['message'] .= " <a onclick=\"CenterWindow('/tools/cms/admin/mail_queue.php?id=".$row['id']."', 'email', 750, 500, 'yes', 'no'); return false;\" href=\"javascript:void(0);\">Читать полностью</a>";
	if ($row['delivery'] != 'ok') {
		$row['message'] .= "<br><font style\"size:10px;\" color=red>$row[status_message] (id:$row[id])</font>";
	}
	return $row;
}

$query = "
	select 
		*,
		case
			when send_dtime is null then concat('<font color=blue> В очереди с ', date_format(create_dtime, '".LANGUAGE_DATE_SQL." %H:%i'), '</font>')
			when delivery='ok' then concat('<font color=green>', date_format(send_dtime, '".LANGUAGE_DATE_SQL." %H:%i'), '<br>ok</font>')
			when delivery='error' then concat('<font color=red>', date_format(send_dtime, '".LANGUAGE_DATE_SQL." %H:%i'), '<br>error</font>')
			else date_format(send_dtime, '".LANGUAGE_DATE_SQL." %H:%i')
		end as send_dtime
	from cms_mail_queue
	where message is not null 
	order by cms_mail_queue.create_dtime desc
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('row_filter', 'setdate');
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', true);
$cmsTable->addColumn('recipient', '20%', 'left', 'Адрес');
$cmsTable->addColumn('message', '55%', 'left', 'Письмо');
$cmsTable->addColumn('send_dtime', '15%', 'center', 'Отправлено');
echo $cmsTable->display();
unset($cmsTable);


?>