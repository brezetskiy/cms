<?php
/*

// Информация о таблице
$query = "select name as table_name, db_id from cms_table where id='".$this->NEW['table_id']."'";
$info = $DB->query_row($query); 

// Обновление структуры таблицы
$cmsDB = new cmsDB($info['db_id']);
$cmsDB->updateTable($info['table_name']);
$cmsDB->buildTableStatic();
$cmsDB->buildFieldStatic();
$cmsDB->checkTable($this->NEW['table_id']);
*/
?>