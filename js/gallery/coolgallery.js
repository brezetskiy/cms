function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
	}
	return {left: curleft, top: curtop};
}

function getDimensions(element) {
    var display = element.style.display;
    if (display != 'none' && display != null) // Safari bug
      return {width: element.offsetWidth, height: element.offsetHeight};

    // All *Width and *Height properties give 0 on elements with display none,
    // so enable the element temporarily
    var els = element.style;
    var originalVisibility = els.visibility;
    var originalPosition = els.position;
    var originalDisplay = els.display;
    els.visibility = 'hidden';
    els.position = 'absolute';
    els.display = 'block';
    var originalWidth = element.clientWidth;
    var originalHeight = element.clientHeight;
    els.display = originalDisplay;
    els.position = originalPosition;
    els.visibility = originalVisibility;
    return {width: originalWidth, height: originalHeight};
}

function getDocumentDimensions(windowFrame) {
	windowFrame = windowFrame || window;
	//dimensions of document (excludes scrollbars)	
	if(windowFrame.document.documentElement && !isNaN(windowFrame.document.documentElement.clientWidth)){
		width = windowFrame.document.documentElement.clientWidth;		
		height = windowFrame.document.documentElement.clientHeight;	
	} else {
		//IE quirks mode
		width = windowFrame.document.body.clientWidth;		
		height = windowFrame.document.body.clientHeight;	
	}
	return {width: width, height: height};	
}

function getMousePosition(e) {
	var posx = 0;
	var posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) 	{
		posx = e.pageX;
		posy = e.pageY;
	}
	else if (e.clientX || e.clientY) 	{
		posx = e.clientX + document.body.scrollLeft
			+ document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop
			+ document.documentElement.scrollTop;
	}
	return {x: posx, y: posy};
}

function clonePositionAndSize(from, to, offset) {
	var pos = findPos(from);
	var size = getDimensions(from);
	to.style.top = (parseInt(pos.top)+offset)+'px';
	to.style.left = pos.left+'px';
	to.style.width = size.width+'px';
	to.style.height = (parseInt(size.height)-offset)+'px';
}

function rebuildImagePositions() {
	var pos;
	var size;
	var new_positions = new Array();
	var c = byId('gallery_container').childNodes
	for (var i = 0; i<c.length; i++) {
		e = c[i];
		if (e.className == 'gallery_image_layer') {
			pos = findPos(e);
			size = getDimensions(e);
			if (new_positions[pos.top]==undefined) {
				new_positions[pos.top] = new Array();
			}
			new_positions[pos.top].push({left: pos.left, top: pos.top, width: size.width, height: size.height, id: e.id});
		}
	}
	image_positions = new_positions;
}

/* ’ранит слой, над которым последний раз перемещалс€ курсор. “аскатьс€ будет именно его клон */
var gallery_image_layer = null;
/* —лой, который таскаетс€ по странице */
var drag_div = null;
/* —тартовые позиции сло€ при начале перетаскивани€ */
var drag_start_offset_x = 0;
var drag_start_offset_y = 0;
/* “екущие позиции картинок на странице. »спользуетс€ дл€ определени€ места вставки при перетаскивании */
var image_positions = new Array();
/* Ёлемент, на котором опустили картинку и позици€ (before/after) */
var drop_on_element = null;
var drop_position = null;

function galleryLayerOver(gallery_layer, width, height, description) {
	var div = byId('gallery_hover_layer');
	clonePositionAndSize(gallery_layer, div, 27);
	div.style.display = 'block';
	gallery_image_layer = gallery_layer;
}

function updateDragHandlerSize() {
	var docsize = getDocumentDimensions();
	byId('gallery_drag_handler').style.width = docsize.width+'px';
	byId('gallery_drag_handler').style.height = docsize.height+'px';
}

function galleryLayerOut() {
	byId('gallery_hover_layer').style.display = 'none';
}

function galleryDragStart(e) {
	updateDragHandlerSize();
	byId('gallery_drag_handler').style.display = 'block';
	
	drag_div = byId('gallery_drag_layer');
	clonePositionAndSize(byId('gallery_hover_layer'), drag_div, -27);
	
	var pos = findPos(gallery_image_layer);
	var mouse = getMousePosition(e);
	drag_start_offset_x = mouse.x - pos.left;
	drag_start_offset_y = mouse.y - pos.top;

	drag_div.innerHTML = gallery_image_layer.innerHTML
	var ch = (navigator.org=='microsoft'?0:1);
	drag_div.childNodes[ch].style.visibility = 'hidden';
	drag_div.style.display = 'block';
	
	rebuildImagePositions();
	return false;
}

function galleryDragStop() {
	drag_div.style.display = 'none';
	byId('gallery_drag_delimiter').style.display = 'none';
	byId('gallery_drag_handler').style.display = 'none';
	
	if (drop_on_element==null) {
		/* drag не удалс€... */
	} else {
		if (drop_position=='after') {
			drop_on_element = drop_on_element.nextSibling;
		}
		byId('gallery_container').insertBefore(gallery_image_layer, drop_on_element);
		
		if (drop_on_element == undefined || gallery_image_layer.id != drop_on_element.id) {
			/**
			 * ¬ этот момент сохран€ем новую сортировку картинок
			 */
			savePriorityList();
		}
	}
	drop_on_element = null;
	return false;
}

function savePriorityList() {
	var priority_list = new Array()
	var index = 0
	var c = byId('gallery_container').childNodes
	for (var i = 0; i<c.length; i++) {
		e = c[i];
		if (e.className == 'gallery_image_layer') {
			priority_list[index] = e.id.substr(3);
			index++;
		}
	}

	AjaxRequest.send('', '/action/admin/gallery/table_sort/', '—охранение...', true, {'_return_path':'void', 'table_id':byId('gallery_table_id').value, 'priority_list':priority_list});
}

function galleryUpdateLayerHeights(new_height) {
//	alert("Update heights to "+new_height);
//	alert("Old:"+max_height+"; New:"+new_height)
	max_height = new_height
	var c = byId('gallery_container').childNodes
	for (var i = 0; i<c.length; i++) {
		e = c[i];
		for (var j=0; j<e.childNodes.length; j++) {
			var subchild = e.childNodes[j]
			if (subchild && subchild.getAttribute && subchild.getAttribute('rel') == 'image_holder') {
				subchild.style.height = new_height+'px'
//				alert(subchild.tagName+' '+subchild.getAttribute('rel'));
			}
//			if (e.getAttribute('rel') == 'gallery_image_layer') {
//				alert(e.childNodes[1].id);
//			}
		}
	}
}

function galleryDragMove(e) {
	var mouse = getMousePosition(e);
	drag_div.style.top = (parseInt(mouse.y)-drag_start_offset_y)+'px'; 
	drag_div.style.left = (parseInt(mouse.x)-drag_start_offset_x)+'px'; 
	var el = getDropAfterElement(e);
	
	var position = 'after';
	if (el.col==-1) {
		el.col = 0;
		position = 'before';
	}
	el = byId(image_positions[el.row][el.col]['id']);

	var pos = findPos(el);
	var size = getDimensions(el);
	var delim = byId('gallery_drag_delimiter');
	delim.style.top = pos.top+'px';
	if (position == 'before') {
		delim.style.left = (parseInt(pos.left)-5)+'px';
	} else {
		delim.style.left = (parseInt(pos.left)+parseInt(size.width)+1)+'px';
	}
	delim.style.height = size.height+'px';
	delim.style.display = 'block';
	drop_on_element = el
	drop_position = position
	return false;
}

function getDropAfterElement(e) {
	var mouse = getMousePosition(e);
	var prev = -1;
	var row = 0;
	/* 1. ќпредел€ем р€д, в который должна быть вставлена картинка */
	for (var i in image_positions) {
		var el = image_positions[i];
		if (mouse.y < i) {
			if (prev != -1) {
				row = prev;
			} else {
				row = i;
			}
			break;
		}
		prev = i;
	}
	if(row==0) row=prev
	
	/* 2. ќпредел€ем картинку в строке, после которой будем вставл€ть таскаемую */
	prev = -1;
	var col = -1;
	for (var i = image_positions[row].length-1; i>=0; i--) {
		var val = image_positions[row][i]
		if (mouse.x > parseInt(val.left)+parseInt(val.width)/2) {
			col = i;
			break;
		} else {
			prev = i
		}
	}
	return {row: row, col: col};
}

function GalleryEditPhotoDescription(id, table_id, language, initial_description) {
	initial_description.replace(new RegExp("&lt;","g"), "<");
	initial_description.replace(new RegExp("&gt;","g"), ">");
	initial_description.replace(new RegExp("&amp;","g"), "&");
	initial_description.replace(new RegExp("&quot;","g"), '"');
	initial_description.replace(new RegExp("&#039;","g"), "'");
	var descr = prompt('¬ведите описание картинки:', initial_description)
	if (descr != null) {
		AjaxRequest.send(null, '/action/admin/gallery/edit_description/', '—охранение...', true, {'_return_path':'void', id: id, table_id: table_id, language: language, description: descr});
	}
}


function show_video(div_id, url) {
	var so = new SWFObject('/extras/longtailvideo/player.swf','ply','470','320','9','#ffffff');
	so.addParam('allowfullscreen','true');
	so.addParam('allowscriptaccess','always');
	so.addParam('wmode','opaque');
	so.addVariable('file', url);
	so.write(div_id + '_video');
	$('#'+div_id).css({'display':'block'});
	centerDiv(div_id);
}