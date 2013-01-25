<?php
/**
 * Состояние рассылки писем
 * @package Maillist
 * @subpackage Content_Admin
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */


$message_id = globalVar($_GET['message_id'], 0);

$query = "
	SELECT 
		tb_queue.email,
		IFNULL(tb_user.login, '<span style=\"color:gray;\">не зарегистрирован</span>') as login,
		concat('<a href=\"./Param/?message_id=', tb_queue.message_id, '&email=', tb_queue.email, '\">Переменные</a>') AS vars,
		tb_queue.delivery,
		DATE_FORMAT(tb_queue.tstamp, '".LANGUAGE_DATETIME_SQL."') AS tstamp
	FROM maillist_queue AS tb_queue
	LEFT JOIN auth_user AS tb_user ON tb_user.email=tb_queue.email
	WHERE tb_queue.message_id='".$message_id."'
	ORDER BY tb_queue.email ASC
";
$cmsTable = new cmsShowView($DB, $query);
$cmsTable->setParam('add', false);
$cmsTable->setParam('edit', false);
$cmsTable->setParam('delete', false);

$cmsTable->addColumn('login', '30%', 'left', 'Пользователь');

$cmsTable->addColumn('email', '30%', 'left', 'E-mail');
$cmsTable->addColumn('vars', '10%', 'center', 'Переменные');
$cmsTable->addColumn('delivery', '10%', 'center');
$cmsTable->addColumn('tstamp', '10%', 'center');
echo $cmsTable->display();
unset($cmsTable);

echo "
	<p>
	<a href='/".LANGUAGE_URL."action/admin/maillist/backerrors/?message_id=$message_id&_return_path=".CURRENT_URL_LINK."'>Поставить письма с ошибками обратно в очередь</a>
	</p>
	<p>
	<a href='/".LANGUAGE_URL."action/admin/maillist/clear_queue/?message_id=$message_id&_return_path=".CURRENT_URL_LINK."'>Очистить очередь</a>
	</p>
";
?>
<div class="context_help">
Очистка очереди приведёт к тому, что при повторной отправке письма оно будет повторно доставлено всем адресатам.
</div>