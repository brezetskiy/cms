<?php
header("Content-type: text/html; charset=windows-1251");

$form_name    = globalVar($_REQUEST['form_id'], '');
$task    = globalVar($_REQUEST['task'], '');

$Form = new FormLight($form_name);
$data = $Form->loadParam();
reset($data);

if(empty($form_name) && empty($task)){
	echo "<div class='form-container-top'>
				<div class='form-content'>					
					<div class='form-result' style='display:block'>
						Товар успешно добавлен в корзину!
					</div>
				</div>
				</div>
				<div class='form-container-bottom'></div>
				<div class='form-container-bottom-left'></div>
				<div class='form-container-bottom-right'></div>";
				
}
else if (empty($task)){

	// Send back the form HTML
		$output = "
			<div class='form-container-top'>
				<div class='form-content'>
					<h1 class='form-title'>".$Form->title."</h1>
					<div class='form-loading' style='display:none'></div>
					<div class='form-message' style='display:none'></div>
					<div class='form-result' style='display:none'></div>
					<div class='form'>
						<form action='/action/eda/loadform/' accept-charset='utf-8' name='".$form_name."' >";
					
		while (list(, $row) = each($data)) {
			
			$mclass='';
			if ($row['type'] != 'hidden') {
				if ($row['required']) {
					$row['title'] .= '*';
					$row['attr'] = 1;
				} else $row['attr'] = 0;
				$output .= "<label>".$row['title']."</label>";
				$mclass = "class='input'";
			}
			
			if ($row['type'] == 'passwd') 
				$row['type'] = 'password';
			$output .= "<input type='".$row['type']."' $mclass name='".$row['uniq_name']."' requir='".$row['attr']."'>";	
			
		}

		if($form_name == 'login'){
				$output .= "<div class='qu'><a href='/user/reminder/'>Забыли пароль?</a></div>";
		}
		$output .= "<div class='sumbmit'><input type='submit' value='".$Form->button."' class='button form-send'></div>
						</form>
					</div>	
				</div>
			</div>
			<div class='form-container-bottom'></div>
			<div class='form-container-bottom-left'></div>
			<div class='form-container-bottom-right'></div>";

		echo $output;
}
else if ($task == 'login'){
	
	$login 		= trim(globalVar($_POST['login'], ''));
	$passwd 	= trim(globalVar($_POST['password'], ''));

	$query = "SELECT `auth_user`.`id`, `auth_user`.`passwd`, `auth_user`.`checked` FROM auth_user		  
		  WHERE `auth_user`.`login`='$login'
		 "; 
		 
	$data = $DB->query_row($query);

	if ($DB->rows == 0) {
		//Auth::logLogin(0, time(), $login);
		echo 'Не правильно указан логин или пароль.'; 
		exit;

	} elseif ($DB->rows == 1 && $data['passwd'] == md5($passwd)) {
		if ($data['checked'] == 0) {
			echo 'Учетная запись не подтверджена администратором.'; 
			exit;
		}
		else {
			$logged_in = Auth::login($data['id'], $remember, null);
			if (!$logged_in) {
				//Auth::logLogin(0, time(), $login);
				echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".CMS_URI."'</script>");
			}
			else echo ("<script language='JavaScript' type='text/javascript'>document.location.href='".CMS_URI."'</script>");
			
			exit;
		}
	} else {
		
		//Auth::logLogin(0, time(), $login);
		echo 'Не правильно указан логин или пароль.';
		exit;
		
	}
}
else if($task == 'rss'){

	$email=trim(globalVar($_POST['email'], ''));
	if (!preg_match(VALID_EMAIL, $email)) {
		echo 'Неправильный email';
		exit;
	} else {
		$Template = new TemplateDB('cms_mail_template', 'Maillist', 'admin_add_email');
		$Template->set('email', $email);
		$content = $Template->display();
		
		$Form = new FormLight($task);
		reset($Form->email);$s='';
		while (list(,$email_a) = each($Form->email)) {
			$s .= $email_a;
			$Sendmail = new Sendmail(MAILLIST_MAIL_ID, $Form->title, $content);
			$Sendmail->send($email_a,true);
		}
			
		echo 'Спасибо, что подписались на нашу рассылку';
		exit;
	}
}
else if($task == 'registration'){

/**
 * Регистрация пользователей
 * 
 */

$form_name    = $task;
$form_data = globalVar($_REQUEST['form'], array());
$uid = 'form_'.uniqid();


$Form = new FormLight($form_name);
$data = $Form->loadParam();
reset($data);


$Template = new TemplateDB('cms_mail_template', 'User', 'admin_notify');

while (list(, $row) = each($data)) {
	$uniq_name = $row['uniq_name'];
	
	#Если по умолчанию написано в поле то обнуляем
	if ($_POST[$uniq_name] == $row['default_value'])
		$_POST[$uniq_name] =  '';
	
	// Проверяем правильность ввода данных
	
		if ($row['required'] && (!isset($_POST[$uniq_name]) || empty($_POST[$uniq_name]))) {
			echo 'Не заполнено обязательное поле "'.$row['title'].'"';
			exit;
		} elseif (!isset($_POST[$uniq_name])) {
			continue;
		} elseif (!empty($_POST[$uniq_name]) && !empty($row['regexp']) && !preg_match($row['regexp'], $_POST[$uniq_name])) {
			echo 'Неправильно заполнено поле "'.$row['title'].'"';
			exit;
		}
	

	if (is_array($_POST[$uniq_name])) {
		$_POST[$uniq_name] = implode(", ", $_POST[$uniq_name]);	
	}
}


$login = trim(globalVar($_POST['login'], ''));
$email = trim(globalVar($_POST['email'], '')); 
$name = globalVar($_POST['name'], '');
$passwd = trim(globalVar($_POST['passwd'], ''));
$city = globalVar($_POST['city'], '');
$phone = globalVar($_POST['phone'], '');
$auto_login = globalEnum($_POST['auto_login'], array('true', 'false'));
$confirm_code='';

$name = iconv("UTF-8", "WINDOWS-1251", $name); 
$email = iconv("UTF-8", "WINDOWS-1251",  $email); 
$passwd = iconv( "UTF-8", "WINDOWS-1251", $passwd); 
$login = iconv("UTF-8", "WINDOWS-1251",  $login); 
$city = iconv("UTF-8", "WINDOWS-1251",  $city); 
$phone = iconv("UTF-8", "WINDOWS-1251",  $phone); 

if (empty($login )){
	$login = $email;
}

	// Проверяем, нет ли такого пользователя
$query = "LOCK TABLES auth_user WRITE, site_structure_site_alias WRITE";
$DB->query($query);

$query = "SELECT id FROM auth_user WHERE email='$email' OR login='$email'";
$DB->query($query);
if ($DB->rows > 0) {
	echo 'Пользователь с таким электронным адресом уже зарегистрирован';	
	exit;
}

if (!empty($login)) {
	$query = "SELECT id FROM auth_user WHERE login='$login' OR email='$login'";
	$DB->query($query);
	if ($DB->rows > 0) {
		echo 'Пользователь с таким логином уже существует';		
		exit;
	}
}

/**
 * Определяем сайт, на котором регистрируется пользователь
 */

$query = "
	select site_id from site_structure_site_alias
	where url = '".globalVar($_SERVER['HTTP_HOST'], '')."'
";
$site_id = $DB->result($query, 0);
 
$query = "
	INSERT INTO auth_user 
	SET
		login = '$login',
		email = '$email',
		passwd = '".md5($passwd)."',
		passwd_plain='$passwd',
		name = '$name',
		confirmation_code = '$confirm_code',
		city = '$city',
		phone = '$phone',
		registration_dtime = NOW(),	
		site_id = '$site_id'
";
$id = $user_id = $DB->insert($query);

$query = "UNLOCK TABLES";
$DB->query($query);



$Template->set('name', $name);
$Template->set('email', $email);


$content = $Template->display();
reset($Form->email);
while (list(,$email_a) = each($Form->email)) {
	$Sendmail = new Sendmail(CMS_MAIL_ID, $Form->title, $content);
	reset($attach);
	while (list(,$row) = each($attach)) {
		$Sendmail->attach($row);
	}
	$Sendmail->send($email_a,true);

}

//Спасибо за регистрацию
$Template = new TemplateDB('cms_mail_template', 'User', 'thanks');
$Template->set('name', $name);
$Template->set('passwd', $passwd);
$Template->set('login', $login);

$Sendmail = new Sendmail(CMS_MAIL_ID, 'Спасибо за регистрацию', $Template->display());
$Sendmail->send($email,true);



$content = $Template->display();

echo 'Спасибо, что прошли регистрацию на нашем ресурсе, Вы получите подтверждение о регистрации в ближайшее время.';
exit;

}


?>