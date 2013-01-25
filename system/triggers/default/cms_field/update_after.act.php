<?php
/** 
 * Триггер, выполняющийся при обновлении таблицы cms_field 
 * @package CMSEdit
 * @subpackage Triggers
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 */ 

// Изменяем местонахождения UPLOADS_ROOT для данной колонки
if ($this->OLD['name'] != $this->NEW['name']) {
	
	$query = "SELECT name FROM cms_table WHERE id='".$this->OLD['table_id']."'";
	$source = UPLOADS_ROOT . $DB->result($query) .'/' . $this->OLD['name'];
	
	$query = "SELECT name FROM cms_table WHERE id='".$this->NEW['table_id']."'";
	$destination = UPLOADS_ROOT . $DB->result($query) .'/' . $this->NEW['name'];
	
	if (is_dir($source) && !file_exists($destination)) {
		Filesystem::rename($source, $destination, false);
		Action::setLog(cms_message('CMS', 'Директория с картинками успешно переименована.'));
	} elseif (is_dir($source) && file_exists($destination)) {
		Action::setError(cms_message('CMS', 'Директория с картинками %s небыла переименована, так как директория назначения уже существует.', Uploads::getURL($source)));
	} else {
		Action::setLog(cms_message('CMS', 'В таблице нет полей с файлами.'));
	}
	
	// Изменяем название колонки в БД
	$query = "
		select
			tb_table.name as table_name,
			tb_db.alias as db_alias
		from cms_table as tb_table 
		inner join cms_db as tb_db on tb_db.id=tb_table.db_id
		where tb_table.id='".$this->OLD['table_id']."'
	";
	$info = $DB->query_row($query);
	$DBServer = DB::factory($info['db_alias']);
	$query = "show create table `$info[table_name]`";
	$create = $DBServer->query_row($query);
	preg_match("/`".$this->OLD['name']."`(.+),$/ismU", $create['create table'], $matches);
	if (count($matches) > 1) {
		$query = "alter table `$info[table_name]` change column `".$this->OLD['name']."` `".$this->NEW['name']."` $matches[1]";
		$DB->query($query);
	}
}

/**
 * Обновление структуры таблицы
 */
//$query = "select name as table_name, db_id from cms_table where id='".$this->NEW['table_id']."'";
//$info = $DB->query_row($query); 
//$cmsDB = new cmsDB($info['db_id']);
//$cmsDB->updateTable($info['table_name']);
//$cmsDB->buildTableStatic();
//$cmsDB->buildFieldStatic();
//$cmsDB->checkTable($this->NEW['table_id']);

?>