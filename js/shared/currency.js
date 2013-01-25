var currency_global_list = new Array();


/**
 * Активирует перерасчет валют в соответствии с переданной валютой
 */
function currency_change(checked){
	currency_global(checked, currency_global_list);
	return false;
}


/**
 * Перерасчет цифровых значений указанных параметров в соответствии с переданной валютой
 */
function currency_global(checked, tags_params){
	$("a[name^='currency_switch_']").removeClass("switched");
	$("a[name='currency_switch_"+checked+"']").addClass("switched");
	
	var currency_default = $("input[name='currency_default']").val();
	var currency_label   = $("input[name='currency_label_"+checked+"']").val(); 
	
	var rate = $("input[name='currency_rate_"+currency_default+"_" +checked+"']").val();
	
	for(j=0; j < tags_params.length; j++){
		var param_nexus = tags_params[j];
		var tag   = param_nexus.substr(0, param_nexus.indexOf('-'));
		var param = param_nexus.substr(param_nexus.indexOf('-') + 1);
		var constants = $("input[name='currency_constant__"+param+"']");
		
		for(i=0; i < constants.size(); i++){
			var value = $("input[name='currency_constant__"+param+"']:eq("+i+")").val();
			
			if(value == '–' || jQuery.trim(value) == ''){
				value = '&ndash;';
			} else if(value == 0){
				value = '0'; 
			} else {
				value = round_number(value * rate, 2); 
			}
			
			if(tag != 'input'){ 
				$(tag+"[name='currency_var__"+param+"']:eq("+i+")").html(value);  
			} else { 
				$("input[name='currency_var__"+param+"']:eq("+i+")").val(value);
			}
		}	
	}
	
	$("span[name='currency_label']").html(currency_label);
	AjaxRequest.send(null, '/action/cms/change_currency/', 'Подождите...', true, {'currency_id':checked});
}


/**
 * Округление числа
 */
function round_number(num, dec) {
		var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
		return result;
}
	