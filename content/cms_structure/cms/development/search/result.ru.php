<?php
/**
 * Вывод искомых значений в файле с подсветкой строк
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$file = globalVar($_GET['file'], '');
$pattern = stripslashes(globalVar($_GET['pattern'], ''));

echo '<h1>'.$file.'</h1><br>';

$last = strrpos($pattern, substr($pattern, 0, 1)) - 1;
$higliht_regexp = substr($pattern, 0, 1).'('.substr($pattern, 1, $last).')'.substr($pattern, $last + 1);

function highlight($matches) {
	static $counter = 0;
	global $total_matches;
	
	$counter++;
	$next = ($counter + 1 > $total_matches) ? 1 : $counter + 1;
	return '<a name="match_'.$counter.'" href="'.CURRENT_URL_FORM.'#match_'.$next.'" style="font-weight:bold;color:white;background-color:red;">'.strip_tags($matches[1]).'</a>';
}

function highlight2($matches) {
	return '<span style="color:black;background-color:#F0F0F0;">'.strip_tags($matches[1]).'</span>';
}

$content = '';
$array =  file(SITE_ROOT.$file);
reset($array);
while(list($index, $row) = each($array)) {
	$content .= sprintf('-|=%03d=|-', ($index + 1))."\t".$row;
}

$content = preg_replace($higliht_regexp, "-+=$1=+-", $content);
$content = highlight_string($content, true);
$total_matches = preg_match_all("/-\+=(.+)=\+-/U", $content, $matches);
$content = preg_replace_callback("/-\+=(.+)=\+-/U", 'highlight', $content);
$content = preg_replace_callback("/-\|=(.+)=\|-/U", 'highlight2', $content);
echo $content;
?>