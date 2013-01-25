<?php
/**
 * ‘ормирование всплывающего под формой поиска списка
 * @package Pilot
 * @subpackage Shop
 * @author Miha Barin <barin@delta-x.ua>
 * @copyright Delta-X, ltd. 2010
 */

$search = globalVar($_REQUEST['text'], '');
$search = substr(trim($search), 0, 64);
$search = preg_replace("/[^\w\x7F-\xFF\s]/", " ", $search);
$good   = trim(preg_replace("/\s(\S{1,2})\s/", " ", ereg_replace(" +", "  "," $search ")));
$good   = ereg_replace(" +", " ", $good);

$search_words = explode(" ", $good);     
$products 	= array();
$categories = array();


/**
 * ѕоиск
 */
if(count($search_words) == 1){
	// если задано лишь одно поисковое слово
	$query_products   = "select id, name, price, _url from shop_product where name like '%".implode(" ", $search_words)."%' order by name asc limit 10";
	$query_categories = "select id, name, url from shop_group where name like '%".implode(" ", $search_words)."%' order by name asc limit 5"; 
} else {
	// если задано больше одного поискового слова
	$query_products = "
		select id, name, price, _url, match(name, _search) against('".implode(" ", $search_words)."' IN BOOLEAN MODE) as rel
		from shop_product
		where match(name, _search) against('".implode(" ", $search_words)."' IN BOOLEAN MODE)
		order by rel desc 
		limit 10
	";
	$query_categories = "
		select id, name, url, match(name) against('".implode(" ", $search_words)."' IN BOOLEAN MODE) as rel
		from shop_group 
		where match(name) against('".implode(" ", $search_words)."' IN BOOLEAN MODE)
		order by rel desc
		limit 5
	"; 
} 

$products   = $DB->query($query_products, "id");
$categories = $DB->query($query_categories, "id");


/**
 * ≈сли никаких данных не обнаружено
 */
if(empty($products) && empty($categories)){
	$_RESULT['search_helper'] = ""; 
	$_RESULT['javascript']    = "$('#search_helper').hide();";
	Action::finish();
}

/**
 * ‘ормирование вывода
 */
$TmplHelper = new Template(SITE_ROOT.'templates/shop/search_helper');
$TmplHelper->set("products_rows_count", count($products));
$TmplHelper->set("categories_rows_count", count($categories));
 

/**
 * ¬ведение перерасчета валют
 */
$data  = array();
$rates = array();
$alternative_currencies = explode(",", SHOPORDER_CURRENCIES);

if(!empty($alternative_currencies)){
	
	reset($alternative_currencies);
	while(list(, $currency) = each($alternative_currencies)){
		$data[] = array('from_id' => SHOPORDER_CURRENCY_DEFAULT, 'to_id' => $currency);
	}
	$rates = Currency::getRatesBy($data);
}


/**
 * —писок картинок дл€ найденых фотографий
 */
$query = "select * from cms_table_static where db_name='{$DB->db_name}' and table_name='shop_product'";
$info = $DB->query_row($query);

$query = "
	SELECT *, description_".LANGUAGE_CURRENT." AS comment
	FROM gallery_photo
	WHERE `{$info['parent_field_name']}` IN (".implode(",", array_keys($products)).")
	ORDER BY priority ASC
";
$return = $DB->query($query);
$photos = array();

if(!empty($return)){ 
	reset($return);
	while (list($index, $row) = each($return)) {
		$photos[$row['group_id']] = '/i/shop_220/gallery_photo/photo/'. Uploads::getIdFileDir($row['id']) .'.'. $row['photo']; 
	}		
}

reset($products);
while(list(, $product) = each($products)){
	$product['photo'] = (!empty($photos[$product['id']])) ? $photos[$product['id']] : ''; 
	$TmplProducts = $TmplHelper->iterate("/products/", null, $product);
	
	reset($rates);  
	while(list(, $rate) = each($rates)){
		$new_currency_price = round($product['price'] * $rate['rate'], 2);
		$rate['product_id'] = $product['id'];
		$rate['price'] = (empty($new_currency_price)) ? '' : $new_currency_price;
		$TmplHelper->iterate('/products/prices/', $TmplProducts, $rate);
	}
}

reset($categories);
while(list(, $category) = each($categories)){
	$category['url'] = str_replace("Shop", "", $category['url']);
	$TmplHelper->iterate("/categories/", null, $category);
}

$_RESULT['search_helper'] = $TmplHelper->display();
$_RESULT['javascript']    = "$('#search_helper').show(); currency_switch($_SESSION[current_currency]);";




?>