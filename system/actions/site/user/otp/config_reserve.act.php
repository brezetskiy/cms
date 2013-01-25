<?php
/**
 * �������� ����� ��������� ����� �������
 *
 * @package Pilot
 * @subpackage User
 * @author Miha Barin <barin@delta-x.com.ua>
 * @copyright Copyright 2011, Delta-X ltd.
 */

if(!Auth::isLoggedIn()) Action::onError("�� �� ��������������.");
$user = Auth::getInfo();

$codes = $DB->fetch_column("SELECT code FROM auth_user_otp_code WHERE user_id = '{$user['id']}'");
$content = "\n ��������� ���� ������� ��� ������� ������ {$user['login']}: \n\n". implode("; \n", $codes) .".";

$file = TMP_ROOT."otp_reserve_codes_".rand(0, 1000000).".txt"; 
file_put_contents($file, $content);
 

/**
 * �������� ����������� ���� ��� ���������� �����
 */
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.$user['login'].'_otp_reserve_codes.txt');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
ob_clean();
flush(); 
readfile($file);


/**
 * �������� �����
 */
unlink($file);
exit;


?>