<?php
/**
 * ����� �������� ��������������� ��������� � ��������� ������������ ���������
 * ��� �� �������, ��� ����, ��� � �� ����������� ������
 * ����� ������������ ��� ������ ���� ���������, �� ������ ����� �� ����, ��� ���
 * ������������ �� ����� �������� ���� ����������
 */
if ($this->NEW != $this->OLD) {
	$DB->delete("delete from auth_online where user_id='".$this->NEW['id']."'");
	
	// ������� ������������ �����������, ���� ������������� �������� ��� �������
	if ($this->OLD['checked'] != 1 && $this->NEW['checked'] == 1) {
		$TmplMail = new TemplateDB('cms_mail_template', 'user', 'checked_notify', LANGUAGE_SITE_DEFAULT);
		$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', '��� ������� ��� �������� ���������������'), $TmplMail->display());
		$Sendmail->send($this->NEW['email']);
	}
}


require($this->triggers_root . "insert_after.act.php");

?>