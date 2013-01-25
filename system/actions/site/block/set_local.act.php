<?
/**
 * Устанавливает блок, как локальный
 * @package Pilot
 * @subpackage Block
 * @author Miha Barin <barin@id.com.ua>
 * @copyright Delta-X, ltd. 2010
 */

$name = globalVar($_REQUEST['name'], '');
$url  = globalVar($_REQUEST['url'], '');

$query = "
	SELECT 
		id, 
		content_".LANGUAGE_CURRENT." as content, 
		title_".LANGUAGE_CURRENT." as title 
	FROM block 
	WHERE uniq_name = '$name'
		  AND is_through = 1
";
$through_block = $DB->query_row($query);  
if($DB->rows == 0){
	exit;
}

/**
 * Создаем локальный блок
 */
$query = "
	INSERT INTO block SET uniq_name = '{$name}',
						  content_".LANGUAGE_CURRENT." = '{$through_block['content']}',
						  title_".LANGUAGE_CURRENT."   = '{$through_block['title']}',
						  is_through = 0, 
						  url        = '$url'
";
$local_id = $DB->insert($query); 

$edit_link    = "<a href=\"javascript:void(0);\" onclick=\"EditorWindow(\'event=editor/content&id={$local_id}&table_name=block&field_name=content_".LANGUAGE_CURRENT."\', \'editor{$local_id}\'); return false;\"><img src=\"/img/block/edit.png\" border=\"0\"> Редактировать</a>"; 

$through_link = "<a href=\"javascript:void(0);\" onclick=\"AjaxRequest.send(null, \'/action/block/set_global/\', \'Сквозной...\', true, {\'name\':\'{$name}\', \'url\':\'$url\'}); return false;\"><img src=\"/img/block/red.png\" border=\"0\" title=\"Локальный\"></a>";

$_RESULT['javascript']  = "$('#".BLOCK_PREFIX."_{$name}_edit').html('$edit_link');";
$_RESULT['javascript'] .= "$('#".BLOCK_PREFIX."_{$name}_is_through').html('$through_link');";
$_RESULT['javascript'] .= "EditorWindow('event=editor/content&id={$local_id}&table_name=block&field_name=content_".LANGUAGE_CURRENT."', 'editor{$local_id}');";  


?>