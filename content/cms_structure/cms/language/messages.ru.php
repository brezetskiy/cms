<?php
/**
 * Вывод списка сообщений по модулям
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

// Загружаем в БД новые сообщения
$file = CACHE_ROOT.'msg_queue.txt';
if (is_file($file)) {
	$data = unserialize(file_get_contents($file));
	unlink($file);
	reset($data); 
	while (list($module_name,) = each($data)) {
		// Проверяем, есть ли такой модуль
		$query = "select id from cms_module where name='$module_name'";
		$module_id = $DB->result($query);
		if ($DB->rows == 0) {
			continue;
		}
		reset($data[$module_name]); 
		while (list(,$message) = each($data[$module_name])) {
		 	 $query = "
		 	 	insert ignore into cms_message (module_id, message_ru)
		 	 	values ('$module_id', '".addslashes($message)."')
		 	 ";
		 	 $DB->insert($query);
		}
	}
}

// Формируем cache для сообщений
$available_languages = preg_split("/,/", LANGUAGE_ALL_AVAILABLE, -1, PREG_SPLIT_NO_EMPTY);
$query = "select id, name from cms_module";
$module = $DB->query($query);
reset($module);
while (list(,$row) = each($module)) {
	reset($available_languages); 
	while (list(,$language_current) = each($available_languages)) {
		$query = "select checksum, message_$language_current as message from cms_message where module_id='$row[id]'"; 
		$message = $DB->fetch_column($query, 'checksum', 'message');
		if ($DB->rows > 0) {
			file_put_contents(CACHE_ROOT.'msg_'.strtolower($row['name']).'.'.$language_current.'.txt', serialize($message));
		}
	}
}

$query = "
	SELECT 
		tb_message.id, 
		tb_message.message_".LANGUAGE_CURRENT." AS message,
		tb_module.name AS module
	FROM cms_message AS tb_message
	INNER JOIN cms_module AS tb_module ON tb_module.id=tb_message.module_id
	ORDER BY tb_message.id ASC
";
$cmsTable = new cmsShowView($DB, $query);

$cmsTable->addColumn('message', '60%');
$cmsTable->setColumnParam('message', 'order', 'tb_message.message_'.LANGUAGE_CURRENT);

$cmsTable->addColumn('module', '10%', 'left', 'Модуль');
$cmsTable->setColumnParam('module', 'order', 'module');

echo $cmsTable->display();
unset($cmsTable);


?>