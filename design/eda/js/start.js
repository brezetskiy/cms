var speed_slow = 800;
var speed_fast = 500;
var duration ={ left: '100%'};
var TimeOutId;

function changeSlide(next){

	
	var count_photos = $('.slider-panels li').length;
	
	var num = 0; var i=0; var current = 0;
	$('.slider-panels li').each(function(){
		
		if ($(this).hasClass('active')) {num = i;current = i;}
		i++;
	}); 
	num = num + next;
		
	if(count_photos == num) {num = 0;}
	else if(num < 0) {num = count_photos - 1;}
	
	if(next == 1) {
		duration ={ left: '100%'};
	}
	else {duration ={ left: '-100%'};}
	
	$('.slider-panels li').eq(current).removeClass('active');
	
	$('.slider-panels li').eq(current).animate( duration,  function (){
		
		
		$('.slider-panels li').eq(num).fadeIn(speed_slow, function () {
			$('.slider-panels li').eq(num).find('.sliderkit-content .title').fadeIn(speed_fast, function(){
				$('.slider-panels li').eq(num).find('.sliderkit-content .weight, .sliderkit-content .price').fadeIn(speed_fast, function(){
					$('.slider-panels li').eq(num).find('.sliderkit-content .want').fadeIn(speed_fast, function(){
						$('.slider-panels li').eq(num).addClass('active');
						$('.slider-panels li').eq(current).css('left',"0%").css('display', 'none');
						$('.slider-panels li').eq(current).
							find('.sliderkit-content .title, .sliderkit-content .price, .sliderkit-content .weight, .sliderkit-content .want').css('display', 'none');
							
							$('.prev').removeClass('prev').addClass('sliderscroll-prev');
							$('.next').removeClass('next').addClass('sliderscroll-next');
							$('.slider-go-button').css('cursor', 'pointer');
							
							clearTimeout(TimeOutId);
					});
				});
			});
		});
	});
}
	
$(document).ready(function() {

	$('.slider-panels li:gt(0)').each(function(){
		$(this).css('display','none');
		$(this).find('.sliderkit-content .title').css('display','none');
		$(this).find('.sliderkit-content .price').css('display','none');
		$(this).find('.sliderkit-content .weight').css('display','none');
		$(this).find('.sliderkit-content .want').css('display','none');
	}); 
	$('.slider-panels li:eq(0)').addClass('active');
	
	$(".sliderscroll-prev").live('click', function() {
			$('.slider-go-button').css('cursor', 'default');
			$('.sliderscroll-prev').removeClass('sliderscroll-prev').addClass('prev');
			$('.sliderscroll-next').removeClass('sliderscroll-next').addClass('next');
			
			TimeOutId = setTimeout(function(){ changeSlide(-1); }, 200);
			
	}); 
	
	$(".sliderscroll-next").live('click', function() {
			$('.slider-go-button').css('cursor', 'default');
			$('.sliderscroll-prev').removeClass('sliderscroll-prev').addClass('prev');
			$('.sliderscroll-next').removeClass('sliderscroll-next').addClass('next');
			
			TimeOutId = setTimeout(function(){ changeSlide(1); }, 200);
		
			
	}); 
	
	$('.form input').each(function() {
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
	
	$(".amount, .amount-s").bind('keyup', function() { 
		var amount;
		
		if($(this).hasClass('amount')) {
			amount = $(this).val();
			
			if(amount.length > 0) {
			if(!/^\d{1,2}$/.test(amount)){
				
				$(this).val('1');
				amount =1 ;
			}}
			amount = amount+'.'+$(this).next().val();
			
		}
		else if($(this).hasClass('amount-s')) {
			amount = $(this).prev().val();
			
			amount_float = $(this).val();
			if(amount_float.length > 0) {
			if(!/^\d+$/.test(amount_float)){
				
				$(this).val('0');
				amount_float =0 ;
			}}
			amount = amount+'.'+amount_float;					
		}
		amount = parseFloat(amount);
		
		var price=$(this).parent('div').parent('div').find('.price span').attr('data-price');
		
		var new_price = Math.round(amount * price*100)/100;
		
		$(this).parent('div').parent('div').find('.price span:first').html(new_price);
		$(this).parent('div').parent('div').find('a.wants').attr('data-amount', amount);
		
	});
	

	
	$(".amount-input, .amount-input-s").bind('keyup', function() { 
		var amount;
		
		if($(this).hasClass('amount-input')) {
			amount = $(this).val();
			
			if(amount.length > 0) {
			if(!/^\d{1,2}$/.test(amount)){
				
				$(this).val('1');
				amount =1 ;
			}}
			amount = amount+'.'+$(this).next().val();
			
		}
		else if($(this).hasClass('amount-input-s')) {
			amount = $(this).prev().val();
			
			amount_float = $(this).val();
			if(amount_float.length > 0) {
			if(!/^\d+$/.test(amount_float)){
				
				$(this).val('0');
				amount_float =0 ;
			}}
			amount = amount+'.'+amount_float;					
		}
		amount = parseFloat(amount);
		price = $(this).parent('div').parent('td').parent('tr').find('td.bsk-yellow span:first').attr('data-price'); 
		discount = $(this).parent('div').parent('td').parent('tr').find('td.discount').html();
		if(discount != undefined){
			discount=parseInt(discount) / 100;
			price = price - price * discount;
		}
		var new_price = Math.round(amount * price*100)/100;
		
		$(this).parent('div').parent('td').parent('tr').find('td.bsk-yellow span:first').html(new_price);
		
		var summ=0.0;
		$('td.bsk-yellow span').each(function(){			
			summ = summ + parseFloat($(this).html());
		});
		
		$('#bsk-price').html(summ);
		
	});
	

	
});


