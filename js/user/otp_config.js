function config_style(button_style, content_style, type){
	$("div[id^='button_activate']").removeClass('button_active').removeClass('button_green').removeClass('button_red').addClass('button_gray');
	$("#button_activate_"+type).removeClass('button_gray').addClass('button_'+button_style);
	  
	$("#otp_content").removeClass('otp_content_gray').removeClass('otp_content_green').removeClass('otp_content_red').addClass('otp_content_'+content_style); 
}


function config_step(step, otp_type, force){
	var type = otp_type;
	
	$("#otp_current_type").val(otp_type);  
	$("#otp_content").removeClass('otp_content_green').removeClass('otp_content_red').addClass('otp_content_gray'); 
	 
	if(step == 2){
		if(type == 'android' || type == 'iphone' || type == 'java') type = 'mobile'; 
		if(type == 'disable'){
			config_style('red', 'red', 'disable');
		} else {
			config_style('active', 'gray', type); 
		}
	} 
	
	if(step == 3){ 
		$("label[id^='otp_mobile_type']").css('color', '#000');
		$("#otp_mobile_type_"+otp_type).css('color', '#1873b4');
	}
	 
	AjaxRequest.send(null, '/action/user/otp/config_handle/', 'Загрузка...', '', {'step':step, 'otp_type':otp_type, 'force':force});
	return false;
}

  
function config_save(){  
	var otp_type = $("#otp_current_type").val();
	     
	AjaxRequest.form('otp_form', 'Загрузка...', {'otp_type':otp_type}); 
	return false;
}


function config_enable_sms_send(){
	var phone_id = 0;
	   
	phone_id = $("input[name='otp_phone']:checked").val(); 
	if (typeof(phone_id) == 'undefined') phone_id = $("#otp_sms_phone").val();
	
	AjaxRequest.send('', '/action/user/otp/config_enable_sms_send/', 'Пожалуйста, подождите...', true, {'phone':phone_id});
	return false;
}

function config_enable_sms_clean(){  
	AjaxRequest.send('', "/action/user/otp/config_enable_sms_clean/");
	return false;
}


function config_disable_open(){
	$('#config_disable_step_submit').show();
	$("#config_disable_step_phone").hide();
	$("#config_disable_button").hide();
}
		
function config_disable_sms_open(){
	$('#config_disable_step_submit').hide();
	$("#config_disable_button").hide();
	$("#config_disable_step_phone").show();
}

function config_disable_sms_send(is_reserve, source){  
	if(typeof(source) == "undefined") source = "disable";
	
	var phone_id = 0;
	 
	phone_id = $("input[name='otp_phone']:checked").val(); 
	if (typeof(phone_id) == 'undefined') phone_id = $("#otp_sms_phone").val();
	
	AjaxRequest.send('', '/action/user/otp/config_disable_sms_send/', 'Пожалуйста, подождите...', true, {'phone':phone_id, 'is_reserve':is_reserve, 'source':source});
	return false;
}

function config_disable_sms_clean(source){ 
	if(typeof(source) == "undefined") source = "disable";
	  
	AjaxRequest.send('', "/action/user/otp/config_disable_sms_clean/", '', true, {'source':source});
	return false;
}

function config_reserve_ban(){  
	switch_code(1); 
	$('a[id^="switch_code_button"]').remove();
}

 
function switch_code(force){
	if(typeof(force) == "undefined") force = $("#reserve_code").val();
	var is_reserve = force;
	  
	$("#code").val("");
	  
	if(is_reserve == 0){
		$("#reserve_code").val(1);
		$("#code_title").html("Резервный код");
		$("#code").attr("maxlength", 8);
		$("#switch_code_button").html("Ввести код с устройства генерации кодов?");
	} else {
		$("#reserve_code").val(0);
		$("#code_title").html("Код");
		$("#code").attr("maxlength", 6);
		$("#switch_code_button").html("Воспользоваться резервным кодом доступа?");
	}
} 