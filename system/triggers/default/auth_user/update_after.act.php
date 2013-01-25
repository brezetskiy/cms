<?php
/**
 * ѕосле внесени€ администратором изменений в настройки пользовател€ отключаем
 * его из системы, дл€ того, что б он залогинилс€ заново
 * когда пользователь сам мен€ет свои параметры, то делать этого не надо, так как
 * пользователь не может изменить свои привилегии
 */
if ($this->NEW != $this->OLD) {
	$DB->delete("delete from auth_online where user_id='".$this->NEW['id']."'");
	
	// ѕослать пользователю уведомление, если администратор проверил его аккаунт
	if ($this->OLD['checked'] != 1 && $this->NEW['checked'] == 1) {
		$TmplMail = new TemplateDB('cms_mail_template', 'user', 'checked_notify', LANGUAGE_SITE_DEFAULT);
		$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', '¬аш аккаунт был проверен администратором'), $TmplMail->display());
		$Sendmail->send($this->NEW['email']);
	}
}


require($this->triggers_root . "insert_after.act.php");

?>