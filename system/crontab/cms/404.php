<?php
/** 
 * ������ ������
 * @package Pilot  
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 * @cron ~/30 * * * *    
 */ 

/**
 * ���������� ���������
 * @ignore
 */
define('CMS_INTERFACE', 'ADMIN');

// ������������� ���������� ������� ����������
chdir(dirname(__FILE__));

/**
* ���������������� ����
*/
require_once('../../config.inc.php');
$DB = DB::factory('default');

/**
* ������� ������� �������� ������ �� ������������������ ������ ������
* @param string $content
* @return array
*/
function parseError($content){
	global $authors;
	
	$result = array();
	
	preg_match("/Date:[\s]+(.*)/", $content, $date);
	preg_match("/URL:[\s]+(.*)/", $content, $url);
	preg_match("/IP:[\s]+(.*)/", $content, $ip); 
	preg_match("/Refferer:[\s]+(.*)/", $content, $refferer);
	preg_match("/UserAgent:[\s]+(.*)/", $content, $user_agent);
	
	$result['date'] 	  = addslashes(trim($date[1]));
	$result['url'] 		  = addslashes(trim($url[1]));
	$result['ip'] 		  = addslashes(trim($ip[1]));
	$result['refferer']   = addslashes(trim($refferer[1]));    
	$result['user_agent'] = addslashes(trim($user_agent[1]));

	return $result;
}
  

/****************************************************************************************/
/*                                     SCRIPT START                                     */
/****************************************************************************************/


$oldfilename = LOGS_ROOT."404.log"; 
$filename    = LOGS_ROOT."404.".uniqid().".php";

if(!file_exists($oldfilename)){
	echo "[i] Done\n";
	exit; 	
}

rename($oldfilename, $filename);
$fp = fopen($filename, 'r');
 
if(!$fp){
	echo "[i] Done\n";
	exit; 		
}

$insert  = array();
$numline = 0;
$content = "";

while ($line = fgets($fp)) {
	$line = trim($line);
	echo "[i] parsing $numline --$line-- \n";
	$numline++;
	
	// ���� ������ ������ - ����� ���������
	if($line == ""){
		echo "[i] Skip empty line\n";
		continue;
	}
	
	// ���� ��������� ������ ������ �� ������ - �������� ���������� ������
	if(strpos($line, "[BEGIN]") === 0){
		echo "[i] 404 error start\n";
		$content = '';
		continue;
	}
	
	// ���� ��������� ����� ������ �� ������ - ���������� ������ � �������-������
	if(strpos($line, "[END]") === 0){
		echo "[i] 404 error end\n";
		
		$record = parseError($content);
		if(empty($record)){
			continue;
		}
		
		$insert[] = "('$record[date]', '$record[url]', '$record[ip]', '$record[refferer]', '$record[user_agent]', 1)";
		 
		// ���� ���������� ������ ��� ������� - ��������� �� � ��
		if (count($insert) > 10) {
	    	$query = "
	    		INSERT INTO cms_log_404 (date, url, ip, referer, user_agent, count) 
	    		VALUES ".implode(",", $insert)." 
	    		ON DUPLICATE KEY UPDATE count = count + 1     
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

// ��������� � �� ���������� ������
if(!empty($insert)){
	$query = "
		INSERT INTO cms_log_404 (date, url, ip, referer, user_agent, count)    
		VALUES ".implode(",", $insert)." 
		ON DUPLICATE KEY UPDATE count = count + 1
	";
	$DB->insert($query);
}

echo "[i] Done\n";

?>
