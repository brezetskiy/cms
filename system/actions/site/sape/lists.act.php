<?php

$_REQUEST['project_id'] = globalVar($_REQUEST['project_id'], 0);
$hrefs_do               = globalVar($_REQUEST['hrefs_do'], 0);

require('system/libs/Sape.php');
$sape = new SapeClient('c_format', 'ihbkfyrf', 'system/crontab/sape/cookie.txt');

$content_complete = '';
$href_count       = '';

$day_begin = $_REQUEST['date_begin'];
$day_end   = $_REQUEST['date_end'];

$day_begin = explode('.', $day_begin);
$day_end   = explode('.', $day_end);

$test_date_begin = mktime(0,  0,  0,  $day_begin[1], $day_begin[0],  $day_begin[2]);
$test_date_end   = mktime(23, 59, 59, $day_end[1],   $day_end[0],    $day_end[2]);

$dates = array();

$i = 0;
while ( mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2]) <= mktime(0, 0, 0, $day_end[1],   $day_end[0],    $day_end[2]) )
{
    $dates[date("d.m.Y", mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2]))] = 0;
    $dates_global[date("d.m.Y", mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2]))] = 0;
    $i++;
};
//echo('<td>Всего</td>');
echo ('</tr>');

$project_cformat_id = $_REQUEST['project_id'];
$urls = $sape->get_urls($project_cformat_id);
$href_count = count($urls);

if (!is_array($urls)) $urls = array();

$i = 0;
reset($urls);
while (list(,$row)=each($urls))
{
        $content = '';
        $i++;
        if ($i <= $hrefs_do) continue;
        
	/*echo ('<pre>');
	print_R($row);
	echo ('</pre>');*/
        
        /*
         * Получаем навзание ссылки
         * И Ид
         */
        $name_url = $row['name'];
        $id_url   = $row['id'];
        $content .= '<tr>';
        $name_url = iconv("UTF-8", "CP1251", $name_url);
        $content .=  '<td>' . $name_url . '</td>';
        
        $count_links_period = 0;
        
        
        /*
         * Получаем список ссылок купленый за период
         */
        $links = $sape->get_url_links($id_url, 'OK', $test_date_begin, $test_date_end);
        /*while ( $links == false )
        {
            //sleep(1000);
            $links = $sape->get_url_links($id_url, 'OK', $test_date_begin, $test_date_end);
        };*/
        if ($links == false)
        {
            $_RESULT['javascript'] = "
                $('main_table tbody').append($content_complete);
                $('div.status').html($hrefs_do + ' из ' + $href_count);
                //setTimeout('AjaxRequest.send(null, \'/action/sape/lists/\' , \'\', true, {\'project_id\': $project_cformat_id, \'hrefs_do': $hrefs_do});', 1000);
                setTimeout('alert(\'test\');', 1000);
            ";
            exit();
        };
        echo ("<div style='margin: 20px;'></div>");        
        reset($links);
        while (list(,$sub_row)=each($links))
        {
            $dates[$sub_row['date_placed']->day . '.' . $sub_row['date_placed']->month . '.' . $sub_row['date_placed']->year]++;
            $dates_global[$sub_row['date_placed']->day . '.' . $sub_row['date_placed']->month . '.' . $sub_row['date_placed']->year]++;
            $count_links_period++;
        };
        
        /*
         * Выводим и обнуляем.
         */
        reset($dates);
        while (list($key,$date_row)=each($dates))
        {
            $content .=  '<td>' . $date_row . '</td>';
            $dates[$key] = 0;
        };

        /*
         * Всего
         */        
        //echo('<td>' . $count_links_period . '</td>');

        $content .= '</tr>';
        $content_complete .= $content;
        //break;
        $hrefs_do++;
};

$_RESULT['javascript'] = "
    $('main_table tbody').append($content_complete);
    $('div.status').html($hrefs_do + ' из ' + $href_count);
";
exit();

?>