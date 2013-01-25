<?php
/**
 * CKEditor
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */
 
/**
* Определяем интерфейс для поддержки интернационализации
* @ignore 
*/
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурационный файл
*/
require_once('../../../system/config.inc.php');

$DB = DB::factory('default');

// Аунтификация при  работе с запароленными разделами
new Auth(true);
 


$id 		= globalVar($_GET['id'], 0);
$extention 	= globalVar($_GET['extention'], "php");
$table_name = globalVar($_GET['table_name'], '');
$field_name = globalVar($_GET['field_name'], '');
$css        = globalVar($_GET['css'], '');
$is_file    = globalVar($_GET['file'], 0);
$is_saved   = globalVar($_SESSION['text_editor']['is_saved'], 0);
$save_dtime = globalVar($_SESSION['text_editor']['save_dtime'], ''); 
$content 	= "";  
 
$window_height = globalVar($_GET['height'], 730);
  
/**
 * Проверка прав редактирования
 */
if (!Auth::editContent($table_name, $id)) {
	$TmplDesign = new Template(SITE_ROOT.'templates/editor/error');
	$TmplDesign->set('message', 'У Вас нет прав на редактирование этой страницы.');
	echo $TmplDesign->display();
	exit;
}


/**
 * Проверяем, не заблокирован ли файл другим пользователем
 */
$owner = CVS::isOwner($table_name, $field_name, $id);
if ($owner !== true) {
	// Вывдим сообщение о том, что страница - заблокирована
	$TmplDesign = new Template(SITE_ROOT.'templates/editor/error');
	$TmplDesign->set('message', '
		Страница, которую вы хотите редактировать открыта<br>
		пользователем <b>'.$owner['login'].'</b>.<br><br>
		Время открытия: <b>'.$owner['datetime'].'</b>.<br><br>
		Одновременное изменение информации на странице <br>двумя пользователями - невозможно.
	');
	echo $TmplDesign->display();
	exit;
}
unset($owner);

/**
 * Проверяем параметры
 */
if (empty($id)) {
	echo '<SCRIPT>alert("В редактор не поступил обязательный\n параметр id!\n\nРедактор будет закрыт.");window.close();</SCRIPT>';
	exit;
} elseif (empty($table_name)) {
	echo '<SCRIPT>alert("В редактор не поступил обязательный\n параметр table_name!\n\nРедактор будет закрыт.");window.close();</SCRIPT>';
	exit;
} 
$TmplDesign = new Template(SITE_ROOT.'templates/cms/admin/editor');
$TmplDesign->setGlobal('extention', $extention);
$TmplDesign->setGlobal('id', $id);
$TmplDesign->setGlobal('is_file', $is_file);  
$TmplDesign->setGlobal('table_name', $table_name);
$TmplDesign->setGlobal('field_name', $field_name);
$TmplDesign->setGlobal('window_height', $window_height);
$TmplDesign->setGlobal('is_saved', $is_saved);
$TmplDesign->setGlobal('save_dtime', $save_dtime);
$TmplDesign->setGlobal('dtime', date("d.m.Y h:i:s"));
$TmplDesign->set('title', 'Визуальный редактор');


if(!empty($is_file)){
	$query    = "SELECT url FROM site_structure WHERE id = '$id'";
	$site_url = $DB->result($query); 
	
	$file_url = strtolower(CONTENT_ROOT."site_structure/$site_url.".LANGUAGE_CURRENT.".".$extention);
	if(!is_writeable($file_url)){
		$TmplDesign = new Template(SITE_ROOT.'templates/editor/error');
		$TmplDesign->set('message', 'Нет прав на запись файла.');
		echo $TmplDesign->display();
		exit;
	}
	
	$content = htmlspecialchars(file_get_contents($file_url));
	$TmplDesign->set('title', "<b>Файл:</b> <u>$file_url</u>");
} else {
	$query = "select `$field_name` from `$table_name` where id='$id'";
	$content = $DB->result($query);
	
	$content = stripslashes(id2url($content, true)); 
	$TmplDesign->set('title', "<b>Поле:</b> <u>$table_name.$field_name</u>");
}
 
$TmplDesign->set('content', $content);
echo $TmplDesign->display();

?>