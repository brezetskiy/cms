<?php 

if (isset($_POST['ok']))
{

$login 		= trim(globalVar($_POST['login'], ''));
$passwd 	= trim(globalVar($_POST['passwd'], ''));



if (!preg_match(VALID_LOGIN, $login)) {
	$_RESULT['javascript'] = "document.location.href='".$_POST['_return_path']."'";
} 

if (!preg_match(VALID_PASSWD, $passwd)) {
	$_RESULT['javascript'] = "document.location.href='".$_POST['_return_path']."'";
}


$query = "SELECT `auth_user`.`id`, `auth_user`.`passwd`, `auth_user`.`checked` FROM auth_user		  
		  WHERE `auth_user`.`login`='$login'
		 "; 
		 
$data = $DB->query_row($query);

if ($DB->rows == 0) {
	Auth::logLogin(0, time(), $login);
	echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".$_POST['_return_path']."'</script>");
	Action::onError(cms_message('CMS', 'Пользователя с указаным логином не существует.')); 

} elseif ($DB->rows == 1 && $data['passwd'] == md5($passwd)) {
	if ($data['checked'] == 0) {
		echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".$_POST['_return_path']."'</script>");
		Action::onError(cms_message('CMS', 'Учетная запись не подтверджена администратором.')); 
	}
	else {
		$logged_in = Auth::login($data['id'], $remember, null);
		if (!$logged_in) {
			Auth::logLogin(0, time(), $login);
			echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".$_POST['_return_path']."'</script>");
		}
		else echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".$_POST['_return_path']."'</script>");
	}
} else {
	
	
	Auth::logLogin(0, time(), $login);
	echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".$_POST['_return_path']."'</script>");
}

}

 
?>