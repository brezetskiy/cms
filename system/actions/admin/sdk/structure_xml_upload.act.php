<?php
/**
 * Создание структуры сайта из xml файла
 * @package Pilot
 * @subpackage Site
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


/**
 * Расшифровка строки
 * @param string $content
 * @return string
 */
function deformat($content) {
	$content = str_replace("![CDATA[", "", $content);
	$content = str_replace("]]", "", $content);
	$content = base64_decode($content);
	return $content;
}


/**
 * Сведение массива структуры в удобный формат
 * @param array $structure
 * @return void
 */
function set_keys($structure){
	$format = array();
	
	reset($structure);
	while(list($index, $row) = each($structure)){
		$format[$row['_name']] = $row;
	}
	return $format;
}


/**
 * Прописывает правельные ссылки
 * @param string $content
 * @param string $new_url
 * @return string
 */
function change_links($content, $new_url) {
	$links = array();
	$pattern = "/src=[\'\"\\\]+(\/{0,1}uploads\/[0-9a-zA-Z\/_\-\.]*)[\'\"\\\]+/U";
	preg_match_all($pattern, $content, $links);
	
	if(empty($links[1])) return $content;
	
	reset($links[1]);
	while(list($index, $link) = each($links[1])){
		$content = str_replace($link, "$new_url/".basename($link), $content);
	}
	return $content;
}


/**
 * Удаляет новосозданную запись
 * @param int $id
 */
function roll_back($id){
	global $DB;
	
	if(empty($id)) return false;
	$query = "DELETE FROM ".UPLOAD_TABLE." WHERE id = '$id'";
	$DB->query($query);
}


/**
 * Сохраняет структуру
 * @param int $parent_id
 * @param array $structure
 * @return void
 */
function save($parent_id, $structure){ 
	global $DB, $languages, $new_root, $upload_fields;
	
	// Создание записи в базе
	$current_id = $DB->insert("INSERT INTO ".UPLOAD_TABLE." SET structure_id = '$parent_id'");
	
	// Формирование url
	$structure_url = $DB->result("SELECT url FROM ".UPLOAD_TABLE." WHERE id = '$parent_id'");
	 
	$uploads_dir = Uploads::getIdFileDir($current_id); 
	$structure_folder = $structure_url."/".substr($structure['url']['_data'], strrpos($structure['url']['_data'], "/")+1);
	if(UPLOAD_TABLE == 'site_structure') $structure_folder = str_replace(substr($structure_folder, 0, strpos($structure_folder, '/')), CMS_HOST, $structure_folder);
	
	// Создание файлов
	reset($languages);
   	while(list(, $lang) = each($languages)){
		
		// картинки
		if(isset($structure['images_'.$lang]) && count($structure['images_'.$lang]['_elements']) > 0){
		    $images_path = strtolower(str_replace("//", "/", SITE_ROOT."uploads/".UPLOAD_TABLE."/content_$lang/$uploads_dir"));  
		    if(!is_dir("$images_path/")) mkdir("$images_path/", 0777, true);	
			
			reset($structure['images_'.$lang]['_elements']);
			while(list($index, $image) = each($structure['images_'.$lang]['_elements'])){
				$image_name = str_replace("image_", "", $image['_name']);
				
				$handle = fopen("$images_path/$image_name", 'a+');
				if (fwrite($handle, deformat($image['_data'])) === FALSE) {
					roll_back($current_id);
					Action::onError("Ошибка записи: не удалось создать файл '$images_path/$image_name'");
				}
				
				fclose($handle);		
			}
		}   
		  
		// контент
		if(isset($structure['php_'.$lang]) && $structure['php_'.$lang]['_data'] != ""){
			$content_path = strtolower(str_replace("//", "/", SITE_ROOT."content/".UPLOAD_TABLE."/$structure_folder")); 
	
			if(!is_dir("$content_path/")) mkdir("$content_path/", 0777, true);	
			$handle = fopen("$content_path.$lang.php", 'w+');
			
			if (fwrite($handle, change_links(deformat($structure['php_'.$lang]['_data']), "/uploads/".UPLOAD_TABLE."/content_$lang/$uploads_dir")) === FALSE) {
				roll_back($current_id);
				Action::onError("Ошибка записи: не удалось создать файл '$content_path.$lang.php'\n");
			}
			
			fclose($handle);	
		}
		
		// шаблон
		if(isset($structure['template_'.$lang]) && $structure['template_'.$lang]['_data'] != ""){
			$template_path = strtolower(str_replace("//", "/", SITE_ROOT."content/".UPLOAD_TABLE."/$structure_folder"));  
			
			if(!is_dir("$template_path/")) mkdir("$template_path/", 0777, true);   	
			$handle = fopen("$template_path.$lang.tmpl", 'a+');
			
			if (fwrite($handle, change_links(deformat($structure['template_'.$lang]['_data']), "/uploads/".UPLOAD_TABLE."/content_$lang/$uploads_dir")) === FALSE){
				roll_back($current_id);
				Action::onError("Ошибка записи: не удалось создать файл '$template_path.$lang.tmpl'\n");
			}
			
			fclose($handle);	
		}
   	}

	
   	// Перенос данных в базу
   	$data = array();
   	
   	reset($upload_fields);
   	while(list(, $field) = each($upload_fields)){
   	 	if(isset($structure[$field])){
   			$data[$field] = "$field = '".$structure[$field]['_data']."'";
   			continue;
   		}	
   		
   		reset($languages);
	   	while(list(, $lang) = each($languages)){
	   		if(isset($structure[$field.'_'.$lang])) $data[$field.'_'.$lang] = $field."_$lang = '".addslashes(deformat($structure[$field.'_'.$lang]['_data']))."'";
	   	}
   	}
   	   
   	if(!empty($data)) $DB->update("UPDATE ".UPLOAD_TABLE." SET ".implode(", ", $data)." WHERE id = '$current_id'");
   	return $current_id;
}


/**
 * Сохранение дерева структур
 * @param int $id
 * @param array $father
 * @return void
 */
function saveSubunits($id, $father){
	global $processed_elements;
	 
	$processed_elements[] = $id;
	if(!isset($father['subunits']) || count($father['subunits']['_elements']) <= 0) return false;
		
	reset($father['subunits']['_elements']);
	while(list($index, $structure) = each($father['subunits']['_elements'])){ 
		$subunit_site_structure = set_keys($structure['_elements']);
		$subunit_id = save($id, $subunit_site_structure);
		saveSubunits($subunit_id, $subunit_site_structure); 
	}
}


/****************************************************************************************/
/*                                     SCRIPT START                                     */
/****************************************************************************************/
 
  
/**
 * Инициализация входных данных
 */
$upload_table = globalVar($_POST['table'], 'site_structure');
$structure_id = globalVar($_POST['structure_id'], 0);
$file 		  = $_FILES['structure']['tmp_name'];
$languages 	  = explode(",", LANGUAGE_AVAILABLE); 
$new_root     = "";
 
$processed_elements = array();


/**
 * Определение таблицы, в которую будут выгружены данные
 */
define("UPLOAD_TABLE", $upload_table);


/**
 * Определяем шаблон дизайна, к которому будут привязаны страницы
 */
if(UPLOAD_TABLE == 'site_structure'){
	$design_id = $DB->result("SELECT id FROM site_template WHERE group_id != 1 ORDER BY priority ASC");
	if($DB->rows == 0) Action::onError("Пожалуйста, добавьте дизайн сайта на странице <a href='/Admin/Site/Design/'>Дизайн</a>.");
}


/**
 * Определяем модуля, к которому будут привязаны страницы
 */
if(UPLOAD_TABLE == 'cms_structure'){
	$module_id = $DB->result("SELECT module_id FROM cms_structure WHERE id = '$structure_id'");
	if($DB->rows == 0) Action::onError("Пожалуйста, укажите модуль для текущего раздела.");
}


/**
 * Определение полей таблицы
 */
$query = "
	SELECT 
		tb_field.id, 
		tb_field.name
	FROM cms_field as tb_field
	INNER JOIN cms_table as tb_table ON tb_table.id = tb_field.table_id
	WHERE tb_table.name = '".UPLOAD_TABLE."' AND tb_field.name != 'id' AND tb_field.name != 'structure_id'
";
$upload_fields = $DB->fetch_column($query, 'id', 'name');


/**
 * Создание пути к корневой папке
 */
if(!empty($structure_id)){ 
	
	// Вытягивание из базы структуры, в которую будет залито содержимое xml-файла
	$folder = $DB->query_row("SELECT * FROM ".UPLOAD_TABLE." WHERE id = '$structure_id'");

	$new_root = "$folder[url]/";
	$main_dir = str_replace("//", "/", SITE_ROOT."content/".UPLOAD_TABLE."/$new_root/");
	
	if(!is_dir($main_dir)){  
		mkdir($main_dir, 0777);	 
	}
}  
 
 
/** 
 * Парсер файла
 */  
if (file_exists($file)) {
	$parser = new XMLToArray();
	$xml = $parser->parseXML(file_get_contents($file));
	$root = $xml[0];
} else {
	Action::onError("Ошибка: файла '$_FILES[structure][tmp_name]' не существует.");
}
    

/**
 * Сохранение распарсенных элементов
 */
reset($root);
while(list($index, $structure) = each($root['_elements'])){ 
	$father = set_keys($structure['_elements']);
	$father_id = save($folder['id'], $father);
	saveSubunits($father_id, $father);
}


/**
 * Обновление url и дизайна
 */ 
if(UPLOAD_TABLE == 'site_structure'){ 
	$DB->update("
		UPDATE site_structure 
		SET url = REPLACE(url, SUBSTRING(url, 1, LOCATE('/', url)-1), '".CMS_HOST."'),
			template_id = '$design_id'
		WHERE id IN (0".implode(',', $processed_elements).")
	");
}


/**
 * Обновление modul_id
 */ 
if(UPLOAD_TABLE == 'cms_structure' && !empty($processed_elements)){ 
	$DB->update("UPDATE cms_structure SET module_id = '$module_id' WHERE id IN (0".implode(',', $processed_elements).")");
}


Action::setSuccess("Структура сайта успешно обновлена.");  


?>