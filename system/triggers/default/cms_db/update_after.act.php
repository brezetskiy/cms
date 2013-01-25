<?php 
/**
* Изменение пути к триггеру при изменении названия таблицы
* @package Main_Actions
* @subpackage Triggers
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

if ($this->NEW['alias'] != $this->OLD['alias']) {
	$source = TRIGGERS_ROOT . $this->OLD['alias'].'/';
	$destination = TRIGGERS_ROOT . $this->NEW['alias'].'/';
	
	if (file_exists($destination)) {
		// Директория назначения - существует
		Action::setError(cms_message('CMS', 'Не удалось переименовать директорию с триггерами, так как директория назначения уже существует.'));
	} elseif (file_exists($source)) {
		Filesystem::rename($source, $destination, false);
		Action::setLog(cms_message('CMS', 'Директория с триггерами успешно переименована.'));
	}
}

// Обновляем конфиг
//Install::updateMyConfig();

?>