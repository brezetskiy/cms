/***********************************************
* CMotion Image Gallery- © Dynamic Drive DHTML code library (www.dynamicdrive.com)
* Visit http://www.dynamicDrive.com for source code
* This copyright notice must stay intact for legal use
* Modified for autowidth and optional starting positions in
* http://www.dynamicdrive.com/forums/showthread.php?t=11839 by jschuer1 8/5/06
***********************************************/
function MGallery (object_name) {
	// Set width of the "neutral" area in the center of the gallery.
	this.restarea = 200;
	
	// Set top scroll speed in pixels. Script auto creates a range from 0 to top speed.
	this.maxspeed = 5;
	
	// Set to maximum width for gallery - must be less than the actual length of the image train.
	this.maxwidth = 1000;
	
	// Set to 1 for left start, 0 for right, 2 for center.
	this.startpos = 0;
	
	// Set message to show at end of gallery. Enter "" to disable message.
	this.endofgallerymsg = '';
	
	this.iedom = document.all || document.getElementById;
	this.scrollspeed = 0;
	this.movestate = '';
	this.actualwidth = '';
	this.cross_scroll = {};
	this.statusdiv = {};
	this.lefttime = 0;
	this.righttime = 0;
	this.menuwidth = 0;
	this.object_name = object_name;
	
	this.init = function (motioncontainer_id, motiongallery_id, truecontainer_id) {
	
		if (this.iedom) {
			crossmain = document.getElementById ? document.getElementById(motioncontainer_id) : document.all[motioncontainer_id];
			if(typeof crossmain.style.maxWidth !== 'undefined') {
				crossmain.style.maxWidth = this.maxwidth+'px';
			}
			
			this.menuwidth=crossmain.offsetWidth;
			this.cross_scroll=document.getElementById? document.getElementById(motiongallery_id) : document.all[motiongallery_id];
			this.actualwidth=document.getElementById? document.getElementById(truecontainer_id).offsetWidth : document.all[truecontainer_id].offsetWidth;
			if (this.startpos) {
				this.cross_scroll.style.left=(this.menuwidth-this.actualwidth)/this.startpos+'px';
			}
		}
		
		if (this.endofgallerymsg!=""){
			this.creatediv();
			this.positiondiv();
		}
		
		if (document.body.filters) {
			this.onresize()
		}
	}
	
	this.mousemove = function(e) {
		this.motionengine(e);
	}
	
	this.mouseout = function(e) {
		this.stopmotion(e);
		this.showhidediv("hidden");
	}
	
	// Возвращает root элемент страницы (body)
	this.ietruebody = function (){
		return (document.compatMode && document.compatMode!="BackCompat")? document.documentElement : document.body;
	}
	
	// Создаёт слой, в котором пишется, что достигнут конец галлереи
	this.creatediv = function (){
		this.statusdiv = document.createElement("div")
		this.statusdiv.setAttribute("id","statusdiv")
		document.body.appendChild(this.statusdiv)
		this.statusdiv = document.getElementById("statusdiv")
		this.statusdiv.innerHTML = this.endofgallerymsg
	}
	
	// Определяет местоположение слоя с сообщением о том, что достигнут конец галлереи.
	this.positiondiv = function (){
		var mainobjoffset = this.getposOffset(crossmain, "left"),
		menuheight = parseInt(crossmain.offsetHeight),
		mainobjoffsetH = this.getposOffset(crossmain, "top");
		this.statusdiv.style.left = mainobjoffset+(this.menuwidth/2)-(this.statusdiv.offsetWidth/2)+"px";
		this.statusdiv.style.top = menuheight+mainobjoffsetH+"px";
	}
	
	// Показывает и прячет сообщение о том, что достигнут конец фотогаллереи
	this.showhidediv = function (what){
		if (this.endofgallerymsg != "") {
			this.positiondiv();
			this.statusdiv.style.visibility=what;
		}
	}
	
	// Определяет расположение элемента относительно края браузера
	this.getposOffset = function (what, offsettype){
		var totaloffset = (offsettype=="left") ? what.offsetLeft : what.offsetTop;
		var parentEl = what.offsetParent;
		while (parentEl != null){
			totaloffset = (offsettype=="left")? totaloffset + parentEl.offsetLeft : totaloffset + parentEl.offsetTop;
			parentEl = parentEl.offsetParent;
		}
		return totaloffset;
	}
	
	// Осуществляет движение галлереи влево
	this.moveleft = function () {
		this.movestate = "left";
		if (this.iedom && parseInt(this.cross_scroll.style.left) > (this.menuwidth - this.actualwidth)){
			this.cross_scroll.style.left=parseInt(this.cross_scroll.style.left) - this.scrollspeed+"px";
			this.showhidediv("hidden");
		} else {
			this.showhidediv("visible");
		}
		this.lefttime = setTimeout(this.object_name+".moveleft()",10);
	}
	
	// Движение галлереи вправо
	this.moveright = function () {
		this.movestate = "right";
		if (this.iedom&&parseInt(this.cross_scroll.style.left) < 0){
			this.cross_scroll.style.left=parseInt(this.cross_scroll.style.left)+this.scrollspeed+"px";
			this.showhidediv("hidden");
		} else {
			this.showhidediv("visible");
		}
		this.righttime = setTimeout(this.object_name+".moveright()",10);
	}
	
	// обработчик события наведения мышки
	this.motionengine = function (e) {
		var mainobjoffset = this.getposOffset(crossmain, "left"),
		dsocx = (window.pageXOffset) ? pageXOffset : this.ietruebody().scrollLeft,
		dsocy = (window.pageYOffset) ? pageYOffset : this.ietruebody().scrollTop,
		curposy = window.event ? event.clientX : e.clientX ? e.clientX : "";
		curposy -= mainobjoffset - dsocx;
		var leftbound = (this.menuwidth-this.restarea)/2;
		var rightbound = (this.menuwidth+this.restarea)/2;
		if (curposy > rightbound){
			this.scrollspeed=(curposy-rightbound)/((this.menuwidth-this.restarea)/2) * this.maxspeed;
			clearTimeout(this.righttime);
			if (this.movestate!="left") this.moveleft();
		} else if (curposy<leftbound){
			this.scrollspeed=(leftbound-curposy)/((this.menuwidth-this.restarea)/2) * this.maxspeed;
			clearTimeout(this.lefttime);
			if (this.movestate!="right") this.moveright();
		} else {
			this.scrollspeed=0;
		}
	}
	
	this.contains_ns6 = function (a, b) {
		if (b!==null) {
			while (b.parentNode) {
				if ((b = b.parentNode) == a) {
					return true;
				}
			}
		}
		return false;
	}
	
	// Останавливает перемещение картинок
	this.stopmotion = function (e){
		if (!window.opera || (window.opera && e.relatedTarget !== null)) {
			if ((window.event&&!crossmain.contains(event.toElement)) || (e && e.currentTarget && e.currentTarget!= e.relatedTarget && !this.contains_ns6(e.currentTarget, e.relatedTarget))){
				clearTimeout(this.lefttime);
				clearTimeout(this.righttime);
				this.movestate="";
			}
		}
	}
	
	this.onresize = function() {
		if (typeof motioncontainer!=='undefined' && motioncontainer.filters){
			motioncontainer.style.width="0";
			motioncontainer.style.width="";
			motioncontainer.style.width=Math.min(motioncontainer.offsetWidth, this.maxwidth)+'px';
		}
		this.menuwidth = crossmain.offsetWidth;
		this.cross_scroll.style.left = this.startpos ? (this.menuwidth-this.actualwidth) / this.startpos+'px' : 0;
	}

};
