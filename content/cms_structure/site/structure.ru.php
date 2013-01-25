<?php
/**
 * Вывод структуры сайта
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$TmplDesign->iterate('/onload/', null, array('function' => "$('#structure_copy').jqm();"));

$structure_id = globalVar($_GET['structure_id'], 0);
$TmplContent->set('structure_id', $structure_id);

// Копирование раздела
$query = "select concat(name_".LANGUAGE_CURRENT.", ' (', url, ')') from site_structure where id='$structure_id'";
$current = $DB->result($query);
$TmplContent->set('current', $current);
$query = "select id, title from site_template order by priority asc";
$template = $DB->fetch_column($query);
$TmplContent->set('template', $template);

$query = "select id, id as real_id, structure_id as parent, name_ru as name from site_structure";
$data = $DB->query($query, 'id');
$Tree = new Tree($data, array());
$tree = $Tree->build(0);
unset($Tree);
$TmplContent->set('tree', $tree);

// Определяем значения для enum полей
$query = "
	select 
		tb_field.name,
		tb_enum.name as enum,
		tb_enum.title_".LANGUAGE_CURRENT." as title
	from cms_field_enum as tb_enum
	inner join cms_field as tb_field on tb_enum.field_id=tb_field.id
	inner join cms_table as tb_table on tb_table.id=tb_field.table_id
	inner join cms_db as tb_db on tb_db.id=tb_table.db_id
	where 
		tb_db.alias='default' and
		tb_table.name='site_structure'
";
$data = $DB->query($query);
$enum = array();
reset($data); 
while (list(,$row) = each($data)) { 
	$enum[ $row['name'] ][ $row['enum'] ] = $row['title'];
}



function cms_prefilter($row) {
	global $enum;
//	$row['name'] = '<img src="/design/cms/img/button/edit_text.gif" align="bottom" width="15" height="14" border="0"> <a href="#" onclick="cw('.$row['id'].');return false;">'.$row['name'].'</a>';
	$row['name'] = '<img src="/design/cms/img/button/edit_text.gif" align="bottom" width="15" height="14" border="0"> '.$row['edit'];
	
	// Определяем размер файла с контентом
	$row['name'] .= ($row['len'] > 0) ?
		"<br><span class=comment>[Размер: ".number_format($row['len'], 0, '.', ' ')." байт]</span>":
		"<br><span class=comment>[Размер: нет]</span>";
	
	// группы доступа
	if (is_module('user')) {
		$access_level = preg_split("/,/", $row['access_level'], -1, PREG_SPLIT_NO_EMPTY);
		$access_level = array_intersect(array_flip($enum['access_level']), $access_level);
		$row['name'] .= '<span class=comment>['.implode(", ", array_flip($access_level)).': '.$row['group_access'].']</span>';
	}
	
	// В каком меню показывать
	$show_menu = preg_split("/,/", $row['show_menu'], -1, PREG_SPLIT_NO_EMPTY);
	$show_menu = array_intersect(array_flip($enum['show_menu']), $show_menu);
	$row['uniq_name'] = "<a href='?structure_id=$row[id]'>$row[uniq_name]</a><br><span class=comment>".implode(", ", array_flip($show_menu)).'</span>';
	
	return $row;
}

$query = "
	SELECT
		tb_structure.id,
		tb_structure.priority,
		tb_structure.access_level,
		tb_structure.show_menu,
		html_editor(tb_structure.id, 'site_structure', 'content_".LANGUAGE_SITE_DEFAULT."', tb_structure.name_".LANGUAGE_SITE_DEFAULT.") as edit,
		length(tb_structure.content_".LANGUAGE_SITE_DEFAULT.") as len,
		tb_structure.url,
		tb_structure.name_".LANGUAGE_SITE_DEFAULT." AS name,
		IFNULL(
			(
				SELECT GROUP_CONCAT(tb_group.name ORDER BY tb_group.name SEPARATOR ', ')
				FROM auth_group AS tb_group
				INNER JOIN site_group_relation AS tb_relation ON tb_relation.group_id=tb_group.id
				WHERE tb_relation.structure_id=tb_structure.id
			), 
			'все'
		) AS group_access,
		CONCAT(
			IF(
				(SELECT COUNT(id) FROM site_structure WHERE structure_id=tb_structure.id) > 0, 
				CONCAT(
					'<img border=0 src=\"/design/cms/img/icons/folder.gif\" width=\"16\" height=\"16\" alt=\"В разделе находится: ', 
					(
						SELECT
							CASE
								WHEN COUNT(id)-1=1 THEN CONCAT(COUNT(id)-1, ' страница')
								WHEN COUNT(id)-1<5 THEN CONCAT(COUNT(id)-1, ' страницы')
								ELSE CONCAT(COUNT(id)-1, ' страниц')
							END
						FROM site_structure_relation 
						WHERE parent=tb_structure.id
					),
					'\">'
				),
				'<img border=0 src=\"/design/cms/img/icons/ie.gif\" width=\"16\" height=\"16\" alt=\"Страница\">'
			), 
			' ',
			IF (tb_structure.active!='true', CONCAT('<font color=silver>', tb_structure.uniq_name, '</font>'), tb_structure.uniq_name)
		) AS uniq_name
	FROM site_structure AS tb_structure
	WHERE tb_structure.structure_id='$structure_id'
	ORDER BY tb_structure.priority ASC
";
$cmsTable = new cmsShowView($DB, $query, 200, 'site_structure');
$cmsTable->setParam('prefilter', 'cms_prefilter');
if ($structure_id > 0) {
	$cmsTable->addColumn('uniq_name', '20%');
} else {
	$cmsTable->addColumn('uniq_name', '20%', 'left', 'Сайт');
}
$cmsTable->addColumn('name', '50%');
$cmsTable->addEvent('copy', "javascript:$('#structure_copy').jqmShow();", false, true, true, '/design/cms/img/event/table/copy.gif', '/design/cms/img/event/table/copy_over.gif', 'Копировать', null, true);
$cmsTable->addEvent('xml', "/action/admin/sdk/structure_xml_builder/", false, true, true, '/design/cms/img/event/table/xml.gif', '/design/cms/img/event/table/xml_over.gif', 'Скачать в формате xml', null, true);
$TmplContent->set('cms_view', $cmsTable->display());
unset($cmsTable);


/**
 * Выводим разделы, которыми ограничен пользователь
 */
$query = "select count(*) from auth_group_structure where group_id='".$_SESSION['auth']['group_id']."'";
$allow = $DB->result($query);
if ($allow) {
	$query = "
		SELECT
			tb_link.structure_id AS id,
			tb_parent.structure_id,
			CONVERT(GROUP_CONCAT(tb_structure.name_".LANGUAGE_SITE_DEFAULT." ORDER BY tb_relation.priority ASC SEPARATOR ' / ') USING ".CMS_CHARSET.") COLLATE ".CMS_COLLATION." AS name
		FROM auth_group_structure AS tb_link
		INNER JOIN auth_user AS tb_user ON tb_user.group_id = tb_link.group_id
		INNER JOIN site_structure_relation AS tb_relation ON tb_relation.id=tb_link.structure_id
		INNER JOIN site_structure AS tb_structure ON tb_structure.id = tb_relation.parent
		INNER JOIN site_structure AS tb_parent ON tb_parent.id = tb_link.structure_id
		WHERE tb_user.id='".$_SESSION['auth']['id']."'
		GROUP BY tb_link.group_id, tb_link.structure_id
		ORDER BY tb_link.structure_id ASC, tb_relation.priority ASC 
	";
	$links = $DB->query($query);
	$TmplContent->set('links', $DB->rows);
	reset($links);
	while(list(,$row) = each($links)) {
		$TmplContent->iterate('/links/', null, $row);
	}
	unset($links);
}

/**
 * Если не экранировать код в is_module('xxx') - при установке системы
 * без модуля Галерея эта страница работать не будет
 */
if (is_module('gallery') && $structure_id!=0) {
	/**
	 * Class CoolGallery
	 */
	$field_name = 'photo';
	$TmplDesign->iterate('/onload/', null, array('function'=>"swf_upload_$field_name = new SWFUpload(gallery_create_swf_config('site_structure', '$structure_id'));"));

	$query = "
		SELECT 
			id,
			photo,
			description_".LANGUAGE_CURRENT." AS description,
			priority
		FROM gallery_photo
		WHERE group_id='$structure_id' and group_table_name='site_structure'
		ORDER BY priority ASC
	";
	$cmsTable = new CoolGallery($DB, $query);
	$cmsTable->setParam('image_field', 'photo');
	$TmplContent->set('cms_gallery', $cmsTable->display());
	unset($cmsTable);
}
?>