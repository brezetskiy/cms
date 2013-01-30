<?php
/**
 * Список таблиц в системе
 * @package CMS
 * @subpackage Content_Admin
 * @author Ilya Rudenko <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd. 2005
 */

/**
 * Типизируем переменные
 * @ignore 
 */
$db_alias = globalVar($_GET['db_alias'], '');
$DBServer = DB::factory($db_alias);

$db_id = $DBServer->result("SELECT id FROM cms_db WHERE alias = '$db_alias'");

define("DB_ID", $db_id);


if($DBServer instanceof dbMSSQL) {
	$TmplContent->set('db_type', 'mssql');
} elseif ($DBServer instanceof dbMySQLi) {
	$TmplContent->set('db_type', 'mysql');
}

// Определяем флажки для языков
$query = "SELECT id, code FROM cms_language";
$flags = $DB->fetch_column($query, 'code', 'id');

/**
 * Функция предварительной обработки данных
 * @ignore 
 * @param string $table
 * @return void
 */
function cms_prefilter($row) {
	global $DB, $flags;
	
	if ($row['is_disabled']) {
		$row['name'] = "<span style=\"text-decoration: line-through;\">$row[name]</span>";
	}
	
	// Использование CVS
	if ($row['use_cvs']) {
		$row['name'] .= ' <img src="/design/cms/img/icons/chart.gif" border="0" alt="CVS">';
	}
	
	$row['name'] .= "<br><span class=comment>$row[table_type]</span>";
	$trigger = array();
	if (is_file(TRIGGERS_ROOT."$row[alias]/$row[table_name]/insert_before.act.php")) $trigger[] = 'insert_before';
	if (is_file(TRIGGERS_ROOT."$row[alias]/$row[table_name]/insert_after.act.php")) $trigger[] = 'insert_after';
	if (is_file(TRIGGERS_ROOT."$row[alias]/$row[table_name]/update_before.act.php")) $trigger[] = 'update_before';
	if (is_file(TRIGGERS_ROOT."$row[alias]/$row[table_name]/update_after.act.php")) $trigger[] = 'update_after';
	if (is_file(TRIGGERS_ROOT."$row[alias]/$row[table_name]/delete_before.act.php")) $trigger[] = 'delete_before';
	if (is_file(TRIGGERS_ROOT."$row[alias]/$row[table_name]/delete_after.act.php")) $trigger[] = 'delete_after';
	if (!empty($trigger)) {
		$row['title'] .= '<br><span class="comment">'.implode(", ", $trigger).'</span>';
	}
		
	$languages = preg_split("/,/", $row['languages'], -1, PREG_SPLIT_NO_EMPTY);
	reset($languages);
	while(list($index, $language) = each($languages)) {
		if (isset($flags[$language])) {
			// Выводим картинку
			$languages[$index] = Uploads::htmlImage(Uploads::getFile('cms_language', 'file', $flags[$language], 'gif'));
		}
	}
	$row['languages'] = implode(' ', $languages);
	
	return $row;
}



/**
 * Выводим таблицу cmsTable
 */
$query = "
	SELECT
		tb_table.id,
		tb_table.is_disabled,
		CASE
			WHEN tb_table._table_type IN ('procedure', 'function') THEN tb_table.name
			WHEN tb_table._check_failed THEN CONCAT('<a href=\"./Fields/?table_id=', tb_table.id, '\">', tb_table.name, '</a> <img src=\"/design/cms/img/icons/warning.png\" align=absmiddle>')
			ELSE CONCAT('<a href=\"./Fields/?table_id=', tb_table.id, '\">', tb_table.name, '</a>')
		END AS name,
		tb_static.cms_type,
		tb_table.name as table_name,
		lower(tb_table._table_type) as table_type,
		tb_table.title_".LANGUAGE_CURRENT." AS title,
		tb_module.name AS module_name,
		IF(tb_table.use_cvs, 'Фиксировать все изменения', '') AS use_cvs,
		tb_static.languages,
		if(tb_table._is_real=1, '<input type=checkbox disabled checked>', '<input type=checkbox disabled>') as _is_real,
		tb_db.alias
	FROM cms_table AS tb_table
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
	left join cms_table_static as tb_static on tb_static.table_id=tb_table.id
	LEFT JOIN cms_module AS tb_module ON tb_module.id=tb_table.module_id
	WHERE tb_table.db_id='$db_id' and tb_table._table_type not in ('function', 'procedure')
	ORDER BY tb_module.name ASC, tb_table.name ASC
";
$cmsTable = new cmsShowView($DB, $query, 120);
$cmsTable->addEvent('xml', "/action/admin/sdk/table_xml_builder/", false, true, true, '/design/cms/img/event/table/xml.gif', '/design/cms/img/event/table/xml_over.gif', 'Скачать в формате xml', null, true);
$cmsTable->setParam('prefilter', 'cms_prefilter');
$cmsTable->setParam('subtitle', 'module_name');
$cmsTable->setParam('add', false);

$cmsTable->setParam('show_parent_link', true); 
$cmsTable->setParam('parent_link', "../?");

$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('title', '50%');
$cmsTable->addColumn('cms_type', '5%', 'center', 'Тип');
$cmsTable->addColumn('languages', '5%', 'center', 'Языки');
$cmsTable->addColumn('_is_real', '5%', 'center', 'Тип');
 

$cms_view = $cmsTable->display();
$TmplContent->set('cms_view', $cms_view);

unset($cms_view);
unset($cmsTable);

?>