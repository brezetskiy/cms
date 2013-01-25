<?php
/** 
 * Событие, которое возвращет данные переданные в него в ActionError массиве 
 * 
 * Это событие необходимо, когда пользователь добавляет в справчник новый параметр
 * и необходимо перегрузить родительское окно, сохранив все значения формы. 
 * 
 * @package Pilot 
 * @subpackage CMS 
 * @author Rudenko Ilya <rudenko@id.com.ua> 
 * @copyright Delta-X, ltd. 2007
 */

Action::onError();


?>