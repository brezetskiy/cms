<?php

/**
 * Инсталяционный скрипт.
 */

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');

$install_ok = (isset($_GET['ok'])) ? $_GET['ok'] : 0; 

if($install_ok == 0 && !isset($_POST['install_submit'])){
	require('../system/config.inc.php');
	
	$url_script_install = "http://".CMS_HOST."/installation/index.php?ok=1";
	
	$cf_exists = (defined("DB_DEFAULT_NAME") && defined("DB_DEFAULT_TYPE")) ? true : false;
	$tb_exists = $pd_exists = 0;
	
	if(!$cf_exists)
		header("Location: ".$url_script_install);
	
	if(DB_DEFAULT_TYPE == 'mysqli')
		$db_exists = @mysqli_connect(DB_DEFAULT_HOST, DB_DEFAULT_LOGIN, DB_DEFAULT_PASSWORD, DB_DEFAULT_NAME);
	else if(DB_DEFAULT_TYPE == 'mysql')
		$db_exists = @mysql_connect(DB_DEFAULT_HOST, DB_DEFAULT_LOGIN, DB_DEFAULT_PASSWORD, DB_DEFAULT_NAME);
	else $db_exists = false;
	
	if(!$db_exists){
		header("Location: ".$url_script_install);
	} else {
		mysqli_close($db_exists);
		$DB = DB::factory('default');
		$tb_count = count($DB->query("SHOW TABLES"));
		$pd_count = count($DB->query("SHOW PROCEDURE STATUS"));
		if($tb_count == 0 || $pd_count == 0)
			header("Location: ".$url_script_install);
			
		$TmplContent = new Template(SITE_ROOT.'installation/templates/reinstall');
		$TmplContent->set('db_name', DB_DEFAULT_NAME);
		$TmplContent->set('tb_count', $tb_count);
		$TmplContent->set('pd_count', $pd_count);
		$TmplContent->set('url', $url_script_install);
		echo $TmplContent->display();
	}
}
else {
		require('part.config.php');
		require(SITE_ROOT.'installation/libs/installation.class.php');
		
		$TmplContent = new Template(SITE_ROOT.'installation/templates/installation');

		$post['dbhost'] = globalVar($_POST['dbhost'], '');
		$post['dbname'] = globalVar($_POST['dbname'], '');
		$post['dbuser'] = globalVar($_POST['dbuser'], '');
		$post['dbpass'] = globalVar($_POST['dbpass'], '');

		$post['email'] = globalVar($_POST['email'], '');
		$post['password'] = globalVar($_POST['password'], '');

		$file_writeble = array(
			'config' 			=> SITE_ROOT.'installation/files/config.inc.php', 
			'dbtable'			=> SITE_ROOT.'installation/files/database.sql',
			'dbtable_procedure' => SITE_ROOT.'installation/files/database_procedure.sql'
		);
		$error = array();

		$TmplContent->set('title', 'Настройка системы');

		if (isset($_POST['install_submit'])) { 
			foreach($post as $value){
					if(empty($value) || $value==''){
						$error[] = "Все поля обязательны для заполнения!";
					}
			}

			$preg_email = '/^[a-z0-9\-\_\.\+]+@[a-z0-9\-\_\.]+\.[a-z]{2,4}$/is';
				if(!preg_match($preg_email, $post['email'])){
					$error[] = "Логин не соответствует требованиям!";
			}
			
			Installation::checkDB($post['dbhost'], $post['dbname'], $post['dbuser'], $post['dbpass'], $error);
			
			foreach($file_writeble as $value){
					if (!is_writable($value)) {
						$error[] = "Файл $value не доступен на запись";
					}
			}
			
			if(count($error) == 0){
				 $replace_match = array('{dbhost}', '{dbname}', '{dbuser}', '{dbpass}', '{user}', '{pass}');
				 $replace_value = array($post['dbhost'], $post['dbname'], $post['dbuser'], $post['dbpass'], $post['email'], md5($post['password']));
				 
				 reset($file_writeble);
				 while(list(, $filename) = each($file_writeble)){					
					Installation::updateFile($replace_match, $replace_value, $filename, $error);
				 }
				 
				 if(count($error) == 0){
						//require($file_writeble['config']);
						
					//	$DB = DB::factory('default');
						//Install::importDB($file_writeble['dbtable'], &$error);
						//Install::importProcedureDB($file_writeble['dbtable_procedure'], &$error);
						//Install::checkExistsTable();
				 }
				 
				 if(count($error) == 0){					
					//header("Location: http://".CMS_HOST."/installation/index.php");					
				 }
			}
		}

		 
		// Вызываем шаблон
		$TmplContent->set('dbhost', $post['dbhost']);
		$TmplContent->set('dbname', $post['dbname']);
		$TmplContent->set('dbuser', $post['dbuser']);
		$TmplContent->set('dbpass', $post['dbpass']);

		$TmplContent->set('email', $post['email']);
		$TmplContent->set('password', $post['password']);

		foreach($error as $row){
			$errors['message'] = $row;
			$TmplContent->iterate('/error/', null, $errors);
		}
		echo $TmplContent->display();

}

?>