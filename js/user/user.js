var identityCheckTimeouts = new Array();
function setCheckIdentityTimeout(name) {
	if (identityCheckTimeouts[name] != null) {
		clearTimeout(identityCheckTimeouts[name]);
	}
	identityCheckTimeouts[name] = setTimeout('checkIdentity("'+name+'")', 500);
}

function checkIdentity(name) {
	
	byId(name+'_check_ok').style.display = 'block';
	byId(name+'_check_failed').style.display = 'none';
	byId(name+'_check_ok').innerHTML = '<span style="color:gray">Проверка...</span>';
	
	var req = new JsHttpRequest();
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			setIdentityStatus(name, req.responseJS['status'], req.responseJS['message']);
		}
	}
	req.caching = true;
	req.open('POST', '/action/user/check_'+name+'/', true);
	req.send({value: byId(name).value});
}


function setIdentityStatus(identity_name, status, message) {
	byId(identity_name+'_check_ok').style.display = 'none';
	byId(identity_name+'_check_failed').style.display = 'none';
	if (status == 'ok') {
		byId(identity_name+'_check_ok').innerHTML = message;
		byId(identity_name+'_check_ok').style.display = 'block';
	} else if (status == 'failed') {
		byId(identity_name+'_check_failed').innerHTML = message;
		byId(identity_name+'_check_failed').style.display = 'block';
	}
}


/*********************************** OTP ***********************************/ 
  
function otp_sms_auth_form(is_reserve){  
	if(typeof(is_reserve) == "undefined") is_reserve = 0;
	 
	AjaxRequest.form('otp_sms_phone_form', 'Отправка смс...', {'is_reserve':is_reserve});
	return false;
}

function otp_code_check(){
	AjaxRequest.form('otp_code_form', 'Проверка...');
	return false;
}

function otp_code_clear(){ 
	AjaxRequest.send('', "/action/user/otp/code_clear/", 'Загрузка...');
	return false;
} 

function otp_session_clear(){  
	AjaxRequest.send('', "/action/user/otp/session_clear/", 'Загрузка...');
	return false;
}
  
function otp_reserve_ban(){  
	$('#otp_reserve').val(1); 
	otp_switch_code();  
	 
	$('a[id^="otp_switch_code"]').remove();
}

function otp_switch_code(){
	var is_reserve = $('#otp_reserve').val();
	$('#otp_value').val('');
	    
	if(is_reserve == 0){
		$('#otp_reserve').val(1);
		$('#otp_title').html("Резервный код:");
		$('#otp_value').attr('maxlength', 8);
		$('#otp_switch_code_button').html("Ввести код с устройства генерации кодов?");
	} else {
		$('#otp_reserve').val(0);
		$('#otp_title').html("Код:");
		$('#otp_value').attr('maxlength', 6);
		$('#otp_switch_code_button').html("Воспользоваться резервным кодом доступа?");
	}
}