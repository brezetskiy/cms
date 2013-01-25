
/*WIDGET*/

function oid_widget__context__openid_open(widget_name, id){
	oid_widget__message_clear();
	 
	$("div[id^='oid_widget__"+widget_name+"_box_openid_block']").hide();
	$("#oid_widget__"+widget_name+"_box_openid_block_"+id).fadeIn(); 
} 
 
function oid_widget__context__close(widget_name){
	$('#oid_widget__'+widget_name).fadeOut();
	oid_widget__context__cancel(widget_name); 
	
	return false;
}

function oid_widget__context__cancel(widget_name){
	oid_widget__message_clear();
	 
	$("div[id^='oid_widget__"+widget_name+"_box']").hide();
	$("#oid_widget__"+widget_name+"_clarify_auto_block").hide();
	
	AjaxRequest.send(null, "/action/user/oid/cancel/");
	return false;
}

 /*CLARIFY AUTO*/
function oid_widget__context__clarify_auto_send(widget_name){
	var clarify_user_id  = $("#oid_widget__"+widget_name+"_clarify_auto_user_id").val();
	var clarify_reserve  = $("#oid_widget__"+widget_name+"_clarify_auto_reserve").val();
	var clarify_code	 = $("#oid_widget__"+widget_name+"_clarify_auto_code").val();
	 
	var clarify_remember = 0; 
	if($("#oid_widget__"+widget_name+"_clarify_auto_remember").is(":checked")) clarify_remember = 1;
	
	AjaxRequest.send("oid_widget__"+widget_name+"_form_clarify_auto", "/action/user/oid/clarify_auto/", '', true, {'clarify_user_id':clarify_user_id, 'clarify_code':clarify_code, 'clarify_reserve':clarify_reserve, 'clarify_remember':clarify_remember});
	return false;
}
  
/*SWITCH CODES*/ 
function oid_widget__context__switch_code(widget_name){
	var is_reserve = $("#oid_widget__"+widget_name+"_clarify_auto_reserve").val();
	$("#oid_widget__"+widget_name+"_clarify_auto_code").val("");
	  
	if(is_reserve == 0){
		$("#oid_widget__"+widget_name+"_clarify_auto_reserve").val(1);
		$("#oid_widget__"+widget_name+"_clarify_auto_code_title").html("Резервный код");
		$("#oid_widget__"+widget_name+"_clarify_auto_code").attr("maxlength", 8);
		$("#oid_widget__"+widget_name+"_clarify_auto_switch_code_button").html("Ввести код с устройства генерации кодов?");
	} else {
		$("#oid_widget__"+widget_name+"_clarify_auto_reserve").val(0);
		$("#oid_widget__"+widget_name+"_clarify_auto_code_title").html("Код");
		$("#oid_widget__"+widget_name+"_clarify_auto_code").attr("maxlength", 6);
		$("#oid_widget__"+widget_name+"_clarify_auto_switch_code_button").html("Воспользоваться резервным кодом доступа?");
	}
} 
