<?php 
/**
 * ����� ����� �������
 *
 * @package Pilot
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */


define('LOGS_ACTIONS_ROOT', LOGS_ROOT.'actions');


/**
 * ��������� �������
 * 
 * TODO: 
 * 	- ���� ������, ��� ��� � ����� ������� ������; 
 *  - ����������� ���� �������; 
 *  - ����������� ������������� ������, ��� ��, ��� ������� ������ �����
 */ 


/**
 * ��������� ������� ����������� � ������������� �������
 */
$current_path = (!empty($_SESSION['log_actions_current_path'])) ? $_SESSION['log_actions_current_path'] : LOGS_ACTIONS_ROOT;
$TmplDesign->iterate('/onload/', null, array('function' => "load('$current_path', 0);")); 


?>