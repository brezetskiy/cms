$(document).ready(function(){
	//option radio
	$('.RadioLabelClass').click(function(){
		var radio_id = $(this).attr("for");
		$('#' + radio_id).trigger("click");
		$(".RadioSelected:not(:checked)").removeClass("RadioSelected");
		$('#' + radio_id).next("label").addClass("RadioSelected");
	});
});