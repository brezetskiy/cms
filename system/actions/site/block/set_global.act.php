<?
/**
 * Устанавливает блок, как сквозной
 * @package Pilot
 * @subpackage Block
 * @author Miha Barin <barin@id.com.ua>
 * @copyright Delta-X, ltd. 2010
 */

$name = globalVar($_REQUEST['name'], '');
$url  = globalVar($_REQUEST['url'], '');

$DB->query("DELETE FROM block WHERE uniq_name = '$name' AND is_through = 0 AND url = '$url'");  
$through_block = $DB->query_row("SELECT id, content_".LANGUAGE_CURRENT." as content, is_through FROM block WHERE uniq_name = '$name' AND is_through = 1");

// Новый блок
if($DB->rows == 0){
	$through_block['id'] 	  = $DB->insert("INSERT INTO block SET uniq_name = '$name', title_".LANGUAGE_CURRENT." = '{$name}'");
	$through_block['content'] = "<div style='margin:10px; color:#999; text-align:center; font-size:10px;'>Новый блок.<br/>Пожалуйста, добавьте контент.</div>";
} 
 
$edit_link    = "<a href=\"javascript:void(0);\" onclick=\"EditorWindow(\'event=editor/content&id={$through_block['id']}&table_name=block&field_name=content_".LANGUAGE_CURRENT."\', \'editor{$through_block['id']}\'); return false;\"><img src=\"/img/block/edit.png\" border=\"0\"> Редактировать</a>"; 

$through_link = "<a href=\"javascript:void(0);\" onclick=\"AjaxRequest.send(null, \'/action/block/set_local/\', \'Локальный...\', true, {\'name\':\'{$name}\', \'url\':\'$url\'}); return false;\"><img src=\"/img/block/green.png\" border=\"0\" title=\"Сквозной\"></a>";

$_RESULT['javascript']  = "$('#".BLOCK_PREFIX."_{$name}_edit').html('$edit_link');";
$_RESULT['javascript'] .= "$('#".BLOCK_PREFIX."_{$name}_is_through').html('$through_link');";
$_RESULT[BLOCK_PREFIX.'_'.$name.'_content'] = $through_block['content'];   
    
?>