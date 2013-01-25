<?php
/**
* Сохранение данных
* @package Pilot
* @subpackage CMS
* @version 5.0
* @author Miha Barin <barin@delta-x.ua>
* @copyright Delta-X, 2010
*/

$id    = globalVar($_REQUEST['id'], 0);
$is_file    = globalVar($_REQUEST['is_file'], 0);
$table_name = globalVar($_REQUEST['table_name'], '');
$field_name = globalVar($_REQUEST['field_name'], '');
$extention  = globalVar($_REQUEST['extention'], "php");
$content    = globalVar($_REQUEST['content'], "");
$content    = stripslashes(trim($content));

if(empty($id)){
	Action::onError("Ошибка: не поступил обязательный параметр id.");
}  

if(!empty($is_file)){
	if(empty($content)){
		Action::onError("Ошибка: вы пытаетесь сохранить пустой файл.");
	}
	
	$query    = "SELECT url FROM site_structure WHERE id = '$id'";
	$site_url = $DB->result($query); 
	
	$file_url = strtolower(CONTENT_ROOT."site_structure/$site_url.".LANGUAGE_CURRENT.".".$extention);
	if(!is_writeable($file_url)){
		Action::onError("Ошибка: редактируемый файл не существует."); 
	}
	
	$handle = fopen($file_url, "w");
	if(!$handle){
		Action::onError("Ошибка: не удалось открыть файл на запись."); 
	}
	
	if (fwrite($handle, $content) === FALSE) {
		Action::onError("Ошибка: невозможно записать контент в файл."); 
	}
} else {
	$content = addslashes($content); 
	$query = "update `$table_name` SET `$field_name` = '$content' where id='$id'";
	$DB->update($query);
} 

$_SESSION['text_editor']['is_saved']   = 1;
$_SESSION['text_editor']['save_dtime'] = date("d.m.Y H:i:s");


?>