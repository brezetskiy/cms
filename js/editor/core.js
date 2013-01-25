/**
* HTML редактор
* @author Rudenko Ilya <rudenko@delta-x.com.ua>
* @copyright Delta-X, 2006
*/
(Editor = {
	// Родительские элементы
	parentNodes : [],
	
	// список элементов, в которых исключены повторения.
	// например если пользователь щёлкнул по таблице, которая вложена в другую таблицу,
	// то current[TABLE] будет содержать элемент верхней таблицы
	current : [],
	
	// список элементов, которые прячутся на странице при выводе контекстного меню
	hiddenElements : [],
	
	// Теги для которых есть контекстное меню
	contentMenuTags : ['A', 'IMG', 'TABLE', 'TR', 'TD', 'THEAD', 'TBODY', 'TFOOT'],
	
	tooltipIndex : 0,
	tooltips : [
		'Для того, чтоб перейти на новую строку нажмите Shift+Enter', 
		'Для поиска строки в документе используйте Ctrl+F',
		'Для ввода жирного текста нажмите Ctrl+B',
		'Для ввода курсивного текста нажмите Ctrl+I',
		'Для вызова стандартного контекстного меню удерживайте Ctrl',
		'Для редактирования картинки щелкниете по ней дважды' 
	],
	
	shTag : ['A', 'TD', 'TABLE', 'IFRAME', 'FORM', 'MARQUEE', 'OBJECT', 'DIV'],
	shAttrib : [
		'background: #D2E6FA; padding: 5px;',
		'border:red dotted 1px;',
		'border:red dotted 1px;',
		'border:red dotted 1px;',
		'background-color:#f0f0f0; border:blue dotted 1px; padding: 3px;',
		'border:red double 3px;',
		'border:red dashed 1px;'
	],
	
	contextElements : [],
	toolbarButtons : [],
	
	mode : 'Edit',
	showHidden : false,
	sSheet : {},
	
	// указатель на таймер, который выдаёт сообщение об окончании сессии
	auth_timer : 0,
	
	// параметры, передаваемые в конструктор
	auth_timeout : 0,
	id : 0,
	table_name : '',
	field_name : '',
	admin_event : '',
	_language : '',
	
	submitForm : {},
		
	Init : function(auth_timeout, id, table_name, field_name, admin_event, _language) {
		// Закрываем окно, если у него нет родителя (пользователь нажал Ctrl+N)
		if(!window.opener) window.close();
		frames.EditFrame.document.designMode = frames.SrcFrame.document.designMode = "On";
		frames.EditFrame.focus();
		
		this.auth_timeout = auth_timeout;
		this.id = id;
		this.table_name = table_name;
		this.field_name = field_name;
		this._language = _language;
		this.admin_event = admin_event;
		
		var html_tidy = getCookie('html_tidy');
		if (html_tidy == 1 || html_tidy == null) {
			document.getElementById('html_tidy').checked = true;
		}
		var html_auto_charset = getCookie('html_auto_charset');
		if (html_auto_charset == 1) {
			document.getElementById('html_auto_charset').checked = true;
		}
		
		this.auth_timer = window.setTimeout("alert('Сохраните файл! \\nВремя максимального бездействия \\nсистемы - заканчивается через 5 минут!')", this.auth_timeout);
		
		frames.EditFrame.document.attachEvent('onclick', function(e){Editor.GetParentNodes(e); Editor.ContextMenuClose(); return true;});
		frames.EditFrame.document.attachEvent('oncontextmenu', function(e){return Editor.ContextMenu(e);});
		frames.EditFrame.document.attachEvent('onkeyup', function(e){Editor.GetParentNodes(e); return true;});
		
		document.all.page.style.visibility = "visible";
		document.all.preload.style.visibility = "hidden";
	},
	// disabled - кнопка может быть выключена? Для двухрежимных плавающих тулбарин
	AddToolbarButton : function(toolbar, image, event, title, disabled) {
		if (!this.toolbarButtons[toolbar]) {
			this.toolbarButtons[toolbar] = [];
		}
		this.toolbarButtons[toolbar][this.toolbarButtons[toolbar].length] = [image, event, title, disabled];
	},
	BuildToolbar : function(toolbar) {
		var html = '';
		html = '<table class="toolbar" width="68" onmousedown="Editor.Drag(document.all.toolbar_'+toolbar+')"><tr>'+
					'<td><img src="/design/cms/img/editor/toolbar/top_left.gif" width="4" height="16" alt="" border="0"></td>'+
					'<td class="head"><a onclick="document.all.toolbar_'+toolbar+'.style.display=\'none\';"><img src="/design/cms/img/editor/toolbar/close.gif" width="12" height="12" alt="" border="0" style="cursor:hand;position:absolute;top:3px;left:50px;"></a></td>'+
					'<td><img src="/design/cms/img/editor/toolbar/top_right.gif" width="4" height="16" alt="" border="0"></td>'+
				'</tr><tr>'+
					'<td class="left"><img src="/img/shared/1x1.gif" width="4" height="1" border="0" alt=""></td><td>'+
						'<table id="'+toolbar+'_toolbar_on" width="58" border="0" cellspacing="0" cellpadding="2" class="float_toolbar" style="visibility:hidden;">';
		for(var i=0;i<this.toolbarButtons[toolbar].length;i++) {
			if (i == 0 || i % 2 == 0) {
				html += '<tr>'
			}
			html += '<td><img src="/design/cms/img/editor/'+this.toolbarButtons[toolbar][i][0]+'.gif" class="button" onClick="'+this.toolbarButtons[toolbar][i][1]+'" onmouseover="Editor.mOver(this);" onmouseout="Editor.mOut(this);" alt="'+this.toolbarButtons[toolbar][i][2]+'"></td>';
		}
		html += '</table></td><td class="right"><img src="/img/shared/1x1.gif" width="4" height="1" border="0" alt=""></td></tr><tr>'+
					'<td><img src="/design/cms/img/editor/toolbar/bot_left.gif" width="4" height="4" alt="" border="0"></td>'+
					'<td class="foot"></td>'+
					'<td><img src="/design/cms/img/editor/toolbar/bot_right.gif" width="4" height="4" alt="" border="0"></td></tr></table>'+
			'<table id="'+toolbar+'_toolbar_off" width="58" border="0" cellspacing="0" cellpadding="2" class="float_toolbar_off">';
		for(i=0;i<this.toolbarButtons[toolbar].length;i++) {
			if (i == 0 || i % 2 == 0) {
				html += '<tr>'
			}
			html += (this.toolbarButtons[toolbar][i][3] == true) ?
				'<td><img src="/design/cms/img/editor/'+this.toolbarButtons[toolbar][i][0]+'.gif" class="disabled" alt="'+this.toolbarButtons[toolbar][i][2]+'"></td>':
				'<td><img src="/design/cms/img/editor/'+this.toolbarButtons[toolbar][i][0]+'.gif" class="button" onClick="'+this.toolbarButtons[toolbar][i][1]+'" onmouseover="Editor.mOver(this);" onmouseout="Editor.mOut(this);" alt="'+this.toolbarButtons[toolbar][i][2]+'"></td>';
		}
		html += '</table>';
		document.getElementById('toolbar_'+toolbar).innerHTML = html;
		document.getElementById('toolbar_'+toolbar).style.left = document.body.clientWidth - 72;
	},
	AddButton : function(toolbar, image, event, alt) {
		document.getElementById('toolbar_' + toolbar).innerHTML += '<img src="/design/cms/img/editor/'+image+'.gif" class="button" onClick="'+event+'" onmouseover="Editor.mOver(this);" onmouseout="Editor.mOut(this);" alt="'+alt+'">';
		if (toolbar != 'main') {
			document.getElementById('toolbar_'+toolbar+'_disabled').innerHTML += '<img src="/design/cms/img/editor/'+image+'.gif" class="disabled" alt="">';
		}
	},
	AddDevider : function(toolbar) {
		document.getElementById('toolbar_' + toolbar).innerHTML += '<img src="/design/cms/img/editor/design/devider.gif" class="devider">';
		if (toolbar != 'main') {
			document.getElementById('toolbar_'+toolbar+'_disabled').innerHTML += '<img src="/design/cms/img/editor/design/devider.gif" class="devider">';
		}
	},
	AddElement : function(toolbar, html, html_disabled) {
		document.getElementById('toolbar_' + toolbar).innerHTML += html;
		if (toolbar != 'main') {
			document.getElementById('toolbar_'+toolbar+'_disabled').innerHTML += html_disabled;
		}
	},
	RemoveFormat : function() {
		var regex_header = /<(\/)?H[0-9]>/g
		this.Exec('RemoveFormat');
		var range = frames.EditFrame.document.selection.createRange();
		if (frames.EditFrame.document.selection.type.toLowerCase() != 'text') return;
		var text = range.htmlText.replace(regex_header, '<$1P>');
		range.pasteHTML(text);
	},
	// Назначение стиля выделенному фрагменту текста
	SetStyle : function (style_name) {
		var data = style_name.split('.');
		var tName = '';
		
		if (data == '') {
			
			var rg = frames.EditFrame.document.selection.createRange();
			var node = rg.parentElement();
			
			if (!node) return;
			while(node && node.tagName.toUpperCase() != "BODY"){
				tName = node.tagName.toUpperCase();
				if (tName == 'SPAN' || tName == 'DIV' || tName == 'P') {
					node.removeNode(false);
					break;
				}
				node = node.parentElement;
			}
			return;
		}
		var rg = frames.EditFrame.document.selection.createRange();
		(data[1]) ?
			rg.pasteHTML('<'+data[0]+' class="'+data[1]+'">'+rg.htmlText+'</'+data[0]+'>'):
			rg.pasteHTML('<'+data[0]+'>'+rg.htmlText+'</'+data[0]+'>');
	},
	// Перемещение панелей управления
	MovePopup : function () {
		if (window.dragObj) {
			window.dragObj.style.left = event.x - window.dx; 
			window.dragObj.style.top = event.y - window.dy;
		}
	},
	Drag : function (obj) {
		obj.ondrag = function drg() { return false;}
		window.dx = event.x - obj.offsetLeft; 
		window.dy = event.y - obj.offsetTop; 
		window.dragObj=obj;
	},
	// Работа с массивами
	Search : function (arr, value) {
		for(i = 0; i < arr.length; i++) {
			if (arr[i].value == value) return i;
		}
		return 0;
	},
	// Работа с таблицами
	InsertCell : function (loc) {
		if (this.current['TD'] == null) return;
		var neTD = document.createElement("TD");
		this.current['TD'].insertAdjacentElement(loc, neTD);
	},
	DeleteRow: function (){
		if (this.current['TR'] == null) return;
		this.current['TR'].removeNode(true);
		if (!this.current['TABLE'].getElementsByTagName("TR").length) this.current['TABLE'].removeNode(true);
		this.ClearStatCache();
	},
	InsertRow : function (loc){
		if (this.current['TR'] == null) return;
		newTr = this.current['TR'].cloneNode(true);
		this.current['TR'].insertAdjacentElement(loc, newTr);
		this.ClearStatCache();
	},
	MergeCell : function (dir){
		if (dir == 'left') this.current['TD'] = this.current['TD'].previousSibling;
		if (this.current['TD'].nextSibling == null) return;
		this.current['TD'].innerHTML += this.current['TD'].nextSibling.innerHTML;
		this.current['TD'].colSpan += this.current['TD'].nextSibling.colSpan;
		this.current['TD'].nextSibling.removeNode(true);
		this.ClearStatCache();
	},
	MergeVert : function (){
		var pos, currentCol, ptrTD, i;
		var tr = this.current['TD'].parentElement.nextSibling;
		for(i = 1; i < this.current['TD'].rowSpan; ++i){
			tr = tr.nextSibling;
		}
		if (tr == null) return;
		currentCol = 0;
		for (pos = 0, ptrTD = this.current['TD']; ptrTD; pos += ptrTD.colSpan, ptrTD = ptrTD.previousSibling);
		var tds = tr.getElementsByTagName("TD");
		for (var i = 0; i < tds.length; ++i){
			currentCol += tds(i).colSpan;
			if (currentCol == pos){
				this.current['TD'].rowSpan = this.current['TD'].rowSpan + tds(i).rowSpan;
				tds(i).removeNode(true);
				break;
			}
		}
		this.ClearStatCache();
	},
	CellDelete : function () {
		this.current['TD'].parentElement.removeChild(this.current['TD']);
		this.ClearStatCache();
	},
	InsertCol : function (loc){
		var pos;
		if (this.current['TD'] == null) return;
		if (loc == 'left') this.current['TD'] = this.current['TD'].previousSibling;
		for (pos = 1; this.current['TD']; pos+=this.current['TD'].colSpan, this.current['TD'] = this.current['TD'].previousSibling);
		var trs = this.current['TABLE'].getElementsByTagName("TR");
		for (var i = 0; i < trs.length; ++i){
			var tds = trs(i).getElementsByTagName("TD");
			var currentCol = 0;
			for (var j = 0; j < tds.length; ++j){
				currentCol += tds(j).colSpan;
				if ((currentCol+1) >= pos){
					if (tds(j).colSpan > 1 && (currentCol + 1) != pos) {
						tds(j).colSpan += 1;
					} else {
						var neTD = document.createElement("TD");
						if (pos == 1) {
							tds(j).insertAdjacentElement('beforeBegin', neTD);
						} else {
							tds(j).insertAdjacentElement('afterEnd', neTD);
						}
					}
					break;
				}
			}
		}
		this.ClearStatCache()
	},
	DeleteCol : function (){
		var pos;
		if (this.current['TD'] == null) return;
		this.current['TD'] = this.current['TD'].previousSibling;
		for (pos = 1; this.current['TD']; pos+=this.current['TD'].colSpan, this.current['TD'] = this.current['TD'].previousSibling);
		var parent = this.current['TABLE'].getElementsByTagName("TR")[0];
		for (var tr = parent; tr && tr.tagName.toUpperCase() == "TR"; tr = tr.nextSibling){
			var currentCol = 0;
			var child = tr.getElementsByTagName("TD")[0];
			for (;child && child.tagName.toUpperCase() == "TD"; child = child.nextSibling){
				currentCol += child.colSpan;
				if (currentCol >= pos){
					if (child.colSpan > 1) child.colSpan -= 1;
					else child.removeNode(true);
					break;
				}
			}
		}
		for (;parent && parent.tagName.toUpperCase() == "TR"; parent = parent.nextSibling){
			var tds = parent.getElementsByTagName("TD");
			if (tds.length == 0){
				this.DeleteRow(parent, this.current['TABLE']);
				--i;
			}
		}
		this.ClearStatCache()
	},
	InsertLayer : function () {
		if (frames.EditFrame.document.selection.type.toLowerCase() != 'text') {
			alert('Выберите текст, который необходимо поместить в слой.');
			return;
		}
		var range = frames.EditFrame.document.selection.createRange();
		range.pasteHTML('<DIV style="background-color:silver;border:1px solid gray;height:100px;width:100px;padding:10px margin: 10px;float:none;">'+range.htmlText+'</DIV>');
	},
	ClearStatCache : function () {
		this.current = [];
		this.ActivateToolbar('table', false);
	},
	ActivateToolbar : function (toolbar, activate) {
		if (activate) {
			document.getElementById(toolbar+'_toolbar_off').style.visibility = "hidden";
			document.getElementById(toolbar+'_toolbar_on').style.visibility = "visible";
		} else {
			document.getElementById(toolbar+'_toolbar_off').style.visibility = "visible";
			document.getElementById(toolbar+'_toolbar_on').style.visibility = "hidden";
		}
	},
	AddContext : function (tagName, image, event, title) {
		// Добавлеят элемент к контекстному меню
		var i = 0;
		if (this.contextElements[tagName]) i = this.contextElements[tagName].length;
		else this.contextElements[tagName] = [];
		
		this.contextElements[tagName][i] = [image, event, title];
	},
	ContextMenu : function (e) {
		var show_menu = 'P';
		
		if(e.ctrlKey) {
			return true;
		}
		
		this.ContextMenuClose();
		this.GetParentNodes(e);
		
		if (frames.EditFrame.document.selection.type.toUpperCase() != 'TEXT') {
			for (var i = 0; i < this.parentNodes.length; ++i) {
				var tName = this.parentNodes[i].tagName.toUpperCase();
				if (tName == 'INPUT') {
					tName = this.parentNodes[i].getAttribute('type').toUpperCase();
				} else if (tName == 'P') {
					continue;
				} else if (tName == 'A' && (i != 0 || this.parentNodes[i - 1].tagName.toUpperCase() == 'IMG')) {
					continue;
				}
				
				if (this.contextElements[tName]) {
					show_menu = tName;
					break;
				}
			}
		}
		
		var context_menu = document.getElementById('context_menu');
		
		context_menu.innerHTML = '';
		for (var i=0;i<this.contextElements[show_menu].length;i++) {
			context_menu.innerHTML += '<div class="context_menu_item" onclick="'+this.contextElements[show_menu][i][1]+'"><img src="/design/cms/img/editor/'+this.contextElements[show_menu][i][0]+'.gif" border="0" align="absmiddle"> '+this.contextElements[show_menu][i][2]+'</div>';
		}
		// Определяем положение меню на странице
		if (document.body.clientWidth - e.clientX - 200 > 0) {
			context_menu.style.posLeft=e.clientX;
		} else {
			context_menu.style.posLeft=e.clientX - 200;
		}
		var menu_height = (context_menu.innerHTML.toUpperCase().split('<DIV').length - 1) * 23;
		if (document.body.clientHeight - e.clientY - 80 - menu_height > 0) {
			context_menu.style.posTop=e.clientY + 60;
		} else {
			context_menu.style.posTop=e.clientY + 60 - menu_height;
		}
		
		// прячем все элементы SELECT которые имеют бесконечный z-index
		var selectEl = frames.EditFrame.document.getElementsByTagName("SELECT");
		var x1 = context_menu.style.posLeft;
		var x2 = x1 + 200;
		var y1 = context_menu.style.posTop - 60;
		var y2 = y1 + menu_height;
		this.hiddenElements = [];
		
		for(var i=0; i < selectEl.length; i++) {
			var xA = selectEl[i].offsetLeft;
			var xB = selectEl[i].offsetLeft + selectEl[i].clientWidth;
			var yA = selectEl[i].offsetTop;
			var yB = selectEl[i].offsetTop + selectEl[i].clientHeight;
			if (
				((x1 < xB && xB < x2) || (x1 < xA && xA < x2) || (x1 > xA && x2 < xB))
				&& ((y1 < yB && yB < y2) || (y1 < yA && yA < y2) || (y1 > yA && y2 < yB))
			) {
				this.hiddenElements[this.hiddenElements.length] = [selectEl[i], selectEl[i].style.width];
				selectEl[i].style.width = 1;
			}
		}
		
		
		
		context_menu.style.display="block";
		context_menu.setCapture();
		
		return false;
	},
	ContextMenuClose : function () {
		var context_menu = document.getElementById('context_menu');
		if (context_menu) {
			context_menu.releaseCapture();
			context_menu.style.display='none';
		}
		// Показываем элементы, которые были спрятаны при выводе меню
		for(var i=0;i<this.hiddenElements.length;i++) {
			this.hiddenElements[i][0].style.width = this.hiddenElements[i][1];
		}
		this.hiddenElements = [];
	},
	ContextMenuClick : function (e) {
		this.ContextMenuClose();
		e.srcElement.click();
	},
	ContextMenuSwitch : function (e) {
		var obj = e.srcElement;
		if (obj.className=="context_menu_item") {
			obj.className="context_menu_hlight";
		} else if (obj.className=="context_menu_hlight") {
			obj.className="context_menu_item";
		}
	},
	GetParentNodes : function (e) {
		if (e.ctrlKey && e.keyCode == 83) {
			this.Save();
			return;
		}
		
		this.ClearStatCache();
		
		var status = '';
		var selType = frames.EditFrame.document.selection.type;
		var rg = frames.EditFrame.document.selection.createRange();
		var node = (selType == "Control")? rg(0) : rg.parentElement();
		
		var font_size_flag = true;
		var font_face_flag = true;
		var style_flag = true;
		
		var path = '';
		
		font_family.options.selectedIndex = 0;
		font_ssize.options.selectedIndex = 0;
		sstyle.options.selectedIndex = 0;
		
		this.parentNodes = [];
		if (!node) return;
		while(node && node.tagName.toUpperCase() != "BODY") {
			this.parentNodes.push(node);
			node = node.parentElement;
		}
		for (var i = 0; i < this.parentNodes.length; ++i) {
			var tName = this.parentNodes[i].tagName.toUpperCase();
			var path_desc = '';
			
			if (tName == 'INPUT') {
				var inputType = this.parentNodes[i].getAttribute('type').toUpperCase();
				this.current[inputType] = this.parentNodes[i];
			} else if (!this.current[tName]) {
				this.current[tName] = this.parentNodes[i];
			}
			
			switch (tName){
				case 'TABLE':
					this.ActivateToolbar('table', true);
				break;
				case 'IMG':
					path_desc = this.parentNodes[i].getAttribute("src");
				break;
				case 'A':
					path_desc = this.parentNodes[i].getAttribute("href");
				break;
				case 'FONT':
					// Определяем размер шрифта
					if (font_size_flag && this.parentNodes[i].getAttribute("size") != '') {
						font_ssize.options.selectedIndex = this.Search(font_ssize.options, this.parentNodes[i].getAttribute("size"));
						path_desc = 'Размер: '+ this.parentNodes[i].getAttribute("size")+'; ';
						font_size_flag = false;
					}
					
					// Определяет название шрифта
					if (font_face_flag && this.parentNodes[i].getAttribute("face") != '') {
						font_family.options.selectedIndex = this.Search(font_family.options, this.parentNodes[i].getAttribute("face"));
						path_desc += 'Шрифт: ' + this.parentNodes[i].getAttribute("face")+'; ';
						font_face_flag = false;
					}
				break;
				case 'DIV':case 'SPAN':case 'H1':case 'H2':case 'H3':case 'H4':case 'H5':case 'H6':
					// Определяем имя таблицы стилей
					if (style_flag && this.parentNodes[i].className != '') {
						sstyle.options.selectedIndex = this.Search(sstyle.options, tName+'.' + this.parentNodes[i].className);
					} else {
						sstyle.options.selectedIndex = this.Search(sstyle.options, tName);
					}
					style_flag = false;
					path_desc = 'Стиль: ' + tName+'.' + this.parentNodes[i].className+'; ';
				break;
			}
			path = (tName == 'TABLE' || tName == 'THEAD' || tName == 'TBODY' || tName == 'TFOOT' || tName == 'TH' || tName == 'TR' || tName == 'TD' ) ? 
				' &raquo; ' + tName + ' ' + path:
				' &raquo; <a style="color:blue;text-decoration:underline;cursor:hand;" onClick="Editor.EditElement(' + i + ', \'' + path_desc + '\');">' + tName + '</a>' + path;		}
		this.StatusBar('BODY' + path);
	},
	EditElement : function (i, description) {
		this.parentNodes[i].removeNode(false);
		this.StatusBar('Объект &laquo;' + this.parentNodes[i].tagName.toUpperCase() + '&raquo; удалён');
	},
	ShowHide : function () {
		if (!frames.EditFrame.document.body.BehaviorStyleSheet) {
			frames.EditFrame.document.body.BehaviorStyleSheet = frames.EditFrame.document.createStyleSheet();
		}
		this.sSheet = frames.EditFrame.document.body.BehaviorStyleSheet
		for (var i=0; i<this.shTag.length;i++) {
			if (this.showHidden) {
				this.sSheet.removeRule(this.shTag[i], this.shAttrib[i]);
			} else {
				this.sSheet.addRule(this.shTag[i], this.shAttrib[i]); 
			}
		}
		
		if (this.showHidden) {
			this.StatusBar("отображение скрытых элементов <B>выключено</B>");
			frames.EditFrame.document.body.innerHTML = this.RemoveSpecial();
		} else {
			this.StatusBar("отображение скрытых элементов <B>включено</B>");
			var regexp = /(<a[^>]+name=[^>]+>)(.*<\/a>)/ig
			frames.EditFrame.document.body.innerHTML = frames.EditFrame.document.body.innerHTML.replace(regexp, '$1<img src="/design/cms/img/editor/inner_html/anchor.gif" width="12" height="14" border="0" alt="show_hide">$2');
		}
		this.showHidden = !this.showHidden;
	},
	// Убирает показанные спецсимволы, в отдельную функцию вынесено из-за того, что используется два раза
	RemoveSpecial : function () {
		var regexp = /<img[^>]+alt="?show_hide"?[^>]*>/ig
		return frames.EditFrame.document.body.innerHTML.replace(regexp, '');
	},
	StatusBar : function (text) {
		if (text == '') {
			if (this.tooltipIndex >= this.tooltips.length) this.tooltipIndex = 0;
			document.all.status_bar.innerHTML = this.tooltips[this.tooltipIndex++];
		} else {
			document.all.status_bar.innerHTML = text;
		}
	},
	Exec : function (command, option) {
		if (this.mode == 'Edit') {
			frames.EditFrame.focus();
			frames.EditFrame.document.execCommand(command, false, option);
		} else {
			frames.SrcFrame.focus();
			frames.SrcFrame.document.execCommand(command);
		}
	},
	HiglihtSource : function (html) {
		var html = frames.SrcFrame.document.body.innerHTML
		
		html = html.replace(/@/gi,"_AT_");
		html = html.replace(/#/gi,"_HASH_");
		
	    var htmltag = /(&lt;[\w\/]+[ ]*[\w\=\"\'\.\/\;\: \)\(-]*&gt;)/gi;
	    html = html.replace(htmltag,"<span class=html_tag>$1</span>");
	
	    var imgtag = /<span class=html_tag>(&lt;(IMG|\/?STYLE)[ ]*[\w\=\"\'\.\/\;\: \)\(-]*&gt;)<\/span>/gi;
	    html = html.replace(imgtag,"<span class=html_img>$1</span>");
	    
	    var formtag = /<span class=html_tag>(&lt;[\/]*(form|input){1}[ ]*[\w\=\"\'\.\/\;\: \)\(-]*&gt;)<\/span>/gi;
	    html = html.replace(formtag,"<br><span class=html_form>$1</span>");
	
	    var tabletag = /<span class=html_tag>(&lt;[\/]*(table|tbody|th|tr|td){1}([ ]*[\w\=\"\'\.\/\;\:\)\(-]*){0,}&gt;)<\/span>/gi;
	    html = html.replace(tabletag,"<span class=html_table>$1</span>");
	
	    var Atag = /<span class=html_tag>(&lt;\/a&gt;){1}<\/span>/gi;
	    html = html.replace(Atag,"<span class=html_A>$1</span>");
	
	    var Atag = /<span class=html_tag>(&lt;a [\W _\w\=\"\'\.\/\;\:\)\(-]+&gt;){1,}<\/span>/gi;
	    html = html.replace(Atag,"<span class=html_A>$1</span>");
	
	    var parameter = /=("[ \w\'\.\/\;\:\)\(-]+"|'[ \w\"\.\/\;\:\)\(-]+')/gi;
	    html = html.replace(parameter,"=<span class=html_paramvalue>$1</span>");
	
	    var entity = /&amp;([\w]+);/gi;
	    html = html.replace(entity,"<span class=html_entity>&amp;$1;</span>");
	
	    var comment = /(&lt;\!--[\W _\w\=\"\'\.\/\;\:\)\(-]*--&gt;)/gi;
	    html = html.replace(comment,"<br><span class=html_comment>$1</span>");
	
	    html = html.replace(/_AT_/gi,"@");
	    html = html.replace(/_HASH_/gi,"#");
	
	    return html;    
	},
	Save : function () {
		window.clearTimeout(this.auth_timer);
		this.auth_timer = window.setTimeout("alert('Сохраните файл! \\nВремя максимального бездействия \\nсистемы - заканчивается через 5 минут!')", this.auth_timeout);
		
		if (this.mode == 'Edit') {
			var content = this.RemoveSpecial();
		} else if (this.mode == 'Src') {
			// Bug fix - обычное сохранение приводит к сохранению заголовков, поэтому перед
			// тем как сохранить переключаемся в режим редактирования
			this.SwitchMode('Edit');
			var content = this.RemoveSpecial();
		} else if (this.mode == 'View') {
			var content = frames.ViewFrame.document.body.innerHTML;
		}
		
		// Сохраняем переключатели
		var html_tidy = document.getElementById('html_tidy').checked;
		if (html_tidy == true) {
			setCookie('html_tidy', 1, 365);
		} else {
			setCookie('html_tidy', 0, 365);
		}
		var html_auto_charset = document.getElementById('html_auto_charset').checked
		if (html_auto_charset == true) {
			setCookie('html_auto_charset', 1, 365);
		} else {
			setCookie('html_auto_charset', 0, 365);
		}

		this.StatusBar('Подождите, идёт сохранение страницы ...');
		var req = new JsHttpRequest();
		req.onreadystatechange = function() {
			if (req.readyState == 4) {
				Editor.StatusBar('Страница успешно сохранена.');
				if (req.responseText.length > 0) {
					var errWin = CenterWindow('', 'error_window', 500, 500, 1, 1);
					errWin.document.open();
					errWin.document.write(req.responseText);
					errWin.document.close();
					Editor.StatusBar('<font color="Red">Документ не сохранен!!!</font>');
				} else if (req.responseJS.source_update) {
					Editor.SwitchMode('Edit');
					frames.EditFrame.document.location.reload();
					Editor.StatusBar('<font color="Green">Картинки с сайтов успешно скачаны. Документ - сохранён.</font>');
				} else {
					Editor.StatusBar('<font color="Green">Сохранено. Текст: '+req.responseJS.content_size+' Кб, картинки: '+req.responseJS.thumb_size+' Кб, файлы: '+req.responseJS.attach_size+' Кб, кодировка: '+req.responseJS.encoding+'</font>');
				}
			}
		}
		req.caching = false;
		req.open('POST', '/actions_admin.php', true);
		req.send({ 
			'_event':this.admin_event,
			'id':this.id,
			'table_name':this.table_name,
			'field_name':this.field_name,
			'content':this.RemoveSpecial(),
			'_language':this._language,
			'html_tidy': html_tidy,
			'html_auto_charset' : html_auto_charset
		});
	},
	SwitchMode : function (mode) {
		
		if (this.mode == mode) return;
		
		// Прячем все окна с контентом
		SrcDiv.style.display = "none"
		ViewDiv.style.display = "none"
		EditDiv.style.display = "none"
		
		// Прячем все тулбарины
		toolbar_standart.style.visibility = "hidden"
		toolbar_format.style.visibility = "hidden"
		toolbar_standart_disabled.style.visibility = "hidden"
		toolbar_format_disabled.style.visibility = "hidden"
		
		// Обновляем контент
		if (this.mode == 'Src') {
			frames.EditFrame.document.body.innerHTML = frames.SrcFrame.document.body.innerText;
			frames.ViewFrame.document.body.innerHTML = frames.SrcFrame.document.body.innerText
		} else if (this.mode == 'Edit') {
			frames.ViewFrame.document.body.innerHTML = "<" + frames.EditFrame.document.documentElement.tagName + ">\n" + frames.EditFrame.document.documentElement.innerHTML + "\n</" + frames.EditFrame.document.documentElement.tagName + ">";
			frames.SrcFrame.document.body.innerText = "<" + frames.EditFrame.document.documentElement.tagName + ">\n" + frames.EditFrame.document.documentElement.innerHTML + "\n</" + frames.EditFrame.document.documentElement.tagName + ">";
			frames.SrcFrame.document.body.innerHTML = this.HiglihtSource();
		}
		
		// Отображаем элементы, которые должны быть видны в режиме
		if (mode == 'Edit') {
			EditDiv.style.display = "block"
			toolbar_standart.style.visibility = "visible"
			toolbar_format.style.visibility = "visible"
			document.getElementById('sstyle').disabled = false;
			document.getElementById('font_family').disabled = false;
			document.getElementById('font_ssize').disabled = false;
			frames.EditFrame.focus()
		} else if (mode == 'Src') {
			SrcDiv.style.display = "block"
			toolbar_standart_disabled.style.visibility = "visible"
			toolbar_format_disabled.style.visibility = "visible"
			document.getElementById('sstyle').disabled = true;
			document.getElementById('font_family').disabled = true;
			document.getElementById('font_ssize').disabled = true;
			frames.SrcFrame.focus()
		} else if (mode = 'View') {
			toolbar_standart_disabled.style.visibility = "visible"
			toolbar_format_disabled.style.visibility = "visible"
			ViewDiv.style.display = "block"
			document.getElementById('sstyle').disabled = true;
			document.getElementById('font_family').disabled = true;
			document.getElementById('font_ssize').disabled = true;
			frames.ViewFrame.focus()
		}
		this.mode = mode;
	},
	PrintPage : function () {
		if (this.mode == 'Edit') {
			frames.EditFrame.document.execCommand("Print")
		} else if (this.mode == 'Src') {
			frames.SrcFrame.document.execCommand("Print")
		} else if (this.mode == 'View') {
			frames.ViewFrame.document.execCommand("Print")
		}
	},
	ieSpellCheck : function () {
		try {
			var tspell = new ActiveXObject('ieSpell.ieSpellExtension');
			tspell.CheckAllLinkedDocuments(frames.EditFrame.document);
		} catch (err){
			if (window.confirm('Для проверки орфографии вам необходим модуль ieSpell.\nПроверять он будет только Английский текст. \nЕго прийдется качать с интернета. \nПриблизительный объем - около 2.5 Мб \nВы все еще хотите его установить?')) {
				window.open('http://www.iespell.com/download.php');
			};
		};
	},
	// Вызывает соответствующий файл для вставки картинки
	InsImage : function () {
		var trimRegexp = /(^[\s\n\r\t]+)|([\s\n\r\t]+$)/g
		var range = frames.EditFrame.document.selection.createRange();
		var type = frames.EditFrame.document.selection.type.toLowerCase();
		if (type == 'control' && range(0).tagName.toUpperCase() == "IMG") {
//			showDialog('/tools/editor/image/edit.php', '', 400, 200);
			CenterWindow('/tools/editor/image/edit.php', '', 400, 230,0,0);
		} else if (type == 'text' && range.htmlText.replace(trimRegexp, '') != '') {
			CenterWindow('/tools/editor/image/link.php?id='+this.id+'&table_name='+this.table_name+'&field_name='+this.field_name,'',550,220,0,0);
		} else {
			CenterWindow('/tools/editor/image/insert.php?id='+this.id+'&table_name='+this.table_name+'&field_name='+this.field_name,'',550,450,0,0);
		}
	},
	// Проверяет на наличие выделения текста на странице
	TextSelected : function () {
		if (frames.EditFrame.document.selection.type.toLowerCase() == 'text') {
			return true;
		} else {
			alert("Выделите текст");
			return false;
		}
	},
	// Производит разблокировку страницы
	VerifyClose : function () {
		if (event.clientY < 0 && event.clientY > -20) {
			return 'Закрывать окно можно только нажав на кнопку Выход.';
		}
	},
	mOver : function (obj) {
		obj.style.background = "EAF3FB";
	},
	mOut : function (obj) {
		obj.style.background = "D2E6FA";
	}
});


window.onbeforeunload = Editor.VerifyClose;