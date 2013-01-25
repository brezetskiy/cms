<?php
/**
 * Изменение параметров пользователя сайта
 * @package Pilot
 * @subpackage Auth
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2010
 */


/**
 * Получаем данные
 */
$user_id     = Auth::getUserId();
$return_path = globalVar($_GET['return_path'], '/User/');
$table_name  = "auth_user_data";

$TmplContent->set("user_id", $user_id);


/**
 * Основные данные пользователя
 */
$auth_user_data = $DB->query_row("
	SELECT id, user_group_id as group_id, login, email
	FROM auth_user
	WHERE id = '$user_id'
"); 

$group_id = $auth_user_data['group_id'];
$TmplContent->set("user_email", $auth_user_data['email']); 


/**
 * Связи: группа - параметры
 */
$tmp = $DB->query("SELECT * FROM auth_user_group_param_relation");
$group_param_relation = array();

reset($tmp);
while(list(, $row) = each($tmp)){
	$group_param_relation[$row['group_id']][] = $row['param_id'];
}

$groups = $DB->query("SELECT id, name, comment FROM auth_user_group ORDER BY priority");
if(empty($group_id) && $DB->rows > 0){
	$group_id = $groups[0]['id'];  
}


/**
 * Если разрешено изменять группу
 */
if(AUTH_USER_GROUP_EDITABLE){ 
	reset($groups);
	while(list(, $row) = each($groups)){
		$row['params'] = (isset($group_param_relation[$row['id']])) ? "'".implode("','", $group_param_relation[$row['id']])."'" : ""; 
		$TmplContent->iterate("user_group", null, $row);
	}	
	$TmplContent->setGlobal("groups_count", $DB->rows);
}


/**
 * Определяем параметры группы
 */
$query = "
	select
		tb_param.id AS param_id,
		tb_param.uniq_name,
		tb_param.name,
		tb_param.description,
		tb_param.data_type,
		case tb_param.data_type
			when 'char' then 'value_char'
			when 'file' then 'value_char'
			when 'image' then 'value_char'
			when 'decimal' then 'value_decimal'
			when 'bool' then 'value_int'
			when 'fkey' then 'value_int'
			when 'fkey_table' then 'value_int'
			when 'date' then 'value_date'
			else 'value_text'
		end as field_type,
		tb_param.info_id,
		tb_param.fkey_table_id,
		tb_param.required,
		tb_param.is_editable,
		tb_param.is_hidden,
		tb_data.value_char,
		tb_data.value_decimal,
		tb_data.value_int,
		tb_data.value_text,
		tb_data.value_date
	from auth_user_group_param as tb_param
	left join auth_user_data as tb_data on tb_param.id=tb_data.param_id and tb_data.user_id='$user_id'
	order by tb_param.priority asc
";
$data = $DB->query($query);
$tmpl_category = null;  

reset($data); 
while (list(, $row) = each($data)) {
	if ($row['data_type'] == 'devider') {
		$tmpl_category = $TmplContent->iterate('/category/', null, $row);
		continue;
	} 
	
	$value = trim($row[$row['field_type']]);
	if(empty($value)) $row['is_editable'] = 1; 
	
	// Подгружаем данные в случае возникновения ошибки
	if (isset($_SESSION['ActionError']['param'][ $row['param_id'] ])) {
		$value = $_SESSION['ActionError']['param'][ $row['param_id'] ];
		$_SESSION['ActionError']['param'][ $row['param_id'] ];
	}
	
	if ($row['data_type'] == 'image') { 
		
		// Картинка
		$row['file'] = $row['thumb'] = '#';
		$file = 'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$row[param_id].$row[value_char]";
		$thumb = Uploads::getThumb(UPLOADS_ROOT.$file);
		
		if (is_file(UPLOADS_ROOT.$file)) {
			$row['file'] = '/'.UPLOADS_DIR.$file;
			$info = getimagesize(UPLOADS_ROOT.$file);
			$row['width'] = $info[0];
			$row['height'] = $info[1];
		}
		 
		if (is_file($thumb)) {
			$row['thumb'] = substr($thumb, strlen(SITE_ROOT) - 1);
			$info = getimagesize($thumb);
			$row['thumb_width'] = $info[0];
			$row['thumb_height'] = $info[1];
		} else {
			$row['thumb_width'] = '-';
			$row['thumb_height'] = '-';
		}
		
	} elseif ($row['data_type'] == 'file_list') {
		continue;
		
		$uploads_root = (empty($user_id)) ? TMP_ROOT.$tmp_dir.$row['uniq_name'].'/': UPLOADS_ROOT.Uploads::getStorage($table_name, $row['uniq_name'], $user_id);
		
		$TmplDesign->iterate('/onload_var/', null, array('function' => "var swf_upload_$row[uniq_name];"));
		$TmplDesign->iterate('/onload/', null, array('function' => "swf_upload_$row[uniq_name] = new SWFUpload(cms_create_swf_config('$table_name', '$row[uniq_name]', '$user_id', '$tmp_dir'));"));
		
		$row['table_name'] = $table_name;
		$tmpl_row = $TmplContent->iterate('/category/row/', $tmpl_category, $row);
		
		// Вывод закачанных файлов
		$files = Filesystem::getDirContent($uploads_root, true, false, true);
		$available = Filesystem::getDirContent(SITE_ROOT.'img/shared/ico/', false, false, true);
		$value = '';
		reset($files); 
		while (list(,$file) = each($files)) { 
			$extension = strtolower(Uploads::getFileExtension($file));
			$icon = (in_array($extension.'.gif', $available)) ? $extension : 'file';
			$file = iconv('UTF-8', CMS_CHARSET.'//IGNORE', $file);
			$TmplContent->iterate('/category/row/uploads/', $tmpl_row, array(
				'table_name' => $table_name,
				'user_id' => $user_id,
				'tmp_dir' => $tmp_dir,
				'field' => $row['uniq_name'],
				'filename' => basename($file),
				'icon' => $icon,
				'file_url' => substr($file, strlen(SITE_ROOT) - 1)
			));
		}
		continue;
	} elseif ($row['data_type'] == 'file') {
		// Файл
		$file = 'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$row[param_id].$row[value_char]";
		if (is_file(UPLOADS_ROOT.$file)) {
			$row['file'] = '/'.UPLOADS_DIR.$file;
		}
	} elseif ($row['data_type'] == 'fkey' || $row['data_type'] == 'multiple') {
		// Внешний ключ
		$query = "
			select id, name
			from auth_user_info_data
			where info_id='$row[info_id]'
		";
		$row['options'] = $DB->fetch_column($query);
	} elseif ($row['data_type'] == 'html' && !empty($row['is_editable'])) {
		// HTML редактор
		if (empty($user_id)) {
			$row['temp_field_id'] = globalVar($_SESSION["ActionError"]["temp_id_param_$row[param_id]"], '');
			if (empty($row['temp_field_id'])) $row['temp_field_id'] = uniqid();
		} else {
			$row['temp_field_id'] = '';
		}
	
		$TmplDesign->iterate('/onload/', null, array('function' => 'integrateCkEditor("param_'.$row['param_id'].'_'.$row['uniq_name'].'", "'.$user_id.'", "auth_user", "'.$row['uniq_name'].'", "'.$row['temp_field_id'].'"); '));
		
	} elseif ($row['data_type'] == 'fkey_table') { 
			// Внешний ключ - таблица
			$is_tree = false;
			$table   = cmsTable::getInfoById($row['fkey_table_id']);
			if (!empty($table['cms_type']) && $table['cms_type'] == 'tree') {
				$tree = cmsTable::loadInfoTree($row['fkey_table_id']);
				$is_tree = true;
			} elseif (!empty($table['cms_type']) && $table['cms_type'] == 'cascade') {
				$tree = cmsTable::loadInfoCascade($row['fkey_table_id']);
				$is_tree = true;
			} else { 
				$tree = cmsTable::loadInfoList($row['fkey_table_id']);
				$is_tree = false;
			}
			
			if($is_tree){
				$Tree = new Tree($tree, $value);
				$row['options'] = $Tree->build();
				unset($Tree);
			} else {
				$row['options'] = $tree;
			}
			$row['is_tree'] = $is_tree;
	}
	if ($row['data_type'] == 'date') {
		$row['value'] = $row['value_date'];
	}	
	if ($row['data_type'] == 'multiple') {
		$query = "select data_id from auth_user_multiple where user_id='$user_id' and param_id='$row[param_id]'";
		$row['value'] = $DB->fetch_column($query);
	} else {
		$row['value'] = htmlspecialchars(stripslashes($value));
	}
	
	$TmplContent->iterate('/category/row/', $tmpl_category, $row);
}


// В случае ошибки восстанавливаем параметры 
if (isset($_SESSION['ActionError'])) {
	$TmplContent->set($_SESSION['ActionError']);
}

$group_id = globalVar($_SESSION['ActionError']['user_group'], $group_id);  
if(!empty($group_param_relation[$group_id])){
	$TmplDesign->iterate('/onload/', null, array('function' => "set_user_group('$group_id', new Array('".implode("','", $group_param_relation[$group_id])."'))"));
}

$TmplContent->setGlobal("user_group", $group_id); 

?>