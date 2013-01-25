 

function phone_country(country){
	phone_error_clear();
	$('#phone_block_input_phone').unmask();
	$('#phone_block_input_phone').val('');
	
	$("a[id^='phone_block_country']").css('color', '#1873B4');
	$("#phone_block_country_"+country).css('color', '#ff9d02');
	
	if(country == 'ukraine'){
		$('#phone_block_input_phone').mask('+38(999) 999-99-99');
	} else if(country == 'russia') {
		$('#phone_block_input_phone').mask('+7(999) 999-99-99');
	} else {
		$('#phone_block_input_phone').mask('+99(999) 999-99-99');
	}
	
	$('#phone_block_input_phone').focus();
} 


function phone_error_clear(){
	$('#phone_block_error').html('');
}


function phone_load(phone_load_id){
	$('#phones_block').html('<div style="margin:20px;"><img src="/img/loader.gif"></div>'); 
	
	AjaxRequest.send('', '/action/user/phone/load/', 'Пожалуйста, подождите...', true, {'phone_load_id':phone_load_id});
	return false;
}
	

function phone_add(){
	phone_error_clear();
	var number = $('#phone_block_input_phone').val();
	
	AjaxRequest.send('', '/action/user/phone/add/', 'Пожалуйста, подождите...', null, {'number':number});
	return false;
}


function phone_send(id, action){
	phone_error_clear();
	
	if(action == 'confirm'){
		AjaxRequest.send('', '/action/user/phone/send_confirm/', 'Пожалуйста, подождите...', null, {'id':id});
		return false;
	}
	
	if(action == 'delete'){
		AjaxRequest.send('', '/action/user/phone/send_delete/', 'Пожалуйста, подождите...', null, {'id':id});
		return false;
	}
	 
	delta_error("Ошибка передачи данных. В обработчик передано неизвестное событие. Пожалуйста, обратитесь в техподдержку");
	return false;
} 


function phone_define(id){
	phone_error_clear(); 
	AjaxRequest.form('phone_block_define_'+id, 'Пожалуйста, подождите...');
	return false;
}


function phone_confirm(id){
	phone_error_clear();
	//AjaxRequest.form('phone_block_confirm_'+id, 'Пожалуйста, подождите...', {'passwd':$('#phone_block_passwd_'+id).val()});
	AjaxRequest.form('phone_block_confirm_'+id, 'Пожалуйста, подождите...');
	return false;
}


function phone_delete(phone_id, phone_send_id){
	phone_error_clear();
	
	AjaxRequest.send('', '/action/user/phone/delete/', 'Пожалуйста, подождите...', true, {'phone_id':phone_id, 'phone_send_id':phone_send_id});
	return false;
}

function phone_delete_open(phone_id){
	phone_error_clear();
	
	$("div[id^='phone_block_delete_define']").hide();
	$("#phone_block_delete_define_"+phone_id).show();
	return false;
}

function phone_delete_define(phone_id){
	phone_error_clear();
	
	AjaxRequest.form('phone_form_delete_define_'+phone_id, 'Отправка кода подтверждения...');
	return false;
}

function phone_delete_confirm(id){
	phone_error_clear();
	
	AjaxRequest.form('phone_block_delete_'+id, 'Пожалуйста, подождите...');
	return false;
}


function phone_delete_clear(id, is_resend){
	phone_error_clear();
	   
	AjaxRequest.send('', '/action/user/phone/clear_delete/', 'Пожалуйста, подождите...', true, {'phone':id, 'is_resend':is_resend});
	return false;
}

function phone_set_main(id){
	phone_error_clear();
	   
	AjaxRequest.send('', '/action/user/phone/set_main/', '', true, {'phone':id});
	return false;
}