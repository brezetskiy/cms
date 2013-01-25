<?php
/**
 * Страница авторизация+регистрация
 * @package Pilot
 * @subpackage User
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


/**
 * Если пользователь уже вошел - пробрасываем его дальше на оформление заказа
 */      
if (Auth::isLoggedIn()) {
	header("Location:/User/Info/");
	exit;
}

 
/**
 * Captcha
 */
if (CMS_USE_CAPTCHA) {
	$TmplContent->set('captcha_html', Captcha::createHtml());
}


/**
 * Связи: группа - параметры
 */
$query = "SELECT * FROM auth_user_group_param_relation";
$tmp = $DB->query($query);
$group_param_relation = array();

reset($tmp);
while(list(, $row) = each($tmp)){
	$group_param_relation[$row['group_id']][] = $row['param_id'];
}


/**
 * Вывод групп пользователей
 */
$groups = $DB->query("SELECT id, name, comment FROM auth_user_group ORDER BY priority");
$TmplContent->set("user_group_count", $DB->rows);

if($DB->rows > 0){
	
	if($DB->rows > 1){
		reset($groups);
		while(list(, $row) = each($groups)){
			$row['params'] = (isset($group_param_relation[$row['id']])) ? "'".implode("','", $group_param_relation[$row['id']])."'" : "";
			$TmplContent->iterate("user_group", null, $row);
		}
	}
	
	// Определяем параметры группы
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
			tb_param.is_display,
			tb_param.required     
		from auth_user_group_param as tb_param         
		order by tb_param.priority asc
	";
	$data = $DB->query($query);
	
	$tmpl_category = null;  
	$devider_display_flag = true;
	
	reset($data); 
	while (list(, $row) = each($data)) {
		
		if($row['data_type'] == 'devider' && empty($row['is_display'])) $devider_display_flag = false;
		if($row['data_type'] == 'devider' && !empty($row['is_display'])) $devider_display_flag = true;
		if(!$devider_display_flag) continue;
		
		if ($row['data_type'] == 'devider') {
			$tmpl_category = $TmplContent->iterate('/category/', null, $row);
			continue;
		}
		
		if(empty($row['is_display'])) continue;
		
		$value = (!empty($row[$row['field_type']]))?$row[$row['field_type']]:"";
		
		// Подгружаем данные в случае возникновения ошибки
		if (isset($_SESSION['ActionError']['param'][ $row['param_id'] ])) {
			$value = $_SESSION['ActionError']['param'][ $row['param_id'] ];
			$_SESSION['ActionError']['param'][ $row['param_id'] ];
		}
		
		if ($row['data_type'] == 'image') {
			
			// Картинка
			$row['file'] = $row['thumb'] = '#';
			$file = 'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$row[param_id].$value";
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
			$file = 'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$row[param_id].$value";
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
			
		} elseif ($row['data_type'] == 'fkey_table') { 
			// Внешний ключ - таблица
			$is_tree = false;
			$table   = cmsTable::getInfoById($row['fkey_table_id']);
			if ($table['cms_type'] == 'tree') {
				$tree = cmsTable::loadInfoTree($row['fkey_table_id']);
				$is_tree = true;
			} elseif ($table['cms_type'] == 'cascade') {
				$tree = cmsTable::loadInfoCascade($row['fkey_table_id']);
				$is_tree = true;
			} elseif ($table['cms_type'] == 'list') {
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
		
		if ($row['data_type'] == 'multiple') {
			$query = "select data_id from auth_user_multiple where user_id='$user_id' and param_id='$row[param_id]'";
			$row['value'] = $DB->fetch_column($query);
		} else {
			$row['value'] = htmlspecialchars(stripslashes($value));
		}
		
		$TmplContent->iterate('/category/row/', $tmpl_category, $row) ;
	}
	
	/**
	 * Ставим группу
	 */
	$group_id = globalVar($_SESSION['ActionError']['user_group'], AUTH_USER_GROUP_DEFAULT);  
	$TmplContent->setGlobal("group_id", $group_id);
	$TmplDesign->iterate('/onload/', null, array('function' => "set_user_group('$group_id', new Array('".implode("','", $group_param_relation[$group_id])."'))"));
}

// В случае ошибки восстанавливаем параметры
if (isset($_SESSION['ActionError'])) {
	$TmplContent->set($_SESSION['ActionError']);
}
 


?>
