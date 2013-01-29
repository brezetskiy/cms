

/**
 * Открывает меню дополнительных полей фильтра
 */
function cms_filter_select_box(instance_number){
	$('#filter_'+instance_number+'_select').show();
}


/**
 * Обработчик кнопки "минус поле" 
 */
function cms_filter_field_hide(instance_number, field_id){
	$("#filter_"+instance_number+"_select_input_"+field_id).attr("checked", "");
	cms_filter_field_switch(instance_number);  
}


/**
 * Обработчик чекбокса "добавить/скрыть поле" 
 */
function cms_filter_field_switch(instance_number){
	
	var fields_checked = new Array();
	var i = 0;

	// Показываем отмеченные поля и их таблицы
	$("input[id^='filter_"+instance_number+"_select_input']:checked").each(function(){
		var value = $(this).val();    
		var table_id = value.substr(0, value.indexOf("_"));
		var field_id = value.substr(value.indexOf("_")+1);
		
		fields_checked[i] = {'id':field_id, 'table_id':table_id};
 		i++; 
	}); 
	
	cms_filter_rows_session(instance_number, fields_checked);
	return false;
}


/**
 * Показать все поля
 */
function cms_filter_fields_show(instance_number){
	var fields = new Array();
	var i = 0;
	
	$("input[id^='filter_"+instance_number+"_select_input']").each(function(){
		$(this).attr("checked", "checked");
		
		var value = $(this).val(); 
		var table_id = value.substr(0, value.indexOf("_"));
		var field_id = value.substr(value.indexOf("_")+1);
		
		fields[i] = {'id':field_id, 'table_id':table_id};
 		i++;
	});
	  
	cms_filter_rows_session(instance_number, fields);
	return false;
}
  

/**
 * Скрыть все поля
 */
function cms_filter_fields_hide(instance_number){
	cms_filter_rows_session(instance_number, new Array());  
	return false;
}
       
 
/**
 * Сохранить отображаемые поля в сессию
 */
function cms_filter_rows_session(instance_number, fields){
	var structure_id = $("#filter_"+instance_number).find("#structure_id").val();
	var params = {'instance_number':instance_number, 'structure_id':structure_id, 'fields':fields};
	
	AjaxRequest.send('', "/action/admin/cms/table_filter_control/", 'Загрузка...', true, params);
	return false; 
}
 
 
/**
 * Перерисовка полей
 */
function cms_filter_rows_repaint(instance_number, fields_checked){
	var main_table_id = $("#filter_"+instance_number).find("#table_id").val();
	
	var fields_unchecked = new Array();
	var tables_unchecked = new Array();
	
	// Определяем все доступные дополнительные поля
	$("input[id^='filter_"+instance_number+"_select_input']").each(function(){
		var value = $(this).val(); 
		var table_id = value.substr(0, value.indexOf("_"));
		var field_id = value.substr(value.indexOf("_")+1);
		
		tables_unchecked[table_id] = table_id;
		fields_unchecked[field_id] = {'id':field_id, 'table_id':table_id};
	});
	
	// Показываем отмеченные поля и их таблицы
	jQuery.each(fields_checked, function(i, field){
		var field_id = field.id;
		var table_id = field.table_id;
		
		if(typeof(fields_unchecked[field_id]) != 'undefined' || fields_unchecked[field_id] !== null) {
			delete fields_unchecked[field_id];
		}
				
		if(typeof(tables_unchecked[table_id]) != 'undefined' || tables_unchecked[table_id] !== null) {
			delete tables_unchecked[table_id];
		}  
 		    
		$('#filter_'+instance_number+'_title_'+table_id).show(); 
		$('#filter_'+instance_number+'_row_'+table_id+'_'+field_id).show();
	}); 
	 
	// Скрываем не отмеченные поля
	for ( key in fields_unchecked ) {
		$('#filter_'+instance_number+'_'+fields_unchecked[key].id+'__condition').val('');
		$('#filter_'+instance_number+'_row_'+fields_unchecked[key].table_id+'_'+fields_unchecked[key].id).hide();  
		$("#filter_"+instance_number+"_select_input_"+fields_unchecked[key].id).attr("checked", "");  
		 
		cms_filter_clear(instance_number, fields_unchecked[key].id);
	}
	
	// Скрываем не отмеченные таблицы
	for ( table_id in tables_unchecked ) {
		if(main_table_id != table_id) {
			$('#filter_'+instance_number+'_title_'+table_id).hide();   
		}
	}
}


/**
 * Очистка значений полей
 */
function cms_filter_clear(instance_number, field_id){
	var field_type = $("#filter_input_type_"+field_id).val();
	
	if(field_type == 'list'){
		$("#filter_"+instance_number+"_"+field_id+"__list").val("").change();
	} else if(field_type == 'int'){
		$("#filter_"+instance_number+"_"+field_id+"__from").val("");
		$("#filter_"+instance_number+"_"+field_id+"__to").val("");
	} else if(field_type == 'ajax_list'){
		$("#filter_"+instance_number+"_"+field_id).val("");
		$("#filter_"+instance_number+"_"+field_id+"__text").val("");
		$("#filter_"+instance_number+"_"+field_id+"__fixed").attr("checked", ""); 
	} else if(field_type == 'checkbox'){
		$("#filter_"+instance_number+"_"+field_id+"__checkbox").attr("checked", ""); 
	} else {
		$("#filter_"+instance_number+"_"+field_id+"__text").val("");
	}
}