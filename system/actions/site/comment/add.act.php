<?php
/** 
 * ���������� ������������ � News
 * @package Pilot 
 * @subpackage FAQ 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */
$table_name = globalVar($_POST['table_name'], '');
$object_id = globalVar($_POST['object_id'], 0);
$comment_id = globalVar($_POST['comment_id'], 0);
$comment = htmlspecialchars(globalVar($_POST['comment'], ''));
$user_name = globalVar($_POST['user_name'], '');
$user_email = globalVar($_POST['user_email'], '');


// �������� ���������� ������
if (!COMMENT_NOT_REGISTER && !Auth::isLoggedIn()) {
	Action::onError(cms_message('Comment', '��������� ����������� ����� ������ ������������������ ������������'));
} elseif (COMMENT_NOT_REGISTER && !Auth::isLoggedIn()) {
	if (empty($user_name)) {
		Action::onError(cms_message('Comment', '���� "���" �� ������ ���� ������'));
	}
	if (empty($user_email)) {
		Action::onError(cms_message('Comment', '���� "E-mail" �� ������ ���� ������'));
	}
	if (!Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
		Action::onError(cms_message('Comment', '����������� ������� ����� �� ��������'));
	}
	if (!preg_match(VALID_EMAIL, $user_email)) {
		Action::onError(cms_message('Comment', '����������� ������ e-mail �����'));
	}
}
if (empty($comment)) {
	Action::onError(cms_message('Comment', '���������� �� ����� ���� ������'));
}

$id = Comment::add($table_name, $comment_id, $object_id, $comment, trim(CMS_URL, '/').$_REQUEST['_return_path'], $user_name, $user_email);

do {
	$query = "CALL build_relation('comment', 'comment_id', 'comment_relation', @total_rows)";
	$DB->query($query);
	$query = "SELECT @total_rows";
	$total_rows = $DB->result($query);
} while ($total_rows > 0);

// ���������� ����������� �������������, �� ��������� ������� ��� ��� �����
if (!COMMENT_PRE_MODERATION) {
	Comment::notify($id);
}

// ����������� ��������������
$Template = new TemplateDB('cms_mail_template', 'Comment', 'admin_notify');
$Template->set('url', rtrim(CMS_URL, '/').$_REQUEST['_return_path']);
$Template->set('comment', nl2br($comment));
$message = $Template->display();
$emails = preg_split("/[\s\n\r\t,]+/", CMS_NOTIFY_EMAIL, -1, PREG_SPLIT_NO_EMPTY);

reset($emails);
while (list(,$email) = each($emails)) {
	$Sendmail = new Sendmail(CMS_MAIL_ID, $Template->title, $message);
	$Sendmail->send($email);
}


?>