<?php
/**
 * Вывод информации о модуле
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
$module_id = globalVar($_GET['module_id'], 0);

$Module = new Module($module_id);
$TmplContent->set('module_id', $Module->id);
$TmplContent->set('module', $Module->name);

// Модули, с которыми связан данный модуль
reset($Module->tables);
while(list(,$row) = each($Module->tables)) {
	$TmplContent->set('show_tables', true);
	$tmpl_table = $TmplContent->iterate('/table/', null, $row);
	reset($row['uploads']);
	while (list(,$row2) = each($row['uploads'])) {
		$TmplContent->iterate('/table/uploads/', $tmpl_table, array('name' => substr($row2, strlen(UPLOADS_ROOT))));
	}
	
	reset($row['content']);
	while (list(,$row2) = each($row['content'])) {
		$TmplContent->iterate('/table/content/', $tmpl_table, array('name' => substr($row2, strlen(CONTENT_ROOT))));
	}
	
	reset($row['triggers']);
	while (list(,$row2) = each($row['triggers'])) {
		$TmplContent->iterate('/table/triggers/', $tmpl_table, array('name' => substr($row2, strlen(TRIGGERS_ROOT))));
	}
}

// События
reset($Module->events);
while (list(,$row) = each($Module->events)) {
	$TmplContent->set('show_events', true);
	$TmplContent->iterate('/event/', null, array('file' => str_replace(ACTIONS_ROOT, '', $row)));
}

// Crontab
reset($Module->crontab);
while (list(,$row) = each($Module->crontab)) {
	$TmplContent->set('show_crontab', true);
	$TmplContent->iterate('/crontab/', null, $row);
}

// Шаблоны
reset($Module->templates);
while(list(,$row) = each($Module->templates)) {
	$TmplContent->set('show_templates', true);
	$TmplContent->iterate('/template/', null, array('file' => str_replace(TEMPLATE_ROOT, '', $row)));
}

// Include
reset($Module->includes);
while(list(,$row) = each($Module->includes)) {
	$TmplContent->set('show_includes', true);
	$TmplContent->iterate('/includes/', null, array('file' => str_replace(INC_ROOT, '', $row)));
}


// Tools
reset($Module->tools);
while(list(,$row) = each($Module->tools)) {
	$TmplContent->set('show_tools', true);
	$TmplContent->iterate('/tools/', null, array('file' => str_replace(SITE_ROOT.'tools/', '', $row)));
}

// Таблицы стилей
reset($Module->css);
while(list(,$file) = each($Module->css)) {
	$TmplContent->set('show_css', true);
	$TmplContent->iterate('/css/', null, array('file' => str_replace(SITE_ROOT.'css/', '', $file)));
}

// Картинки
reset($Module->img);
while(list(,$file) = each($Module->img)) {
	$TmplContent->set('show_img', true);
	$TmplContent->iterate('/img/', null, array('file' => str_replace(SITE_ROOT.'img/', '', $file)));
}


// Страницы сайта
reset($Module->site_content);
while (list(,$row) = each($Module->site_content)) {
	$TmplContent->set('show_site', true);
	$TmplContent->iterate('/site/', null, array('file' => str_replace(CONTENT_ROOT.'site_structure/', '', $row)));
}

// Шаблоны сайта
reset($Module->site_template);
while (list(,$row) = each($Module->site_template)) {
	$TmplContent->set('show_site_template', true);
	$TmplContent->iterate('/site_template/', null, array('file' => str_replace(TEMPLATE_ROOT.'content/site_structure/', '', $row)));
}


// Страницы админки
reset($Module->admin_content);
while (list(,$row) = each($Module->admin_content)) {
	$TmplContent->set('show_admin', true);
	$TmplContent->iterate('/admin/', null, array('file' => str_replace(CONTENT_ROOT.'cms_structure/', '', $row)));
}

// Шаблоны админки
reset($Module->admin_template);
while (list(,$row) = each($Module->admin_template)) {
	$TmplContent->set('show_admin_template', true);
	$TmplContent->iterate('/admin_template/', null, array('file' => str_replace(TEMPLATE_ROOT.'content/cms_structure/', '', $row)));
}
?>