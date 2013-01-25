<?php
/** 
 * Оформление заказа
 * @package Pilot 
 * @subpackage ShopOrder 
 * @author Miha Barin <barin@delta-x.com.ua> 
 * @copyright Delta-X, ltd. 2010
 */ 
 


if(isset($_REQUEST['user_name']) && trim($_REQUEST['user_name']) == ""){
	Action::onError("Поле \"Ваше имя (ФИО)\" является обязательным для заполнения.");
}
if(isset($_REQUEST['user_phone']) && trim($_REQUEST['user_phone']) == ""){
	Action::onError("Поле \"Мобильный телефон\" является обязательным для заполнения.");
}
if(isset($_REQUEST['user_email']) && trim($_REQUEST['user_email']) == ""){
	Action::onError("Поле \"Электронная почта\" является обязательным для заполнения."); 
}
if(isset($_REQUEST['user_email']) && preg_match(VALID_EMAIL, $_REQUEST['user_email']) == 0){
	Action::onError("Неверно заполнено поле \"Электронная почта\"."); 
}

$order = new ShopOrder();

if($order->total_order_products == 0){ 
	Action::onError("Невозможно оформить пустой заказ.");  
}
if(Auth::isLoggedIn()){
	$user = Auth::getInfo();
	$data['discount_value'] = $user['discount_value']; 
}
$data['user_id'] = Auth::getUserId(); 
$data['name']    = globalVar($_REQUEST['user_name'], '');   
$data['phone']   = globalVar($_REQUEST['user_phone'], '');
$data['email']   = globalVar($_REQUEST['user_email'], '');
$data['comment']   = globalVar($_REQUEST['user_comment'], '');
$data['timeorder']   = globalVar($_REQUEST['memory_date'], '').' '. globalVar($_REQUEST['memory_time'], '');

$data['address'] = 'улица '. globalVar($_REQUEST['user_address'], '');
$home = globalVar($_REQUEST['user_home'], ''); if(!empty($home)) $data['address'] .= ', дом '.$home;
$apartment =  globalVar($_REQUEST['user_apartment'], ''); if(!empty($apartment)) $data['address'] .= ', квартира '.$apartment;
$floor = globalVar($_REQUEST['user_floor'], ''); if(!empty($floor)) $data['address'] .= ', этаж '.$floor;

if(empty($data['user_id'] )){
	$data['user_id'] = 0;
}

$order->complete($data); 



$Template = new TemplateDB('cms_mail_template', 'ShopOrder', 'email');
$Template->set($data); 
$Template->set('id', $order->order_id);

$product = $order->getOrderProductsInfo();
reset($product);
while (list($index, $row) = each($product)) {
	$row['price'] = (empty($row['price']))?'':$row['price'].' грн.';
	$Template->iterate('/product/', null, $row);
}

$email_list = explode(',', SHOPORDER_NOTIFY_EMAIL);
//x($email_list);
//exit;
while (list(,$email) = each($email_list)) {
	$Sendmail = new Sendmail(CMS_MAIL_ID, 'Сформирован новый заказ на сайте', $Template->display());
	$Sendmail->send(trim($email),true);

}

if (isset($_POST['_return_path']))
echo ("<script type=\"text/javascript\">window.location = \"".$_POST['_return_path']."\";</script></body></html>");

?>  