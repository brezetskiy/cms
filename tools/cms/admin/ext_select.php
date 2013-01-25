<?php
/**
 * Выбор разделовв через подгружаемое меню
 * @package Pilot
 * @subpackage CMS
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

/**
 * Определяем языковой интерфейс
 * @ignore 
 */
define('CMS_INTERFACE', 'ADMIN');

/**
* Конфигурация
*/
require_once('../../../system/config.inc.php');
$DB = DB::factory('default');

// Аунтификация при  работе с запароленными разделами
new Auth(true);


/**
* Определяет параметры таблицы
* @param int $table_id
* @return array
*/
function get_table_data($table_id) {
	global $DB;
	
	$data = $DB->query_row("
		SELECT
			UPPER(tb_db.alias) AS db_alias,
			tb_table.name AS table_name,
			tb_parent_field.name AS parent_field,
			IF(tb_show._is_multilanguage, CONCAT(tb_show.name, '_', tb_language.code), tb_show.name) AS show_field,
			IF(tb_order._is_multilanguage, CONCAT(tb_order.name, '_', tb_language.code), tb_order.name) AS order_field,
			tb_table.fk_order_direction AS order_direction,
			tb_parent_table.name AS parent_table
		FROM cms_table AS tb_table
		INNER JOIN cms_db as tb_db ON tb_db.id = tb_table.db_id
		INNER JOIN cms_interface AS tb_interface ON tb_interface.id=tb_table.interface_id
		INNER JOIN cms_language AS tb_language ON tb_language.id=tb_interface.default_language
		INNER JOIN cms_field AS tb_show ON tb_show.id = tb_table.fk_show_id
		LEFT JOIN cms_field AS tb_order ON tb_order.id=tb_table.fk_order_id
		LEFT JOIN cms_field AS tb_parent_field ON tb_parent_field.id=tb_table.parent_field_id
		LEFT JOIN cms_table AS tb_parent_table ON tb_parent_table.id=tb_parent_field.fk_table_id
		WHERE tb_table.id='".$table_id."'
	");
	
	if(!empty($data['db_alias'])){
		$data['db_name'] = db_config_constant("name", $data['db_alias']); 
	}
	
	if (empty($data['order_field'])) {
		$data['order_field'] = $data['show_field'];
	}
	
	return $data;
}



if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
	define('AJAX_LOADER', 1);
} else {
	define('AJAX_LOADER', 0);
}

if (AJAX_LOADER == 1) {
	$JsHttpRequest = new JsHttpRequest("windows-1251");
}

/**
 * Типизируем принятые параметры
 */
// id раздела, который выбран (его необходимо открыть и подсветить)
define('ID', globalVar($_REQUEST['id'], 0));

// Название INPUT поля, в которое будет добавлена информация
define('FIELD_NAME', globalVar($_REQUEST['field_name'], ''));

// id раздела, который необходимо открыть
define('OPEN_ID', globalVar($_REQUEST['open_id'], 0));

// id таблицы, которая указана во внешнем ключе
define('TABLE_ID', globalVar($_REQUEST['table_id'], 0));

// id таблицы, с которой была открыта данная страница
define('TABLE_REFFERER', globalVar($_REQUEST['table_refferer'], 0));

$TmplDesign = new Template(SITE_ROOT.'templates/cms/admin/ext_select');

/**
 * Определяем данные из какой таблицы необходимо выводить
 */
$current_table = TABLE_ID;
$current_id = OPEN_ID;

do {
	$prev_table = $current_table;
	$prev_id = $current_id;
	
	/**
	 * Определяем id поля, которое необходимо открыть
	 */
	$data = get_table_data($prev_table);
	
	if (!empty($data['parent_field'])) {
		$query = "
			SELECT `$data[parent_field]` AS current_id
			FROM `$data[db_name]`.`$data[table_name]`
			WHERE id='$current_id'
		";
		$current_id = $DB->result($query);
	} else {
		$current_id = 0;
	}
	
	unset($data);
	
	// Определяет id родительской таблицы
	$current_table = cmsTable::getParentTable($current_table);
} while (!empty($current_table) && $current_table != $prev_table && $current_table != TABLE_REFFERER);

define('CURRENT_TABLE_ID', $prev_table);

$table_data = get_table_data(CURRENT_TABLE_ID);

/**
 * Определяем какие поля открывать для таблиц, которые ссылаются сами на себя
 */
$open_list = array();
if ($table_data['parent_table'] == $table_data['table_name']) {
	$current_id = OPEN_ID;
	do {
		$query = "
			SELECT `$table_data[parent_field]` AS open_list
			FROM `$table_data[db_name]`.`$table_data[table_name]`
			WHERE id='$current_id'
		";
		$open_list[] = $current_id = $DB->result($query);
	} while ($current_id != 0);
}

/**
 * Выбираем значения из таблицы для вывода
 */
$where_clause = (!empty($table_data['parent_field'])) ? " WHERE `$table_data[parent_field]`='".ID."' ":"";
$query = "
	SELECT 
		tb_current.id,
		tb_current.`$table_data[show_field]` AS name
	FROM `$table_data[db_name]`.`$table_data[table_name]` AS tb_current
	$where_clause
	ORDER BY tb_current.`$table_data[order_field]` $table_data[order_direction]
";
$data = $DB->query($query);

// Выводим, что в данном разделе нет подразделов
if (ID != 0 && $DB->rows == 0) {
	$TmplDesign->set('sub_units', 'нет подразделов');
}

reset($data);
while (list(, $row) = each($data)) {
	
	// Выделяем текущий раздел
	$row['class'] = (OPEN_ID == $row['id'] && TABLE_ID == CURRENT_TABLE_ID) ? 'class="selected"' : '';
	
	// Выводим элемент дерева
	if (TABLE_ID != CURRENT_TABLE_ID || $table_data['parent_table'] == $table_data['table_name']) {
		$row['childs'] = 1;
		// Флаг для открытия данного раздела (для обычных таблиц и для ссылающихся на себя таблиц)
		if ($row['id'] == $prev_id || in_array($row['id'], $open_list)) {
			if (AJAX_LOADER == 0) {
				$TmplDesign->set('expand_list', "expand('$row[id]', '".TABLE_ID."', '".CURRENT_TABLE_ID."', '".OPEN_ID."', '".FIELD_NAME."');");
			} else {
				$_RESULT['id'] = $row['id'];
				$_RESULT['table_id'] = TABLE_ID;
				$_RESULT['current_table_id'] = CURRENT_TABLE_ID;
				$_RESULT['open_id'] = OPEN_ID;
				$_RESULT['field_name'] = FIELD_NAME;
			}
		}
		
	} else {
		$row['childs'] = 0;
	}
	
	$row['name_filtered'] = str_replace(array('"', "'"), array('&quot;', "\'"), $row['name']);
	
	$TmplDesign->iterate('/node/', null, $row);
}

echo $TmplDesign->display();

exit;
?>