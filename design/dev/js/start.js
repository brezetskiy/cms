jQuery(function($){
	
	function equalHeight(group) {
		tallest = 0;
		group.each(function() {
			thisHeight = $(this).height();
			if(thisHeight > tallest) {
				tallest = thisHeight;
			}
		});
		group.height(tallest);
	};
	
	$(document).ready(function(){
	
		$('.tipped').formtips({
			tippedClass: 'tipped'
		});
		
		$('.support__contact').click(function(){
			var temp=$('.support__city');
			
			if(temp.css('display') === 'none'){
				$(this).addClass('support__contact_top');
				temp.slideDown();
			}else{
				$(this).removeClass('support__contact_top');
				temp.slideUp();
			};
		
		});
		
		$('.sort').click(function(){
		
		var temp = $(this).find('.dropdown__container')
			if(temp.css('display') === 'none'){
				temp.show();
			}else{
				temp.hide();
			};
		});
		
		$('.tmenu__list__item').hover(function(){
			if($(this).next().length){
				$(this).next().addClass('tmenu__list__item_noborder');
			};
		},
		function(){
			if($(this).next().length){
				$(this).next().removeClass('tmenu__list__item_noborder')
			};
		});
		
		
		/*submen width calculation*/
		setTimeout(function(){
		$('.submenu__body_i').width(5000);
		$('.tmenu__list__item').each(
			function(){
				var temp=0;
				$(this).find('.submenu__list').each(function(){
					temp = temp + $(this).width()+16;
				});
				$(this).find('.submenu__body_i').width(temp);
		});});
		


	
				//slider

				$("#foo").carouFredSel({	circular    :  true,
					
					scroll:{
					fx:"none"
					},
					pagination: {container: ".slider__paginator"}
				});
		

	
	
				$("#foo2").carouFredSel({
					circular: false,
					infinite: true,
					auto 	: false,

					prev	: {	
						button	: "#foo2_prev",
						key		: "left"
					},
					next	: { 
						button	: "#foo2_next",
						key		: "right"
					}
				});

				$("#foo3").carouFredSel({	circular    :  true,
							
					scroll:{
					fx:"none"
					},
					pagination: {container: ".slider__paginator"}
				});
		
				$("#doo").carouFredSel({	circular    :  true,
							
					scroll:{
					fx:"none"
					},
					pagination: {container: ".slider__paginator_item"}
				});


	
						// Tabs
				$('#tabs').tabs();
				
				//hover states on the static widgets
				$('#dialog_link, ul#icons li').hover(
					function() { $(this).addClass('ui-state-hover'); }, 
					function() { $(this).removeClass('ui-state-hover'); }
				);
				
				jQuery(".niceCheck").each(
				function() {
				changeCheckStart(jQuery(this));
				});
				jQuery(".niceRadio").each(
				function() {
				changeRadioStart(jQuery(this));
				}); 
	
});
	
	
$(window).load(function(){
	
		equalHeight($('.mycarousel__item__name'));
	
	});
	

	
});