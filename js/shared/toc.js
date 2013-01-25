// helper functions
function strtrim(value)
{
  return value.replace(/^\s+/,'').replace(/\s+$/,'');
}

var selection = null;

function expand(node, a) {
	var good = false;
	ul = document.getElementById("ul" + node);
	img = document.getElementById("img" + node);
	if (good = (ul != null && img != null && ul.className != null && ul.innerHTML != null)) {
		str = img.src;
		if ((ul.className == "Shown") || (ul.className == "")) {
			ul.className = "Hidden";
			img.src = str.substr(0, str.length-5) + "c.gif";
			if (a != null) a.href = "javascript:;";
		} else {
			if (ul.className == "Hidden") {
				img.src = str.substr(0, str.length-5) + "o.gif";
				ul.className = "Shown";
			}
		}
		
		if (strtrim(ul.innerHTML) == "") {
			ul.innerHTML = "<DIV onclick='cancelLoad(" + node + ");' class='loadMsg'>&nbsp;Загрузка,&nbsp;пожалуйста&nbsp;подождите...&nbsp;</DIV>";
			ul.className = "";
		}

		if ((a != null) && ("object" == typeof(a)) && (window.length > 0)) {
			a.target = 'fra' + node;
		}
	}
	return true;
}

function clearSelection(node)
{
	if (node != null && node.className != null)
		node.className = "";
}

function setSelection(node)
{
	if (node != null && node.className != null)
		node.className = "Current";
}

function setSel(node)
{
	if (selection != null) {
		if (selection.length != null) {
			// array
			for (var i = 0; i < selection.length; i++)
				clearSelection(selection[i]);
		} else {
			// scalar value
			clearSelection(selection);
		}
	}
	selection = node;
	
	if (node) {
		var textNode = node;
		if (typeof(node[0]) != "undefined") {
			// array
			for (var i = 0; i < node.length; i++)
				setSelection(node[i]);
			// to retrieve text use first element
			textNode = node[0];
		} else {
		// scalar value
			setSelection(node);
		}
//		if	(textNode != null && "object" == typeof(window.parent))
//			if (window.parent.document.title != null)
//				window.parent.document.title = textNode.innerHTML + " - RSDN";
	}
	return;
}

function cancelLoad(id) {
	fra = document.getElementById("fra" + id);
	if (fra != null)
		fra.src = "about:blank";
	expand(id);
}

function loadNode(id, node, selID) {
	ul = document.getElementById("ul"+id);
	
	if (ul != null && "object" == typeof(ul) && node && "object" == typeof(node)) {
		ul.className = "Hidden";
		ul.innerHTML = node.innerHTML;
		expand(id);
	}
	
	if (selID) {
		a = document.getElementById("a" + selID);
		setSel(a);
	} else {
		a = document.getElementById("a" + id);
		if (selection == null) {
			setSel(a);
		} else {
			if (selection.innerHTML == null || selection.innerHTML == '') {
				setSel(a);
			}
		}
	}
	return;
}

function initSel(id)
{
	var select;
	var focus = null;
  // array
	if (typeof(id[0]) != "undefined")
	{
		select = new Array(id.length);
		for (var i = id.length - 1; i >= 0 ; i--)
		{
			select[i] = document.getElementById("a" + id[i]);
			if ("object" == typeof(select[i]))
				focus = select[i];
		}
	}
	else
	{
		select = document.getElementById("a" + id);
		if ("object" == typeof(select))
			focus = select;
	}

	setSel(select);
		
	if (focus != null)
	{
		if (focus.offsetTop != null && document.body.clientHeight != null)
			window.scrollTo(0, focus.offsetTop - (document.body.clientHeight / 2));
	}
	return;
}