	$(window).load(function(){ 
		  // initialize scrollable
		  $(".scrollable").scrollable({circular: true});
	});
	$(window).load(function(){ 
		  // initialize scrollable
		  $(".scrolltrener").scrollable({circular: true, prev:'.trener-prev',	next:'.trener-next'});
	});

	$(window).load(function(){ 
		$('.voting__body__form label:first').addClass('RadioSelected');
		$('.voting__body__form input:first').attr('checked', 'checked');
	});
	$(function() {
			  // initialize scrollable
			  $(".scrollable-news-inside").scrollable({circular: true, prev:'.news-prev', next:'.news-next'});
	});
			
	var time_fade_in = 2000;
	var time_fade_trener = 800;

    $("#slider-ball a").click(function () {
		if (!$(this).hasClass("active")) {
			$("#slider-ball a ").removeClass("active");
			$(this).addClass("active");
			
			$("#slider img").css('display','none');
			newslide = $(this).attr('rel'); 
			$("#sl"+newslide).fadeIn(time_fade_in);
		}
    });
	
	$(".tr-mask .trener-next").click(function() {
		n = $("#trener-name-slider > div").length - 1 ; 
		current = $("#trener-name-slider .active").attr('name'); 
		$('#trener-name-slider div').removeClass("active");
		$("#trener-name-slider > div").css('display','none'); 
		if(current == n) {$('#trener-name-slider div:first').fadeIn(time_fade_trener).addClass('active'); }
		else {  current = current -1 +2;
				$("#trener-name-slider > div:eq("+current+")").fadeIn(time_fade_trener).addClass('active');				
		}
		
	});
	$(".tr-mask .trener-prev").click(function() {
		current = $("#trener-name-slider .active").attr('name'); 
		$('#trener-name-slider div').removeClass("active");
		$("#trener-name-slider > div").css('display','none'); 
		if(current == 0) {$('#trener-name-slider > div:last').fadeIn(time_fade_trener).addClass('active'); }
		else {  current = current -1;
				$("#trener-name-slider > div:eq("+current+")").fadeIn(time_fade_trener).addClass('active');				
		}
		
	});
	
$(document).ready(function() {

 $('ul.menu ul').each(function(i) { //Проверить все подменю

    $(this).prev().addClass('collapsible').click(function() { //Присоединить обработчик события
		var this_i = $('ul.menu ul').index($(this).next()); //Получить индекс щёлкнутого подменю
		if ($(this).next().css('display') == 'none') {   //Когда открыто подменю, свернуть остальные подменю

			$(this).parent('li').parent('ul').find('ul').each(function(j) {
				if (j != this_i) {
					$(this).slideUp(200, function () {
						$(this).prev().css('display', 'block');
					});
				}
			});

			//Конец блока сворачивания остальных подменю

			$(this).next().slideDown(200, function () { //Показать подменю
				$(this).prev().css('display', 'block');
			});
		}
		else {
			$(this).next().slideUp(200, function () { //Спрятать подменю
				$(this).prev().css('display', 'block');				
			});

		}

  //return false; //Не следовать по ссылке; true - следовать 

	});

 });

});

	
	