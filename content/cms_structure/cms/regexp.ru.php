<?php
/**
 * Регулярные выражения для проверки вводимых в таблицы данных
 * @package CMS
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$query = "
	SELECT 
		id, 
		concat(
			'<b>', name_".LANGUAGE_CURRENT.", '</b>', 
			' (VALID_', UPPER(uniq_name), ')<br>',
			regular_expression, 
			'<br><span class=comment>', error_message_".LANGUAGE_CURRENT.", '</span>'
		) AS regular_expression
	FROM cms_regexp 
	ORDER BY id ASC";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->addColumn('regular_expression', '90%');
echo $cmsTable->display();
unset($cmsTable);
?>