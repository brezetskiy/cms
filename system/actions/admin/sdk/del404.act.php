<?php
/** 
 * ��������� 404 ������ �� ������� ���� �� url
 * @package Pilot 
 * @subpackage CMS
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 

$url = globalVar($_GET['url'], '');
$url = str_replace("[AND]", "&", $url);

$query = "DELETE FROM cms_log_404 WHERE url = '$url'";
$DB->delete($query);
  
Action::setSuccess("������ ��� 404 ������ ��� URL: $url");

?>