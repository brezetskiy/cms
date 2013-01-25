function changeRadio(el)
{

	var el = el,
		input = el.find("input").eq(0);
	var nm=input.attr("name");
		
	jQuery(".niceRadio input").each(
	
	function() {
     
		if(jQuery(this).attr("name")==nm)
		{
			jQuery(this).parent().removeClass("radioChecked");
		}
	   
	   
	});					  
	
	
	if(el.attr("class").indexOf("niceRadioDisabled")==-1)
	{	
		el.addClass("radioChecked");
		input.attr("checked", true);
	}
	
    return true;
}

function changeVisualRadio(input)
{

	var wrapInput = input.parent();
	var nm=input.attr("name");
		
	jQuery(".niceRadio input").each(
	
	function() {
     
		if(jQuery(this).attr("name")==nm)
		{
			jQuery(this).parent().removeClass("radioChecked");
		}
	   
	   
	});

	if(input.attr("checked")) 
	{
		wrapInput.addClass("radioChecked");
	}
}

function changeRadioStart(el)
{

try
{
var el = el,
	radioName = el.attr("name"),
	radioId = el.attr("id"),
	radioChecked = el.attr("checked"),
	radioDisabled = el.attr("disabled"),
	radioTab = el.attr("tabindex"),
	radioValue = el.attr("value");
	if(radioChecked)
		el.after("<span class='niceRadio radioChecked'>"+
			"<input type='radio'"+
			"name='"+radioName+"'"+
			"id='"+radioId+"'"+
			"checked='"+radioChecked+"'"+
			"tabindex='"+radioTab+"'"+
			"value='"+radioValue+"' /></span>");
	else
		el.after("<span class='niceRadio'>"+
			"<input type='radio'"+
			"name='"+radioName+"'"+
			"id='"+radioId+"'"+
			"tabindex='"+radioTab+"'"+
			"value='"+radioValue+"' /></span>");
		
	if(radioDisabled)
	{
		el.next().addClass("niceRadioDisabled");
		el.next().find("input").eq(0).attr("disabled","disabled");
	}
			
	el.next().bind("mousedown", function(e) { changeRadio(jQuery(this)) });
	if(jQuery.browser.msie)	el.next().find("input").eq(0).bind("click", function(e) { changeVisualRadio(jQuery(this)) });	
	else el.next().find("input").eq(0).bind("change", function(e) { changeVisualRadio(jQuery(this)) });
	el.remove();
}
catch(e)
{
}

    return true;
}




                                 


function changeCheck(el)
{

	var el = el,
		input = el.find("input").eq(0);
		  
	if(el.attr("class").indexOf("niceCheckDisabled")==-1)
	{	
   		if(!input.attr("checked")) {
		//	el.addClass("niceChecked");
			input.attr("checked", true);
		} else {
			//el.removeClass("niceChecked");
			input.attr("checked", false).focus();
		}
	}
	
    return true;
}

function changeVisualCheck(input)
{
var wrapInput = input.parent();
	if(!input.attr("checked")) {
		wrapInput.removeClass("niceChecked");
	}
	else
	{
		wrapInput.addClass("niceChecked");
	}
}

function changeCheckStart(el)

{

try
{
var el = el,
	checkName = el.attr("name"),
	checkId = el.attr("id"),
	checkChecked = el.attr("checked"),
	checkDisabled = el.attr("disabled"),
	checkTab = el.attr("tabindex"),
    checkValue = el.attr("value");
	if(checkChecked)
		el.after("<span class='niceCheck niceChecked'>"+
			"<input type='checkbox'"+
			"name='"+checkName+"'"+
			"id='"+checkId+"'"+
			"checked='"+checkChecked+"'"+
            "value='"+checkValue+"'"+
			"tabindex='"+checkTab+"' /></span>");
	else
		el.after("<span class='niceCheck'>"+
			"<input type='checkbox'"+
			"name='"+checkName+"'"+
			"id='"+checkId+"'"+
             "value='"+checkValue+"'"+
			"tabindex='"+checkTab+"' /></span>");
		
	if(checkDisabled)
	{
		el.next().addClass("niceCheckDisabled");
		el.next().find("input").eq(0).attr("disabled","disabled");
	}
	
	
	el.next().bind("mousedown", function(e) { changeCheck(jQuery(this)) });
	el.next().find("input").eq(0).bind("change", function(e) { changeVisualCheck(jQuery(this)) });
	if(jQuery.browser.msie)
	{
		el.next().find("input").eq(0).bind("click", function(e) { changeVisualCheck(jQuery(this)) });	
	}
	el.remove();
}
catch(e)
{

}

    return true;
}