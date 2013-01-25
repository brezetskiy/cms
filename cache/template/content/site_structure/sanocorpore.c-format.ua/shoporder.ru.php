<script type="text/javascript">
    var blocked_days = [];
    var available_days = <?php echo $this->vars['str_time']; ?>;
</script>

		<script type="text/javascript" src="/design/eda/js/datepicker/jquery.ui.datepicker-ru.js"></script>
		<script type="text/javascript" src="/design/eda/js/datepicker/jquery-ui-1.8.18.js"></script>
		<script type="text/javascript" src="/design/eda/js/frontend.js"></script>
		



<div class="cart_order">
	<div class="top-in">
		<div class="bott-in">
			<form id="cart_order" method="post" action="/action/shoporder/cart_order_handler/">
			<input type="hidden" id="is_deleted" value="<?php echo $this->vars['is_deleted']; ?>">
				<table id="bsk-order"  cellspacing="0" cellpadding="0">
				<?php if($this->vars['rows_count']==0): ?> 
					<tbody> 
						<tr align="center">
							<td colspan="5" style="padding:10px; font-size:14px;"><strong>Корзина пуста</strong></td>
						</tr>
					</tbody> 
				<?php else: ?>       
					<thead>
						<tr class="bsk-th">
						<th class="first" ><span>Блюдо</span></th><th>Название</th><th>Количество</th><?php if($this->vars['user_name']): ?><th >Цена за ед.</th><th >Скидка</th><th>Цена со скидкой</th><?php else: ?><th>Цена</th><?php endif; ?><th class="last"><span>&nbsp;</span></th>
						</tr>
							
					</thead>
					<tbody> 
						<?php
			reset($this->vars['/products/'][$__key]);
			while(list($_products_key,) = each($this->vars['/products/'][$__key])):
			?>
							<tr>							
							<td style="width:170px;"><img src="<?php echo $this->vars['/products/'][$__key][$_products_key]['img']; ?>" /></td>
							<td class="bsk-title"><a href="/<?php echo $this->vars['/products/'][$__key][$_products_key]['url']; ?>.html"><?php echo $this->vars['/products/'][$__key][$_products_key]['name']; ?></a></td>
							
							<td class="in-b"><div class="weight-block">
							<input type="text" value="<?php echo $this->vars['/products/'][$__key][$_products_key]['amount1']; ?>" class="amount-input small" onchange="cartOrderHandler('recount');" name="amount1[<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>]" maxlength="2"/>, 
							<input type="text" value="<?php echo $this->vars['/products/'][$__key][$_products_key]['amount2']; ?>" class="amount-input-s" maxlength="3" onchange="cartOrderHandler('recount');" name="amount2[<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>]"> 
							<?php if($this->vars['/products/'][$__key][$_products_key]['measure'] == 'мл'): ?> л.<?php elseif($this->vars['/products/'][$__key][$_products_key]['measure'] == 'грамм'): ?> кг.<?php endif; ?></div></td>
							<?php if($this->vars['/products/'][$__key][$_products_key]['discount_price'] >= 0): ?><td><?php echo $this->vars['/products/'][$__key][$_products_key]['price']; ?></td><?php endif; ?>
							<?php if($this->vars['/products/'][$__key][$_products_key]['discount_price'] >= 0): ?><td class="discount"><?php echo $this->vars['/products/'][$__key][$_products_key]['discount_price']; ?>%</td><?php endif; ?>
							<td class="bsk-yellow"><span data-price=<?php echo $this->vars['/products/'][$__key][$_products_key]['price']; ?>><?php echo $this->vars['/products/'][$__key][$_products_key]['total']; ?></span> грн</td>
							<td><a title="Удалить" onclick="return deleteFromCart(<?php echo $this->vars['/products/'][$__key][$_products_key]['product_id']; ?>);" href="#java" class='del' style="font-size:12px;">delete</a></td>
							</tr>

						<?php 
			endwhile;
			?>	
					</tbody>
				<?php endif; ?>
				</table>

			</form>
		</div>	
	</div>
</div>

			<table class="titlePage titleh3"><tr><td class="tp-left"></td><td class="tp-middle" ><h3>Выбор даты и времени доставки</h3></td><td class="tp-right"></td></tr></table>
			
			<table id="bsk-cal">
				<tr><td>Чтобы выбрать дату доставки кликните  на число, которое Вас устраивает</td>
				<td width="90"></td><td>Чтобы выбрать время доставки,  кликните по стрелкам.</td></tr>
				<tr><td>
						<div class="datepicker">
							<div class="datepicker-before"></div>
							<div id="datepicker"></div>
							<div class="datepicker-after"></div>
						</div>
					</td>
					<td width="90"></td>
					
					<td align=top><div class="cprepare"><div class="prepare">Выберите пожалуйста другой день или время</div></div><div id="timepicker"><a class="tp-prev"></a><a class="tp-next"></a>
						<div class="clock"><div><input type="text" name="hour" value="15" readonly="readonly" class="hours"/><span>:</span><input readonly="readonly" class="minutes" name="minute" value="30" type="text"></div></div>
					</div></td>
				</tr>
			</table>
			
			
	<table class="titlePage titleh3"><tr><td class="tp-left"></td><td class="tp-middle" ><h3>Ваши данные</h3></td><td class="tp-right"></td></tr></table>
	
<div class="bsk-form">
<div id='action_error' class='action_error' style="color:yellow;"><?php echo $this->vars['error']; ?></div>
	<form id="Form" name="Form" action="/action/shoporder/order/" method="POST" >   
		<input type="hidden" name="_return_path" value="/ShopOrder/Complete/"> 
		<input type="hidden" name="_error_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input type="hidden" name="user_id" value="<?php echo $this->vars['user_id']; ?>"> 
		<table id="bsk-form">
					<tr>
						<td >
							<label>Ваше имя*</label>
							<input type="text" id="name" class="input" maxlength="40" name="user_name" value='<?php echo $this->vars['user_name']; ?>'>							
						</td>
						<td width="40px"> </td>
						<td>
							<label>Телефон*</label>
							<input type="text" class="input" id="phone" name="user_phone" value="<?php echo $this->vars['user_phone']; ?>">
						</td>
					</tr>
					<tr>
						<td>
							<label>Улица*</label>
							<input type="text" id="adress" name="user_address" class="input" maxlength="150">
							<table><tr>
								<td>
									<label>Номер дома*</label>
									<input type="text" id="home_id" name="user_home" class="small" maxlength="3" >
								</td>
								<td>
									<label>Квартира</label>
									<input  type="text" id="apartment_id" name="user_apartment" class="small" maxlength="3">
								</td>
								<td>
									<label>Этаж</label>
									<input type="text" id="floor_id" class="small" name="user_floor" maxlength="3">
								</td>
							</tr></table>
						</td>
						<td width="40px"> </td>
						<td >
							<label>Комментарии к заказу</label>
							<textarea class="input" id="comment" name="user_comment"></textarea>
						</td>
					</tr>
		</table>
		 <p>
                <?php
			reset($this->vars['/products/'][$__key]);
			while(list($_products_key,) = each($this->vars['/products/'][$__key])):
			?>
					<span><?php echo $this->vars['/products/'][$__key][$_products_key]['name']; ?>,</span>
                <?php 
			endwhile;
			?>
				<?php if($this->vars['measure_l']): ?>общий объем: <?php echo $this->vars['measure_l']; ?> л. <?php endif; ?><?php if($this->vars['measure_g']): ?> общий вес: <?php echo $this->vars['measure_g']; ?> кг. <?php endif; ?>
				На доставку: <span class="big" id="bsk-date">3 июля</span> к <span id="bsk-time" class="big">17:30</span>
            </p>
            <p>
                К оплате: <span id="bsk-price" class="price"><?php echo $this->vars['total_all']; ?></span> <span class="price-currency price">грн.</span>
            </p>

	 <div class="bsk-button">
				<input type="submit" value="oформить ЗАКАЗ" class="button disabled" id="bsk-form-submit"  />
	</div>
	<input type="hidden" id="cart-memory-date" value="0" name="memory_date"/>
	<input type="hidden" id="cart-memory-time" value="0" name="memory_time">
	</form> 
</div><!--form-block end-->

<br/><br/>
<script>
	
		$('#Form').submit(function() {
			
			if(!$("#name").val()){
				$('#action_error').html('Поле "Ваше имя" является обязательным для заполнения.');
				return false;
			}
			if(!$("#phone").val()){
				$('#action_error').html('Поле "Телефон" является обязательным для заполнения.');
				return false;
			} else {
				phone = $("#phone").val();
				if (!/^\d{3} \d{3}(-|\s){1}\d{2}(-|\s){1}\d{2}$/.test(phone)) {
					$('#action_error').html('Не верно заполнено поле "Телефон". Пример 067 322-43-21.');
					return false;
				}
			}
			$('#action_error').html('');
				if(!$("#adress").val()){
				$('#action_error').html('Поле "Улица" является обязательным для заполнения.');
				return false;
			}
			if(!$("#home_id").val()){
				$('#action_error').html('Поле "Номер дома" является обязательным для заполнения.');
				return false;
			}
			return true;
		});
	
  	function cartOrderHandler(action){
		AjaxRequest.form('cart_order', 'Подождите...', {'action':action});
		//setTimeout("location.reload(true);", 100);
		return false;
  	} 
  	 
  	function deleteFromCart(id){
		AjaxRequest.send(null, '/action/shoporder/del_from_cart/', 'Удаление...', true, {'id':id});
		setTimeout("location.reload(true);", 100);
		return false;
  	}
</script>