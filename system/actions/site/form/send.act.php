<?php
/**
 * �������� ������ � ����� �� e-mail
 * @package Pilot
 * @subpackage Form
 * @author Rudenko Ilya <rudenko@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */
$form_name    = globalVar($_REQUEST['form_name'], '');
$current_path = globalVar($_REQUEST['current_path'], '');
$form_data = globalVar($_REQUEST['form'], array());
$uid = 'form_'.uniqid();


/**
 * ��������� CAPTCHA
 */
if (!Auth::isLoggedIn() && FORM_CAPTCHA && !Captcha::check(globalVar($_REQUEST['captcha_uid'], ''), globalVar($_REQUEST['captcha_value'], ''))) {
	echo '<span style="font-weight:bold;color:red;">����������� ������� ����� �� ��������</span>'; 
	exit; 
}

$Template = new Template(SITE_ROOT.'templates/form/send');
$Template->set('current_path', $current_path);

$Form = new FormLight($form_name);
$data = $Form->loadParam();
reset($data);
while (list(, $row) = each($data)) {
	$uniq_name = $row['uniq_name'];
	
	// ��������� ������������ ����� ������
	if ($row['required'] && (!isset($form_data[$uniq_name]) || empty($form_data[$uniq_name]))) {
		echo '<span style="font-weight:bold;color:red;">�� ��������� ������������ ���� <nobr>"'.$row['title'].'"</nobr></span>';
		exit;
	} elseif (!isset($form_data[$uniq_name])) {
		continue;
	} elseif (!empty($form_data[$uniq_name]) && !empty($row['regexp']) && !preg_match($row['regexp'], $form_data[$uniq_name])) {
		echo '<span style="font-weight:bold;color:red;">����������� ��������� ���� <nobr>"'.$row['title'].'"</nobr></span>';
		exit;
	}
	
	if (is_array($form_data[$uniq_name])) {
		$form_data[$uniq_name] = implode(", ", $form_data[$uniq_name]);
	}
	$Template->iterate('/row/', null, array('title' => $row['title'], 'value' => nl2br($form_data[$uniq_name])));
}

// ����������� �����

$attach = array();
if (isset($_FILES) && !empty($_FILES)) {
	reset($_FILES);
	while (list($title, $row) = each($_FILES)) {
		if ($row['error'] != 0) {
			// ���� ������� � �������, ���������� ���
			continue;
		}
		$extension = Uploads::getFileExtension($row['name']);
		Uploads::moveUploadedFile($row['tmp_name'], TMP_ROOT.$uid.'/'.$title.'.'.$extension);
		$attach[] = TMP_ROOT.$uid.'/'.$title.'.'.$extension;
	}
}

$content = $Template->display();

// ��������� ������
reset($Form->email);
while (list(,$email) = each($Form->email)) {
	$Sendmail = new Sendmail(CMS_MAIL_ID, $Form->title, $content);
	reset($attach);
	while (list(,$row) = each($attach)) {
		$Sendmail->attach($row);
	}
	$Sendmail->send($email,true);

}

// ������� ��������� �����
Filesystem::delete(TMP_ROOT.$uid.'/');


// �������� ����������
$form_reply = $DB->query_row("select from_email_id, autoreply from form where uniq_name='$form_name'");

if(!empty($form_reply['from_email_id']) && !empty($form_data['email'])) {
	$from = $form_reply['from_email_id'];
	$Sendmail = new Sendmail($from, '���� ������ �������', $form_reply['autoreply']);
	$Sendmail->send($form_data['email'],true);
}



if (!empty($Form->destination_url)) {
	$_RESULT['javascript'] = "document.location.href='$Form->destination_url'";
}


if (!empty($Form->result_text)) {
	$_RESULT['form_'.$form_name] = $Form->result_text;
}

exit;
?>