<?php
/**
 * Напоминание пароля
 * @package User
 * @subpackage Content_Site
 * @author Rudenko Ilya <rudenko@delta-x.com.ua>
 * @copyright Delta-X, ltd. 2005
 */
if (isset($_SESSION['ActionError']['email'])) {
	$TmplContent->set('email', $_SESSION['ActionError']['email']);
}

$TmplContent->set('captcha_html', Captcha::createHtml());

?>