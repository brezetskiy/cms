(Dialog = {
	// объект, который редактируется
	editObject : {},
	inputObj : [],
	imgObj : [],
	selectObj : [],
	
	Init : function(obj, msg) {
//		resizeDialog();
		if (!obj) {
			alert(msg);
			window.close();
		}
		this.editObject = obj;
		
		// Определяем элементы которые есть на странице
		var type = '';
		var id = 0;
		var i = 0;
		
		// Элементы типа input
		var elements = document.getElementsByTagName('INPUT');
		for (i=0;i<elements.length;i++) {
			type = elements[i].getAttribute('type');
			id = elements[i].getAttribute('id');
			if(type == 'submit' || type == 'reset' || type == 'button' || !id) {
				continue;
			}
			this.inputObj[this.inputObj.length] = elements[i];
			this.Load(elements[i].getAttribute('id'));
		}
		
		// Элементы select
		var elements = document.getElementsByTagName('SELECT');
		for (i=0;i<elements.length;i++) {
			id = elements[i].getAttribute('id');
			if (!id) continue;
			this.selectObj[this.selectObj.length] = elements[i];
			this.Load(elements[i].getAttribute('id'));
		}
		
		// Элементы, представленные в виде картинки
		var elements = document.getElementsByTagName('IMG');
		for (i=0;i<elements.length;i++) {
			id = elements[i].getAttribute('id');
			if (!id) continue;
			elements[i].style.cursor='hand';
			this.imgObj[this.imgObj.length] = elements[i];
			this.Load(elements[i].getAttribute('id'));
		}
	},
	SaveAll : function () {
		for (i=0;i<this.inputObj.length;i++) {
			this.Save(this.inputObj[i].getAttribute('id'));
		}
		for (i=0;i<this.selectObj.length;i++) {
			this.Save(this.selectObj[i].getAttribute('id'));
		}
	},
	Load : function(name) {
		name = name.toLowerCase();
		var obj = document.getElementById(name);
		if (!obj) {
			alert('Невозможно найти объект с id='+name);
			return false;
		}
		
		// считываем значение аттрибута у редактируемого объекта
		if (obj.tagName.toUpperCase() == 'IMG' && name.substring(0, 6) == 'style.') {
			var val = this.editObject.style.getAttribute( name.substring(name.indexOf('.') + 1, name.lastIndexOf('.')) );
		} else if (obj.tagName.toUpperCase() == 'IMG') {
			var val = this.editObject.getAttribute( name.substring(0, name.lastIndexOf('.')) );
		} else if (name.substring(0, 6) == 'style.') {
			var val = this.editObject.style.getAttribute( name.substring(6, name.length) );
		} else {
			var val = this.editObject.getAttribute(name);
		}
		
//		alert(name + ' ' + val);
		
		if (obj.tagName.toUpperCase() == 'SELECT') {
			selectOption(name, val);
		} else if (obj.getAttribute('type') == 'checkbox' && val == true) {
			obj.checked = true;
		} else if (obj.tagName.toUpperCase() == 'IMG' && name.substring(name.lastIndexOf('.') + 1) != val) {
			obj.style.border = '1px solid silver';
		} else if (obj.tagName.toUpperCase() == 'IMG' && name.substring(name.lastIndexOf('.') + 1) == val) {
			obj.style.border = '1px solid red';
		} else {
			obj.value = val;
		}
	},
	Save : function(name) {
		var val = '';
		var obj = document.getElementById(name);
		
		if (!obj) {
			alert('Невозможно найти объект с id='+name);
			return false;
		}
		if (obj.tagName.toUpperCase() == 'SELECT') {
			val = obj.options.value
		} else if (obj.getAttribute('type') == 'checkbox') {
			val = obj.checked;
		} else {
			val = obj.value;
		}
		
		if (val.lenght == 0 || val == 'default') {
			if (name.substring(0,6) == 'style.') {
				this.editObject.style.removeAttribute(name.substring(6).toUpperCase(), 0);
			} else {
				this.editObject.removeAttribute(name, 0);
			}
		} else {
			if (name.substring(0,6) == 'style.') {
				this.editObject.style.setAttribute(name.substring(6).toUpperCase(), val, 0);
			} else {
				this.editObject.setAttribute(name, val, 0);
			}
		}
	},
	Img : function (obj) {
		var name = obj.getAttribute('id');
		var attr = name.substring(0, name.lastIndexOf('.') + 1);
		var value = name.substring(name.lastIndexOf('.')  + 1);
		var hidden = name.substring(0, name.lastIndexOf('.'));
		
		document.getElementById(hidden).value = value;
		
		// Подсвечиваем выбранный элемент
		for(var i=0;i<this.imgObj.length;i++)  {
			if (this.imgObj[i].getAttribute('id').substring(0, attr.length) != attr) {
				// Данная картинка не относится к тем, которые есть в группе
				continue;
			}
			if (this.imgObj[i].getAttribute('id').substring(attr.length) == value) {
				this.imgObj[i].style.border = '1px solid red';
			} else {
				this.imgObj[i].style.border = '1px solid silver';
			}
		}
	}
})