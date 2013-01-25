
function block_load_content(prefix){
	var blocks  = $("div[id^='"+prefix+"']");
	var request = new Array();
	var counter = 0;
	
   	jQuery.each(blocks, function() {
    	request[counter] = this.id;
    	counter++;
   	});
	
   	AjaxRequest.send(null, '/action/site/block_load/', 'Загрузка', true, {'request':request});	
}

