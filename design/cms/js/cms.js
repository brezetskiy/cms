// Игнорирует нажатие всех клавиш, кроме Ctrl+R и Tab (для больших внешних полей)
function ignoreKey() {
	// Ctrl + R
//	if (window.event.ctrlKey && event.keyCode==82) {
	if (window.event.ctrlKey) {
		return true;
	}
	// Tab
	if (event.keyCode == 9) {
		return true;
	}
	return false;
}
function ignoreEnter(event) {
	var key = event.keyCode || event.charCode;
	if (key == 13 && navigator.appName == 'Microsoft Internet Explorer') {
		return false;
	} else if (key == 13) {
		event.cancelBubble = true;
		event.preventDefault();
		event.stopPropagation();
		event.returnValue = false;
		return false;
	} else {
		return true;
	}
}

function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		curleft = obj.offsetLeft
		curtop = obj.offsetTop
		while (obj = obj.offsetParent) {
			curleft += obj.offsetLeft
			curtop += obj.offsetTop
		}
	}
	return [curleft,curtop];
}

var dbg_counter = 0;
function dbg(msg) {
	if (document.getElementById('dbg')) {
		dbg_counter++;
		document.getElementById('dbg').innerHTML += "<br>" + dbg_counter + '. ' + msg;
	}
}
   
/**
 * Обновляет форму, когда обновился внешний ключ, что б форма загрузила обновлённые 
 * выпадающие меню с внешними ключами.
 */
function refreshFKey(form_id, field_id, field_name, value) {
	byId(form_id).action = '/action/admin/cms/refresh/';
	
	if (byId(field_id).parentNode) { // FF
   		byId(field_id).parentNode.removeChild(byId(field_id));
	} else if (byId(field_id)) { // IE
		byId(field_id).removeNode(true);
	}
	var oElement = document.createElement("input");
	oElement.type='hidden';
	oElement.name=field_name;
	oElement.value=value;
	byId(form_id).appendChild(oElement);
	byId(form_id).submit();
}


(AjaxSelect = {
	
	previous_value: null,
	input_id: null,
	
	init: function(table_id, input_id, field) {
		this.input_id = input_id
		$("#"+input_id).autocomplete("/tools/cms/admin/ajax_select.php", { minChars:2, matchSubset:0, matchContains:0, cacheLength:1, onItemSelect:this.selectItem, selectOnly:1, extraParams: {'table_id': table_id, 'field_name': field} });
		$("#"+input_id).keydown(function() {AjaxSelect.keydown(input_id)}).keyup(function() {AjaxSelect.keyup(input_id)});
	},
	
	keydown: function(input_id) {
		this.previous_value = byId(input_id).value;
	},
	
	keyup: function(input_id) {
		if(this.previous_value != byId(input_id).value) {
			this.fixValue(input_id, 0);
		}
	},
	
	fixValue: function(input_id, value) {
		if (value>0) {
			$('#'+input_id+'_fixed').attr('checked', 'checked');
		} else {
			$('#'+input_id+'_fixed').removeAttr('checked');
		}
		byId(input_id+'_value').value = value;
	},
	
	selectItem: function(li) {
		AjaxSelect.fixValue(li.extra[1], li.extra[0])
	}
	
});



/* cms_edit */


var loaded = new Array();
function extMultiple(layer_id, checkbox_name, fk_table_id, level, parent_id, master_id, relation_table_name, relation_select_field, relation_parent_field) {
	// Показываем или прячем слой, если слой хоть раз был загружен,
	// то повторно загружать его не надо
	if (document.getElementById(layer_id).style.display == 'inline') {
		document.getElementById(layer_id).style.display = 'none';
		document.getElementById('img_' + layer_id).src = '/img/shared/toc/plus.png';
	} else {
		document.getElementById(layer_id).style.display = 'inline';
		document.getElementById('img_' + layer_id).src = '/img/shared/toc/minus.png';
	}
	
	if (loaded[layer_id] == 1) {
		return;
	}
	
	document.getElementById(layer_id).innerHTML = '<span style="margin-left:' + (10 * level) + 'px" class="comment">идёт загрузка, подождите...</span><br>';
	
	var req = new JsHttpRequest();
	req.onreadystatechange = function() {
		if (req.readyState == 4) {
			document.getElementById(layer_id).innerHTML = req.responseText;
			if (req.responseJS && req.responseJS['exec'] != '') {
				eval(req.responseJS['exec']);
			}
			loaded[layer_id] = 1;
		}
	}
	req.caching = true;
	req.open('POST', '/action/admin/cms/ext_multiple/', true);
	req.send({ 'parent_element':layer_id, 'level':level, 'checkbox_name':checkbox_name, 'fk_table_id':fk_table_id, 'parent_id':parent_id, 'master_id':master_id, 'relation_table_name':relation_table_name, 'relation_select_field':relation_select_field, 'relation_parent_field':relation_parent_field });
}
function extMultipleNoSubmenu(layer_id) {
	document.getElementById('img_' + layer_id).src = '/img/shared/toc/dot.png';
}

function getWeekDay(table_id, field_name) {
	var year = document.getElementById(table_id + '_'+field_name+'__year').value;
	var month = document.getElementById(table_id + '_'+field_name+'__month').value;
	var day = document.getElementById(table_id + '_'+field_name+'__day').value;
	var mydate = new Date(year, month-1, day);
	var div = document.getElementById('weekday_'+field_name);
	
	switch (mydate.getDay()) {
		case 0:
			div.innerHTML = '<font color="red">Воскресенье</font>';
		break;
		case 1:
			div.innerHTML = 'Понедельник';
		break;
		case 2:
			div.innerHTML = 'Вторник';
		break;
		case 3:
			div.innerHTML = 'Среда';
		break;
		case 4:
			div.innerHTML = 'Четверг';
		break;
		case 5:
			div.innerHTML = 'Пятница';
		break;
		case 6:
			div.innerHTML = '<font color="red">Суббота</font>';
		break;
	}
}
function set_null(field, state) {
	if (state) {
		$('#'+field).addClass('null_value');
		$('#'+field+'_null').attr('checked', 'checked');
	} else {
		$('#'+field).removeClass('null_value');
		$('#'+field+'_null').removeAttr('checked');
	}
}



/* cms view */

(cmsView = {
	showFilter: function (instance_number) {
		var filter = byId('filter_form_'+instance_number);
		filter.style.display = (filter.style.display=='block') ? 'none' : 'block';
	},
	filterBetween: function (element_id) {
		if($("#"+element_id+"_condition").val() == "between") {
			$("#"+element_id+"_condition").val("=");
			$("#to_input_"+element_id).css("display", "none");
			$("#to_switcher_"+element_id).attr("title", "Внутри интервала");
			$("#to_switcher_"+element_id).find("img").attr("src", "/design/cms/img/filter/gray_spacer.png");
		} else {
			$("#"+element_id+"_condition").val("between");
			$("#to_input_"+element_id).css("display", "inline");
			$("#to_switcher_"+element_id).attr("title", "Равно");
			$("#to_switcher_"+element_id).find("img").attr("src", "/design/cms/img/filter/gray_spacer_active.png");
		}
	}, 
	filterActivate: function (element_id, condition) {
		if(typeof(condition) == 'undefined') condition = "="; 
		if ($("#"+element_id+"_condition").val() == "") $("#"+element_id+"_condition").val(condition);
	},
	changeAction: function (action, instance_number) {
		var form = document.getElementById('form_'+instance_number);
		form.action = action;
		form.submit();
	},
	
	// Определяем количество выбранных разделов и подсвечиваем нужные события
	eventToggle: function () {
		$('.cms_view_form').each(function() {
			var count = $(this).find('.id:checked').length;
			$(this).find('.event').each(function() {
				(cmsView.isActive($(this).attr('rel'), count)) ? $(this).fadeTo('slow', 1) : $(this).fadeTo('slow', 0.33);
			});
		});
	},
	init: function () {
		// Аттачим пометку ряда при наведении мышки
		$('.cms_view tr').hover(function() { $(this).addClass('over')}, function() { $(this).removeClass('over')});
		
		// Кнопка "выделить всё"
		$('.cms_view .check_all').bind('click', function() {
			if(this.checked) {
				$(this).parents('table.cms_view').find('.id').attr('checked', 'checked');
				$(this).parents('table.cms_view').find('tr:has(.id)').addClass('selected');
			} else {
				$(this).parents('table.cms_view').find('.id').removeAttr('checked');
				$(this).parents('table.cms_view').find('tr:has(.id)').removeClass('selected');
			}
			cmsView.eventToggle();
		});
		
		// Пометка выделенного ряда
		$('.cms_view .id').bind('click', function() {
			if (this.checked) {
				$(this).parents('tr').addClass('selected');
			} else {
				$(this).parents('tr.selected').removeClass('selected');
			}
			cmsView.eventToggle();
		});
		
		// выделяем все выделенные по умолчанию ряды (firefox восстанавливает значения при возврате)
		$('table.cms_view').find('tr:has(.id:checked)').addClass('selected');
		
		// hover над событиями
		$('.event').hover(function(){
			$(this).attr('src', $(this).attr('lang'));
		}, function() {
			$(this).attr('src', $(this).attr('longdesc'));
		});
		
		// деактивируем недоступные кнопки
		cmsView.eventToggle();
	},
	isActive: function(flag, count) {
		return (flag === '' || (count == 0 && flag & 100) || (count == 1 && flag & 010) || (count > 1 && flag & 001)) ? true : false;
	},
	
	click: function(obj, message) {
		if (message && !confirm(message)) return false;
		var count = $(obj).parents('.cms_view_form').find('.id:checked').length;
		var flag = $(obj).find(':first-child').attr('rel');
		return cmsView.isActive(flag, count);
	},
	
	editWindow: function (obj, copy) {
		var current_url = $(obj).parents('.cms_view_form:first').find('input[name=_current_url]').val();
		var return_path = $(obj).parents('.cms_view_form:first').find('input[name=_return_path]').val();
		var language_current = $(obj).parents('.cms_view_form:first').find('input[name=_language_current]').val();
		var table_id = $(obj).parents('.cms_view_form:first').find('input[name=_table_id]').val();
		var id = '';
		$(obj).parents('.cms_view_form:first').find('.id:checked').each(function() {
			id += $(this).val()+',';
		});
		var advanced_param='';
		if (copy) advanced_param='_copy=1';
		EditWindow(id, table_id, current_url, escape(return_path), language_current, advanced_param)
	},
	
	addWindow: function (obj, params) {
		var table_id = $(obj).parents('.cms_view_form:first').find('input[name=_table_id]').val();
		var current_url = $(obj).parents('.cms_view_form:first').find('input[name=_current_url]').val();
		var return_path = $(obj).parents('.cms_view_form:first').find('input[name=_return_path]').val();
		var language_current = $(obj).parents('.cms_view_form:first').find('input[name=_language_current]').val();
		EditWindow(0, table_id, current_url, escape(return_path), language_current, params)
	},
	
	getChecked: function (obj_id) {
		var list = new Array();
		$('#'+obj_id+' input.id:checked').each(function() {
			list[list.length] = $(this).val();
		});
		return list;
	}
});


function tableDnDonDrop() {
	var form_name = $(table).parents('form.cms_view_form:first').attr('id');
    AjaxRequest.send(form_name, '/action/admin/cms/table_sort/', 'Сохранение', true, {});
}

/* hotkey */


(Hotkey = {
	_hotKeys : {},
	hotElements : ['a', 'input'],
	Init : function() {
		for (i = 0; i < this.hotElements.length;i++) {
			var e = document.getElementsByTagName(this.hotElements[i]);
			if (e) 
				for (var j = 0; j< e.length; j++) 
					if (e[j].accessKey) this.AddKeyCode(e[j], e[j].accessKey);
	    }
	    if (document.attachEvent) document.attachEvent('onkeydown', function(){return Hotkey.KeyDown(event)});
	    else document.addEventListener('keypress', function(ev){Hotkey.KeyDown(ev)}, true);
	},
	AddKeyCode : function(obj, keycode) {
		if (typeof(obj) == 'function' || typeof(obj) == 'object') {
			this._hotKeys[keycode] = obj;
			return keycode;
		} else return false;
	},
	KeyDown : function (event) {
		if (event.ctrlKey) {
			var key = event.keyCode || event.charCode;
			if (this._hotKeys[key]) {
				if (navigator.appName == 'Microsoft Internet Explorer') this._hotKeys[key].click();
				else {
					event.cancelBubble = true;
					event.preventDefault();
					event.stopPropagation();
					if (this._hotKeys[key].nodeName == 'INPUT') this._hotKeys[key].click();
					else if (this._hotKeys[key].onclick) this._hotKeys[key].onclick();
					else window.location = this._hotKeys[key].href;
				}
				event.returnValue = false;
				return false;
			}
		}
	}
});