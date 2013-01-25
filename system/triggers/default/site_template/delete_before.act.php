<?php
/**
* Удаляет файл с шаблоном, до того как удалит запись в таблице
* @package Main_Temaplates
* @subpackage Actions
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

$query = "select name from site_template_group where id='".$this->OLD['group_id']."'";
$group = $DB->result($query);

$files = Filesystem::getDirContent(SITE_ROOT.'design/'.$group.'/', false, false, true);
reset($files); 
while (list(,$file) = each($files)) { 
	if (substr($file, 0, strlen($this->OLD['name'])) == $this->OLD['name']) {
		Filesystem::delete(SITE_ROOT."design/$group/$file");
	}
}

?>