
function oid_widget__box__start(name, template, return_path, subtemplate, providers){
	if(return_path === undefined) return_path = '';
	if(subtemplate === undefined) subtemplate = '';
	if(providers === undefined) providers = '';
	
	$("#oid_widget__box__content").html('');
	AjaxRequest.send('', '/action/user/oid/start/', '', true, {'name':name, 'template':template, 'return_path':return_path, 'subtemplate':subtemplate, 'providers':providers});
	return false;
}


/*WIDGET*/
function oid_widget__box__openid_open(widget_name, id){
	oid_widget__message_clear();
	
	$("tr[id^='oid_widget__"+widget_name+"_box_openid_block']").hide();
	$("#oid_widget__"+widget_name+"_box_openid_block_"+id).fadeIn();
} 

function oid_widget__box__openid_send(widget_name, id, name){ 
	oid_widget__message_clear();
	
	var _return_path 	  = $("#oid_widget__"+widget_name+"_openid_return_path_"+id).val();
	var _a 				  = $("#oid_widget__"+widget_name+"_openid_a_"+id).val();
	var openid_provider   = $("#oid_widget__"+widget_name+"_openid_provider_"+id).val();
	var openid_link 	  = $("#oid_widget__"+widget_name+"_openid_link_"+id).val();
	var openid_identifier = $("#oid_widget__"+widget_name+"_openid_identifier_"+id).val();
	  
	var params = "_return_path="+urlencode(_return_path)+"&_own="+widget_name+"&_a="+_a+"&openid_provider="+openid_provider+"&openid_link="+openid_link+"&openid_identifier="+openid_identifier;
	StableWindow('/action/user/oid/openid/?'+params, name, 1);
	return false;
}

function oid_widget__box__close(widget_name){
	//$('#oid_widget__'+widget_name).fadeOut(); 
	//oid_widget__box__cancel(widget_name); 
	$("#oid_widget__box__content").html('');
	return false;  
}

function oid_widget__box__cancel(widget_name){
	oid_widget__message_clear();
	 
	$("tr[id^='oid_widget__"+widget_name+"_box']").hide();
	
	$("tr[id^='oid_widget__"+widget_name+"_clarify_auto']").hide();
	$("tr[id^='oid_widget__"+widget_name+"_clarify_manual']").hide();
	
	$("tr[id^='oid_widget__"+widget_name+"_box_provider']").show();
	$("tr[id^='devider']").show();
	$("#oid_widget__"+widget_name+"_clarify_manual_row_email").show();
	
	AjaxRequest.send(null, "/action/user/oid/cancel/");
	return false;
}

 /*CLARIFY AUTO*/
function oid_widget__box__clarify_auto_send(widget_name){
	var clarify_email 	 = $("#oid_widget__"+widget_name+"_clarify_auto_email").val();
	var clarify_name  	 = $("#oid_widget__"+widget_name+"_clarify_auto_name").val();
	var clarify_user_id  = $("#oid_widget__"+widget_name+"_clarify_auto_user_id").val();
	var clarify_code	 = $("#oid_widget__"+widget_name+"_clarify_auto_code").val();
	var clarify_reserve  = $("#oid_widget__"+widget_name+"_clarify_auto_reserve").val();
	 
	var clarify_remember = 0; 
	if($("#oid_widget__"+widget_name+"_clarify_auto_remember").is(":checked")) clarify_remember = 1;
	
	AjaxRequest.form("oid_widget__"+widget_name+"_form_clarify_auto", "", {'clarify_email':clarify_email, 'clarify_name':clarify_name, 'clarify_user_id':clarify_user_id, 'clarify_code':clarify_code, 'clarify_reserve':clarify_reserve, 'clarify_remember':clarify_remember});
	return false;
}


/*CLARIFY MANUAL*/
function oid_widget__box__clarify_manual_send(widget_name, action){
	oid_widget__message_clear();
	 
	var email  = $("#oid_widget__"+widget_name+"_clarify_manual_email").val();
	var name   = $("#oid_widget__"+widget_name+"_clarify_manual_name").val();
	
	var captcha_uid = $("input[name='captcha_uid']").val();
	var captcha = $("#oid_widget__"+widget_name+"_clarify_manual_captcha").val();
	
	var passwd	 = $("#oid_widget__"+widget_name+"_clarify_manual_passwd").val(); 
	var code	 = $("#oid_widget__"+widget_name+"_clarify_manual_code").val(); 
	var reserve  = $("#oid_widget__"+widget_name+"_clarify_manual_reserve").val();
	var remember = 0;  
	if($("#oid_widget__"+widget_name+"_clarify_manual_remember").is(":checked")) remember = 1;
	   
	AjaxRequest.form("oid_widget__"+widget_name+"_form_clarify_manual", "", {'action':action, 'email':email, 'name':name, 'captcha_uid':captcha_uid, 'captcha_value':captcha, 'passwd':passwd, 'code':code, 'reserve':reserve, 'remember':remember});
	return false;
}

function oid_widget__box__clarify_manual_open(widget_name, action, passwd){
	$("tr[id^='oid_widget__"+widget_name+"_box']").hide(); 
	$("tr[id^='devider']").hide(); 
	$("#oid_widget__"+widget_name+"_clarify_manual_row_email").hide();
	 
	$("#oid_widget__"+widget_name+"_clarify_manual_passwd").val(passwd);
	if(action == 'register') $("tr[id^='oid_widget__"+widget_name+"_clarify_manual_row_register']").show();
	if(action == 'auth') $("tr[id^='oid_widget__"+widget_name+"_clarify_manual_row_auth']").show();
}

  
/*SWITCH CODES*/ 
function oid_widget__box__switch_code(widget_name, type){
	var is_reserve = $("#oid_widget__"+widget_name+"_clarify_"+type+"_reserve").val();
	$("#oid_widget__"+widget_name+"_clarify_"+type+"_code").val("");
	  
	if(is_reserve == 0){
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_reserve").val(1);
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_code_title").html("Резервный код");
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_code").attr("maxlength", 8);
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_switch_code_button").html("Ввести код с устройства генерации кодов?");
	} else {
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_reserve").val(0);
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_code_title").html("Код");
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_code").attr("maxlength", 6);
		$("#oid_widget__"+widget_name+"_clarify_"+type+"_switch_code_button").html("Воспользоваться резервным кодом доступа?");
	}
} 


function oid_widget__box__reload_passwd(){
	AjaxRequest.send('', '/action/user/oid/clarify_passwd/');
	return false;
}