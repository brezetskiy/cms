

/**
 * ����������� ������-���������
 */
function delta_message(stack){
	if (stack.length == 0) return false;
	
	var message_total = 0;
	jQuery.each(stack, function(display_type, messages){
		jQuery.each(messages, function(i, message){
			message_total++;	
		});
	});
	
	var message_counter = 0;
	jQuery.each(stack, function(display_type, messages){
		jQuery.each(messages, function(i, message){
			$("<div id='delta-message-"+message_counter+"' class='delta-message-border' />").appendTo("body"); 
			
	        var current_number = message_counter+1;
	        
	        if (message_total == 1){
	        	var messages_counter_block = '';
	        } else {
	       		var messages_counter_block = '<div style="float:right; font-weight:bold;">��������� '+current_number+' �� '+message_total+'</div><div style="clear:both;">&nbsp;</div>'; 	
	        }
	        
	        if (message_total == 1 || current_number == message_total){
	       		var button = '<button class="process-button default-button" onclick="message_close();">�������</button>';
	        } else {
	       		var button = '<button class="process-button default-button" onclick="message_next('+current_number+')">�����</button>';
	        }
	          
	        $("#delta-message-"+message_counter).html('<div class="delta-message-container process-'+display_type+'"><div class="delta-message-container-content">'+messages_counter_block+' '+message+'</div><div class="delta-message-container-button">'+button+'</div></div>');
	        message_counter++;
		});  
	});  
	 
	$.blockUI.defaults.css = {top: '30%', left:	'35%'};
    $.blockUI({ message: $('#delta-message-0') });  
}
     

/**
 * �������������� ������-���������
 */
function message_next(message_id){
	$.blockUI({ message: $('#delta-message-'+message_id) });  
}


/**
 * ������� ��� ������-���������
 */
function message_close(){ 
	$.unblockUI({ onUnblock: function(){ $("div[id^='delta-message']").remove(); } }); 
}


/**
 * ����� ������� ��� ������ ������-��������� � ������������ �� �������� �� ��������
 */
function delta_error(content){	
	delta_message({'error':{0:content}}); 
	return false;
}

function delta_success(content){	
	delta_message({'success':{0:content}});  
	return false;
}
 
function delta_warning(content){	
	delta_message({'warning':{0:content}});  
	return false;
}

function delta_info(content){	
	delta_message({'info':{0:content}});  
	return false;
}


/**
 * ����������� ������-����
 *
 * @param string form_id - id �����, ��� ���������� ������� � ������-��������� 
 * @param string action_js - �������� javascript �������, ��� ���������� ����� �� ������
 */
function delta_action(action_js, content, cancel_js){
	if(typeof(cancel_js) == "undefined") cancel_js = "message_close";
	var uniq_id = Math.floor(Math.random() * 9999) + 1;
	
	$("#delta-message-action").remove();     
	$("<div id='delta-message-action' class='delta-message-border' />").appendTo("body"); 
			     
	var buttons = ''+ 
		'<button class="process-button default-button" onclick="$(\'#delta-message-action-loader-'+uniq_id+'\').show(); '+action_js+';">�����</button>&nbsp;&nbsp;&nbsp;' + 
   		'<a onclick="$(\'#delta-message-action-loader-'+uniq_id+'\').show(); '+cancel_js+';" style="cursor:pointer">�������� <img src="/img/cms/message/del.gif" border="0" align="absmiddle" style="margin-top:4px;"></a>';
   
      
    $("#delta-message-action").html(
    	'<div class="delta-message-container process-info">' +   
    		'<div class="delta-message-container-content">'+content+'</div>' + 
    		'<div id="delta-message-action-loader-'+uniq_id+'" style="display:none; float:left;"><img src="/img/cms/loader-circle.gif" border="0" align="absmiddle"></div>' +
    		'<div class="delta-message-container-button">'+buttons+'</div>' +
    	'</div>'
    );   
	            
	$.blockUI.defaults.css = {top: '30%', left:	'35%'};
    $.blockUI({ message: $("#delta-message-action") });  
     
    return false;
}


function delta_loader_clear(){
	$("div[id^='delta-message-action-loader']").hide();
	return false;
}