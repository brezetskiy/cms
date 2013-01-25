
function call_search_helper(text){
	if(text.length <= 2) {
		return hide_search_helper();
	}
	AjaxRequest.send(null, '/action/shop/search_helper/', 'Поиск...', true, {'text':text});
}

function hide_search_helper(){
	$("#search_helper").hide();
	return false; 
}