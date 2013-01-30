var ua = navigator.userAgent.toLowerCase();
// Определим Internet Explorer
var isIE = (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1 && ua.indexOf("webtv") == -1);
// Opera
var isOpera = (ua.indexOf("opera") != -1);
// Gecko = Mozilla + Firefox + Netscape
var isGecko = (ua.indexOf("gecko") != -1);
// Safari, используется в MAC OS
var isSafari = (ua.indexOf("safari") != -1);
// Konqueror, используется в UNIX-системах
var isKonqueror = (ua.indexOf("konqueror") != -1);

/**
 * События
 */
function addHandler(object, event, handler, useCapture) {
	if (object.addEventListener) {
		object.addEventListener(event, handler, useCapture ? useCapture : false);
	} else if (object.attachEvent) {
		object.attachEvent('on' + event, handler);
	} else {
		alert("Add handler is not supported");
	}
}


/**
 * ОКНА
 */
var win = null;
function CenterWindow(myurl, myname, w, h, scroll, status) {
	if(win) win.close();
	status = 1;
	
	if (screen.width <= w + 20) {
		// размер выдаваемого окна больше чем размер экрана пользователя
		scroll = 1;
		w = screen.width - 20;
	}
	
	if (screen.height <= h + 50) {
		// размер выдаваемого окна больше чем размер экрана пользователя
		scroll = 1;
		h = screen.height - 50;
	}
	
	
	// -10 и -50 - поправка для скроллинга и для нижней полосы с меню "Старт"
	LeftPosition = (screen.width) ? (screen.width-w - 10)/2: 0;
	TopPosition = (screen.height) ? (screen.height-h - 50)/2 : 0;
	settings = 'height='+h+',width='+w+',top='+TopPosition+',left='+LeftPosition+',scrollbars='+scroll+',toolbar=0,location=0,resizeable=0,menubar=0,directories=0,dependent=1,status='+status;
	win = window.open(myurl,'_blank',settings)
	return win;
}
function showDialog(myurl, null_reserved, w, h) {
	window.showModalDialog(myurl, window, "dialogHeight:"+h+"px; dialogWidth:"+w+"px; edge: Raised; center: Yes; help: No; resizable: No; status: No; scroll: 0; ");
}
// Центрирует диалоговое окно
function centerDialog() {
	var height = document.body.scrollHeight + 55;
	window.dialogHeight = height + 'px';
	window.dialogLeft = ((screen.width - parseInt(window.dialogWidth)) / 2) + 'px';
	window.dialogTop = ((screen.height - parseInt(window.dialogHeight)) / 2) + 'px';
}
function showImage(url) {
	if (win == '[object]') win.close();
	LeftPosition = (screen.width) ? (screen.width-640 - 10)/2: 0;
	TopPosition = (screen.height) ? (screen.height-480 - 50)/2 : 0;
	win = window.open('/tools/cms/site/image6.php?url='+url,'image','height=480,width=640,top='+TopPosition+',left='+LeftPosition+',scrollbars=1,toolbar=0,location=0,menubar=0,directories=0,dependent=1,status=1')
}
function resizeImageDialog(img) {
	var img = document.getElementById(img);
	var w = img.width + 50;
	var h = img.height + 100;
	resizeDialog(w,h);
}
function resizeDialog(w,h) {
	var mv = 80;
	var mh = 50;
	w = (screen.width <= w + mh) ? screen.width - mh : w;
	h = (screen.height <= h + mv) ? screen.height - mv : h;
	w = (w < 100) ? 100 : w;
	h = (h < 100) ? 100 : h;
	
	LeftPosition = (screen.width) ? (screen.width - w) / 2: 0;
	TopPosition = (screen.height) ? (screen.height - h) / 2 : 0;
	window.resizeTo(w,h);
	window.moveTo(LeftPosition,TopPosition);
}
// Определяет внутреннюю ширину и высоту окна браузера
function windowWidth() {
 return (window.innerWidth)?window.innerWidth:((document.all)?document.body.offsetWidth:null);
}
function windowHeight() {
	return (window.innerHeight)?window.innerHeight:((document.all)?document.body.offsetHeight:null);
}
// Определяет высоту внешней части окна
function windowSpecialHeight() {
	if (window.outerHeight) {
		return window.outerHeight - window.innerHeight;
	} else {
		var fixed = 400;
	    var offW = document.body.offsetWidth;
	    var offH = document.body.offsetHeight;
	    window.resizeTo(fixed, fixed);
	    diffW = document.body.offsetWidth  - offW;
	    diffH = document.body.offsetHeight - offH;
	    var w = fixed - diffW;
	    var h = fixed - diffH;
	    window.resizeTo(w, h);
	    return h - offH;
	}
}

/**
* По нажатию кнопки Esc - закрывает окно,
* по нажатию кнопки Enter - подписывает форму с именем=Form
* Использование : <BODY  onKeyPress="EnterEsc(event);">
*/
function EnterEsc(evt) {
	var charCode = (evt.which) ? evt.which : evt.keyCode
	if (charCode == 13 && document.Form == '[object]') {
		document.Form.submit();
	} else if (charCode == 27) {
		window.close();
	} else {
		return true;
	}
}

/**
* По нажатию кнопки Esc - закрывает окно
* Кнопку Enter - не отрабатываем, в модальных
* окнах он пытается засабмитить форму в новом окне
*/
function Esc(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	if (charCode == 27) {
		window.close();
	} else {
		return true;
	}
}

function EditScript(myurl, myname){
	var w = 0;
	var h = 0; 
	var screen_width = screen.width;
	var screen_height =  screen.height;
	
	if (screen_width >= 1280 && screen_height >= 1024) {
		w = 1060;
		h = 768;
	} else if (screen_width >= 1024 && screen_height >= 768) {
		w = 830;
		h = 600; 
	} else {
		w = 780;
		h = 540;
	}
	return CenterWindow('/tools/cms/admin/editor.php?height'+h+'&'+myurl, myname, w, h, 0, 0);
}

function EditorWindow(myurl, myname) {
	var w = 0;
	var h = 0;
	var screen_width = screen.width;
	var screen_height =  screen.height;
	
	if (screen_width >= 1280 && screen_height >= 1024) {
		w = 1060;
		h = 768;
	} else if (screen_width >= 1024 && screen_height >= 768) {
		w = 830;
		h = 600;
	} else {
		w = 780;
		h = 540;
	}
	
	var ua = navigator.userAgent.toLowerCase();
	var isIE = (ua.indexOf("msie") != -1 && ua.indexOf("opera") == -1 && ua.indexOf("webtv") == -1);
	
	if (!isIE) {
		return CenterWindow('/tools/ckeditor/ckeditor.php?'+myurl, myname, w, h, 0, 0);
	} else {
		return CenterWindow('/tools/editor/editor.php?'+myurl, myname, w, h, 0, 0);
	}
}

function EditWindow(id, table_name_or_id, current_url, return_path, language_current, advanced_param) {
	CenterWindow('/Admin/Edit/'+current_url+'?id='+id+'&_return_path='+return_path+'&_table_id='+table_name_or_id+'&_language='+language_current+'&'+advanced_param, 'edit_'+table_name_or_id, 600, 800, 1, 0);
}



/**
 * КУКИ
 */
function setCookie(cookieName, cookieValue, nDays, path, domain, secure) {
	var today = new Date();
	var expire = new Date();
	if (nDays==null || nDays==0) nDays=1;
	expire.setTime(today.getTime() + 3600000*24*nDays);
	document.cookie = 
		cookieName+"="+escape(cookieValue)+ ";expires="+expire.toGMTString() +
		((path) ? "; path=" + path : "") +
        ((domain) ? "; domain=" + domain : "") +
        ((secure) ? "; secure" : "");
}
function getCookie(name) {
	var dc = document.cookie;
	var prefix = name + "=";
	var begin = dc.indexOf("; " + prefix);
	if (begin == -1) {
	    begin = dc.indexOf(prefix);
	    if (begin != 0) return null;
	} else {
	    begin += 2;
	}
	var end = document.cookie.indexOf(";", begin);
	if (end == -1) {
	    end = dc.length;
	}
	return unescape(dc.substring(begin + prefix.length, end));
}
function delCookie(name) {
  document.cookie = name+"=; expires=Fri, 31 Dec 1999 23:59:59 GMT;";
}


/**
 * ЭЛЕМЕНТЫ ФОРМ
 */
// В форме SELECT MULTIPLE 1. выбирает все 2. снимает со всех выделение 3. инвертирует выделение
function select_options(id, value) {
	var select_id = document.getElementById(id);
	for(i=0; i < select_id.options.length; i++) {
		if (value == 'invert') {
			select_id.options[i].selected = (select_id.options[i].selected) ? false : true;
		} else {
			select_id.options[i].selected = value;
		}
	}
}
// Выбирает опцию поля SELECT, останавливается на первой опции
function select_option(id, value) {
	var elem = document.getElementById(id);
	for(var i=0;i<elem.options.length;i++) {
		if(elem.options[i].value == value) {
			elem.options[i].selected = true;
			return;
		}
	}
}
// при загрузке страницы устанавливает курсор пользователя на первый
// попавшийся текстовый элемент формы
function FormFocus() {
	var obj = document.getElementById('Form');
	if(!obj) return; // На странице нет форм
	for(var i = 0; i < obj.elements.length; i++) {
		if ((obj.elements[i].type == 'text' || obj.elements[i].type == 'textarea') && obj.elements[i].disabled == false) {
			obj.elements[i].focus();
			return;
		}
	}
}
/**
 * Устанавливаем галочки на checkbox атрибут name которых начинается на парамтер name
 */
var check_checkbox_state = true;
function checkCheckbox(name, state, form_id) {
	if (state == null) {
		state = check_checkbox_state;
		check_checkbox_state = !check_checkbox_state;
	}
	var parentElement = (byId(form_id)) ? byId(form_id) : document;
	var input_obj = parentElement.getElementsByTagName('INPUT');
	for(var i=0;i<input_obj.length;i++) {
		if (input_obj[i].type.toLowerCase() == 'checkbox' && input_obj[i].name.substr(0, name.length) == name) {
			input_obj[i].checked=state;
		}
	}
}
// Активирует radio кнопки, у которых определённое значение
function selectRadio(name, value) {
	var input_obj = document.getElementsByTagName('INPUT');
	
	for(var i=0;i<input_obj.length;i++) {
		if (input_obj[i].type!='radio') {
			continue;
		}
		if (input_obj[i].name.substr(0, name.length) == name && input_obj[i].value == value) {
			input_obj[i].checked=true;
		}
	}
}
// Позволяет вставлять в поле input только цифры Использование :  onKeyPress="return digitsOnly(event);"
function digitsOnly(evt) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	return ((charCode > 47 && charCode < 58) || charCode == 13) ? true : false;
}
// Позволяет в полях file выбирать только файлы картинок JPG, PNG, GIF Использование : onChange="imagesOnly(this);"
function imagesOnly(file_element) {
	var imgRegExp = /\.(jpg)|(gif)|(png)$/i;
	if(null == file_element.value.match(imgRegExp)) {
		file_element.select()
		alert ("Выбранный файл не является картинкой. Пожалуйста, выберите файл с картинкой в формате JPG, GIF или PNG")
	}
}
/**
* Ограничивает поля input type="text" и textarea по количеству символов
* и показывает визуально сколько символов введено
* Использоавние :onKeyDown="return countTextField(this, event, 255, 300);"
* @param text_limit - ограничение по количеству символов
* @param bar_length - длинна строки, которая отображает кол-во введенных символов
*/
function countTextField(obj, evt, text_limit) {
	var charCode = (evt.which) ? evt.which : event.keyCode
	var bar_size = obj.scrollWidth;
	var bar_width = Math.floor(bar_size * obj.value.length / text_limit);
	
	document.getElementById(obj.id + "_ctf").style.display = "inline";
	document.getElementById(obj.id + "_ctf_bar").style.width = bar_size;
	
	document.forms["Form"].elements[obj.id + "_ctf_counter"].value = obj.value.length + '/' + text_limit;
	document.getElementById(obj.id + "_ctf_filler").style.width = bar_width;
	document.getElementById(obj.id + "_ctf_counter_div").style.left = (bar_width < bar_size) ? bar_width - 15: bar_size - 15;
	
	return (obj.value.length > text_limit && (charCode > 47 || charCode == 13 || charCode == 32)) ? false : true;
}

// Выбирает опцию поля SELECT, останавливается на первой опции
function selectOption(id, value) {
	var elem = document.getElementById(id);
	for(var i=0;i<elem.options.length;i++) {
		if(elem.options[i].value == value) {
			elem.options[i].selected = true;
			return;
		}
	}
}






/**
 * Слои
 */
function getBodyScrollTop() {
	return self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
}

// Ставит слой в центр экрана или в центр элемента center_of_element
function centerDiv(layer) {
	$('#'+layer).css('left', Math.floor(($(window).width() - $('#'+layer).width()) /2)+'px');
	$('#'+layer).css('top', Math.floor(($(window).height() - $('#'+layer).height()) /2 + $(document).scrollTop() )+'px');
}

/**
 * Разное
 */
// Алиас для функции document.getElementById()
function byId(id) {
	return document.getElementById(id);
}
// Определение положения элемента на странице
function position(el)	{ 
	var p = { x: el.offsetLeft, y: el.offsetTop };
	while (el.offsetParent)	{
		el = el.offsetParent;
		p.x += el.offsetLeft;
		p.y += el.offsetTop;
		if (el != document.body && el != document.documentElement) {
			p.x -= el.scrollLeft;
			p.y -= el.scrollTop;
		}
	}
	return p;
}

function var_dump(d,l) {
    if (l == null) l = 1;
    var s = '';
    if (typeof(d) == "object") {
        s += typeof(d) + " {\n";
        for (var k in d) {
            for (var i=0; i<l; i++) s += "  ";
            s += k+": " + var_dump(d[k],l+1);
        }
        for (var i=0; i<l-1; i++) s += "  ";
        s += "}\n"
    } else {
        s += "" + d + "\n";
    }
    return s;
}
/**
* Блокировка нажатия правой кнопки мышки
* Использование BODY|INPUT onContextMenu="return contextMenu();"
*/
function contextMenu() {
	event.cancelBubble = true;
	return false;
}

/* Перевод из 10-й в 16-ю систему, используется в редакторе при выборе цвета */
function dec2hex(n){
	var hex = "0123456789ABCDEF";
	var mask = 0xf;
	var retstr = "";
	while(n != 0) {
		retstr = hex.charAt(n&mask) + retstr;
		n>>>=4;
	}
	return retstr.length == 0 ? "00" : retstr;
}

var get_brace_number = [];
function getBraceNumber (name) {
	if (name.substr( name.length - 2 ) != '[]') return name;
	name = name.substr(0, name.length - 2 );
	get_brace_number[ name ] = (typeof(get_brace_number[ name ]) == 'undefined') ? 0 : get_brace_number[ name ]+1;
	return name+'['+ get_brace_number[ name ] +']';
}
function form2array(form_id) {
	get_brace_number = [];
	var param = new Array();
	$('#'+form_id+' input:text, #'+form_id+' textarea,#'+form_id+' input:checked, #'+form_id+' select, #'+form_id+' input[type=hidden], #'+form_id+' input[type=file]').each(function() {
		val = $(this).val();
		if (this.type == 'file') {
			param[ getBraceNumber(this.name) ] = this;
		} else if (val instanceof Array) {
			for(var i=0;i<val.length;i++) {
				param[ getBraceNumber(this.name) ] = val[i];
			}
		} else {
			param[ getBraceNumber(this.name) ] = val;
		}
	});
	return param;
}

/**
 * Функция генерирует случайный код длиной length из символов chars (или стандартных, если chars не указаны)
 */
function createUniqCode(length, chars) {
	if (chars == null) {
		chars = "abcdefghijklmnopqrstuvwxyz1234567890";
	}
	
	if (length == null) {
		length = 20;
	}
	
	uniq = "";
	for(i=0;i<length;i++)
	{
		c = Math.floor(Math.random() * chars.length);
		uniq += chars.charAt(c);
	}
	return uniq;
}


function toggleAdminBar(mode) {
	if(adminbar_mode!='hidden') {
		document.getElementById('adminbar_holder').style.width = '140px';
		document.getElementById('adminbar_toggle').src = '/design/cms/img/ui/fam/control_play_blue.gif';
		adminbar_mode = 'hidden';
	} else {
		document.getElementById('adminbar_holder').style.width = '100%';
		document.getElementById('adminbar_toggle').src = '/design/cms/img/ui/fam/control_back_blue.gif';
		adminbar_mode = 'visible';
	}
	document.getElementById('adminbar_toggle').blur();
	document.getElementById('adminbar_toggle').parentNode.blur();
	delCookie('adminbar_mode');
	setCookie('adminbar_mode', adminbar_mode, 365, '/');
	return false;
}


/**
 * считывает данные с в указанной форме и передаёт их через ajax в скрипт
 */
(AjaxRequest = {
	req : {}, // Объект JsHtppRequest
	
	form : function(form_id, preloader_message, advanced_param) {
		var event_file = $('#'+form_id).attr('action');
		this.send(form_id, event_file, preloader_message, true, advanced_param);
	},
	
	action : function(event_file, preloader_message, advanced_params) {
		this.send('', event_file, preloader_message, true, advanced_params);
	},
	
	/**
	 * Depricated use AjaxRequest.form, AjaxRequest.data instead 
	 */
	send : function (form_id, event_file, preloader_message, use_lock, advanced_params) {
		this.get_brace_number = new Array();
		this.lock = true;
		this.req = new JsHttpRequest();
		this.req.caching = false;
		this.req.open('POST', event_file, true);
		this.req.onreadystatechange = function() {
			AjaxRequest.responseParser(form_id);
		}
		var param = (byId(form_id)) ? form2array(form_id) : new Array();
				
		// Добавляем дополнтельные параметры
		if (advanced_params) {
			for (key in advanced_params) {
				field_name = getBraceNumber(key);
				param[ field_name ] = advanced_params[key];
			}
		}
		this.req.send(param);
		
		// Показываем preloader
		if (preloader_message) {
			$('#ajaxPreloader').css('display', 'block').html(preloader_message);
			centerDiv('ajaxPreloader');
		}
	},
	

	responseParser : function (form_id) {
//		alert(this.req.readyState);
		if (this.req.readyState != 4) {
			return;
		}

		//alert(this.req.responseText);
		if (form_id != 'form_order_form')
		{
			if (this.req.responseText.replace(/(^\s+)|(\s+$)/g, "") != '') {
				//centerDiv('ajaxPreloader');
				//alert(this.req.responseText);
				showPopup('reference');
				$('#popup').append("<div id='popup-message'>"+this.req.responseText+"</div>");
				//$('#ajaxPreloader').css('text-align', 'left').html('<div style="text-align:right;"><a href="javascript:void(0);" onclick="$(\'#ajaxPreloader\').css(\'display\', \'none\')">[x] Закрыть</a></div>'+this.req.responseText).show();
			} else {
				if (form_id != null)
				{
					$('#' + form_id).each(function(){
						this.reset();
					});
					showPopup('reference');
				//$('#popup').append("<div id='popup-message'>"+this.req.responseText+"</div>");
				$('#popup').append("<div id='" + form_id + "'></div>");
				$('#ajaxPreloader').css('display', 'none');
				};
			}
		};

		var exec = '';
		for (key in this.req.responseJS) {
			if (key == 'javascript') {
//				alert(JSON.parse(this.req.responseJS[key]));
				exec = this.req.responseJS[key];
			} else if (key == 'action_ok') {
				$.jGrowl(this.req.responseJS[key], {position:'center','life':3000});
			} else if (key == 'action_warning') {
				$.jGrowl(this.req.responseJS[key], {position:'center','life':3000});
			} else if (key == 'action_error') {
				$.jGrowl(this.req.responseJS[key], {position:'center','life':3000});
			} else {
				$('#'+key).html(this.req.responseJS[key]);
			}
		}
		
		// Выполняем JavaScript после того как отобразим все переданные значения
		if (exec != '') {
			eval(exec);
		}

	}
}
);

/* Изменение месяца в календаре */
function updateCalendar(month, year, current_date, type) {
	AjaxRequest.send('', '/action/news/calendar', '', true, {'month':month, 'year':year, 'current_date':current_date, 'type': type});
	return false;
}


function is_numeric (mixed_var) {
    return (typeof(mixed_var) === 'number' || typeof(mixed_var) === 'string') && mixed_var !== '' && !isNaN(mixed_var);
}


/**
 * Округление числа
 */
function round_number(num, dec) {
	var result = Math.round(num*Math.pow(10,dec))/Math.pow(10,dec);
	return result;
}


/**
 * Аналог одноименной php функции
 */
function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
        
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    
    return s.join(dec);
}