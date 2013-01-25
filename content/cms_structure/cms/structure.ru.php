<?php
/**
 * Структура административного интерфейса
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$structure_id = globalVar($_GET['structure_id'], 0);

$query = "
	SELECT
		tb_structure.id,
		tb_structure.priority,
		IF(
			tb_module.id IS NOT NULL,
			CONCAT(tb_structure.name_".LANGUAGE_CURRENT.", '<br><span class=comment>Модуль: ', IFNULL(tb_module.name, '<font color=red>не указан</font>'), '</span>'),
			tb_structure.name_".LANGUAGE_CURRENT."
		) AS name,
		CONCAT(
			IF(
				(SELECT COUNT(id) FROM cms_structure WHERE structure_id=tb_structure.id) > 0, 
				CONCAT(
					'<img border=0 src=\"/design/cms/img/icons/folder.gif\" width=\"16\" height=\"16\" alt=\"В разделе находится: ', 
					(
						SELECT
							CASE
								WHEN COUNT(id)-1=1 THEN CONCAT(COUNT(id)-1, ' страница')
								WHEN COUNT(id)-1<5 THEN CONCAT(COUNT(id)-1, ' страницы')
								ELSE CONCAT(COUNT(id)-1, ' страниц')
							END
						FROM cms_structure_relation 
						WHERE parent=tb_structure.id
					), 
					'\">'
				),
				'<img border=0 src=\"/design/cms/img/icons/php.gif\" width=\"16\" height=\"16\" alt=\"Страница\">'
			), 
			' <a href=\"?structure_id=', tb_structure.id, '\"', IF(TRIM(tb_structure.show_menu) != '', '', ' style=\'color:#777;\' '), '>', tb_structure.uniq_name, '</a>'
		) AS uniq_name
	FROM cms_structure AS tb_structure
	LEFT JOIN cms_module AS tb_module ON tb_module.id=tb_structure.module_id
	WHERE tb_structure.structure_id='$structure_id'
	ORDER BY tb_structure.priority ASC
";
$cmsTable = new cmsShowView($DB, $query, 200, 'cms_structure');
$cmsTable->addEvent('xml', "/action/admin/sdk/structure_xml_builder/", false, true, true, '/design/cms/img/event/table/xml.gif', '/design/cms/img/event/table/xml_over.gif', 'Скачать в формате xml', null, true);
$cmsTable->addColumn('uniq_name', '20%');
$cmsTable->addColumn('name', '50%');
$TmplContent->set("structure_id", $structure_id);
$TmplContent->set("cmsTable", $cmsTable->display());
unset($cmsTable);
?>