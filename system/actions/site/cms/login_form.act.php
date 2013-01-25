<?php
/**
 * Форма для логина, AJAX версия
 * @package Pilot
 * @subpackage CMS
 * @author Eugen Golubenko <eugen@delta-x.ua>
 * @copyright Delta-X, ltd. 2009
 */

$Template = new Template(SITE_ROOT.'templates/cms/site/ajax_login');

/**
 * Хакерам показываем капчу в форме логина
 */
if (true or Auth::isHacker()) {
	$Template->set('captcha_html', Captcha::createHtml('login'));
}

echo $Template->display();

