
	
var currency_rates = new Array();
var currency_current = 980;

var currency_num_decimal_places = 2;
var currency_dec_seperator = '.';
var currency_thousands_seperator = '';


/**
 * Инициализация переменных
 */ 
function currency_init(rates, current){
	currency_rates = rates;
	currency_current = current;
	currency_properties_bind();
} 


/**
 * Смена формата вывода числа
 */ 
function currency_number_format(num_decimal_places, dec_seperator, thousands_seperator){
	if(typeof num_decimal_places == 'undefined') num_decimal_places = 2;
	if(typeof dec_seperator == 'undefined') dec_seperator = '.';
	if(typeof thousands_seperator == 'undefined') thousands_seperator = '';
	
	currency_num_decimal_places = num_decimal_places;
	currency_dec_seperator = dec_seperator;
	currency_thousands_seperator = thousands_seperator;
}


/**
 * Привязка дополнительных свойств
 */ 
function currency_properties_bind(){
	var currency_codes = '';
	var c = 0;
	
	jQuery.each(currency_rates, function(id, item){
		if(c > 0) currency_codes = currency_codes + '|';
		var symbol = item.symbol;
		if(symbol == '$') symbol = "\\"+symbol;
		currency_codes = currency_codes + item.code + '|' + symbol;
		c++;
	});
	
	$('.currency_item').each(function(i){
		if($(this).get(0).tagName != 'INPUT') { 
			var value_html = $(this).html();  
			var value = parseFloat(jQuery.trim(value_html.replace(new RegExp("("+currency_codes+")", 'g'), '').replace(',', '.')));
			var currency = jQuery.trim(value_html.replace(new RegExp("[0-9,\.]*", 'g'), ''));
			
			$(this).data('currency-constant', value);
			$(this).data('currency-code', jQuery.trim(currency.replace('\-', '')));  
		}
	});
}


/**
 * Смена валют
 */ 
function currency_switch(currency_checked, only_value, trigger_before, trigger_after){
	$("select[id^='currency_pseudo_switcher']").val(currency_checked);
	
	if(typeof(trigger_before) != 'undefined' && trigger_before != ''){
		jQuery.each(trigger_before.split(','), function(index, func){
			window[func]();
		});
	}
	
	currency_current = currency_checked;  
	setCookie('currency_current', currency_current); 
	
	$("a[name^='currency_switch']").removeClass("switched");
	$("a[name='currency_switch_"+currency_checked+"']").addClass("switched");
	
	$('.currency_item').each(function(i){
		var tag = $(this).get(0).tagName.toUpperCase();
		var value = '';
		var value_mod = '';
		var currency = '';
		
		value = (tag == 'INPUT') ? parseFloat($(this).attr('currency-constant').replace(',', '.')) : parseFloat($(this).data('currency-constant'));
		currency = (tag == 'INPUT') ? $(this).attr('currency-code') : $(this).data('currency-code');
		
		if(!isNaN(value)) {
			jQuery.each(currency_rates, function(currency_to_id, item){	
				if(item.code.toUpperCase().indexOf(currency.toUpperCase()) >= 0 || item.symbol.indexOf(currency) >= 0 || (currency_to_id == 978 && currency == '€')){
        			value = (currency_checked == currency_to_id) ? value : round_number(value * currency_rates[currency_checked][currency_to_id], 2);
        			currency = currency_rates[currency_checked].symbol; 
        		}
			});
			
			value = number_format(value, currency_num_decimal_places, currency_dec_seperator, currency_thousands_seperator);
			
			if(tag == 'INPUT') {
		    	$(this).val(value);
			} else {
				if(typeof only_value == 'undefined' || isNaN(only_value) || only_value == 0 || only_value == ''){
					$(this).html(value + ' ' + currency); 
				} else {
					$(this).html(value); 
				}
			}
		} else if(tag != 'INPUT' && $(this).html() != '') {
			$(this).html(currency_rates[currency_checked].symbol); 
		}
	});
	 
	if(typeof(trigger_after) != 'undefined' && trigger_after != ''){
		jQuery.each(trigger_after.split(','), function(index, func){
			window[func]();
		});
	}
	
	return true;
}


/**
 * Смена валют через дополнительные переключатели 
 */
function currency_pseudo_switch(index){
	var value = $("#currency_pseudo_switcher_"+index).val();
	$("a[name='currency_switch_"+value+"']").click();
}

