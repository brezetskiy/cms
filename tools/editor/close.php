<?php
/**
* Закрывает окно редактора и разлочивает сессию с данной страницей
* @package Pilot
* @subpackage Editor
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

/**
* Определяем интерфейс для поддержки интернационализации
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
require_once('../../system/config.inc.php');

$DB = DB::factory('default');

new Auth('admin');

$id = globalVar($_GET['id'], 0);
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');

$query = "
	DELETE FROM cvs_lock 
	WHERE 
		edit_id='$id' 
		AND table_name='$table_name'
		AND field_name='$field_name' 
		AND admin_id='".$_SESSION['auth']['id']."'
	";
$DB->delete($query);

$query = "DELETE FROM cvs_lock WHERE UNIX_TIMESTAMP(dtime) + ".AUTH_TIMEOUT." < UNIX_TIMESTAMP()";
$DB->delete($query);

?>
<html>
<head>
</head>
<script>
window.close();
</script>
</html>