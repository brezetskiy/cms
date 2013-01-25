<?php
/** 
 * Сохранение параметров продукта 
 * @package Pilot 
 * @subpackage Shop 
 * @author Rudenko Ilya <rudenko@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2008
 */
$name 		= globalVar($_POST['name'], '');
$price 		= globalVar($_POST['price'], 0);
$product_id = globalVar($_POST['product_id'], 0);
$group_id 	= globalVar($_POST['group_id'], 0);
$values 	= globalVar($_POST['param'], array());
$delete 	= globalVar($_POST['delete'], array());
$tmp_dir 	= globalVar($_POST['tmp_dir'], '');
$table_name = globalVar($_POST['table_name'], '');
$type = (empty($product_id)) ? 'insert' : 'update';
$OLD = array();

//if(empty($values['259']) && !empty($values['244'])) {
//	$seo = new SEO();
//	$seo->contentFromString($values['244']);
//	$values['259'] = $DB->escape($seo->updateKeywords());
//}

if (empty($name)) {
	Action::onError(cms_message('Shop', 'Не указано название товара'));
}

$ShopEdit = new ShopEdit($group_id);

// Создаём новый товар
if ($type == 'insert') {
	// определяем priority для нового товара
	$query = "select ifnull(max(priority), 0) + 1 from shop_product where group_id='$group_id'";
	$priority = $DB->result($query);
	$url = ShopEdit::getURL('shop_product', '_url', 0, $group_id, $name);
	$query = "insert ignore into shop_product (group_id, name, price, _url, priority) values ('$group_id', '$name', '$price',  '$url', '$priority')";
	$product_id = $DB->insert($query);
	if($DB->rows == 0){
		Action::onError('Уже существует товар, что ссылается на указанный URL.');
	}
} else {
	$query = "update shop_product set group_id='$group_id', name='$name', price='$price' where id='$product_id'";
	$DB->update($query);
   
	// Загружаем старую информацию о товаре  
	$query = "
		select tb_value.*
		from shop_product_value as tb_value
		inner join shop_group_param as tb_param on tb_param.id=tb_value.param_id
		where tb_value.product_id='$product_id'
	";
	$OLD = $DB->query($query, 'param_id');
}


/**
 * Удаляем файлы, которые помечены на удаление
 */
reset($delete); 
while (list(,$param_id) = each($delete)) {
	delete_file($param_id, $product_id, $OLD);
}


// Удаляем старую информацию
$query = "delete from shop_product_value where product_id='$product_id'";
$DB->delete($query);
$query = "delete from shop_product_multiple where product_id='$product_id'";
$DB->delete($query);


/**
 * Изменяем свойства товара
 */
$insert = array();
$data = $ShopEdit->getGroupParams();
reset($data);
while (list(, $row) = each($data)) {
	$param_id = $row['id'];
	// Обработка файлов
	if (in_array($row['data_type'], array('file', 'image')) && !empty($_FILES['param']['tmp_name'][$param_id]) && $_FILES['param']['error'][$param_id] == 0) {
		// Закачиваем файлы
		delete_file($param_id, $product_id, $OLD);
		$extension = Uploads::getFileExtension($_FILES['param']['name'][$param_id]);
		$extension = strtolower($extension);
		$file = UPLOADS_ROOT.'shop_product/'.Uploads::getIdFileDir($product_id)."/$param_id.$extension";
//		$thumb = UPLOADS_ROOT.'shop_product/'.Uploads::getIdFileDir($product_id)."/{$param_id}_thumb.jpg";
		$url = substr($file, strlen(UPLOADS_ROOT) - strlen(UPLOADS_DIR) - 1);
		Uploads::moveUploadedFile($_FILES['param']['tmp_name'][$param_id], $file);
		$query = "
			insert into shop_product_value (`product_id`,`param_id`,`value_char`, `value_text`, `value_date`)
			values ('$product_id', '$param_id', '$extension', '$url', null)
		";
		$DB->insert($query);
		// Создаём пиктограмму для картинки
//		if ($row['data_type'] == 'image' && !empty($row['thumb_width']) && !empty($row['thumb_height'])) {
//			$Image = new Image($file);
//			$Image->thumb($thumb, $row['thumb_width'], $row['thumb_height']);
//			$Image->resize($row['image_width'], $row['image_height']);
//			$Image->watermarkId($row['watermark_id']);
//			$Image->save();
//			unset($Image);
//		}
		
	} elseif (in_array($row['data_type'], array('file', 'image')) && empty($_FILES['param']['tmp_name'][$param_id]) && isset($OLD[$param_id]['value_char']) && !empty($OLD[$param_id]['value_char'])) {
		// Восстанавливаем данные старых файлов
		$file = UPLOADS_ROOT.'shop_product/'.Uploads::getIdFileDir($product_id)."/$param_id.".$OLD[$param_id]['value_char'];
		$url = substr($file, strlen(UPLOADS_ROOT) - strlen(UPLOADS_DIR) - 1);
		$query = "
			insert into shop_product_value (`product_id`,`param_id`,`value_char`, `value_text`)
			values ('$product_id', '$param_id', '".$OLD[$param_id]['value_char']."', '$url')
		";
		$DB->insert($query);
	}
	
	// Выдаём ошибку для обязательных полей и пропускаем необязательные для которых не переданы данные
	if (isset($values[$param_id]) && empty($values[$param_id]) && $row['required']) {
		Action::setWarning(cms_message('Shop', 'Не указано значение параметра "%s"', $row['name']));
		continue;
	} elseif (!isset($values[$param_id]) || empty($values[$param_id]) && $row['data_type'] == 'bool') {
		$values[$param_id] = 0;
	} elseif (!isset($values[$param_id]) || empty($values[$param_id])) {
		continue;
	}
	
	$value = $values[$param_id];
	
	// Добавляем переданные данные
	if ($row['data_type'] == 'char') {
		// Текстовое поле
		$insert[] = "('$product_id', '$param_id', null, null, '".addslashes($value)."', null, null)";
	} elseif ($row['data_type'] == 'decimal') {
		// Десятичное значение
		$insert[] = "('$product_id', '$param_id', null, null, null, '".str_replace(',', '.', round(preg_replace("/[^\d\,\.]+/", '', $value), 2))."', null)";
	} elseif ($row['data_type'] == 'bool') {
		// Checkbox
		$insert[] = "('$product_id', '$param_id', null, '".intval($value)."', null, null, null)";
	} elseif ($row['data_type'] == 'fkey') {
		// Внешний ключ
		$query = "select group_concat(name) from shop_info_data where id='$value'";
		$insert[] = "('$product_id', '$param_id', null, '$value', '".addslashes($DB->result($query))."', null, null)";
	} elseif ($row['data_type'] == 'fkey_table') {
		// БД/таблица
		$query = "select concat(db_name, '.', table_name) from cms_table_static where id='$value'";
		$insert[] = "('$product_id', '$param_id', null, '$value', '".addslashes($DB->result($query))."', null, null)";
		
	} elseif (($row['data_type'] == 'multiple')&&(!empty($value))) {
		// Внешнее многозначное поле
		$query = "
			insert ignore into shop_product_multiple (`product_id`,`param_id`,`data_id`)
			values ('$product_id', '$param_id', '".implode("'), ('$product_id', '$param_id', '", $value)."')
		";
		$DB->insert($query);
		
		$query = "select group_concat(name) from shop_info_data where id in (0".implode(",", $value).")";
		$insert[] = "('$product_id', '$param_id', '".addslashes($DB->result($query))."', null, null, null, null)";
		
	} elseif ($row['data_type'] == 'text') {
		// Текст
		$insert[] = "('$product_id', '$param_id', '".addslashes($value)."', null, null, null, null)";
	} elseif ($row['data_type'] == 'html') {
		// HTML
		if ($type == 'insert') {
			/**
			 * Заменяем временные файлы на постоянные
			 * Только при вставке, т.к. при редактировании нам уже известен id продукта,
			 * и uploads грузятся сразу в нужный каталог
			 */
			$temp_id = globalVar($_POST['temp_id_param_'.$param_id], '');
			$temp_uploads_dir = TMP_ROOT."fck-editor/$temp_id/";
			if (is_dir($temp_uploads_dir)) {
				$new_dir = UPLOADS_ROOT.Uploads::getStorage('shop_product', LANGUAGE_CURRENT, $product_id).'/';
				$new_url = Uploads::getURL($new_dir);
				Filesystem::rename($temp_uploads_dir, $new_dir);
				$value = str_replace(Uploads::getURL($temp_uploads_dir), $new_url, $value);
			}	
		}
		$insert[] = "('$product_id', '$param_id', '".addslashes($value)."', null, null, null, null)";
	} elseif ($row['data_type'] == 'date') {
		// Дата
		$value = explode(".", $value);
		$value = implode(".", array_reverse($value));
		$insert[] = "('$product_id', '$param_id', null, null, null, null, '".addslashes($value)."')";
	}
}

if (!empty($insert)) {
	$query = "insert into shop_product_value (`product_id`,`param_id`,`value_text`,`value_int`,`value_char`,`value_decimal`, `value_date`) values ".implode(",", $insert);
	$DB->insert($query);
}

/**
 * Проверяем, есть ли таблица, в которую необходимо закачивать товар
 */
$query = "select * from information_schema.tables where table_schema='".DB_DEFAULT_NAME."' and table_name='$ShopEdit->table_name' limit 1";
$data = $DB->query($query);

if ($DB->rows == 0) { 
	// Запрошенной таблицы не существует, она была удалена.
	// Восстанавливаем данные
	if ($ShopEdit->createTable()) {
		$ShopEdit->loadData($ShopEdit->param_group_id, true);
		$ShopEdit->commitCreate();
	}
}

/**
 * Переносит созданный товар в статическую таблицу
 */
if ($type != 'insert') {
	$ShopEdit->deleteProduct($product_id);
}

// Обновляем описание товара
ShopEdit::updateDescription($product_id);
$insert_id = $ShopEdit->insertProduct($product_id);

// Копируем файлы, которые закачаны в поле file_list через swf_upload, когда создается новый товар и еще не присвоен id
// strlen($tmp_dir) > 2  необходимо для того, что б не удалить случайно TMP_ROOT, если параметр прийдет пустым или со знаком /
if (strlen($tmp_dir) > 2 && is_dir(TMP_ROOT.$tmp_dir)) {
	$fields = Filesystem::getDirContent(TMP_ROOT.$tmp_dir, false, true, false);
	reset($fields);
	while (list(,$field) = each($fields)) {
		$destination = UPLOADS_ROOT.$table_name.'/'.$field.Uploads::getIdFileDir($insert_id).'/';
		$files = Filesystem::getDirContent(TMP_ROOT.$tmp_dir.$field, false, false, true);
		reset($files);
		while (list(,$file) = each($files)) {
			Filesystem::rename(TMP_ROOT.$tmp_dir.$field.$file, $destination.$file);
		}
	}
	Filesystem::delete(TMP_ROOT.$tmp_dir);
}

$_REQUEST['_return_path'] = "/Admin/Shop/Groups/Info/?product_id=$product_id";

//$ShopEdit->loadData($ShopEdit->param_group_id);
//$ShopEdit->insertProduct($product_id);

/**
 * Удаляет файлы, которые помечены на удаление
 *
 * @param int $param_id
 */
function delete_file($param_id, $product_id, $OLD) {
	if (!isset($OLD[$param_id]) || !in_array($OLD[$param_id]['data_type'], array('file', 'image'))) return;
	
	$file = UPLOADS_ROOT.'shop_product/'.Uploads::getIdFileDir($product_id).'/'.$param_id.'.'.$OLD[$param_id]['value_char'];
	if (is_file($file)) unlink($file);
}
?>