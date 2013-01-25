<?php
/**
 * Работа с заказами
 * @package Pilot
 * @subpackage ShopOrder
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */


/**
 * Базовый класс обработки заказа
 */
class ShopOrder {
	
	
	/**
	 * id пользователя
	 * @var int
	 */
	public $user_id 			 = 0;
	
	
	/**
	 * id пользователя
	 * @var int
	 */
	public $session_id 			 = 0;
	
	
	/**
	 * id заказа
	 * @var int
	 */
	public $order_id 			 = 0;
	
	/**
	 * Инфомрация о заказе
	 * @var array
	 */
	public $order 				 = array();
	
	/**
	 * Товары в заказе
	 * @var array
	 */
	public $order_products 		 = array();
	
	/**
	 * Таблица, которая содержит информацию о товарах
	 * @var array
	 */
	public $order_products_info  = array();
	
	
	/**
	 * Количество в данном раздел продуктов
	 * @var int
	 */
	public $total_order_products = 0;

	
	/**
	 * Конструктор
	 */
	public function __construct() {
		global $DB;
		
		$this->order_id   = globalVar($_SESSION['shop_order_id'], 0);
		$this->session_id = session_id(); 
		
		if(empty($this->order_id)){
			if(!Auth::isLoggedIn()){
				$this->user_id = 0;
			} else {
				$user = Auth::getInfo();
				$this->user_id = $user['id'];
			}
			
			self::loadOrder();
		} else {
			$query = "SELECT * FROM shop_order WHERE id='{$this->order_id}' AND accepted=0"; 
			$this->order = $DB->query_row($query);    
			
			if($DB->rows == 0){
				$_SESSION['shop_order_id'] = 0; 
				$this->order_id 		   = 0;
			}
		}
		 
		if(!empty($this->order_id)){
			$query = "select product_id from shop_order_product where order_id='{$this->order_id}'";
			$this->order_products = $DB->fetch_column($query, "product_id", "product_id");
			$this->total_order_products = $DB->rows;      
		}
	}
	
	
	/**
	 * Возвращает информацию про незавершенный заказ пользователя
	 * @param int $user_id
	 * @return bool
	 */
	protected function loadOrder() { 
		global $DB;
		
		$where = (empty($this->user_id))?"session_id='{$this->session_id}'":"user_id='{$this->user_id}'";
		
		$query = "SELECT * FROM shop_order WHERE $where AND accepted=0"; 
		$order = $DB->query_row($query);
		
		if(!empty($order)){
			$this->order_id = $order['id'];
			$this->order 	= $order; 
			return true; 
		}
		
		return false; 
	}
	
	
	/**
	 * Загружает информацию про товары в заказе пользователя
	 * @param int $user_id
	 * @return bool
	 */
	protected function reloadOrderProductsInfo(){
		global $DB;
		
		if (empty($this->order_products)){
			return false;
		} 
		
		$this->order_products_info = array();
		$query = "
			SELECT id, product_id, name, description, amount, price
			FROM shop_order_product 
			WHERE order_id = '{$this->order_id}' ".where_clause("product_id", $this->order_products)."
		"; 
		$this->order_products_info = $DB->query($query, "product_id");   
	}
	
	
	/**
	 * Возвращает информацию о товарах заказа   
	 * @return array
	 */
	public function getOrderProductsInfo() { 
		if (empty($this->order_products_info)){
			self::reloadOrderProductsInfo();   
		}
		return $this->order_products_info;
	}
	
	
	/**
	 * Добавление товара в заказ
	 * @param int $product_id  
	 * @param int $amount  
	 * @return int         
	 */
	public function addProduct($product_id, $amount=1, $pricevar = 'price', $group_id=0) {
		global $DB;
		
		$group_id = globalVar($_REQUEST['group_id'], $group_id);   
				
		if(empty($group_id)){
			$group_id = $DB->result("SELECT group_id FROM shop_product WHERE id = '$product_id'");
		}
		
		$Shop = new Shop($group_id);
		$product = $Shop->getProductInfo($product_id); 
		$product['amount'] = $amount;
		
		if(empty($this->order_id)){
			$this->order['user_id']    = $this->user_id;
			$this->order['session_id'] = $this->session_id; 
			$this->order['ip']  	   = HTTP_IP;
			$this->order['local_ip']   = HTTP_LOCAL_IP;
			$query = "
				INSERT INTO shop_order 
				SET user_id     = '{$this->order['user_id']}',
				 	session_id  = '{$this->order['session_id']}',
					ip 			= '{$this->order['ip']}',
					local_ip 	= '{$this->order['local_ip']}' 
			";
			$this->order_id = $DB->insert($query); 
			$_SESSION['shop_order_id'] = $this->order_id; 
		} 
		
		$product['description'] = (isset($product['description']))?$product['description']:"";
		$product['amount']      = (isset($product['amount']))?$product['amount']:0;
		$product[$pricevar]     = (isset($product[$pricevar]))?$product[$pricevar]:0;
		
		$query = "
			INSERT INTO shop_order_product 
			SET order_id    = '{$this->order_id}',
				product_id  = '{$product['id']}',
				name  		= '{$product['name']}',
				description = '{$product['description']}',
				amount  	= '{$product['amount']}',   
				price  		= '{$product[$pricevar]}'
			ON DUPLICATE KEY UPDATE amount=amount+'{$product['amount']}', price=VALUES(price) 
		"; 
		$DB->insert($query);
		$this->order_products[$product['id']] = $product['id']; 
		$this->total_order_products = $this->total_order_products + 1;
	}
	
	
	/**
	 * Удаление продукта из заказа
	 *
	 * @param int $product_id
	 * @return void
	 */
	public function deleteProduct($id){
		global $DB;
		
		if(empty($id)){
			return false;
		}  
		
		$query = "DELETE FROM shop_order_product WHERE order_id = '{$this->order_id}' ".where_clause("product_id", $id); 
		$DB->query($query);    
		
		if(is_array($id)){
			reset($id);
			while(list(, $row) = each($id)){  
				unset($this->order_products[$row]);
				$this->total_order_products = $this->total_order_products - 1; 
			}
		} else {
			unset($this->order_products[$id]);
			$this->total_order_products = $this->total_order_products - 1;
		}
		
	}
	
	
	/**
	 * Изменение количества товаров в корзине
	 * @param array $amounts
	 * @return void
	 */
	public function recount($amounts){
		global $DB;
		
		if(empty($amounts)){
			return false;
		}
		
		$insert = array();
		
		reset($amounts);
		while(list($product_id, $count) = each($amounts)){
			$insert[] = "('{$this->order_id}', '$product_id', '$count')";
		}
		
		$query = "
			INSERT INTO shop_order_product (order_id, product_id, amount) 
			VALUES ".implode(",", $insert)." 
	    	ON DUPLICATE KEY UPDATE amount=VALUES(amount)
	    ";
		$DB->insert($query); 
		self::reloadOrderProductsInfo();          
	}
	
	
	/**
	 * Отметка заказа, как принятого
	 * @param array $data
	 */
	public function complete($data){
		global $DB;
		
		$data['user_id'] = (isset($data['user_id']))?$data['user_id']:0;
		$data['name']    = (isset($data['name']))?$data['name']:"";
		$data['phone'] 	 = (isset($data['phone']))?$data['phone']:"";
		$data['address'] = (isset($data['address']))?$data['address']:"";
		$data['email']   = (isset($data['email']))?$data['email']:"";
		$data['comment'] = (isset($data['comment']))?$data['comment']:"";
		$data['discount_value'] = (isset($data['discount_value']))?$data['discount_value']:"0";		
		$query = "
			UPDATE shop_order  
			SET user_id = '$data[user_id]',
				name 	= '$data[name]',
				phone	= '$data[phone]',
				address	= '$data[address]',
				email 	= '$data[email]',
				comment = '$data[comment]',
				discount_value = '$data[discount_value]',
				accepted = '1'
			WHERE id = '{$this->order_id}'
		";
		$DB->update($query);
		$_SESSION['analytics_ecommerce_order_id'] = $_SESSION['shop_order_id'];
		unset($_SESSION['shop_order_id']);
	}
	
	
	/**
	 * Вычисление и добавление комиссии в заказ
	 */
	public function setCommission(){
		global $DB;
		
		$total = 0;
		$products = $this->getOrderProductsInfo();
		
		if(!empty($products)){
			reset($products);
			while(list(, $product) = each($products)){
				$total = $total + ($product['amount']*$product['price']);
			}
		}
		
		$user = Auth::getInfo();
		$commission = str_replace(",", ".", round($total*$user['referral_percent']/100, 2));
		
		$query = "
			UPDATE shop_order  
			SET commission = '$commission'
			WHERE id = '{$this->order_id}'
		";
		$DB->update($query);
	}
	
	
	/**
	 * Возвращает форму корзины
	 *
	 * @return string
	 */
	public function showCartForm(){
		$TmplCart = new Template('shoporder/cart');
		$TmplCart->set("order_products_count", $this->total_order_products);
		return $TmplCart->display();
	}


	/**
	 * Возвращает форму заказа
	 * @return string
	 */
	public function showOrderForm(){
		$TmplOrder = new Template('shoporder/cart_order');
		$total_all = 0;
		$class     = 0; 
		
		/**
		 * Введение перерасчета валют
		 */
		$data  = array();
		$rates = array();
		$alternative_currencies = explode(",", SHOPORDER_CURRENCIES);
		$TmplOrder->set("currencies", trim(SHOPORDER_CURRENCIES)); 
		
		if(!empty($alternative_currencies)){
			reset($alternative_currencies);
			while(list(, $currency) = each($alternative_currencies)){
				$data[] = array('from_id' => SHOPORDER_CURRENCY_DEFAULT, 'to_id' => $currency);
			}
			
			$rates = Currency::getRatesBy($data);
			reset($rates);
			while(list(, $rate) = each($rates)){
				$TmplOrder->iterate("/currency/", null, $rate); 
			}
		}
		
		$products = $this->getOrderProductsInfo();
		$TmplOrder->set("rows_count", $this->total_order_products);
		$TmplOrder->set("order_id", $this->order_id);
		
		reset($products);
		while(list(, $row) = each($products)){
			$row['class'] = ($class % 2 == 0)?"even":"odd"; 
			$class = $class+1;
			$row['total'] = $row['price']*$row['amount'];
			$total_all = $total_all + $row['total'];
			$TmplProducts = $TmplOrder->iterate("/products/", null, $row);
			
			// перерасчет валют
			reset($rates);  
			while(list(, $rate) = each($rates)){
				$new_currency_price = round($row['price'] * $rate['rate'], 2);
				$new_currency_total = round($row['total'] * $rate['rate'], 2);
				$rate['product_id'] = $row['product_id'];
				$rate['price'] = (empty($new_currency_price)) ? '' : $new_currency_price;
				$rate['total'] = (empty($new_currency_total)) ? '' : $new_currency_total;
				$TmplOrder->iterate('/products/prices/', $TmplProducts, $rate);
			}
		}
		
		reset($rates);  
		while(list(, $rate) = each($rates)){
			$new_currency_total_all = round($total_all * $rate['rate'], 2);
			$rate['total_all'] = (empty($new_currency_total_all)) ? '' : $new_currency_total_all;
			$TmplOrder->iterate('/total_prices/', null, $rate);
		}
		$TmplOrder->set("total_all", $total_all);
		return $TmplOrder->display();
	} 
	
}

?>