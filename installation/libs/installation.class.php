<?php
/** 
 * Класс конфигурирования и установки системы
 * @package Pilot
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@delta-x.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

class Installation {
	
	public static function checkDB($DB_HOST, $DB_NAME, $DB_LOGIN, $DB_PASSWORD,  &$error)
	{	
		$db_exists = @mysqli_connect($DB_HOST, $DB_LOGIN, $DB_PASSWORD, $DB_NAME);
		if(!$db_exists)
			$db_exists = @mysql_connect($DB_HOST, $DB_LOGIN, $DB_PASSWORD, $DB_NAME);
		
		if(!$db_exists){
		  
		  $error[] = "Соединение с базой не установлено. Проверьте пожалуйста настройки соединения.";
		  return false;
		 }
		 else {
			mysqli_close($db_exists);
			return true;
		 }
		
	}
	/**
	 * Загрузка базы данных, разделитель _;_
	 *
	 */
	public static function importDB($filename, &$error) 
	{ 
		global $DB;
		
		$queries = self::getQueries($filename, $error, '_;_');
		if($queries === false){return false;}
		
		/* Run each line as a query */ 
		$part_query = '';
		$trigger = false;

		foreach($queries as $query)    { 
			$query = trim($query); 
			if($query == "") { continue; }

			// проверка триггеров
			if (!preg_match("/DELIMITER/", $query) && !$trigger)   {		
				$DB->query($query);
			
			}  else if(preg_match("/DELIMITER/", $query)) {
			
				$part = explode('/', $query);			  
				if(count($part) == 1)
					continue;
				  
				//конец триггера
				if($trigger){
					$part_query .= ' '.array_shift($part);					
					$DB->query($part_query);
					$part_query = '';
					$trigger = false;
				}
				else {				
					$part_query = array_pop($part).'; ';
					$trigger = true;
				}
		 } else {	
			$part_query .= $query.'; ';
		 }
		} 

		/* All is well */ 
		return true; 
	} 

	public static function importProcedureDB($filename, &$error) 
	{ 
		global $DB;
	   
		$queries = self::getQueries($filename, $error, '$$');
		if($queries === false){return false;}
		/* Run each line as a query */ 

	   $part_query = '';
	   
	   foreach($queries as $query) 
	   { 
			$query = trim($query); 
			if(preg_match("/DELIMITER/", $query) || $query == '') { continue; }			
			$DB->query($query);
	   }
	   /* All is well */ 
	   return true; 
	} 
	/* 
	* Обновление файлов
	*/
	public static function updateFile($old_value, $new_value, $source, &$error) 
	{
		$tmp_dir = SITE_ROOT.'installation/tmp';
		if(!is_dir($tmp_dir)){
			@mkdir($tmp_dir, '0777');
		}
	
		$path_file = explode('/', $source);
		$name_file = array_pop($path_file);
		
		if(empty($name_file)){return false;}		
		
		$new_name_file = $tmp_dir.'/'.$name_file;
		if(file_exists($new_name_file)){
			@unlink($new_name_file);
		}
		
		if (!copy($source, $new_name_file)) {
			$error[] = "не удалось скопировать $source...";			 			
			return false;
		}
		
		$config_data = fread(fopen($new_name_file, 'r'), filesize($new_name_file));
		$config_data = str_replace($old_value, $new_value, $config_data);
		$fp = fopen($new_name_file,"w");
		if(fwrite($fp,$config_data) === FALSE){
			fclose($fp);
			$error[] = 'Не удалось записать данные в файл '.$new_name_file;
			return false;		
		}
		fclose($fp);
		
		return true;
	}
	
	
	/*
	* Функция парсинт файл и возвращает строки, созданные разбиением содержимое файла разделителем $slash 
	*/
	public static function getQueries($filename, &$error, $slash){
		/* Read the file */ 
		$lines = file($filename); 
		if(!$lines)    { 
			$error[] = "cannot open file $filename"; 
			return false; 
		 } 

		$scriptfile = false;
		/* Комментарии удаляем */ 
		foreach($lines as $line)	   { 
		  $line = trim($line); 
		  if(!ereg('^--', $line))	  { 
			 $scriptfile.=" ".$line; 
		  } 
	   } 

		if(!$scriptfile) { 
		  $error[] = "no text found in $filename"; 
		  return false; 
		} 

		/* Split the jumbo line into smaller lines */ 
		return explode($slash, $scriptfile); 
	}
}