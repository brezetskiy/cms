<?php
/** 
 * Редактирование пользователя 
 * @package Pilot 
 * @subpackage Auth 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 */ 

$user_id = globalVar($_GET['user_id'], 0);

$table_name = 'auth_user_data';
$tmp_dir = Auth::getUserId().'/'.$table_name.'/'.uniqid().'/';

$TmplDesign->iterate('/onload/', null, array('function' => '$("#idTabs ul").idTabs();'));
$TmplContent->set('user_id', $user_id);


/**
 * Группа пользователя
 */
$group_id = $DB->result("SELECT user_group_id FROM auth_user WHERE id = '$user_id'"); 


/**
 * Определяем параметры группы
 */
$group_params = array();

if(!empty($group_id)){
	
	/**
	 * Связь параметров и групп
	 */
	$group_param_relation = array();
	$group_param_relation_list = $DB->query("SELECT * FROM auth_user_group_param_relation");
	
	reset($group_param_relation_list);
	while(list(, $row) = each($group_param_relation_list)){
		$group_param_relation[$row['group_id']][] = $row['param_id'];
	}
	
	/**
	 * Перечень всех параметров
	 */
	$params = $DB->query("select * from auth_user_group_param order by priority asc");  
	$group_params_collect = false;
	
	reset($params);
	while(list($index, $row) = each($params)){
		if($row['data_type'] == 'devider' && in_array($row['id'], $group_param_relation[$group_id])){
			$group_params_collect = true;
		} elseif($row['data_type'] == 'devider' && !in_array($row['id'], $group_param_relation[$group_id])){
			$group_params_collect = false;
			continue;
		}
		
		if($group_params_collect) $group_params[$row['id']] = $row;
	}
}


/**
 * Определяем параметры пользователя
 */
$data = $DB->query("
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
			else 'value_text'
		end as field_type,
		tb_param.info_id,
		tb_param.fkey_table_id,
		tb_param.required,
		tb_data.value_char,  
		tb_data.value_decimal,
		tb_data.value_int,
		tb_data.value_text
	from auth_user_group_param as tb_param
	left join auth_user_data as tb_data on tb_param.id=tb_data.param_id and tb_data.user_id='$user_id'
	where 1 ".where_clause('tb_param.id', array_keys($group_params))."
	order by tb_param.priority asc
");

$tmpl_category = null; 

reset($data); 
while (list(, $row) = each($data)) {
	
	/**
	 * Выводится новый ярлык
	 */
	if ($row['data_type'] == 'devider') {
		$tmpl_category = $TmplContent->iterate('/category/', null, $row);
		continue;
	}
	
	$value = $row[$row['field_type']];
	
	/**
	 * Подгружаем данные в случае возникновения ошибки для параметров таблицы shop_product_value
	 */
	if (isset($_SESSION['ActionError']['param'][ $row['param_id']])) {
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
		$row['options'] = $DB->fetch_column("select id, name from auth_user_info_data where info_id='{$row['info_id']}'");
		
	} elseif ($row['data_type'] == 'html') {
		// HTML редактор
		if (empty($product_id)) {
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
		$row['value'] = $DB->fetch_column("select data_id from auth_user_multiple where user_id='$user_id' and param_id='{$row['param_id']}'");
		
	} else {
		if ($row['data_type'] == 'html') {
			$row['value'] = stripslashes($value);
		} else {
			$row['value'] = htmlspecialchars(stripslashes($value));
		}
	}
	
	$TmplContent->iterate('/category/row/', $tmpl_category, $row) ;
}



/**
 * Подгрузка телефонов
 */
$query = "
	SELECT id, phone, phone_original, confirmed, priority
	FROM auth_user_phone as tb_phone
	WHERE tb_phone.user_id = '$user_id'
	ORDER BY priority
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('phone', '40%', 'center', 'Номер');
$cmsTable->addColumn('phone_original', '40%', 'center', 'Формат');
$cmsTable->addColumn('confirmed', '10%', 'center', 'Подтвержден');
$cmsTable->setColumnParam('confirmed', 'editable', true);

$TmplContent->set('cms_phones', $cmsTable->display());
unset($cmsTable);


/**
 * Class cmsShowCoolGallery
 */
if (!empty($user_id)) {
	$TmplDesign->iterate('/onload/', null, array('function'=>"swf_upload_photo = new SWFUpload(gallery_create_swf_config('auth_user_data', '$user_id'));"));
	
	$query = "
		SELECT 
			id,
			photo,
			description_".LANGUAGE_CURRENT." AS description,
			priority
		FROM gallery_photo
		WHERE group_id='$user_id' and group_table_name='auth_user_data'
		ORDER BY priority ASC  
	";
	$cmsTable = new CoolGallery($DB, $query);
	$cmsTable->setParam('image_field', 'photo');
	$TmplContent->set('gallery', $cmsTable->display());
}


?>