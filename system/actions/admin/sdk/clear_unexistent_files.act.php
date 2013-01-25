<?php
/** 
 * Очистка полей в БД, которые содержат несуществующие файлы 
 * @package Pilot 
 * @subpackage CMS 
 * @author Eugen Golubenko <eugen@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */ 
$files = globalVar($_POST['file'], array());
$cleared = 0;

reset($files); 
while (list($table, $row) = each($files)) { 
	reset($files[$table]);
	while (list($field, $id) = each($files[$table])) {
		reset($files[$table][$field]);
		while (list(,$id) = each($files[$table][$field])) {
			$query = "SELECT * FROM cms_table WHERE name='$table'";
			$DB->query($query);
			if ($DB->rows == 0) continue;
			
			$query = "UPDATE `$table` SET `$field`='' WHERE id='$id'";
			$DB->update($query);
			$cleared++;
		}
	}
}

Action::setSuccess("Очищено $cleared строк в таблицах");

?>