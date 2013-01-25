<?php
/**
 * Создание xml файла структуры сайта
 * @package Pilot
 * @subpackage Site
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


/**
 * Форматирует контент для передачи без изменений в точности до символа
 * @param string $dir
 * @return array
 */
function format($content){
	return "<![CDATA[".base64_encode($content)."]]>";
}


/**
 * Добавляет картинки в dom элемент
 * @param string $dir
 * @return array
 */
function addImagesToDom(&$root, $dir, $lang){
	global $dom;
	
	if(is_dir($dir)){
		$i = 0;
		$images = $dom->createElement("images_$lang");
		
		$elements = array_values(array_diff(scandir($dir), array( ".", ".." ))); 
		reset($elements);
		while(list($index, $element) = each($elements)){	
			preg_match("/.*\.([a-zA-Z]{1,4})/", $element, $type);	
			if(isset($type[1]) && $type[1] != ""){	
				addElementToDom($images, "image_$element", format(file_get_contents($dir.$element))); 
				$i++;
			}
		}
		$root->appendChild($images);
	}
}


/**
 * Добавляет элемент с именем $name и значением $value в элемент $root
 * @param DOMElement $root
 * @param string $name
 * @param string $value
 * @return void
 */
function addElementToDom(&$root, $name, $value){
	global $dom;
	$element = $dom->createElement($name);
	$value 	 = $dom->createTextNode($value);
	$element->appendChild($value);
	$root->appendChild($element);  
}


/**
 * Возвращает subunits елемента структуры
 * @param int $structure_id
 * @return DOMElement
 */
function fillSubunits($structure_id, &$root){
	global $DB, $dom;
	
	$query = "SELECT * FROM ".UPLOAD_TABLE." as tb_structure WHERE structure_id = '$structure_id'";
	$structure = $DB->query($query);
	 
	if(count($structure) > 0){
		$subunits = $dom->createElement("subunits");
		
		reset($structure);
		while(list(, $row) = each($structure)){	
			$subunit_site_structure = createSiteStructureElement($row);
			$subunits->appendChild($subunit_site_structure);
			
			fillSubunits($row['id'], $subunit_site_structure);
		}
		$root->appendChild($subunits);
	}
}


/**
 * Возвращает DOM элемент структуры
 * @param array $structure
 * @return DOMElement
 */
function createSiteStructureElement($structure){
	global $dom, $languages, $upload_fields;
	
	// создаем корневой элемент
   	$site_structure = $dom->createElement("site_structure_$structure[id]");
   	
 	// создаем параметры элемента
   	reset($upload_fields);
   	while(list(, $field) = each($upload_fields)){
	 	if(isset($structure[$field])){
	   		addElementToDom($site_structure, $field, $structure[$field]);
	   		continue;
	 	}
	 	
	 	reset($languages);
   		while(list(, $lang) = each($languages)){
		 	if(isset($structure[$field.'_'.$lang])){ 
	   			addElementToDom($site_structure, $field.'_'.$lang, format($structure[$field.'_'.$lang]));
		 	}
   		}
   	}
   	
   	// создаем записи о файлах
   	reset($languages);
   	while(list(, $lang) = each($languages)){
   		
   		// content
		$dir_content = strtolower(SITE_ROOT."content/".UPLOAD_TABLE."/$structure[url].$lang.php");
        if(file_exists($dir_content)){
   			addElementToDom($site_structure, "php_$lang", format(file_get_contents($dir_content)));	
        } elseif(isset($structure['content_'.$lang])) {	
   			addElementToDom($site_structure, "content_$lang", format($structure['content_'.$lang])); 
   		}
        
        // template
		$dir_template = strtolower(SITE_ROOT."content/".UPLOAD_TABLE."/$structure[url].$lang.tmpl");
        if(file_exists($dir_template)){
        	addElementToDom($site_structure, "template_$lang", format(file_get_contents($dir_template))); 	
        }
        
        // image
		$dir_uploads = strtolower(SITE_ROOT."uploads/".UPLOAD_TABLE."/content_$lang/".Uploads::getIdFileDir($structure['id'])."/");
        if(file_exists($dir_uploads)){
        	 addImagesToDom($site_structure, $dir_uploads, $lang);
        }
   	}
   	
   	return $site_structure;
}


/****************************************************************************************/
/*                                     SCRIPT START                                     */
/****************************************************************************************/


/**
 * Инициализация входных данных
 */
$table_id 		= $_REQUEST['_table_id'];
$sites_indexes 	= $_REQUEST[$table_id]['id'];
$languages 		= explode(",", LANGUAGE_AVAILABLE); 
$dom 			= new DOMDocument("1.0", "utf-8"); 


/**
 * Определение таблицы, с которой будут выгружены данные
 */
$query = "SELECT name FROM cms_table WHERE id = '$table_id'";
$upload_table = $DB->result($query);

define("UPLOAD_TABLE", $upload_table);


/**
 * Определение полей таблицы
 */
$query = "
	SELECT 
		tb_field.id,
		tb_field.name
	FROM cms_field as tb_field
	INNER JOIN cms_table as tb_table ON tb_table.id = tb_field.table_id
	WHERE tb_table.name = '".UPLOAD_TABLE."'
";
$upload_fields = $DB->fetch_column($query, 'id', 'name');


/**
 * Создание контейнера для структур высшего уровня
 */
$root = $dom->createElement("root");


/**
 * Для каждого сайта: перевод его структуры из БД в xml формат
 */
reset($sites_indexes); 
while(list(, $structure_id) = each($sites_indexes)){
	
	// Вытягивание информации о корневой единице структуры
	$query = "SELECT * FROM ".UPLOAD_TABLE." WHERE id = '$structure_id'";
	$father = $DB->query_row($query);
	
	// Создание элемента
	$subroot = createSiteStructureElement($father);
	
	// Заполнение элемента дочерними элементами
	fillSubunits($structure_id, $subroot);
	$root->appendChild($subroot);
} 


/**
 * Контейнер root ставится в dom
 */
$dom->appendChild($root);


/**
 * Сохранение временного файла
 */
$file = "system/actions/admin/site/structure.xml";
$dom->save($file);


/**
 * Открытие диалогового окна для скачивания файла
 */
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($file));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
ob_clean();
flush();
readfile($file);


/**
 * Удаление файла
 */
unlink($file);
exit;

?>