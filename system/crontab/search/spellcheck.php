<?php 
/**
 * Создает индекс для поиска
 * @package Pilot
 * @subpackage Search
 * @author Rudenko Ilya <rudenko@delta-x.ua> Делал это Дима Марковский
 * @copyright Delta-X, ltd. 2009
 * @cron none
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
$reload = globalVar($argv[1],'');
$DB = DB::factory('default');

// Блокировка паралельного запуска скрипта
Shell::collision_catcher();

$query = "
	select *
	from search_content
	where 
		content not like '%і%'
		and content not like '%є%'
		and content not like '%ї%'
";
if ($reload!='reload'){
	$query .= " and DATE_FORMAT(TIMESTAMP(change_dtime), '%Y.%m.%d') = '".date('Y.m.d', time() - 24*3600)."'";
}
$data = $DB->query($query);
$DB->query("CREATE TEMPORARY TABLE tmp_spell LIKE search_spell_check;");
reset($data);
while (list(, $row) = each($data)) {
	echo "[i] $index/".count($data)."\n";
	$text = iconv(CMS_CHARSET, 'utf-8', $row['content']);
	$Download = new Download();
	$response = $Download->post("http://speller.yandex.net/services/spellservice/checkText", array('text' => $text, 'ie' => 'utf-8', 'options' => 543, 'lang' => 'ru'));
	$response = iconv(CMS_CHARSET, 'utf-8//IGNORE', $response);
	$dom = new DOMDocument();
	$dom->loadXML($response);
	$errors = $dom->getElementsByTagName('error');
	foreach ($errors as $error) {
		
		$errorword = iconv('UTF-8', CMS_CHARSET.'//IGNORE', $error->getElementsByTagName('word')->item(0)->nodeValue);
		$checks = $error->getElementsByTagName('s');
		$checkword = '';
		foreach ($checks as $check) {
			$checkword .= $check->nodeValue." ";
		}
		$checkword = iconv('UTF-8', CMS_CHARSET, $checkword);
		
		$query = "insert into tmp_spell set content_id='$row[id]', field_id = '".$row['field_id']."', error = '".$errorword."', fixed = '".$checkword."'";		
		$DB->insert($query);
	}

	sleep(rand(3, 8));
}
$DB->query("LOCK TABLES tmp_spell WRITE, search_spell_check WRITE;");
$DB->query("DELETE FROM search_spell_check;");
$DB->query("INSERT INTO search_spell_check SELECT * FROM tmp_spell;");
$DB->query("UNLOCK TABLES;");

?>