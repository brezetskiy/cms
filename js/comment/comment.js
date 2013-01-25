(Comment = {
	
	publish: function(id, active) {
		AjaxRequest.action('/action/admin/comment/publish/', 'Подождите...', {'id': id, 'active':active});
	},
	edit:function(id) {
		var height = getScrollHeight()+document.body.clientHeight; 
		$('#bg_layer').css("height", height); 
		$('#bg_layer').css("display","block");
		$('#task').css("display","block");
		$('#task').css("margin-top", height-800); 
		$("body").css("overflow", "hidden");
		$("#edit_commnet").text($("#comment-content-"+id).text());
		$("#edit_comment_id").attr('value', id);
	},
	hideTask:function() {
		$('#bg_layer').css("display","none");
		$('#task').css("display","none");
		$("#edit_commnet").text();
		$("body").css("overflow", "visible");
	}
});

function getScrollHeight(){
   var h = window.pageYOffset ||
           document.body.scrollTop ||
           document.documentElement.scrollTop;
           
   return h ? h : 0;
}