<?php
/**
 * —оздание xml файла со структурой таблиц
 * @package Pilot
 * @subpackage Site
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */


/**
 * ‘орматирует контент дл€ передачи без изменений в точности до символа
 * @param string $dir
 * @return array
 */
function format($content){
	return "<![CDATA[".base64_encode($content)."]]>";
}

/**
 * ƒобавл€ет элемент с именем $name и значением $value в элемент $root
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
 * ¬озвращает пол€ по table_id
 * @param int $table_id
 * @return array
 */
function getFieldsByTableId($table_id){ 
	global $DB, $fields_by_table;
	
	if(count($fields_by_table) == 0){  
		$query  = "SELECT * FROM cms_field"; 
		$arr = $DB->query($query);
		
		reset($arr);
		while(list(, $field) = each($arr)){
			$fields_by_table[$field['table_id']][] = $field;
		}
	}
	
	if(!isset($fields_by_table[$table_id])){
		return array();
	}
	return $fields_by_table[$table_id];  
}

/** 
 * ¬озвращает значени€ полей по field_id
 * @param int $field_id
 * @return array
 */
function getEnumByFieldId($field_id){ 
	global $DB, $enums;
	
	if(count($enums) == 0){  
		$query  = "SELECT * FROM cms_field_enum"; 
		$arr = $DB->query($query);
		
		reset($arr);
		while(list(, $enum) = each($arr)){
			$enums[$enum['field_id']][] = $enum;
		}
	}
	
	if(!isset($enums[$field_id])){
		return array();
	}
	return $enums[$field_id]; 	
}

/**
 * ¬озвращает таблицу по id
 * @param int $id
 * @return array
 */
function getTableById($id){ 
	global $DB, $tables;
	
	if(count($tables) == 0){  
		$query  = "SELECT * FROM cms_table"; 
		$tables = $DB->query($query, 'id');
	}
	
	if(!isset($tables[$id])){
		return array();	
	} 
	return $tables[$id];
}

/**
 * ¬озвращает поле по id
 * @param int $id
 * @return array
 */
function getFieldById($id){ 
	global $DB, $fields;
	
	if(count($fields) == 0){  
		$query  = "SELECT * FROM cms_field"; 
		$fields = $DB->query($query, 'id');
	}
	
	if(!isset($fields[$id])){
		return array();	
	} 
	return $fields[$id]; 
}

/**
 * ¬озвращает regexp по id
 * @param int $id
 * @return array
 */
function getRegexpById($id){ 
	global $DB, $regexp;
	
	if(count($regexp) == 0){  
		$query  = "SELECT * FROM cms_regexp"; 
		$regexp = $DB->query($query, 'id');
	}
	
	if(!isset($regexp[$id])){
		return array();  
	}  
	return $regexp[$id]; 
}

/**
 * ¬озвращает DOM элемент таблицы
 * @param array $table
 * @return DOMElement
 */
function createCmsTableElement($table){
	global $DB, $dom, $languages;
	
   	$cms_table = $dom->createElement("cms_table");
   	  
 	// создаем параметры корневого элемента 
   	reset($languages);
   	while(list(, $lang) = each($languages)){
    	if(isset($table['title_'.$lang])){
   			addElementToDom($cms_table, "title_$lang", iconv("windows-1251", "utf-8", $table['title_'.$lang]));   		   	 
   		}  		
   	} 
   	addElementToDom($cms_table, "db_id", 			   $table['db_id']); 
   	addElementToDom($cms_table, "name", 			   $table['name']); 
   	addElementToDom($cms_table, "interface_id", 	   $table['interface_id']); 
   	addElementToDom($cms_table, "module_id", 		   $table['module_id']); 
   	
   	// измен€ем параметр с id таблицы на им€ таблицы, которой соответствует id
   	$relation_table = getTableById($table['relation_table_id']);
   	if(!isset($relation_table['name'])){
   		$relation_table['name'] = '';
   	}
   	addElementToDom($cms_table, "relation_table_id",   $relation_table['name']);    
   	
   	// измен€ем параметр с id пол€ на им€ пол€, которому соответствует id, и им€ таблицы, которой принадлежит поле
    $parent_field = getFieldById($table['parent_field_id']);
   	if(isset($parent_field['table_id'])){ 
    	$parent_field_table = getTableById($parent_field['table_id']);
   	} else {
   		$parent_field_table['name'] = '';
   		$parent_field['name'] 		= '';
   	}
   	addElementToDom($cms_table, "parent_field_id", $parent_field_table['name']." ".$parent_field['name']); 
   	
   	// измен€ем параметр с id пол€ на им€ пол€, которому соответствует id, и им€ таблицы, которой принадлежит поле
   	$fk_show = getFieldById($table['fk_show_id']);
   	if(isset($fk_show['table_id'])){
   		$fk_show_table = getTableById($fk_show['table_id']);
   	} else {
   		$fk_show_table['name'] = '';
   		$fk_show['name']	   = '';  
   	}
    addElementToDom($cms_table, "fk_show_id", $fk_show_table['name']." ".$fk_show['name']);  
   	
   	// измен€ем параметр с id пол€ на им€ пол€, которому соответствует, id и им€ таблицы, которой принадлежит поле
   	$fk_order = getFieldById($table['fk_order_id']);
   	if(isset($fk_order['table_id'])){
    	$fk_order_table = getTableById($fk_order['table_id']);
   	} else {
   		$fk_order_table['name'] = '';
   		$fk_order['name']       = ''; 
   	}
    addElementToDom($cms_table, "fk_order_id", $fk_order_table['name']." ".$fk_order['name']);  
   	
   	addElementToDom($cms_table, "fk_order_direction",  $table['fk_order_direction']); 
   	addElementToDom($cms_table, "use_cvs", 			   (int) $table['use_cvs']); 
   	addElementToDom($cms_table, "is_disabled", 		   $table['is_disabled']); 
   	addElementToDom($cms_table, "orm_class_name",      $table['orm_class_name']); 
   	addElementToDom($cms_table, "_check_failed",       $table['_check_failed']); 
   	addElementToDom($cms_table, "_table_type",         $table['_table_type']); 
   	addElementToDom($cms_table, "_create_dtime",       $table['_create_dtime']); 
   	addElementToDom($cms_table, "_is_real",      	   $table['_is_real']); 
   	
   	// создаем dom элементы полей таблицы 
   	$fields = getFieldsByTableId($table['id']);
   	$cms_fields = $dom->createElement("cms_fields"); 
   	reset($fields);
   	while(list(, $field) = each($fields)){
   		$cms_field = $dom->createElement("cms_field");
	   	reset($languages); 
	   	while(list(, $lang) = each($languages)){
	    	if(isset($field['title_'.$lang])){
	   			addElementToDom($cms_field, "title_$lang", iconv("windows-1251", "utf-8", $field['title_'.$lang]));   		   	 
	   		}  		
	   		 
	    	if(isset($field['comment_'.$lang])){
	   			addElementToDom($cms_field, "comment_$lang", iconv("windows-1251", "utf-8", $field['comment_'.$lang]));   		   	 
	   		}  	
	   	}
   		addElementToDom($cms_field, "module_id", 	 $field['module_id']); 
   		addElementToDom($cms_field, "name", 		 $field['name']); 
   		addElementToDom($cms_field, "field_type", 	 $field['field_type']); 
   		addElementToDom($cms_field, "is_obligatory", 	 $field['is_obligatory']); 
   		
   		$fk_table = getTableById($field['fk_table_id']);
   		if(!isset($fk_table['name'])){
   			$fk_table['name'] = '';
   		}
   		addElementToDom($cms_field, "fk_table_id", $fk_table['name']);   
   		   		
   		$fk_link_table = getTableById($field['fk_link_table_id']);
   		if(!isset($fk_link_table['name'])){
   			$fk_link_table['name']	 = '';
   		}
   		addElementToDom($cms_field, "fk_link_table_id", $fk_link_table['name']);    
   		
   		addElementToDom($cms_field, "priority", 		 $field['priority']); 
   		addElementToDom($cms_field, "stick", 			 (int) $field['stick']); 
   		addElementToDom($cms_field, "group_edit", 		 (int) $field['group_edit']); 
   		addElementToDom($cms_field, "show_in_filter", 	 $field['show_in_filter']); 
   		addElementToDom($cms_field, "is_reference", 	 $field['is_reference']); 
   		addElementToDom($cms_field, "_column_default", 	 $field['_column_default']); 
   		addElementToDom($cms_field, "_is_nullable", 	 $field['_is_nullable']); 
   		addElementToDom($cms_field, "_ordinal_position", $field['_ordinal_position']); 
   		addElementToDom($cms_field, "_data_type", 		 $field['_data_type']); 
   		addElementToDom($cms_field, "_column_type", 	 $field['_column_type']); 
   		addElementToDom($cms_field, "_max_length", 		 $field['_max_length']); 
   		addElementToDom($cms_field, "_is_multilanguage", $field['_is_multilanguage']); 
   		addElementToDom($cms_field, "_is_real", 		 $field['_is_real']); 
   		
   		// измен€ем параметр regexp_id на uniq_name cms_regexp обьекта, который соответствует данному regexp_id
   		$regexp = getRegexpById($field['regexp_id']);
   		$regexpElem = $dom->createElement("regexp");
   		if(isset($regexp['id'])){
   			addElementToDom($regexpElem, "uniq_name", $regexp['uniq_name']);
   			addElementToDom($regexpElem, "regular_expression", format($regexp['regular_expression']));
   			
   			reset($languages);
		   	while(list(, $lang) = each($languages)){
		    	if(isset($regexp['name_'.$lang])){
		   			addElementToDom($regexpElem, "name_$lang", iconv("windows-1251", "utf-8", $regexp['name_'.$lang]));   		   	 
		   		}  		
		    	if(isset($regexp['error_message_'.$lang])){
		   			addElementToDom($regexpElem, "error_message_$lang", iconv("windows-1251", "utf-8", $regexp['error_message_'.$lang]));   		   	 
		   		} 
		   	} 
   		} 
   		$cms_field->appendChild($regexpElem);
   		
   		// добавл€ем значени€ enum пол€
   		$enums  = getEnumByFieldId($field['id']);
		$cms_field_enums = $dom->createElement("cms_field_enums");
   		reset($enums);
   		while(list(, $enum) = each($enums)){
				$cms_field_enum = $dom->createElement("cms_field_enum");
   				addElementToDom($cms_field_enum, "name", $enum['name']); 
   				addElementToDom($cms_field_enum, "priority", $enum['priority']); 
   				addElementToDom($cms_field_enum, "_is_real", $enum['_is_real']);  
   				
			   	reset($languages);
			   	while(list(, $lang) = each($languages)){
			    	if(isset($enum['title_'.$lang])){
			   			addElementToDom($cms_field_enum, "title_$lang", iconv("windows-1251", "utf-8", $enum['title_'.$lang]));   		   	 
			   		}  		
			   	} 
			   	
				$cms_field_enums->appendChild($cms_field_enum);
   		}
		$cms_field->appendChild($cms_field_enums);
		
		// заносим поле в общий блок полей
   		$cms_fields->appendChild($cms_field);
   	}
   	
   	// заносим общий блок полей в dom элемент таблицы
   	$cms_table->appendChild($cms_fields);
   	return $cms_table;
}




/*-------------------------------------------------------------------------------------*/

// оптимизационные переменные
$fields_by_table = array();
$fields = array();
$tables = array();
$regexp = array(); 
$enums  = array();  

// необходимые данные
$data      = $_REQUEST[$_REQUEST['_table_id']]['id']; 
$languages = explode(",", LANGUAGE_AVAILABLE); 
$dom 	   = new DOMDocument("1.0", "utf-8"); 
  
// создаем контейнер
$root = $dom->createElement("root");

// дл€ каждой таблицы переводим ее описание из Ѕƒ в xml формат
reset($data);
while(list(, $table_id) = each($data)){
	
	// ¬ыт€гиваем информацию о таблице
	$query = "SELECT * FROM cms_table WHERE id = '$table_id'";
	$table = $DB->query_row($query); 
	
	$cms_table = createCmsTableElement($table);
	$root->appendChild($cms_table);
}

// ставим контейнер root в dom 
$dom->appendChild($root);

// сохран€ем временный файл
$file = SITE_ROOT."/cms_table.xml";
$dom->save($file);

// открываем диалоговое окно дл€ скачивани€ файла
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

// удал€ем файл
unlink($file);
exit;

?>