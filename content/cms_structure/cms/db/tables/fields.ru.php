<?php
/**
 * Список полей в таблице
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X ltd. 2005
 */

$obligatory_update = globalVar($_REQUEST['obligatory_update'], 0);

/**
* После редактирования enum поля передается параметр field_id, в качестве родительского,
* а нам нужен table_id
*/
$field_id = globalVar($_GET['field_id'], 0);
if (!empty($field_id)) {
	$query = "SELECT table_id FROM cms_field WHERE id='".$field_id."'";
	$_GET['table_id'] = $DB->result($query);
}
$table_id = globalVar($_GET['table_id'], 0);
define('TABLE_ID', $table_id);

$query = "select db_id, module_id, name as table_name from cms_table where id='$table_id'";
$info = $DB->query_row($query);

$cmsDB = new cmsDB($info['db_id']);
if ($obligatory_update) {
	$cmsDB->updateTable($info['table_name'], 'table', $obligatory_update);
	$cmsDB->buildTableStatic();
	$cmsDB->buildFieldStatic();
}
$TmplContent->set('check_failed', implode("<p>", $cmsDB->checkTable(TABLE_ID)));




function cms_field_filter($row) {
	
	// выводим модуль
	if (!empty($row['module'])) {
		$row['name'] .= " [<font color=green>$row[module]</font>]";
	}
	
	// Колонка используется в фильтре
	if ($row['show_in_filter']) {
		$row['name'] .= ' <img src="/design/cms/img/icons/filter.gif" border="0">';
	}
	
	// Колонка используется в фильтре
	if ($row['is_reference']) {
		$row['name'] .= ' <img src="/design/cms/img/icons/reference.gif" border="0" align="absmiddle">';
	}
	
	// Обязательная ли колонка
	if ($row['stick']) {
		$row['name'] .= ' <img src="/design/cms/img/icons/save.gif" border="0">';
	}
	
	// Обязательная ли колонка
	if ($row['is_obligatory']) {
		$row['name'] .= '<font color=red>*</font>';
	}
	
	// Разделитель
	if ($row['field_type'] == 'devider'){
		$row['_class'] = "green";
	}
	
	// Выводим тип колонки
	if (is_null($row['column_type'])) {
		$row['name'] .= '<br><span class="comment">нет в таблице</span>';
	} else {
		$row['name'] .= '<br><span class="comment">'.$row['column_type'].'</span>';
	}
	
	
	return $row;
}

/**
 * Список полей
 */
$query = "
	SELECT
		tb_field.id, 
		tb_field.stick,
		tb_field.is_reference,
		tb_field.priority,
		if(tb_field._is_real=1, '<input type=checkbox disabled checked>', '<input type=checkbox disabled>') as _is_real,
		CASE 
			WHEN tb_fk_table.name IS NOT NULL THEN CONCAT(tb_field.name, '<a href=\"./?table_id=', tb_fk_table.id ,'\"><img src=\"/design/cms/img/icons/table_link.gif\" border=0 title=\"', tb_fk_table.name, '\"></a>')
			ELSE tb_field.name
		END AS name,
		tb_module.name as module,
		tb_field.show_in_filter,
		tb_field.is_obligatory,
		tb_field._data_type as column_type,
		tb_fk_table.name AS fk_table_name,
		CONCAT(
			tb_field.title_".LANGUAGE_CURRENT.", 
			'<BR><span class=\"comment\">', 
			IFNULL(tb_field.comment_".LANGUAGE_CURRENT.", ''),
			'</span>'
		) AS title,
		case
			when tb_static.cms_type='error' or tb_static.cms_type is null then concat('<font color=red>', tb_field.field_type, '</font>') 
			else tb_static.cms_type
		end as field_type,
		tb_field.show_in_filter
	FROM cms_field AS tb_field
	INNER JOIN cms_table AS tb_table ON tb_table.id=tb_field.table_id
	LEFT JOIN cms_module AS tb_module ON tb_module.id=tb_field.module_id
	LEFT JOIN cms_table AS tb_fk_table ON tb_fk_table.id=tb_field.fk_table_id
	LEFT JOIN cms_field_static as tb_static on tb_static.id=tb_field.id
	WHERE tb_field.table_id='$table_id'
	GROUP BY tb_field.id
	ORDER BY tb_field.priority ASC
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('prefilter', 'cms_field_filter');
$cmsTable->setParam('show_parent_link', true);
$cmsTable->setParam('parent_link', "../?db_alias=".$cmsDB->getAlias());
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('title', '50%');
$cmsTable->addColumn('field_type', '10%', 'left');
$cmsTable->addColumn('_is_real', '50');
$table_info = $cmsTable->getTableInfo();
$cms_view = $cmsTable->display();

$TmplContent->setGlobal('table_id', $table_info['id']);
$TmplContent->set('cms_view', $cms_view);

unset($cms_view);
unset($table_info);
unset($cmsTable);



/**
* Выводим enum значения
*/
$query = "
	SELECT 
		tb_cms.id,
		tb_field.name AS field,
		tb_cms.name,
		tb_cms.title_".LANGUAGE_CURRENT." AS title,
		tb_cms.priority
	FROM cms_field_enum AS tb_cms
	INNER JOIN cms_field AS tb_field ON tb_field.id = tb_cms.field_id
	WHERE tb_field.table_id='".TABLE_ID."'
	ORDER BY tb_field.name ASC, tb_cms.priority ASC
";
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->addColumn('field', '20%', null, 'Поле');
$cmsTable->addColumn('name', '20%');
$cmsTable->addColumn('title', '50%');
$cms_enum = $cmsTable->display();
unset($cmsTable);
$TmplContent->set('cms_enum', $cms_enum);


/**
 * Таблицы, которые используют данную в качестве справчника
 */
$query = "SELECT name FROM cms_table WHERE id='".TABLE_ID."'";
$table_name = $DB->result($query);
$query = "
	SELECT 
		tb_table.id,
		concat('<a href=\"./?table_id=', tb_table.id, '\">', tb_table.name, '</a>') AS `table`,
		tb_field.name AS `field`
	FROM cms_field AS tb_field
	INNER JOIN cms_table AS tb_table ON tb_table.id=tb_field.table_id
	WHERE tb_field.fk_table_id='".TABLE_ID."'
	ORDER BY tb_table.name ASC, tb_field.name ASC
";
$data = $DB->query($query);
$cmsTable = new cmsShowView($DB, $query, 200);
$cmsTable->setParam('title', 'Используют '.$table_name.' в качестве справочника');
$cmsTable->setParam('show_path', false);
$cmsTable->setParam('show_parent_link', false);
$cmsTable->setParam('priority', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('add', false);
$cmsTable->setParam('delete', false);
$cmsTable->addColumn('table', '20%', null, 'Таблицы');
$cmsTable->addColumn('field', '20%', null, 'Поле');
$TmplContent->set('cms_parent', $cmsTable->display());
unset($cmsTable);





/**
 * Быстрый переход к другой таблице
 */
$query = "
	select id, name
	from cms_table
	where module_id='$info[module_id]'
	order by name asc
";
$data = $DB->fetch_column($query);
$TmplContent->set('quick_link', $data);
?>