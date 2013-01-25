<?php
/**
 * Навигация по логам
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

function array_to_html($str){
	$str = preg_replace("/\n/", "<br>", $str);
	$str = preg_replace("/\r/", "<br>", $str);
	$str = preg_replace("/\t/", "&nbsp;&nbsp;&nbsp;&nbsp;", $str);
	return str_replace(array('[', ']'), array('<b>[', ']</b>'), $str);
}


/**
 * Получаем данные
 */
$path = globalVar($_REQUEST['path'], "");  

$sort = globalVar($_REQUEST['sort'], "date");
$destination = globalVar($_REQUEST['destination'], "desc");

$page_start = globalVar($_REQUEST['page'], 0);   
$rows_per_page = 25;


/**
 * Cканируем файл
 */
$file = fopen($path, "r");  
if (!$file){
	echo "Ошибка: невозможно открыть файл <b>$file</b>\n";
	exit;
} 

$rows = array();

while(!feof($file)){
	$row = array();
	
	/**
	 * Получение новой строки
	 */
	$line = trim(fgets($file));
	if(empty($line)) continue;
	
	/**
	 * Парсер строки
	 */
    $parsed = preg_split("/[\s,]+/", $line);
    if(count($parsed) < 6) continue;
    
    $row['date'] = $parsed[0] . ' ' . $parsed[1];  
    $row['ip'] = $parsed[2];
    $row['local_ip'] = $parsed[3];
    $row['user_login'] = $parsed[4];
    
    /**
     * Определяем параметры события
     */
    preg_match('/<ACTION_PARAMS_START>(.*)<ACTION_PARAMS_END>/', $line, $matched);
    $params  = (!empty($matched[1])) ? unserialize($matched[1]) : array();
    
    $row['request'] = print_r($params['request'], true);
	preg_match('/Array[\s]*\((.*)\)[\s]*/ismU', $row['request'], $matched_request);
	$row['request'] = (!empty($matched_request[1])) ? $matched_request[1] : $row['request'];  
     
    $row['request'] = (!empty($params['request'])) ? array_to_html(htmlspecialchars($row['request'])) : array();
    $row['session'] = (!empty($params['session'])) ? str_replace(array('[', ']'), array('<b>[', ']</b>'), htmlspecialchars(print_r($params['session'], true))) : array();
    
    $rows[] = $row;
}
 
fclose($file);


/**
 * Пейджинг
 */
$total_rows = count($rows);
if ($total_rows - $rows_per_page < 0) {
	$rows_count = $total_rows;     
} elseif ($page_start + $rows_per_page > $total_rows) {
	$rows_count = $total_rows;
} else { 
	$rows_count = $page_start + $rows_per_page;
}
  

/**
 * Сортировка
 */
order_structure($rows, "$sort $destination");     


/**
 * Загружаем шаблон 
 */
$TmplTable = new Template("cms/admin/logs_view");
$TmplTable->setGlobal('path', $path);

/**
 * Загружаем данные в шаблон
 */
$counter = 0;
for($i=$page_start; $i<$rows_count; $i++){
	if(!isset($rows[$i])) continue; 
	
	$rows[$i]['class'] = ($counter % 2 == 0) ? "even" : "odd";
	$rows[$i]['count'] = $counter;
	$rows[$i]['date'] = date('d.m.Y H:i:s', convert_date('Y-m-d H:i:s', $rows[$i]['date']));
	
	$counter++;
	
	$TmplTable->iterate('/rows/', null, $rows[$i]);  	
}


/**
 * Добавляем листалку по страницам
 */
$TmplTable->set('pages_list', Misc::pages($total_rows, $rows_per_page, 10, 'logs_view', false, false, '', 'log_display(\''.$path.'\', \''.$sort.'\', \''.$destination.'\', {$offset})', $page_start)); 
   

/**
 * Вывод результата
 */
$_SESSION['log_actions_current_log'] = $path; 
$_RESULT['content_log'] = $TmplTable->display();


?>