var TimeOutId;
var TimeOutSec = 4000;
var animation_speed = 500;
var count_photos = 0;

function ChangeSlideTimer()
{
// Changer img when time end
	if (count_photos < 2) return;

	$('#slider').addClass('change');
	
	var numb = 0, num = 0, i = 0;
	$('#slider_ball a').each(function(){
		i++;
		if ($(this).hasClass('active')) num = i;
	});
	
	numb = num;
	
	$('#slider_ball a').eq(num-1).removeClass('active');
		
	if (num == count_photos) num = 0;
	
	$('#slider_ball a').eq(num).addClass('active');
		
	$('#slider .slider_photo').eq(numb-1).removeClass('active').parent().find('.desc').fadeOut(animation_speed, function(){
                $(this).removeClass('active');
        });
	$('#slider .slider_photo').eq(numb-1).fadeOut(animation_speed, function () {
		$('#slider .slider_photo').eq(num).fadeIn(animation_speed, function () {
			$('#slider .slider_photo').eq(num).addClass('active').parent().find('.desc').show().addClass('active');
			$('#slider').removeClass('change');
			if (!$('.slider_border').hasClass('onHover')) TimeOutId = setTimeout(function(){ ChangeSlideTimer(); }, TimeOutSec);
		});
	});
	
};

function ChangeSlideTimerById(num)
{
// Changer Slider img Time when click in a button
	if (count_photos < 2) return;

	$('#slider').addClass('change');
	
	var numb = 0, i = 0;
	//numb = num;
	
	$('#slider .slider_photo').each(function(){
		if ($(this).hasClass('active')) numb = i;
		i++;
	});	
	
	$('#slider_ball a').eq(numb).removeClass('active');
		
	$('#slider_ball a').eq(num).addClass('active');
		
	$('#slider .slider_photo').eq(numb).removeClass('active').parent().find('.desc').fadeOut(animation_speed, function(){
                $(this).removeClass('active');
        });
	$('#slider .slider_photo').eq(numb).fadeOut(animation_speed, function () {
		$('#slider .slider_photo').eq(num).fadeIn(animation_speed, function () {
			$('#slider .slider_photo').eq(num).addClass('active').parent().find('.desc').fadeIn(animation_speed).addClass('active');
			$('#slider').removeClass('change');
			//if (!$('.slider_border').hasClass('onHover')) 
			//{
				clearTimeout(TimeOutId);
				TimeOutId = setTimeout(function(){ ChangeSlideTimer(); }, TimeOutSec);
			//};
                        
		});
	});
	
};

$(document).ready(function(){
	/*** SLIDER ***/
	count_photos = $('#slider .slider_photo').length;
        //alert(count_photos);
        
        for(i=1; i<=count_photos; i++){ 
            $('#slider_ball').append('<a href="#" rel="'+i+'">&nbsp;</a>');
        }
        $('#slider_ball a:eq(0)').addClass('active');
        $('#slider .slider_border:eq(0)').find('.slider_photo').addClass('active');
        $('#slider .slider_border:eq(0)').find('.desc').show().addClass('active');
	
	$('.slider_border').hover(function(){
		clearTimeout(TimeOutId);
		$('.slider_border').addClass('onHover');
	}, function(){
		$('.slider_border').removeClass('onHover');
		TimeOutId = setTimeout(function(){ ChangeSlideTimer(); }, TimeOutSec);
	});
        
	
	$('#slider_ball a').live("click", function(){
                
		var numb = $('#slider_ball a').index($(this));
		
		if ($(this).hasClass('active')) return;
		
		if (!$('#slider').hasClass('change'))
		{
			clearTimeout(TimeOutId);
			ChangeSlideTimerById(numb);
		};
		return false;
	});
	
	TimeOutId = setTimeout(function(){ ChangeSlideTimer(); }, TimeOutSec);
	
});

