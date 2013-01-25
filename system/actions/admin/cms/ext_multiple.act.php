<?php
/** 
 * Страница, которая подгружает дерево внешних ключей для поля типа ext_select
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2006
 * @todo 
 * 1. Поставить проверку на привелегии.
 * 2. Оптимизировать скрипт.
 * 4. Протестить на рекурсивных таблицах
 * 6. Испытатьт вложенность в 5 уровней
 */ 

/**
 * Уровень, на котором находится таблица, которую разворачиваем
 */
define('LEVEL', globalVar($_REQUEST['level'], 0));

/**
 * id таблицы, на которую указывает ссылающееся поле. 
 * Этот параметр необходим для построения дерева
 */
define('FK_TABLE_ID', globalVar($_REQUEST['fk_table_id'], 0));

/**
 * Значение аттрибута name в элементе checkbox
 */
define('CHECKBOX_NAME', globalVar($_REQUEST['checkbox_name'], ''));

/**
 * id ряда, который редактируется. Используется для определения значений,
 * которые уже есть в данном поле. После чего скрипт пытается развернуть все уровни
 * родителей, что б показать все выбранные поля. Сразу при загрузке основной таблицы.
 */
define('MASTER_ID', globalVar($_REQUEST['master_id'], 0));

/**
 * id записи, щелчёк по которой привёл к открытию данного подменю.
 * Используется для выборки значений, которые отображаются в подменю.
 */
define('PARENT_ID', globalVar($_REQUEST['parent_id'], 0));

/**
 * id слоя, в котором будет отображатся содержимое, сформированное данным скриптом
 * Используется для смены + на точку, если в данном разделе нет подразделов.
 */
define('PARENT_ELEMENT', globalVar($_REQUEST['parent_element'], ''));

/**
 * Название таблицы, в которой содержаться связи
 * 
 */
define('RELATION_TABLE_NAME', globalVar($_REQUEST['relation_table_name'], ''));

/**
 * Название колонки в таблице RELATION_TABLE_NAME, в которая указывает на таблицу FK_TABLE_ID
 *
 */
define('RELATION_SELECT_FIELD', globalVar($_REQUEST['relation_select_field'], ''));

/**
 * Название колонки в таблице RELATION_TABLE_NAME, в которой хранится значение MASTER_ID
 *
 */
define('RELATION_PARENT_FIELD', globalVar($_REQUEST['relation_parent_field'], ''));

/**
 * перечень родительских таблиц, используется для:
 * 1. Определения названий таблиц и имён полей, которые являются родительскими в таблице
 * 2. Определения номера текущей таблицы.
 * 3. Определения наличия дочерних таблиц у таблицы, которую сейчас отображаем.
 */
$parent_tables = cmsTable::getParentTables(FK_TABLE_ID);

// Убираем из списка родительских колонок те уровни, которые уже открыты
if (count($parent_tables) > 1) {
	for($i=0; $i<LEVEL-1;$i++) {
		array_shift($parent_tables);
	}
}

// Информация о таблице, которую мы выводим
$table = cmsTable::getInfoById(reset($parent_tables));

$DBServer = DB::factory($table['db_alias']);


// определяем перечень элементов, которые необходимо разворачивать
if ($table['id'] != $table['parent_table_id']) {
	Misc::extMultipleOpen($DBServer, MASTER_ID, $parent_tables, RELATION_TABLE_NAME, RELATION_SELECT_FIELD, RELATION_PARENT_FIELD);
} else {
	$query = "
		DROP TEMPORARY TABLE IF EXISTS `tmp_open`;
		CREATE TEMPORARY TABLE `tmp_open` (id INT UNSIGNED NOT NULL, PRIMARY KEY (id)) ENGINE=MyISAM;
	";
	$DBServer->multi($query);
	$query = "
		INSERT IGNORE INTO tmp_open (id) 
		SELECT tb_optimized.parent
		FROM `".$table['relation_table_name']."` AS  tb_optimized
		INNER JOIN `".RELATION_TABLE_NAME."` AS tb_relation ON tb_optimized.id=tb_relation.`".RELATION_SELECT_FIELD."`
		WHERE 
			tb_relation.`".RELATION_PARENT_FIELD."`='".MASTER_ID."'
			AND tb_optimized.id<>tb_optimized.parent
	";
	$DBServer->insert($query);
}

$code = uniqid();
$query = "
	SELECT
		tb_main.id,
		tb_main.`$table[fk_show_name]` AS name,
		IF(tb_relation.`".RELATION_SELECT_FIELD."` IS NULL, '', 'checked') AS checked,
		IF(tb_open.id IS NOT NULL, 'true', 'false') AS open
	FROM `$table[table_name]` AS tb_main
	LEFT JOIN `".RELATION_TABLE_NAME."` AS tb_relation ON tb_relation.`".RELATION_PARENT_FIELD."`='".MASTER_ID."' AND tb_relation.`".RELATION_SELECT_FIELD."`=tb_main.id
	LEFT JOIN tmp_open AS tb_open ON tb_open.id=tb_main.id
	WHERE tb_main.`$table[parent_field_name]`='".PARENT_ID."'
	ORDER BY tb_main.`$table[fk_order_name]` ASC
";
$data = $DBServer->query($query);
if ($DBServer->rows == 0) {
	$_RESULT = array('exec' => "extMultipleNoSubmenu('".PARENT_ELEMENT."');");
//	echo '<span class="comment" style="margin-left:'.(10 * LEVEL).'px;">нет информации</span><br>';
	exit;
}

$exec = array();
reset($data);
while(list($index, $row) = each($data)) {
	if (count($parent_tables) > 1 || $table['id'] == $table['parent_table_id']) {
		$function = 'extMultiple(\''.$code.'_'.$row['id'].'\', \''.CHECKBOX_NAME.'\', '.FK_TABLE_ID.', '.(LEVEL + 1).', '.$row['id'].', '.MASTER_ID.', \''.RELATION_TABLE_NAME.'\', \''.RELATION_SELECT_FIELD.'\', \''.RELATION_PARENT_FIELD.'\');';
		$style = ' style="margin-left:'.(10 * LEVEL).'px;" ';
//		$checked = ($row['open'] === 'true') ? 'checked' : '';
		if ($table['id'] == $table['parent_table_id']) {
			echo '<input '.$style.' type="checkbox" '.$row['checked'].' name="'.CHECKBOX_NAME.'" value="'.$row['id'].'"> ';
			$style = '';
		}
		echo '<a hidefocus '.$style.' href="javascript:void();" onclick="'.$function.'; return false;"><img src="/img/shared/toc/plus.png" border="0" width="11" height="11" id="img_'.$code.'_'.$row['id'].'"> '.$row['name'].'</a><br><div style="display:none;" id="'.$code.'_'.$row['id'].'"></div>';
		if ($row['open'] == 'true') {
			$exec[] = $function;
		}
	} else {
		echo '<input '.$row['checked'].' style="margin-left:'.(10 * LEVEL).'px;" type="checkbox" name="'.CHECKBOX_NAME.'" value="'.$row['id'].'" id="'.$code.'_'.$row['id'].'"><label for="'.$code.'_'.$row['id'].'">'.$row['name'].'</label><br>';
	}
}

$_RESULT = array('exec' => implode("; ", $exec));

exit;
?>