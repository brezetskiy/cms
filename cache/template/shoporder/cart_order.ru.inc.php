<script>
  	function cartOrderHandler(action){
		AjaxRequest.form('cart_order', 'Подождите...', {'action':action});
  	} 
  	 
  	function deleteFromCart(id){
		AjaxRequest.send(null, '/action/shoporder/del_from_cart/', 'Удаление...', true, {'id':id});
  	}
</script>
<div style="float: right; margin: 5px 5px 0 0;"><a href="#" onclick="hideFadeCart()"><img border="0" src="/img/shoporder/cross.png"></a></div> 

<div class="cart_order">
	<center><h1><img src="/img/shoporder/cart2.png"> Ваша корзина</h1></center>
	<form id="cart_order" method="post" action="/action/shoporder/cart_order_handler/">
	<input type="hidden" id="is_deleted" value="<?php echo $this->vars['is_deleted']; ?>">
	
		<?php if($this->vars['currencies'] != ""): ?>
		<div style=" margin:0px 5px 5px 25px; float:right;">
			<span id="currency_<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>" class="currency_switcher_on"><a href="javascript:void(0);" onclick="currency_switch(<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>);">грн.</a></span>&nbsp;
			<?php
			reset($this->vars['/currency/'][$__key]);
			while(list($_currency_key,) = each($this->vars['/currency/'][$__key])):
			?>
				<span id="currency_<?php echo $this->vars['/currency/'][$__key][$_currency_key]['currency_to_id']; ?>" class="currency_switcher_off">
					<a href="javascript:void(0);" onclick="currency_switch(<?php echo $this->vars['/currency/'][$__key][$_currency_key]['currency_to_id']; ?>);"><?php echo $this->vars['/currency/'][$__key][$_currency_key]['symbol']; ?></a>
				</span>&nbsp;
			<?php 
			endwhile;
			?>
		</div>
		<div style="clear:both;"></div>
		<?php endif; ?> 	
		
		<div class="cart_order_overflow">
		<table align="center" cellpadding="0" cellspacing="0" border="0">
		<?php if($this->vars['rows_count']==0): ?>
				<tr align="center">
					<td colspan="7">Корзина пуста</td>
				</tr>
		<?php else: ?>          
			<thead>
				<tr align="center">
					<td>&nbsp;</td>
					<td>Номер</td>
					<td>Описание</td>
					<td>Цена</td> 
					<td>Количество</td>
					<td>Всего, (грн.)</td>
				</tr>	
			</thead>
			<tbody style="max-height:500px;overflow:auto;">
				<?php
			reset($this->vars['/products/'][$__key]);
			while(list($_products_key,) = each($this->vars['/products/'][$__key])):
			?>	
				<tr id="product_<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>" class="<?php echo $this->vars['/products/'][$__key][$_products_key]['class']; ?>">
					<td align="center" width="10px" title="удалить"><a href="javascript:void();" onclick="deleteFromCart(<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>);"><img src="/img/shoporder/delete.png" border="0"></a></td>
					<td align="left"  width="5%"><?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?></td>
					<td align="left"   width="60%"><b><?php echo $this->vars['/products/'][$__key][$_products_key]['name']; ?></b><p><?php echo $this->vars['/products/'][$__key][$_products_key]['description']; ?></p></td>  
					<td align="right"  width="10%">
						<span id="prodprice_<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>" ><?php echo $this->vars['/products/'][$__key][$_products_key]['price']; ?> грн</span>
						<?php
			reset($this->vars['/products/prices/'][$_products_key]);
			while(list($_products_prices_key,) = each($this->vars['/products/prices/'][$_products_key])):
			?>
							<span id="prodprice_<?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['currency_to_id']; ?>" style="display:none;"><?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['price']; ?> <?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['symbol']; ?></span>
						<?php 
			endwhile;
			?>
					</td>
					<td align="center" width="10%"><input onkeyup="cartOrderHandler('recount');" type="text" name="amount[<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>]" value="<?php echo $this->vars['/products/'][$__key][$_products_key]['amount']; ?>" size="3" style="text-align:right;"></td>
					<td align="right"  width="10%">
						<span id="prodprice_<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>"><span id="cart_order_total_<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>_<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>"><?php echo $this->vars['/products/'][$__key][$_products_key]['total']; ?></span> грн</span>
						<?php
			reset($this->vars['/products/prices/'][$_products_key]);
			while(list($_products_prices_key,) = each($this->vars['/products/prices/'][$_products_key])):
			?>
							<span id="prodprice_<?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['currency_to_id']; ?>" style="display:none;">
								<span id="cart_order_total_<?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['product_id']; ?>_<?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['currency_to_id']; ?>"><?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['total']; ?></span> <?php echo $this->vars['/products/prices/'][$_products_key][$_products_prices_key]['symbol']; ?>
							</span>
						<?php 
			endwhile;
			?> 
					</td> 
				</tr> 
				<?php 
			endwhile;
			?>	
				
				<tr class="title">
					<td colspan="5" align="right">Итого в корзине:</td>
					<td align="right"  width="5%" colspan="2">  
						<span id="prodprice_<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>"><span id="order_total_<?php echo SHOPORDER_CURRENCY_DEFAULT; ?>"><?php echo $this->vars['total_all']; ?></span> грн</span>
						<?php
			reset($this->vars['/total_prices/'][$__key]);
			while(list($_total_prices_key,) = each($this->vars['/total_prices/'][$__key])):
			?>
							<span id="prodprice_<?php echo $this->vars['/total_prices/'][$__key][$_total_prices_key]['currency_to_id']; ?>" style="display:none;"><span id="order_total_<?php echo $this->vars['/total_prices/'][$__key][$_total_prices_key]['currency_to_id']; ?>"><?php echo $this->vars['/total_prices/'][$__key][$_total_prices_key]['total_all']; ?></span> <?php echo $this->vars['/total_prices/'][$__key][$_total_prices_key]['symbol']; ?></span>
						<?php 
			endwhile;
			?>
					</td>
				</tr> 
			</tbody>
		<?php endif; ?>
		</table>
		</div>
		<table align="center" cellpadding="0" cellspacing="0" border="0" width="100%">
				<tr>
					<td align="center">   
					<img src="/img/shoporder/checked.png" align="top"> <a href="javascript:void(0);" onclick="document.location.reload();">Продолжить</a> &nbsp;
					<img src="/img/shoporder/cart2.png"  align="top"> <a href="javascript:void(0);" onclick="document.location.href='/ShopOrder/'">Оформить заказ</a> &nbsp;   
					</td>   
				</tr>
		</table>
	</form>
</div>
<br/><br/>