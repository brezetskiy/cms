function showFadeCart(id, fd){

	var amount = $(fd).attr('data-amount'); 
	
	AjaxRequest.send(null, '/action/shoporder/add_to_cart/', 'Добавление...', true, {'product_id':id, 'amount':amount	});
	
	smform.init('','/action/eda/loadform/');
	
	return false;
}
function hideFadeCart() {
	location.reload();
	return false;
}

function getScrollHeight(){
   var h = window.pageYOffset ||
           document.body.scrollTop ||
           document.documentElement.scrollTop;
           
   return h ? h : 0;
}

function currency_switch(id){ 
	$("span[id^='currency']").removeClass('currency_switcher_on').addClass('currency_switcher_off'); 
	$("#currency_"+id).removeClass('currency_switcher_off').addClass('currency_switcher_on'); 
	
	$("span[id^='prodprice']").hide();
	$("span[id^='prodprice_"+id+"']").show(); 
	AjaxRequest.send(null, '/action/shoporder/currency_switch/', 'Подождите...', true, {'id':id});
}