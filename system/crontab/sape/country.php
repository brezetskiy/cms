<style>
    table tr:last-child{background: red;}
    table td:last-child{background: red;}
    table td{vertical-align: middle; text-align: center;}
</style>

<script src="http://cdn.jquerytools.org/1.2.5/full/jquery.tools.min.js"></script>
<script type="text/javascript" src="/js/shared/jshttprequest.js"></script>
<script type="text/javascript" src="/js/shared/global.js"></script>

<?php
/*
 * require main sape class
 */

$day_begin = $_REQUEST['date_begin'];
$day_end   = $_REQUEST['date_end'];

$day_begin = explode('.', $day_begin);
$day_end   = explode('.', $day_end);

/*$test_date_begin = mktime(0, 0, 0, $day_begin[1], $day_begin[0],  $day_begin[2]);
$test_date_end   = mktime(0, 0, 0, $day_end[1],   $day_end[0],    $day_end[2]);*/

$test_date_begin = mktime(0,  0,  0,  $day_begin[1], $day_begin[0],  $day_begin[2]);
$test_date_end   = mktime(23, 59, 59, $day_end[1],   $day_end[0],    $day_end[2]);

$dates = array();

require('../../libs/Sape.php');
////create sape user and save cookie in txt file
$sape = new SapeClient('c_format', 'ihbkfyrf', 'cookie.txt');

$user = $sape->get_user();

echo('<center>');
echo('<a href="usage.php">Вернуться назад</a><br />');
echo ('List of words');
echo ('<div class="status">Идет загрузка...</div>');
echo('<table id="main_table" border="1" cellspacing="0" cellpadding="6">');

/*
 * Выводим заглавную строку
 */
echo ('<tr>');
echo ('<td>Слово</td>');
$i = 0;
while ( mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2]) <= mktime(0, 0, 0, $day_end[1],   $day_end[0],    $day_end[2]) )
{
    $dates[date("d.m.Y", mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2]))] = 0;
    $dates_global[date("d.m.Y", mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2]))] = 0;
    echo ('<td>' . date("d.m.Y", mktime(0, 0, 0, $day_begin[1], $day_begin[0] + $i,  $day_begin[2])) . '</td>');
    $i++;
};
//echo('<td>Всего</td>');
echo ('</tr>');

/*
 * Идем по все словам
 */
//$project_cformat_id = $_REQUEST['project_id'];
//$urls = $sape->get_urls($project_cformat_id);
//reset($urls);
//while (list(,$row)=each($urls))
//{
//	/*echo ('<pre>');
//	print_R($row);
//	echo ('</pre>');*/
//        
//        /*
//         * Получаем навзание ссылки
//         * И Ид
//         */
//        $name_url = $row['name'];
//        $id_url   = $row['id'];
//        echo('<tr>');
//        $name_url = iconv("UTF-8", "CP1251", $name_url);
//        echo('<td>' . $name_url . '</td>');
//        
//        $count_links_period = 0;
//        
//        
//        /*
//         * Получаем список ссылок купленый за период
//         */
//        $links = $sape->get_url_links($id_url, 'OK', $test_date_begin, $test_date_end);
//        /*while ( $links == false )
//        {
//            //sleep(1000);
//            $links = $sape->get_url_links($id_url, 'OK', $test_date_begin, $test_date_end);
//        };*/
//        if ($links == false) $links = array();
//        echo ("<div style='margin: 20px;'></div>");        
//        reset($links);
//        while (list(,$sub_row)=each($links))
//        {
//            $dates[$sub_row['date_placed']->day . '.' . $sub_row['date_placed']->month . '.' . $sub_row['date_placed']->year]++;
//            $dates_global[$sub_row['date_placed']->day . '.' . $sub_row['date_placed']->month . '.' . $sub_row['date_placed']->year]++;
//            $count_links_period++;
//        };
//        
//        /*
//         * Выводим и обнуляем.
//         */
//        reset($dates);
//        while (list($key,$date_row)=each($dates))
//        {
//            echo('<td>' . $date_row . '</td>');
//            $dates[$key] = 0;
//        };
//
//        /*
//         * Всего
//         */        
//        echo('<td>' . $count_links_period . '</td>');
//
//        echo('</tr>');
//        //break;
//};
//
//unset($dates);

#var_dump($dates_global);
/*
echo('<tr>');
echo('<td>Итого</td>');
$count_links_period = 0;
reset($dates_global);
while (list($key,$date_row)=each($dates_global))
{
    echo('<td>' . $date_row . '</td>');
    $count_links_period += $date_row;
};

unset($dates_global);
        
echo('<td>' . $count_links_period . '</td>');
echo('</tr>');

echo('</table>');
echo('</center>');*/









/*
exit();
$link_id = 14600979;
$links = $sape->get_url_links($link_id, 'WAIT');
reset($links);
while (list(,$row)=each($links))
{
	echo ('<pre>');
	print_R($row);
	echo ('</pre>');
};

$anchors = $sape->get_url_anchors($link_id);
reset($anchors);
while (list(,$row)=each($anchors))
{
	/*echo ('<pre>');
	print_R($row);
	echo ('</pre>');*/
//};

?>
<script>
    
    AjaxRequest.send(null, '/action/sape/lists/' , '', true, {'date_begin': '<? echo($_REQUEST['date_begin']); ?>', 'test_date_end': '<? echo($_REQUEST['date_end']); ?>', 'project_id': <? echo($_REQUEST["project_id"]); ?>});
    
</script>