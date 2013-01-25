<?php
/**
 * Определяем имя файла и добавляем его в таблицу
 */
if (isset($_FILES[ $this->table['table_id'] ]['name']['file']['file'])) {
	$filename = $_FILES[ $this->table['table_id'] ]['name']['file']['file'];
	$this->NEW['name'] = substr($filename, 0, strrpos($filename, '.'));
}

?>