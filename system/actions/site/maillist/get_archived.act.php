<?php
/**
* Рассылка сообщений по требованию пользователя
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/

// функция заменена просмотром сообщения в окне браузера
Action::onError();

$data = globalVar($_POST['data'], array());
$user = Auth::getInfo();

if (empty($user) || !isset($user['email']) || empty($user['email'])) {
	// Произошла ошибка авторизации, обратитесь к вебмастеру за подробностями.
	Action::onError(cms_message('CMS', 'Произошла ошибка авторизации, обратитесь к вебмастеру за подробностями.'));
}

/**
 * Формирование условия доступа к рассылке (registered, confirmed, checked)
 */
$access_level = array('registered');
if ($user['checked'] == 'true') {
	$access_level[] = 'checked';
}
if ($user['confirmed'] == 'true') {
	$access_level[] = 'confirmed';
}

$count = 0;
reset($data);
while (list($id) = each($data)) {
	
	// Определяем, есть лу у пользователя право получать эту рассылку
	$query = "
		SELECT * 
		FROM maillist_message 
		WHERE 
			id = '".intval($id)."' AND 
			access_level IN ('".implode("', '", $access_level)."')
	";
	$DB->query($query);
	if ($DB->rows == 0) {
		continue;
	}
	
	// Добавляем в очередь на отправку
	$query = "
		INSERT INTO maillist_queue
		SET
			message_id = '".intval($id)."',
			email = '".$_SESSION['auth']['email']."',
			expire_dtime = NOW()
		ON DUPLICATE KEY UPDATE delivery = 'wait'
	";
	$DB->insert($query);
	$count++;
}

// Вы поставлены в очередь на получение рассылок. Общее количество выбранных вами рассылок - %s.
if ($count > 0) {
	Action::setSuccess(cms_message('Maillist', 'Вы поставлены в очередь на получение рассылок.<br> Общее количество выбранных вами рассылок - %s.', $count));
} else {
	// Выберите рассылки, которые Вы бы хотели получить.
	Action::onError(cms_message('Maillist', 'Выберите рассылки, которые Вы бы хотели получить.'));
}

?>