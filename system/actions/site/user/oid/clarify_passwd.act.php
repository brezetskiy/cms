<?php

/**
 * ������������� �������
 * 
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2011
 */


/**
 * �������� ������ 
 */
if(empty($_SESSION['oid_widget']['name'])){
	echo "<div class='widget_error'>���� ������ ���������</div>";
	exit; 
}

$_RESULT['javascript'] = "$('#oid_widget__".$_SESSION['oid_widget']['name']."_clarify_manual_passwd').val('".gen_password(8)."');";


?>