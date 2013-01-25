<?php
/** 
 * Редактирование описания товара 
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */ 
$product_id = globalVar($_GET['product_id'], 0);
$group_id = globalVar($_GET['group_id'], 0);
$table_name = 'shop_product';
$tmp_dir = Auth::getUserId().'/'.$table_name.'/'.uniqid().'/';

$TmplDesign->iterate('/onload/', null, array('function' => '$("#idTabs ul").idTabs();'));

if (!empty($product_id)) {
	$query = "select group_id from shop_product where id='$product_id'";
	$group_id = $DB->result($query);
}
$TmplContent->set('group_id', $group_id);
$TmplContent->set('product_id', $product_id);
$TmplContent->set('tmp_dir', $tmp_dir);
$TmplContent->set('table_name', $table_name);


$Shop = new Shop($group_id);
$info = $Shop->getProductInfo($product_id, true);
$path = $Shop->getPath();
$TmplContent->iterateArray('/path/', null, $path);

// Ссылки на предыдущий и следующий товар
if (!empty($info) && !empty($info['id'])) {
	$info['name'] = htmlspecialchars(stripslashes($info['name']));
	
	$query = "select id from shop_product where group_id='$Shop->group_id' and priority > '$info[priority]' order by priority asc limit 1";
	$next_id = $DB->result($query);

	$query = "select id from shop_product where group_id='$Shop->group_id' and priority < '$info[priority]' order by priority desc limit 1";
	$prev_id = $DB->result($query);
	
	$TmplContent->set('next', $next_id);
	$TmplContent->set('previous', $prev_id);
	$TmplContent->set($info);
	if (!empty($next_id)) {
		$TmplContent->set('del_return_path', urlencode('/Admin/Shop/Groups/Info/?product_id='.$next_id));
	} elseif (!empty($prev_id)) {
		$TmplContent->set('del_return_path', urlencode('/Admin/Shop/Groups/Info/?product_id='.$prev_id));
	} else {
		$TmplContent->set('del_return_path', urlencode('/Admin/Shop/Groups/?group_id='.$group_id));
	}
} else {
	$TmplContent->set('name', 'Новый товар');
}



$tmpl_category = $TmplContent->iterate('/category/', null, array('param_id' => 0, 'name' => 'Основные'));
if (isset($info['name'])) {
	$TmplContent->iterate('/category/row/', $tmpl_category, array('product' => $info['name'], 'price' => (isset($info['price'])) ? $info['price'] : ''));
} else {
	$TmplContent->iterate('/category/row/', $tmpl_category, array());
}

$data = $Shop->getGroupParams();

reset($data); 
while (list(,$row) = each($data)) {
	if ($row['data_type'] == 'devider') {
		$tmpl_category = $TmplContent->iterate('/category/', null, $row);
		continue;
	}
	
	$value = (isset($info[$row['uniq_name']])) ? $info[$row['uniq_name']] : '';
	// Подгружаем данные в случае возникновения ошибки
	if (isset($_SESSION['ActionError']['param'][ $row['param_id'] ])) {
		$value = $_SESSION['ActionError']['param'][ $row['param_id'] ];
		$_SESSION['ActionError']['param'][ $row['param_id'] ];
	}
	
	if ($row['data_type'] == 'image') {
		// Картинка
		$row['file'] = '#';
		if (isset($info[$row['uniq_name']]) && is_file(SITE_ROOT.trim($info[$row['uniq_name']], '/'))) {
			$row['file'] = $info[$row['uniq_name']];
			$img_info = getimagesize(SITE_ROOT.trim($info[$row['uniq_name']], '/'));
			$row['upload_width'] = $img_info[0];
			$row['upload_height'] = $img_info[1];
		}
		
	} elseif ($row['data_type'] == 'file_list') {
		$uploads_root = (empty($product_id)) ? TMP_ROOT.$tmp_dir.$row['uniq_name'].'/': UPLOADS_ROOT.Uploads::getStorage($table_name, $row['uniq_name'], $product_id);
		
		$TmplDesign->iterate('/onload_var/', null, array('function' => "var swf_upload_$row[uniq_name];"));
		$TmplDesign->iterate('/onload/', null, array('function' => "swf_upload_$row[uniq_name] = new SWFUpload(cms_create_swf_config('$table_name', '$row[uniq_name]', '$product_id', '$tmp_dir'));"));
		
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
				'product_id' => $product_id,
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
		$file = 'shop_product/'.Uploads::getIdFileDir($product_id)."/$row[param_id].".$info[$row['uniq_name']]['value_char'];
		if (is_file(UPLOADS_ROOT.$file)) {
			$row['file'] = '/'.UPLOADS_DIR.$file;
		}
	} elseif ($row['data_type'] == 'fkey' || $row['data_type'] == 'multiple') {
		// Внешний ключ
		$query = "select id, name from shop_info_data where info_id='$row[info_id]' order by name";
		$row['options'] = $DB->fetch_column($query);
	} elseif ($row['data_type'] == 'html') {
		// HTML редактор
		$TmplDesign->iterate('/onload/', null, array('function' => 'integrateFckEditor("param_'.$row['param_id'].'", 300, "Shop", "shop_product", "'.LANGUAGE_CURRENT.'", "'.$product_id.'"); '));
	} elseif ($row['data_type'] == 'fkey_table') {
		// Внешний ключ - таблица
		$table = cmsTable::getInfoById($row['fkey_table_id']);
		if ($table['cms_type'] == 'tree') {
			$tree = cmsTable::loadInfoTree($row['fkey_table_id']);
			$Tree = new Tree($tree, $info[$row['uniq_name']]['value_int']);
			$row['options'] = $Tree->build(0);
		} elseif ($table['cms_type'] == 'cascade') {
			$tree = cmsTable::loadInfoCascade($row['fkey_table_id']);
			$Tree = new Tree($tree, $info[$row['uniq_name']]['value_int']);
			$row['options'] = $Tree->build(0);
		} elseif ($table['cms_type'] == 'list') {
			$tree = cmsTable::loadInfoList($row['fkey_table_id']);
			$row['options'] = TemplateUDF::html_options(array('options' => $tree, 'selected' => $info[$row['uniq_name']]['value_int']));
		}
		unset($Tree);
	}
	
	if ($row['data_type'] == 'multiple') {
		$query = "select data_id from shop_product_multiple where product_id='$product_id' and param_id='$row[param_id]'";
		$row['value'] = $DB->fetch_column($query);
	} else {
		$row['value'] = htmlspecialchars(stripslashes($value));
	}
	
	if ($row['data_type'] == 'date') {
		//если не присвоено ранее знаение, то ставим сегодняшнюю дату
		//иначе нужно записать дату в обратном порядке.
		if (($row['value']=="") || ($row['value']=="0000-00-00")) $row['value'] = date("d.m.Y");
		else {
			$row['value'] = explode("-", $row['value']);
			$row['value'] = implode(".", array_reverse($row['value']));			
		}
	};

	$TmplContent->iterate('/category/row/', $tmpl_category, $row) ;
}


// Список категорий между которыми можно перемещать товар
$query = "
	select tb_group.id, tb_group.id as real_id, tb_group.group_id as parent, tb_group.name
	from shop_group as tb_group
	inner join shop_group_relation as tb_relation on tb_group.id=tb_relation.id
	where tb_relation.parent='$Shop->param_group_id'
";
$data = $DB->query($query, 'id');

$Tree = new Tree($data, $Shop->group_id);
$info = $Tree->build($data[$Shop->param_group_id]['parent']);
$TmplContent->setGlobal('group_options', $info);







/**
 * Class cmsShowCoolGallery
 */
if (!empty($product_id)) {
	$TmplDesign->iterate('/onload/', null, array('function'=>"swf_upload_photo = new SWFUpload(gallery_create_swf_config('shop_product', '$product_id'));"));
	
	$query = "
		SELECT 
			id,
			photo,
			description_".LANGUAGE_CURRENT." AS description,
			priority
		FROM gallery_photo
		WHERE group_id='$product_id' and group_table_name='shop_product'
		ORDER BY priority ASC
	";
	$cmsTable = new CoolGallery($DB, $query);
	$cmsTable->setParam('image_field', 'photo');
	$TmplContent->set('gallery', $cmsTable->display());
}
?>