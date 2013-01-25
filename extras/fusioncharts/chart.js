if(typeof infosoftglobal == "undefined") var infosoftglobal = new Object();
if(typeof infosoftglobal.FusionChartsUtil == "undefined") infosoftglobal.FusionChartsUtil = new Object();
infosoftglobal.FusionCharts = function(swf, id, w, h, debugMode, registerWithJS, c, scaleMode, lang){
	if (!document.getElementById) { return; }
	
	this.initialDataSet = false;
	
	this.params = new Object();
	this.variables = new Object();
	this.attributes = new Array();
	
	if(swf) { this.setAttribute('swf', swf); }
	if(id) { this.setAttribute('id', id); }
	if(w) { this.setAttribute('width', w); }
	if(h) { this.setAttribute('height', h); }
	
	if(c) { this.addParam('bgcolor', c); }
	
	this.addParam('quality', 'high');
	
	this.addParam('allowScriptAccess', 'always');
	
	this.addVariable('chartWidth', w);
	this.addVariable('chartHeight', h);

	debugMode = debugMode ? debugMode : 0;
	this.addVariable('debugMode', debugMode);

	this.addVariable('DOMId', id);

	registerWithJS = registerWithJS ? registerWithJS : 0;
	this.addVariable('registerWithJS', registerWithJS);
	
	scaleMode = scaleMode ? scaleMode : 'noScale';
	this.addVariable('scaleMode', scaleMode);

	lang = lang ? lang : 'EN';
	this.addVariable('lang', lang);
}

infosoftglobal.FusionCharts.prototype = {
	setAttribute: function(name, value){
		this.attributes[name] = value;
	},
	getAttribute: function(name){
		return this.attributes[name];
	},
	addParam: function(name, value){
		this.params[name] = value;
	},
	getParams: function(){
		return this.params;
	},
	addVariable: function(name, value){
		this.variables[name] = value;
	},
	getVariable: function(name){
		return this.variables[name];
	},
	getVariables: function(){
		return this.variables;
	},
	getVariablePairs: function(){
		var variablePairs = new Array();
		var key;
		var variables = this.getVariables();
		for(key in variables){
			variablePairs.push(key +"="+ variables[key]);
		}
		return variablePairs;
	},
	getSWFHTML: function() {
		var swfNode = "";
		if (navigator.plugins && navigator.mimeTypes && navigator.mimeTypes.length) { 
			swfNode = '<embed type="application/x-shockwave-flash" src="'+ this.getAttribute('swf') +'" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'"  ';
			swfNode += ' id="'+ this.getAttribute('id') +'" name="'+ this.getAttribute('id') +'" ';
			var params = this.getParams();
			 for(var key in params){ swfNode += [key] +'="'+ params[key] +'" '; }
			var pairs = this.getVariablePairs().join("&");
			 if (pairs.length > 0){ swfNode += 'flashvars="'+ pairs +'"'; }
			swfNode += '/>';
		} else {
			swfNode = '<object id="'+ this.getAttribute('id') +'" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="'+ this.getAttribute('width') +'" height="'+ this.getAttribute('height') +'">';
			swfNode += '<param name="movie" value="'+ this.getAttribute('swf') +'" />';
			var params = this.getParams();
			for(var key in params) {
			 swfNode += '<param name="'+ key +'" value="'+ params[key] +'" />';
			}
			var pairs = this.getVariablePairs().join("&");			
			if(pairs.length > 0) {swfNode += '<param name="flashvars" value="'+ pairs +'" />';}
			swfNode += "</object>";
		}
//		alert(swfNode);
		return swfNode;
	},
	setDataURL: function(strDataURL){
		if (this.initialDataSet==false){
			this.addVariable('dataURL',strDataURL);
			this.initialDataSet = true;
		}else{
			var chartObj = infosoftglobal.FusionChartsUtil.getChartObject(this.getAttribute('id'));
			chartObj.setDataURL(strDataURL);
		}
	},
	setDataXML: function(strDataXML){
		if (this.initialDataSet==false){
			this.addVariable('dataXML',strDataXML);
			this.initialDataSet = true;
		}else{
			var chartObj = infosoftglobal.FusionChartsUtil.getChartObject(this.getAttribute('id'));
			chartObj.setDataXML(strDataXML);
		}
	},
	render: function(elementId){
		var n = (typeof elementId == 'string') ? document.getElementById(elementId) : elementId;
		n.innerHTML = this.getSWFHTML();
		return true;
	}
}

infosoftglobal.FusionChartsUtil.cleanupSWFs = function() {
	if (window.opera || !document.all) return;
	var objects = document.getElementsByTagName("OBJECT");
	for (var i=0; i < objects.length; i++) {
		objects[i].style.display = 'none';
		for (var x in objects[i]) {
			if (typeof objects[i][x] == 'function') {
				objects[i][x] = function(){};
			}
		}
	}
}

infosoftglobal.FusionChartsUtil.prepUnload = function() {
	__flash_unloadHandler = function(){};
	__flash_savedUnloadHandler = function(){};
	if (typeof window.onunload == 'function') {
		var oldUnload = window.onunload;
		window.onunload = function() {
			infosoftglobal.FusionChartsUtil.cleanupSWFs();
			oldUnload();
		}
	} else {
		window.onunload = infosoftglobal.FusionChartsUtil.cleanupSWFs;
	}
}
if (typeof window.onbeforeunload == 'function') {
	var oldBeforeUnload = window.onbeforeunload;
	window.onbeforeunload = function() {
		infosoftglobal.FusionChartsUtil.prepUnload();
		oldBeforeUnload();
	}
} else {
	window.onbeforeunload = infosoftglobal.FusionChartsUtil.prepUnload;
}

if (Array.prototype.push == null) { Array.prototype.push = function(item) { this[this.length] = item; return this.length; }}

infosoftglobal.FusionChartsUtil.getChartObject = function(id)
{
  if (window.document[id]) {
      return window.document[id];
  }
  if (navigator.appName.indexOf("Microsoft Internet")==-1) {
    if (document.embeds && document.embeds[id])
      return document.embeds[id];
  } else {
    return document.getElementById(id);
  }
}

var getChartFromId = infosoftglobal.FusionChartsUtil.getChartObject;
var FusionCharts = infosoftglobal.FusionCharts;

function renderDataChart(swf, width, height, name, data) {
	var chart = createChart(swf, width, height, name);
	chart.setDataXML(data);
	chart.render(name + 'div');
	return chart;
}
function renderUrlChart(swf, width, height, name, data) {
	var chart = createChart(swf, width, height, name);
	chart.setDataURL(escape(data));
	chart.render(name + 'div');
	return chart;
}

function createChart(swf, width, height, name) {
	var chart = new FusionCharts("/extras/fusioncharts/" + swf, name + 'id', width, height, 0, 1);
	chart.addParam("wmode", "transparent");
	chart.addVariable("XMLLoadingText", "Загружаются данные. Пожалуйста подождите.");
	chart.addVariable("ParsingDataText", "Обрабатываются данные. Пожалуйста подождите.");
	chart.addVariable("ChartNoDataText", "Нет данных для отображения.");
	chart.addVariable("RenderingChartText", "Рисуется диаграмма. Пожалуйста подождите.");
	chart.addVariable("LoadDataErrorText", "Ошибка при загрузке данных.");
	chart.addVariable("InvalidXMLText", "Ошибка в формате данных.");
	return chart;
}
