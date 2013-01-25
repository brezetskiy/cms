	//var blocked_days = [];
    //var available_days = {"1":[{"h":09, "m":00},{"h":09, "m":15},{"h":09, "m":25}, {"h":09, "m":30}],"2":[{"h":09, "m":00}, {"h":09, "m":30}],"3":[{"h":09, "m":00}, {"h":09, "m":30}],"4":[{"h":09, "m":00}, {"h":09, "m":30}],"5":[{"h":07, "m":00}, {"h":07, "m":30}],"6":[{"h":08, "m":00}, {"h":8, "m":30}]};
    
	var current_day_week;
	var dateDatapicker;
	
$(document).ready( function(){

   Datepicker();
   dateDatapicker = new Date();
   current_day_week = dateDatapicker.getDay();

   
   Timepicker.init();
   
   var insert = false;
   for(i=0; i<available_days[current_day_week].length; i++){
			if(Timepicker.isValidDate(dateDatapicker, available_days[current_day_week][i].h, available_days[current_day_week][i].m)){
				Timepicker.setTime(available_days[current_day_week][i].h, available_days[current_day_week][i].m);   
				insert = true;
				break;
			}
   }
   if(!insert){
		$('.prepare').css('display', 'block');
   }
   
   
   $('.tp-next').click( function(e){
			$('.prepare').css('display', 'none');
			var hours = Math.round($('.hours').val());
            var minutes = Math.round($('.minutes').val());
			
			var len = available_days[current_day_week].length; 
			for (var i = 0; i < len; i++)
            {				
                if ((available_days[current_day_week][i].h == hours) && (available_days[current_day_week][i].m == minutes))
                {		
					if(available_days[current_day_week][i+1] != undefined) {
						if(Timepicker.isValidDate(dateDatapicker, available_days[current_day_week][i+1].h, available_days[current_day_week][i+1].m)){
							var minutes = available_days[current_day_week][i+1].m;
							var hours = available_days[current_day_week][i+1].h;
							Timepicker.setTime(hours, minutes);						
							return false;						
						}
					}
                }				
            }
			return false;
        });
		
	$('.tp-prev').click( function(e){
			$('.prepare').css('display', 'none');
            var hours = Math.round($('.hours').val());
            var minutes = Math.round($('.minutes').val());
			for (var i = 0; i < available_days[current_day_week].length; i++)
            {
                if (available_days[current_day_week][i].h == hours && available_days[current_day_week][i].m == minutes)
                {
					
					if(available_days[current_day_week][i-1] != undefined) {
						if(Timepicker.isValidDate(dateDatapicker, available_days[current_day_week][i-1].h, available_days[current_day_week][i-1].m)){
							var minutes = available_days[current_day_week][i-1].m;
							var hours = available_days[current_day_week][i-1].h;
							Timepicker.setTime(hours, minutes);
							return true;
						}
						else{return false;}
					}
					
                }
            }
        });

});


function Datepicker(){
    var $memoryDate = $('#cart-memory-date');
    var $datePicker = $('#datepicker');

    var current_day = new Date().getDate();


    if ($datePicker.length == 0)
        return;
    

    function unavailable(date) {
        var day = date.getDay();
        var chislo = date.getDate();

        if (day == 0)
            day = 7;

        if (available_days[day] == undefined || (current_day == chislo && $(".only_tomorrow").length > 0))
        {
            return [false,"","Unavailable"];
        }

        for (var i in blocked_days)
        {
            if (day == i)
            {
                return [false,"","Unavailable"];
            }

        }
        return [true, ""];

    }

    function getAvailableDay()
    {
        var available_date = new Date();
        var first = true;
        var next_day = available_date.getDate();
        for (var i = 0; i < 10; i++)
        {

            if (i != 0)
            {
                first = false;
                next_day++;
                available_date.setDate(next_day);
            }
            
            var result = unavailable(available_date);
            if (result[0])
            {
                return available_date;
            }
                
        }
    }
    var available_date = getAvailableDay();
    
    $datePicker.datepicker({
        altField: $memoryDate,
        showOtherMonths: true,
        minDate: available_date,
        onSelect: function(dateText, inst) {           
			setDate();
        },
        beforeShowDay: unavailable
    });
    
    setDate();
    function setDate(){
        var day, month;
        var all = $('#datepicker').datepicker('getDate').toDateString().split(' ');
		
		if(navigator.appName == 'Opera'){
			day = all[1];
			month = all[2];
		}
		else {
			day = all[2];
			month = all[1];		
		}
		
		
        switch (month) {
            case 'Jan':month = '€нвар€';break;
            case 'Feb':month = 'феврал€';break;
            case 'Mar':month = 'марта';break;
            case 'Apr':month = 'апрел€';break;
            case 'May':month = 'ма€';break;
            case 'Jun':month = 'июн€';break;
            case 'Jul':month = 'июл€';break;
            case 'Aug':month = 'августа';break;
            case 'Sep':month = 'сент€бр€';break;
            case 'Oct':month = 'окт€бр€';break;
            case 'Nov':month = 'но€бр€';break;
            case 'Dec':month = 'декабр€';break;
        }
        $('#bsk-date').text(Math.round(day) + ' ' + month);
       
        dateDatapicker = new Date($('#datepicker').datepicker('getDate').toDateString());
		current_day_week = dateDatapicker.getDay();
		Timepicker.init();
		$('.prepare').css('display', 'none');
		var insert;
		for(i=0; i<available_days[current_day_week].length; i++){
					
					if(Timepicker.isValidDate(dateDatapicker, available_days[current_day_week][i].h, available_days[current_day_week][i].m)){
					
						Timepicker.setTime(available_days[current_day_week][i].h, available_days[current_day_week][i].m);   
						insert = true;
						break;
					}
		   }
		   if(!insert){
				$('.prepare').css('display', 'block');
		   }
		
		
    }
}


var Timepicker = {
    $memoryTime: "",
    $timepicker: "",
    init: function(){
        var self = this;
        self.$timepicker = $("#timepicker");
        self.$memoryTime = $('#cart-memory-time');
        
        self.setTime();

        /*self.$timepicker.find(".hours, .minutes").blur( function(e){
            var hours = Math.round(self.$timepicker.find('.hours').val());
            var minutes = Math.round(self.$timepicker.find('.minutes').val());

            self.setTime(hours, minutes);
        });*/
    },
    WriteMemory: function(){
        var self = this;
        var time = self.$timepicker.find('.hours').val()+':'+self.$timepicker.find('.minutes').val();
        self.$memoryTime.val(time);
        $('#bsk-time').text(time);
    },
	isValidDate:function(date, h, m){
		
		var today = new Date();
		
		if(today.toDateString() == date.toDateString()){
			
			var today_hours = today.getHours() + 1;
			var today_minutes = today.getMinutes();
		
			if(today_hours > h){
				
				return false;
			}
			else if(today_hours == h && today_minutes > m){
				return false;
			}
		}
		return true;
	},
    setTime: function(h, m)
    {
        var self = this;
        
        if (m >= 60)
            m = 00;

        if (h > 23)
        {
            h = 23;
        }
		 
        self.$timepicker.find('.hours').val(h < 10 ? '0'+h : h);
        self.$timepicker.find('.minutes').val(m < 10 ? '0'+m : m);
		
        
		self.WriteMemory();
        
    }
}



