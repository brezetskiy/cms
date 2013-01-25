<?php
/**
* Функции для генерации наполнения темплейта
* @package Template
* @subpackage Includes
* @version 3.0
* @author Rudenko Ilya <rudenko@id.com.ua>
* @copyright Delta-X ltd, 2004
*/
// При щелчке по [Shift][+] добавляется новый раздел
//	if ($index == 0) {
//		$TmplDesign->set('plus_go', 'onkeypress="if(event.shiftKey && event.keyCode==43) {document.location.href=\''.$row['url'].'\'}"');
//	}

if(IS_DEVELOPER) {
	if (empty($page_data['module_id'])) {
		$TmplDesign->iterate('/privileg/', null, array('message' => "Для страницы не указан модуль к которому она принадлежит. <a href=\"/admin/cms/structure/\">Исправить....</a>"));
	} 
	
	if (!empty($page_data['module_id'])) {
		
		// проверяем права доступа к модулю
		$query = "select * from auth_action_view where structure_id='".CMS_STRUCTURE_ID."'";
		$result = $DB->query($query);
		if(empty($result)) {
			$TmplDesign->iterate('/privileg/', null, array('message' => "Необходимо назначить права доступа к странице. <a href=\"/admin/user/actions/module/?module_id=".$page_data['module_id']."\">Исправить....</a>"));
		}
		
		// проверяем наличие неправильно созданных таблиц
		$query = "select concat('<a href=\"/admin/cms/db/tables/fields/?table_id=', id, '\">', name, '</a>') from cms_table where _check_failed=1 and module_id='$page_data[module_id]' and is_disabled=0 and _table_type='table' order by name";
		$data = $DB->fetch_column($query);
		if ($DB->rows > 0) {
			$TmplDesign->iterate('/privileg/', null, array('message' => "Необходимо исправить ошибки в таблицах ".implode(", ", $data)));
		}
		
		// наличие привилегий с ошибками в описании таблиц /admin/User/Actions/Module/?module_id=50
		$query = "
			SELECT DISTINCT tb_table.name
			FROM cms_table tb_table
			LEFT JOIN auth_action_table_select tb_select ON (tb_table.id = tb_select.table_id)
			LEFT JOIN auth_action_table_update tb_update ON (tb_table.id = tb_update.table_id)
			WHERE tb_table._table_type='table' and tb_table.is_disabled=0 and tb_table.module_id='$page_data[module_id]' and (tb_select.action_id is null or tb_update.action_id is null)
		";
		$data = $DB->fetch_column($query);
		if (!empty($data)) {
			$TmplDesign->iterate('/privileg/', null, array('message' => "Нет привилегий, которые дадут доступ к таблицам ".implode(", ", $data).". <a href=\"/admin/user/actions/module/?module_id=$page_data[module_id]\">Исправить...</a>"));
		}
		
		// наличие привилегий с ошибками в описании событий /admin/User/Actions/Module/?module_id=50
		$query = "
			SELECT DISTINCT tb_event.name
			FROM cms_event tb_event
			LEFT JOIN auth_action_event tb_relation ON (tb_event.id = tb_relation.event_id)
			WHERE tb_relation.action_id is null and tb_event.module_id='$page_data[module_id]'
		";
		$data = $DB->fetch_column($query);
		if (!empty($data)) {
			$TmplDesign->iterate('/privileg/', null, array('message' => "Нет привилегий, которые дадут доступ к событиям ".implode(", ", $data).". <a href=\"/admin/user/actions/module/?module_id=$page_data[module_id]\">Исправить...</a>"));
		}
		
		// наличие привилегий в которых не указывается доступ к страницам админки /admin/User/Actions/Module/?module_id=50
		$query = "
			SELECT DISTINCT tb_structure.url
			FROM cms_structure tb_structure
			INNER JOIN auth_action_view tb_relation ON (tb_structure.id = tb_relation.structure_id)
			WHERE tb_relation.action_id is null and tb_structure.module_id='$page_data[module_id]'
		";
		$data = $DB->fetch_column($query);
		if (!empty($data)) {
			$TmplDesign->iterate('/privileg/', null, array('message' => "Нет привилегий, которые дадут доступ к страницам ".implode(", ", $data).". <a href=\"/admin/user/actions/module/?module_id=$page_data[module_id]\">Исправить...</a>"));
		}
		
		// проверяем наличие событий, которые не привязаны к модулю /admin/User/EventGroup/EventFile/?module_id=30
		$query = "select name from cms_module where id='$page_data[module_id]'";
		$module = $DB->result($query);
		
		$path = ACTIONS_ROOT.'admin/'.strtolower($module).'/';
		if (is_dir($path)) {
			$new_events = array();
			$data = $DB->fetch_column("select name from cms_event where module_id='$page_data[module_id]'", 'name', 'name');
			$files = Filesystem::getAllSubdirsContent($path, true);
			reset($files); 
			while (list(,$filename) = each($files)) {
				$filename = substr($filename, strlen($path), -8);
				
				if (strpos(PHP_OS, "WIN") !== FALSE) {  
					$filename = substr(str_replace('\\', '/', $filename), 1); 
				}
				
				if (!isset($data[$filename])) {
					$new_events[] = $filename;
				} else {
					unset($data[$filename]);
				}
			}
			if (!empty($new_events)) {
				$TmplDesign->iterate('/privileg/', null, array('message' => "Необходимо описать события ".implode(", ", $new_events).". <a href=\"/admin/user/eventgroup/eventfile/?module_id=$page_data[module_id]\">Исправить...</a>"));
			}
			
			if (!empty($data)) {
				$TmplDesign->iterate('/privileg/', null, array('message' => "Необходимо удалить описание событий ".implode(", ", $data).". <a href=\"/admin/user/eventgroup/eventfile/?module_id=$page_data[module_id]\">Исправить...</a>"));
			}
			
		}
		
	}
}

	

/**
* Формируем левое меню, с учетом прав доступа пользователя к разделам
*/
if (IS_DEVELOPER) {
	$query = "
		SELECT
		    tb_structure.id,
		    tb_structure.structure_id AS parent,
		    tb_structure.name_".LANGUAGE_CURRENT." AS name, 
		    IF (tb_structure.no_link='true', 'javascript:void(0);', CONCAT('/admin/', tb_structure.url, '/')) AS url
		FROM cms_structure AS tb_structure
		WHERE
		    tb_structure.active='true'
		    AND FIND_IN_SET('left_menu', tb_structure.show_menu) > 0
		ORDER BY tb_structure.priority ASC
	";
} else {
	$query = "
		SELECT
		    tb_structure.id,
		    tb_structure.structure_id AS parent,
		    tb_structure.name_".LANGUAGE_CURRENT." AS name, 
		    IF (tb_structure.no_link='true', 'javascript:void(0);', CONCAT('/admin/', tb_structure.url, '/')) AS url
		FROM cms_structure AS tb_structure
		INNER JOIN cms_structure_relation AS tb_relation ON tb_structure.id=tb_relation.parent
		INNER JOIN auth_action_view AS tb_action_view ON tb_action_view.structure_id=tb_relation.id
		INNER JOIN auth_group_action AS tb_group_action ON tb_group_action.action_id=tb_action_view.action_id
		INNER JOIN auth_user AS tb_user ON tb_user.group_id=tb_group_action.group_id
		WHERE
		    tb_structure.active='true'
		    AND FIND_IN_SET('left_menu', tb_structure.show_menu) > 0
		    AND tb_user.id='".$_SESSION['auth']['id']."'
		GROUP BY tb_structure.id
		ORDER BY tb_structure.priority ASC
	";
}
$data = $DB->query($query, 'id');

// Определяем раздел, который необходимо выделить как текущий
for ($i=count($Site->parents);$i >= 1; $i--) {
	$id = $Site->parents[$i];
	if (isset($data[$id])) {
		$data[$id]['name'] = '<span class=selected_menu>'.$data[$id]['name'].'</span>';
		break;
	}
		
}


$BTree = new BTree($data);
$tree = $BTree->treemenu();
reset($tree);
while (list(, $id) = each($tree)) {
	$TmplDesign->iterate('/menu_item/', null, $BTree->data[$id]);
}

unset($tree);
unset($BTree);
unset($data);
?>