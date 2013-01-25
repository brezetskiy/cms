<?php
/** 
 * Отсылка уведомления об обновлении данных пользователя или 
 * уведомления о регистрации нового пользователя. Обязательные параметры:
 * $old - старые значения, $mailto - e-mail администратора, $user_id - id пользователя
 * @package Pilot
 * @subpackage User
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */


if(AUTH_USER_NOTIFY_NEW){

	$Template = new TemplateDB('cms_mail_template', 'User', 'notification');
	$Template->set('id', $user_id);
	
	// Название полей в БД
	$query = "
		SELECT name, title_".LANGUAGE_CURRENT." AS title
		FROM cms_field 
		WHERE table_id=(SELECT id FROM cms_table WHERE name='auth_user' AND db_id=1)
		HAVING title!=''
		ORDER BY priority ASC
	";
	$data = $DB->query($query);
	
	// Новые значения
	$query = "SELECT * FROM auth_user WHERE	id='".$user_id."'";
	$new = $DB->query_row($query);
	
	reset($data);
	while(list(,$row) = each($data)) {
		$old_val = (isset($old[$row['name']])) ? $old[$row['name']] : '';
		$new_val = (isset($new[$row['name']])) ? $new[$row['name']] : '';
		$style = ($old_val != $new_val) ? 'font-weight:bold;color:red;' : '';
		$Template->iterate('/row/', null, array(
				'key' => $row['title'],
				'old' => $old_val,
				'new' => $new_val,
				'style' => $style
			)
		);
	}
	
	$Sendmail = new Sendmail(CMS_MAIL_ID, cms_message('CMS', 'Регистрация на %s: подтверждение e-mail', CMS_HOST), $Template->display());
	$Sendmail->send($mailto, false);
}


?>