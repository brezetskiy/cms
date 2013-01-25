<?php
/**
* Список заблокированных подписчиков
*
* @package Pilot
* @subpackage Maillist
* @version 3.0
* @author Eugen Golubenko <eugen@delta-x.com.ua>
* @copyright Copyright 2006, Delta-X ltd.
*/


function setdate($row) {
	
	$row['dates'] = " <a onclick=\"CenterWindow('/tools/cms/admin/stoplist.php?id=".$row['id']."', 'email', 750, 500, 'yes', 'no'); return false;\" href=\"javascript:void(0);\">$row[dates]</a>";

	return $row;
}

$query = "
	SELECT 
		id,
		email,
		message,
		GROUP_CONCAT(DISTINCT DATE_FORMAT(dtime, '".LANGUAGE_DATE_SQL."') ORDER BY dtime SEPARATOR ', ') AS dates
	FROM maillist_stoplist
	GROUP BY email
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('row_filter', 'setdate');
$cmsTable->setParam('title', 'Заблокированные подписчики');
$cmsTable->setParam('edit', false);

$cmsTable->addColumn('email', '20%', null, 'E-mail');
$cmsTable->setColumnParam('email', 'order', 'email');
$cmsTable->addColumn('dates', '40%', null, 'Даты блокировки');

echo $cmsTable->display();
unset($cmsTable);

?>