var speed = 500;
$(document).ready(function() {
    
	// li click
	$('#cuselFrame-city').live('click', function() {
			$('#hover_city').remove();
			var city__input = $('#cuselFrame-city span.cuselActive').attr('val'); 
			//$('div.city_' + city__input).show();	
			if($('div.city_' + city__input).length > 0){
					var html = $('div.city_' + city__input).html();	
					$('.cusel .jScrollPaneContainer').append('<div id="hover_city">'+html+'</p>');
					var top = (city__input - 1) * 32;
					$('#hover_city').css('top', top+'px');
			}
				
			$("#cuselFrame-city span").hover(
			  function () {	
				$('#hover_city').remove();
				var city__input = $(this).attr('val'); 
				//$('div.city_' + city__input).show();	
				if($('div.city_' + city__input).length > 0){
					var html = $('div.city_' + city__input).html();	
					$('.cusel .jScrollPaneContainer').append('<div id="hover_city">'+html+'</p>');
					var top = (city__input - 1) * 32;
					$('#hover_city').css('top', top+'px');
				}
			  }, 
			  function () {
				$('#hover_city').remove();
			 });
	});
	
	if($('#mcs_container.other_article').find('.content').length > 0) {
		var hgt = $('#mcs_container').find('.content').height();
		if(hgt < 250){$('#mcs_container.other_article').css('height', hgt+'px');}
	}

	 
        // clear input field
        $('.clear_input').each(function() {
            var default_value = this.value;
            $(this).focus(function() {
                if(this.value == default_value) {
                    this.value = '';
                }
            });
            $(this).blur(function() {
                if(this.value == '') {
                    this.value = default_value;
                }
            });
        });
        
        //$('#groups_pages a.gallery').lightBox();
        
		$('#groups_pages a.gallery').fancybox({
			transitionIn	:	'elastic',
			transitionOut	:	'elastic',
			speedIn		:	600, 
			speedOut		:	200, 
			overlayShow	:	false,
			helpers : {
					title : {
						type : 'over'
					}
				}
		});
        $('#groups_pages a.readmore').click(function(){
            $(this).parent().find('a.gallery').trigger('click');
        });
        
});
	
$(document).ready(function() {

        ///////////////////////
        // menu tabs	
	$('.menu_tab > div').css('display', 'none');
	
	if( $('#menuTabs li.active').length == 0)
		$('#menuTabs li:eq(0)').addClass('active');
		
	$('#menuTabs li').each(function() {
            if ($(this).hasClass('active'))
            {
                at = $(this).find("a").attr('href');
                var to = at.length;
                at = at.substring(1,to);			
                $('#'+at).css('display', 'block');	
            }
        });
	
	// li click
	$('.menuTabs li').live('click', function() {
		var at;
		$('.menuTabs li').each(function() {
			if ($(this).hasClass('active'))
				{
				$(this).removeClass('active');
				at = $(this).find("a").attr('href');
				var to = at.length;
				at = at.substring(1,to);			
				$('#'+at).css('display', 'none');			
			}
		});
		at = $(this).find('a').attr('href');
		var to = at.length;
		at = at.substring(1,to);
		$('#'+at).css('display', 'block');
		$(this).addClass('active');
		
		$('#'+at).find('.group .selected').next('.mcs_container').css('display', 'block').mCustomScrollbar("vertical",400,"easeOutCirc",1.05,19,"yes","yes",10);
			
		return false;		
	});         
        // end menu tabs	
        //////////////////////
        
        /////////////////////
        // gorizontal scroll        
        $('.groups_scroll').each(function() {            
            $(this).find('.group:gt(0)').css('display', 'none');
            $(this).find('.group:eq(0)').addClass('active');
            $(this).find('.group_one:eq(0)').addClass('active');
        });   

		
		// click
        $('.arrows_button a.prev, .arrows_button a.next ').live("click", function(){
            var count_elements = $(this).parent().parent().find('.groups_scroll .group').length - 1;
            var index;
            // не обрабатываем если один
            if (count_elements == 0)
                return false;
            
            var index_last = $(this).parent().parent().find('.groups_scroll').find('.active').index();
            var position = $(this).parent().parent().find('.groups_scroll .group').width()+ 50;
           
            position = position +'px';
                        
            //выбераем какой элемент показывать
            if( $(this).hasClass('prev')){
                position = '-'+position;
                if(index_last == 0) 
                    index = count_elements;
                else 
                    index = index_last - 1;                
            }
            else {                 
                if(index_last == count_elements){
                    index = 0;
                }
                else 
                    index = index_last + 1;
            }
            
            var type_animation = $(this).parent().parent().attr('class');
                        
            
            // animation
            if(type_animation == 'fade'){           
                
                // fade in
                $(this).parent().parent().find('.groups_scroll .group').eq(index_last).fadeOut(speed, function () {
                    $(this).removeClass('active');
                    $(this).parent().find('.group').eq(index).fadeIn(speed, function () {
                        $(this).addClass('active');
                    });
                });           
            }
            else if(type_animation == 'scroll'){
                // scroll
                $(this).parent().parent().find('.groups_scroll .group').eq(index).css('left', position).css('display', 'block');

                $(this).parent().parent().find('.groups_scroll .group').eq(index_last).fadeOut(speed, function () {
                    $(this).removeClass('active');
                    $(this).parent().find('.group').eq(index).animate(
                        {left: '0px'},
                        300,
                        '',
                        function () {
                            $(this).addClass('active');                    
                        }
                    );
                });           
            }
			
            return false;
        });
        // gorizontal scroll
        /////////////////////
        
		//other gorizontal scroll / scroll one /circle
		var block = new Array();
		var width_wrap_partner = new Array();
        $('.arrows_one_button a.prev, .arrows_one_button a.next').live("click", function(){
			var block_id = $(this).parent().parent().attr('id');
			
			if(!$(this).hasClass('prev-btn') && !$(this).hasClass('next-btn')) return false;
			
			var margin = parseInt( $(this).parent().parent().find('.groups_scroll .group_one').css('margin-right')) + 4;
			if(typeof width_wrap_partner[block_id] == "undefined"){
				width_wrap_partner[block_id] =  0;
				$(this).parent().parent().find('.group_one').each(function() { 
					width_wrap_partner[block_id] = width_wrap_partner[block_id] + $(this).width() + margin;
				});
				$(this).parent().parent().find('.wrap_one_scroll').css('width', width_wrap_partner[block_id]+'px');
			}
			if (typeof block[block_id] == "undefined")
			{
                $(this).parent().parent().find('.wrap_one_scroll .group_one.active').removeClass('active');	
                block[block_id] = $(this).parent().parent().find('.wrap_one_scroll').html();	
				block_count =  $(this).parent().parent().find('.groups_scroll .group_one').length;
				block_width =  $(this).parent().parent().find('.wrap_one_scroll').width()
				$(this).parent().parent().find('.wrap_one_scroll .group_one:eq(0)').addClass('active');	
			}
			var dparent = $(this).parent().parent();
            var count_elements = $(dparent).find('.groups_scroll .group_one').length - 1;
            var index;
            // не обрабатываем если один
            if (count_elements == 0)
                return false;
            
			
			$(dparent).find('.arrows_one_button a.prev').removeClass('prev-btn');
			$(dparent).find('.arrows_one_button a.next').removeClass('next-btn');
							
			var width_next = $(dparent).find('.groups_scroll .group_one.active').width()+ margin;
			var index_last = $(dparent).find('.groups_scroll .group_one.active').index(); 
			var position = parseInt($(dparent).find('.wrap_one_scroll').css('left'));
			
			$(dparent).find('.groups_scroll .group_one.active').removeClass('active');
            //выбераем какой элемент показывать
            if( $(this).hasClass('prev')){
                if(index_last == 0) {
					// если первый то добавляем еще элементов
					index_last = block_count; //alert(index_last);
					var wd = $(dparent).find('.wrap_one_scroll').width() + width_wrap_partner[block_id];									
					$(dparent).find('.wrap_one_scroll .group_one:eq(0)').before(block[block_id]);
					
					position = - block_width;
					$(dparent).find('.wrap_one_scroll').css('width', wd+'px').css('left', position+'px');
					
				}
					$(dparent).find('.groups_scroll .group_one').eq(index_last-1).addClass('active');
					width_next = $(dparent).find('.groups_scroll .group_one').eq(index_last-1).width()+ margin;
                    position = position + width_next;
					position = position + 'px';
				
            }
            else {
				
				is_need_add = $(dparent).find('.wrap_one_scroll').width() + parseInt($(dparent).find('.wrap_one_scroll').css('left'));
				
				//alert(is_need_add);
				if(is_need_add < 1000) {               
                    var wd = $(dparent).find('.wrap_one_scroll').width() + width_wrap_partner[block_id];
					$(dparent).find('.wrap_one_scroll .group_one:last-child').after(block[block_id]);
					$(dparent).find('.wrap_one_scroll').css('width', wd+'px');
                }
               
					$(dparent).find('.groups_scroll .group_one').eq(index_last+1).addClass('active');
					position = position - width_next;
					position = position + 'px';
					
			}
			
                $(dparent).find('.groups_scroll .wrap_one_scroll').animate(
                        {left: position},
                        250,
						function(){
							$(dparent).find('.arrows_one_button a.prev').addClass('prev-btn');
							$(dparent).find('.arrows_one_button a.next').addClass('next-btn');
						}
                    );          
         		
            return false;
        });
		

		//отслежуем текущий элемент
		
        $('.wrapper').each(function() {  
			var findactive = true;			
			var len = $(this).find('.group').length - 1;
			
			if(len == 0)
				$(this).parent().parent().find('.next').css('display', 'none');
						
			$(this).find('.group').each(function(i){
				$(this).css('display', 'none');
				
				if ( $(this).is(':has(.selected)') ){						
					$(this).css('display', 'block').addClass('active');
					if(i == 0)
						$(this).parent().parent().parent().find('.prev').css('display', 'none');
					
					if(i == len)
						$(this).parent().parent().parent().find('.next').css('display', 'none');
					
					//var ind = i
					var num = i*4 + $(this).find('.selected').index();
					
					//alert( num );
					
					$(this).parent().find('.mcs_container').css('display', 'none');
					$(this).find('.selected').next('.mcs_container').css('display', 'block').mCustomScrollbar("vertical",400,"easeOutCirc",1.05,19,"yes","yes",10);
			
					findactive = false;
				}
			});
			
			if(findactive == true){				
				$(this).find('.group:eq(0)').css('display', 'block').addClass('active');
				$(this).parent().parent().find('.prev').css('display', 'none');
							
				$(this).find(".block").eq(0).addClass('selected');
				$(this).find(".mcs_container").css('display', 'none');
				$(this).find(".mcs_container").eq(0).css('display', 'block').mCustomScrollbar("vertical",400,"easeOutCirc",1.05,19,"yes","yes",10);  
				
			}
        }); 

		$('.group a.change').live('click', function(){
			
			if($(this).parent().hasClass('selected')) return false;
			
			//$(this).addClass('act');
			var group = $(this).parent();
			$(group).parent().find('.selected').removeClass('selected').next('.mcs_container').hide(speed);
		/*	$(group).addClass('selected').next('.mcs_container').show(speed, function () {
					$(this).mCustomScrollbar("vertical",400,"easeOutCirc",1.05,"auto","yes","yes",10);
			
			});*/
			$(group).addClass('selected').next('.mcs_container').css('display', 'block').mCustomScrollbar("vertical",400,"easeOutCirc",1.05,19,"yes","yes",10);
			$(group).next('.mcs_container').show(speed);			
			
			return false;
		});
        
		
        // click
        $('.groups_scroll_wrapper a.prev, .groups_scroll_wrapper a.next ').live("click", function(){
            var count_elements = $(this).parent().find('.wrapper .group').length - 1;
            var index;
            // не обрабатываем если один
            if (count_elements == 0)
                return false;
            
            var index_last = $(this).parent().find('.wrapper').find('.active').index();
            var position = $(this).parent().find('.wrapper .group').width()+ 100;
          
            position = position +'px';
            
            var button = $(this);
            //выбераем какой элемент показывать
            if( $(this).hasClass('prev')){
                position = '-'+position;
			    index = index_last - 1;                
            }
            else {                 
                   index = index_last + 1;
            }

			
            //alert(index_last+ '='+ index);
            //var type_animation = $(this).parent().parent().attr('class');
            type_animation = 'scroll';           
            
            // animation
            if(type_animation == 'scroll'){
				
                // scroll
                $(this).parent().find('.wrapper .group').eq(index).css('left', position).css('display', 'block');

                $(this).parent().find('.wrapper .group').eq(index_last).fadeOut(1, function () {
                    $(this).removeClass('active');
                    $(this).parent().find('.group').eq(index).animate(
                        {left: '0px'},
                        400,
                        '',
                        function () {
                            $(this).addClass('active'); 
                               //скрытие кнопки навигации
                            if(index == 0){
                                    $(button).parent().find('a.prev').hide();
                                    $(button).parent().find('a.next').show();
                            }
                            else if(index == count_elements){
                                    $(button).parent().find('a.next').hide();
                                    $(button).parent().find('a.prev').show();
                            }
                            else {
                                    $(button).parent().find('a.prev').show();
                                    $(button).parent().find('a.next').show();
                            }
                        }
                    );
					//alert($(this).parent().attr('class'));
					
                    
                });           
            }
           
			$(this).parent().find('.wrapper .group').eq(index).find(".mcs_container").css('display', 'none');
			$(this).parent().find('.wrapper .group').eq(index).find(".block").removeClass('selected');
	
			$(this).parent().find('.wrapper .group').eq(index).find(".block").eq(0).addClass('selected').next(".mcs_container").eq(0).css('display', 'block').mCustomScrollbar("vertical",400,"easeOutCirc",1.05,19,"yes","yes",10);  
            
			return false;
        });        
        
		
			
	$(".CheckBoxLabelClass").click(function(){
			var check_id = $(this).attr("for");
			$('#'+check_id).trigger('click');
			
			//$(".LabelSelected:not(:checked)").removeClass("LabelSelected");
			//$('#' + check_id).next("label").addClass("LabelSelected");
		
			if(!$(this).hasClass("LabelSelected")){
				$(this).addClass("LabelSelected");
				$(this).parent().parent().parent().addClass("select");
			}else{
			$(this).removeClass("LabelSelected");
			$(this).parent().parent().parent().removeClass("select");
			}
		});
});


	function playYT(a){
				
		//var ifrm = $('#frame1');//document.getElementById('frame1');
		var href = $(a).attr('href');
                
                $(a).css('display', 'none').parent().find('img').css('display', 'none');
                $(a).parent().find('.frame').css('display', 'block');
                
                //$(a).parent().find('.frame1').attr('src', 'http://www.youtube.com/embed/'+href);
               // ifrm.css('display', 'block');
                
		return false;
	} 
        
/*
 * Change city desc when change city
 */
function city_change(select){
    
    var city__input = $('input#city').val();
	
    $('div.region_wrap').hide();
    $('div.city_' + city__input).show();
    return false;
};


/* scroll pane */
$(window).load(function() {
	mCustomScrollbars();
});

function mCustomScrollbars(){
	/* 
	malihu custom scrollbar function parameters: 
	1) scroll type (values: "vertical" or "horizontal")
	2) scroll easing amount (0 for no easing) 
	3) scroll easing type 
	4) extra bottom scrolling space for vertical scroll type only (minimum value: 1)
	5) scrollbar height/width adjustment (values: "auto" or "fixed")
	6) mouse-wheel support (values: "yes" or "no")
	7) scrolling via buttons support (values: "yes" or "no")
	8) buttons scrolling speed (values: 1-20, 1 being the slowest)
	*/
	$("#mcs_container").mCustomScrollbar("vertical",250,"easeOutCirc",1.05,149,"yes","yes",10); 
	//$("#mcs_container_other").mCustomScrollbar("vertical",20,"easeOutCirc",1.05,149,"yes","yes",10); 
}

/* select */
jQuery(document).ready(function(){

var params = {
		changedEl: ".select200",
		scrollArrows: true
	}

	cuSel(params);

});

function displayAnswer(a){
	$(a).parent().find('.answer').toggle("slow");
}