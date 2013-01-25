<?php
/** 
 * Автоматическая отсылка письма с формы, сгенерированной в редакторе 
 * @package Pilot
 * @subpackage CKEditor
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 
$mailto = globalVar($_REQUEST['mailto'], MAILSERVER_FROM_EMAIL);
$subject = globalVar($_REQUEST['subject'], 'Message from your site');

if (!preg_match(CMS_EDITOR_MAILTO, $mailto)) {
	Action::setError('Wrong mailto field');
	Action::finish();
}


$Mime = new Mime(MAILSERVER_FROM_EMAIL, $mailto);
$Template = new TemplateDB('cms_mail_template', 'Editor', 'form');

// Добавляем переданные поля
reset($_REQUEST);
while(list($key,$val) = each($_REQUEST)) {
	if (in_array($key, array('_event', 'mailto', '_return_path', '_language', 'PHPSESSID', 'subject')) || isset($_COOKIE[$key])) {
		continue;
	}
	if (preg_match('/[а-яА-Я]+/', $key)) {
		$key = str_replace("_", ' ', $key);
	}
	$Template->iterate('/row/', null, array('title'=>$key, 'text'=>nl2br($val)));
}

$counter = 0;
reset($_FILES);
while(list($key, $val) = each($_FILES)) {
	$counter++;
	$extension = Uploads::getFileExtension($val['name']);
	$Mime->attachImage($val['tmp_name'], $counter.'.'.$extension);
	$Template->iterate('/row/', null, array('title'=>$key, 'text'=>'<img src="'.$counter.'.'.$extension.'" border="0">'));
}

$Mime->setHeader('Subject', $subject);
$Mime->setHtml($Template->display());

Mailq::queue(MAILSERVER_FROM_EMAIL, $mailto, $Mime->getHeaders(), $Mime->getMessageBody(), false);

unset($Mime);
?>