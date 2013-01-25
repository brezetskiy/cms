<?php
/**
 * Подтверждение получения письма, ввод регистрационного кода
 * @package User
 * @subpackage Content_Site
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */

$code = globalVar($_GET['code'], '');
header("Location: /".LANGUAGE_URL."action/user/confirm/?code=$code&_return_path=/User/");
exit;
?>