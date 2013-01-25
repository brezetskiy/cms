<?php

/**
 * После внесения администратором изменений в настройки пользователя отключаем
 * его из системы, для того, что б он залогинился заново
 * когда пользователь сам меняет свои параметры, то делать этого не надо, так как
 * пользователь не может изменить свои привилегии
 */
$query = "delete from auth_online where user_id='".$this->OLD['id']."'";
$DB->delete($query);


if (is_module('Hosting') || is_module('Billing')) {
	/**
	 * Хостинг пользователей удалять нельзя.
	 */
	$new_login = $this->OLD['login'].'-del';
	$query = "select * from auth_user where login = '$new_login' or email = '$new_login'";
	$DB->query($query);
	if ($DB->rows > 0) {
		$new_login .= '-'.strtolower(Misc::randomKey(10));
	}
	
	$new_email = $this->OLD['email'].'-del';
	$query = "select * from auth_user where login = '$new_email' or email = '$new_email'";
	$DB->query($query);
	if ($DB->rows > 0) {
		$new_email .= '-'.strtolower(Misc::randomKey(10));
	}
	
	$query = "
		update auth_user set 
			active = 0,
			login = '$new_login',
			email = '$new_email'
		where id = '$current_id'
	";
	$DB->update($query);
	
	Action::onError("Изменены данные пользователя: логин - $new_login, email - $new_email");
}