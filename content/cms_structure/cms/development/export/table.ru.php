<?php
/**
 * Экспорт данных из таблицы
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 1993-2005
 */

define('TABLE_ID', globalVar($_GET['table_id'], 0));
define('PARENT_ID', globalVar($_GET['parent_id'], 0));

if (isset($_GET['export_id']) && !is_array($_GET['export_id'])) {
	$export_id = preg_split("/[^0-9]+/", $_GET['export_id'], -1, PREG_SPLIT_NO_EMPTY);
} elseif (!isset($_GET['export_id'])) {
	$export_id = array();
}

/**
 * Определяем связи таблиц
 * @param int $table_id
 * @return void
 */
function relations($table_id) {
	global $DB, $relations;
	static $indent = 0;
	$query = "
		SELECT tb_table.id, tb_table.name
		FROM cms_table AS tb_table
		WHERE tb_table.Parent_field_id IN (SELECT id FROM cms_field WHERE fk_table_id='".$table_id."')
	";
	$tables = $DB->query($query);
	$indent++;
	reset($tables);
	while(list(,$row) = each($tables)) {
		$relations[] = array(
			'indent' => str_repeat('-', $indent),
			'id' => $row['id'],
			'name' => $row['name']
		);
		
		// Блокировка от зацикливания
		if ($row['id'] == $table_id) continue;
		
		// Поиск ссылающихся таблиц
		relations($row['id']);
	}
	$indent--;
}

// Определяем информацию по основной таблице
$query = "
	SELECT
		id,
		name,
		'' AS indent
	FROM cms_table
	WHERE id='".TABLE_ID."'
";
$relations = $DB->query($query);

relations(TABLE_ID);

// Размер окна со списком таблиц
$TmplContent->set('select_size', count($relations));

// Выводим список связанных таблиц
reset($relations);
while(list(,$row) = each($relations)) {
	$TmplContent->iterate('/table/', null, $row);
}

// hidden поля с номерами разделов, которые необходимо экспортировать
reset($export_id);
while(list(,$id) = each($export_id)) {
	$TmplContent->iterate('/hidden/', null, array('id' => $id));
}


?>