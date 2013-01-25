
function oid_widget__message_clear(){
	$("#oid_widget__error").html('');
	$("#oid_widget__warning").html('');
	$("#oid_widget__ok").html('');
} 

 
function oid_widget__form_openid_open(widget_name, widget_type, provider_id){ 
	oid_widget__message_clear();
	  
	AjaxRequest.send('', "/action/user/oid/form_openid/", 'Загрузка...', true, {'widget_name':widget_name, 'widget_type':widget_type, 'provider_id':provider_id});
	return false;
} 

 
function oid_widget__form_openid_send(widget_name, name, id){ 
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


function urlencode(str) {
    str = (str + '').toString();
    return encodeURIComponent(str).replace(/!/g, '%21').replace(/'/g, '%27').replace(/\(/g, '%28').
    replace(/\)/g, '%29').replace(/\*/g, '%2A').replace(/%20/g, '+');
}