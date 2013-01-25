<?php 
/**
* Удаляет аттачи, прикрепленные к письму
* @package Maillist
* @subpackage Triggers
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2005
*/

$available_languages = preg_split("/[^a-z]+/", LANGUAGE_SITE_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);

$query = "SELECT id, file FROM maillist_attachment WHERE message_id='".$current_id."'";
$extensions = $DB->fetch_column($query, 'id', 'file');

reset($extensions);
while(list($id, $extension) = each($extensions)) {
	
	$file = strtolower('maillist_attachment/file/'.Uploads::getIdFileDir($id)).'.'.$extension;
	
	Action::setLog(cms_message('Maillist', 'Удален аттач %s.', $file));
	
	if (is_file(UPLOADS_ROOT.$file)) {
		unlink(UPLOADS_ROOT.$file);
	}
	
}

?>