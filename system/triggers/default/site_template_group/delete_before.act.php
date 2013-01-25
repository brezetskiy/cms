<?php
/**
* Удаляет файл с шаблоном, до того как удалит запись в таблице
* @package Main_Temaplates
* @subpackage Actions
* @version 3.0
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2004
*/

if (!empty($this->OLD['name'])) {
	Filesystem::delete(SITE_ROOT.'design/'.$this->OLD['name'].'/');
}

?>