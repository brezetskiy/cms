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
	SELECT id, user_group_id as group_id, login, email, name, nickname
	FROM auth_user
	WHERE id = '$user_id'
"); 

$group_id = $auth_user_data['group_id'];
$TmplContent->set("user", $auth_user_data);


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
		tb_param.required,
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
	$value = $row[$row['field_type']];
	
	// Подгружаем данные в случае возникновения ошибки
	if (isset($_SESSION['ActionError']['param'][ $row['param_id'] ])) {
		$value = $_SESSION['ActionError']['param'][ $row['param_id'] ];
		$_SESSION['ActionError']['param'][ $row['param_id'] ];
	}
	
	if ($row['data_type'] == 'image') { 
		
		// Картинка
		$row['file'] = $row['thumb'] = '#';
		$file = 'auth_user_data/'.Uploads::getIdFileDir($user_id)."/$row[param_id].$row[value_char]";
		
		if (is_file(UPLOADS_ROOT.$file)) {
			$row['file'] = '/'.UPLOADS_DIR.$file;
			$info = getimagesize(UPLOADS_ROOT.$file);
			$row['width'] = $info[0];
			$row['height'] = $info[1];
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
			select name
			from auth_user_info_data
			where info_id='$row[info_id]' and id = '$value'
		";
		$row['option'] = $DB->result($query);
		
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
	if ($row['data_type'] == 'date') {
		$row['value'] = $row['value_date'];
	}	
	if ($row['data_type'] == 'multiple') {
		$query = "select data_id from auth_user_multiple where user_id='$user_id' and param_id='$row[param_id]'";
		$row['value'] = $DB->fetch_column($query);
	} else {
		$row['value'] = htmlspecialchars(stripslashes($value));
	}
	
	if (empty($row['value'])) {
		continue;
	}
	
	$TmplContent->iterate('/category/row/', $tmpl_category, $row);
}

if(!empty($group_param_relation[$group_id])){
	$TmplDesign->iterate('/onload/', null, array('function' => "set_user_group('$group_id', new Array('".implode("','", $group_param_relation[$group_id])."'));"));
}

$TmplContent->setGlobal("user_group", $group_id);  

/**
 * Подгрузка блока телефонов
 */  
$TmplDesign->iterate('/onload/', null, array('function' => "phone_load();"));

?>