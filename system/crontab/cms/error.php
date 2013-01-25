<?php
/** 
 * Парсер ошибок
 * @package Pilot  
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2009
 * @cron ~/30 * * * * 
 */ 

/**
 * Определяем интерфейс
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');

// Устанавливаем правильную рабочую директорию
chdir(dirname(__FILE__));

/**
* Конфигурационный файл
*/
require_once('../../config.inc.php');
$DB = DB::factory('default');

/**
* Функция разбора контента ошибки на структуризированый массив данных
* @param string $content
* @return array
*/
function parseError($content){
	global $authors;
	
	$result = array();
	preg_match("/Date:[\s]+(.*)/", $content, $date);
	preg_match("/URL:[\s]+(.*)/", $content, $url);
	preg_match("/IP:[\s]+(.*)/", $content, $ip);
	preg_match("/File:[\s]+(.*)/", $content, $file);
	preg_match("/Mtime:[\s]+(.*)/", $content, $mtime);
	preg_match("/Line:[\s]+(.*)/", $content, $line);
	preg_match("/Type:[\s]+(.*)/", $content, $type);
	preg_match("/Refferer:[\s]+(.*)/", $content, $refferer);
	preg_match("/UserAgent:[\s]+(.*)/", $content, $user_agent);
	
	$result['date'] 	  = addslashes(trim($date[1]));
	$result['url'] 		  = addslashes(trim($url[1]));
	$result['ip'] 		  = addslashes(trim($ip[1]));
	$result['file'] 	  = addslashes(trim($file[1]));
	$result['mtime'] 	  = addslashes(trim($mtime[1]));
	$result['line'] 	  = addslashes(trim($line[1]));
	$result['type'] 	  = addslashes(trim($type[1]));
	$result['refferer']   = addslashes(trim($refferer[1]));    
	$result['user_agent'] = addslashes(trim($user_agent[1]));
	
	// обрабатываем многострочные параметры
	$proc_pos = strpos($content, "Process");
	$mess_pos = strpos($content, "Message");
	$mess_len = strlen($content) - (strlen(substr($content, 0, $mess_pos)) + strlen("Message:") + strlen(substr($content, $proc_pos)));
	
	$result['message'] 	= addslashes(trim(substr($content, $mess_pos+strlen("Message:"), $mess_len)));
	$result['process'] 	= addslashes(trim(substr($content, $proc_pos+strlen("Process:"))));
	
	$file = $result['file'];
	
	// Игнорируем файлы, которые были изменены с момента ошибки
	if (is_file(SITE_ROOT.$file)) {
		$stat = stat(SITE_ROOT.$file);
		if ($stat['mtime'] > $result['mtime']) {
			echo "[i] Skip error because file was modified since last error (".date('Y-m-d H:i:s', $stat['mtime'])." > ".date('Y-m-d H:i:s', $result['mtime']).")\n";
			return array();
		}
	}
	
	// вытягиваем автора файла
	if(!isset($authors[$file])){
		$file_content = @file_get_contents("/home/hoster/ukraine.com.ua/www/".$file);
		$authors[$file] = (!empty($file_content) && preg_match('/@author[\s]*(.+)/', $file_content, $author) == 1) ? $author[1] : 'unknown';
	} 
	 
	$result['author'] = (!empty($authors[$file])) ? $authors[$file] : "unknown";
	return $result;
}
  

/****************************************************************************************/
/*                                     SCRIPT START                                     */
/****************************************************************************************/


$oldfilename = LOGS_ROOT."error.log"; 
$filename    = LOGS_ROOT."error.".uniqid().".php";

if(!file_exists($oldfilename)){
	echo "[i] Done\n";
	exit; 	
}

rename($oldfilename, $filename);
$fp = @fopen($filename, 'r');
 
if(!$fp){
	echo "[i] Done\n";
	exit; 		
}

$authors = array();
$insert  = array();
$numline = 0;
$content = "";

while ($line = fgets($fp)) {
	$line = trim($line); 
	echo "[i] parsing $numline --$line-- \n";
	$numline++;
	
	// если строка пустая - берем следующую
	if($line == ""){
		echo "[i] Skip empty line\n";
		continue;
	}
	
	// если встретили начало записи об ошибке - обнуляем предыдущую запись
	if(strpos($line, "[BEGIN]") === 0){
		echo "[i] Error message start\n";
		$content = '';
		continue;
	}
	
	// если встретили конец записи об ошибке - отправляем запись в функцию-парсер
	if(strpos($line, "[END]") === 0){
		echo "[i] Error message end\n";
		
		$record = parseError($content);
		if(empty($record)) continue;
		
		$record['user_agent'] = addslashes($record['user_agent']);
		$record['url'] 		= addslashes($record['url']);
		$record['file'] 	= addslashes($record['file']);
		$record['author'] 	= addslashes($record['author']);
		$record['refferer'] = addslashes($record['refferer']);
		$record['message'] 	= addslashes($record['message']);
		$record['process'] 	= addslashes($record['process']);
		
		$insert[] 	= "('$record[date]', '$record[url]', '$record[ip]', '$record[file]', from_unixtime('$record[mtime]'), '$record[author]', '$record[line]', '$record[type]', '$record[refferer]', '$record[user_agent]', '$record[message]', '$record[process]', 1)";
		 
		// если накопилось больше ста записей - сохраняем их в БД
		if (count($insert) > 100) {
			
	    	$query = "
	    		INSERT INTO cms_log_error (date, url, ip, file, mtime, author, line, type, refferer, user_agent, message, process, count) 
	    		VALUES ".implode(",", $insert)." 
	    		ON DUPLICATE KEY UPDATE  
					refferer=VALUES(refferer),
					user_agent=VALUES(user_agent),
					count = count + 1     
	    	";
	    	$DB->insert($query);
	    	$insert = array();
    	}
    	
    	continue;
	}	
	
	$content .= $line."\n";	
}

fclose($fp);  
unlink($filename);

// сохраняем в БД оставшиеся данные
if(!empty($insert)){
	$query = "
		INSERT INTO cms_log_error (date, url, ip, file, mtime, author, line, type, refferer, user_agent, message, process, count)    
		VALUES ".implode(",", $insert)." 
		ON DUPLICATE KEY UPDATE 
			refferer=VALUES(refferer),
			user_agent=VALUES(user_agent),
		 	count = count + 1
	";
	$DB->insert($query);
}

// Удаляем сообщения, которые происходили до изменения файла
$query = "select file from cms_log_error group by file";
$data = $DB->query($query);
reset($data);
while (list(,$row) = each($data)) {
	if (!is_file(SITE_ROOT.$row['file'])) continue;
	
	$stat = stat(SITE_ROOT.$row['file']);
	$DB->delete("delete from cms_log_error where file='$row[file]' and mtime < from_unixtime('$stat[mtime]')");
	echo "[i] Delete $row[file] - $DB->affected_rows rows\n";
}


echo "[i] Done\n";


?>
