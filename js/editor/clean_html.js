/**
* ������ ������������ ������ �� ������� HTML ����
*/
(Cleaner={
	
	editor : {},
	buffer : {},
	output : {},
	goodTags : [],
	canEmptyTags : [],
	goodAttributes : [],
	goodClasses : [],
	
	// ������� ����
	goodTags0 : ["a","p","br","strong","b","em","font","i","tt","code","pre","ul","ol","li","img","table","tbody","thead","tfoot","caption","tr","td","th","col","colgroup","h1","h2","h3","h4","h5","h6","small","big","sub","sup","div"],
	
	// ����, ������� canHaveHTML() � ����� ���� �������.
	canEmptyTags0 : ["td","th"],
	
	goodAttributes0 : [],
	
	/**
	* ���������� ��������� ������-�������� (����������� � ��������� attributes)
	* �������� - � 6-�� MSIE �������� �������������� ��������� � ���� TABLE ����� � ����� ��������
	*/
	mustDieAttributes : ["x:str","x:num","x:fmla"],

	// ������� ������ (�� �������)
	goodClasses0 : ["important","noindent","note","h1","h2","h3","h4","h5","h6"],
	
	
	Init : function () {
		this.editor = window.frames.clipBrd.document.body
		this.buffer = document.getElementById("buffer");
		this.output = document.getElementById("output");
		
		(getCookie('clean_html_a') == 1) ? document.getElementById('delete_a').checked = true : document.getElementById('delete_a').checked = false;
		(getCookie('clean_html_img') == 1) ? document.getElementById('delete_img').checked = true : document.getElementById('delete_img').checked = false;
		(getCookie('clean_html_colors') == 1) ? document.getElementById('delete_colors').checked = true : document.getElementById('delete_colors').checked = false;
		(getCookie('clean_html_aligns') == 1) ? document.getElementById('delete_aligns').checked = true : document.getElementById('delete_aligns').checked = false;

		window.frames.clipBrd.focus();
		var range = window.frames.clipBrd.document.selection.createRange();
		range.execCommand('Paste');

		// ������� �������� (��������� � ����� �����)
		this.goodAttributes0[""] = ["href", "target", "name", "title", "alt", "src", "id"];
		// �������������� ���������� �������� ��� ��������� �����
		this.goodAttributes0["img"] = ["width", "height", "border", "align"];
		
		// width % - �� ������� - ������, ���� ������ ���������� ���� ���������, ��������� ��������...
		this.goodAttributes0["table"] = ["cellSpacing", "cellPadding", "border", "width %"];
		this.goodAttributes0["tr td col colgroup"] = ["noWrap", "align", "vAlign", "width %", "colSpan", "rowSpan"];
		this.goodAttributes0["a area"] = ["href", "name"];
		this.goodAttributes0["br"] = ["clear"];
		
		for(i in this.goodTags0){
			this.goodTags[this.goodTags0[i]]=true
		}
		for(i in this.canEmptyTags0){
			this.canEmptyTags[this.canEmptyTags0[i]]=true
		}
		for(i in this.goodAttributes0){
			var splitted=i.split(" ")
			for(ii in splitted){
				this.goodAttributes[splitted[ii]]=[]		
				for(j in this.goodAttributes0[i]){
					this.goodAttributes[splitted[ii]][this.goodAttributes0[i][j]]=true
				}		
			}
		}
		for(i in this.goodClasses0){
			this.goodClasses[this.goodClasses0[i]]=true
		}
	},
	cleanObject : function (o) {
		var s=""
		var myAttributes=[]
		var i
		
		if (o.outerHTML.substr(0,2) == "<?") { // "?>" ) {
			o.removeNode(false)
			return
		}
		var tag=o.tagName.toLowerCase()
		
		/**
		 * ������� ������, �������� � ������ ����
		 */
		if(!this.goodTags[tag] || (self.clean_a && tag=="a") || (self.clean_img && tag=="img") || (o.canHaveHTML && o.innerHTML=="" && !this.canEmptyTags[tag])){
			try{
				o.removeNode(false)
			} catch(e) {
				// ������, �� ��� �� �� �� ��������
			}
	
			return;
		}
	
		var a = o.attributes
		if (!a) return
	
		for(i in a){ // ��...
			if(""+a[i]!="null"){ // �������, �� ������ if(a[i]) �� �����������
				myAttributes[i]=a[i]
			}
		}
		
		/** 
		 * ������ ������ �������� � ������ ������
		 */
		for(i in myAttributes){
			if ((i == 'class' || i == 'className') && this.goodClasses[o.className]) {
				// ���������� ����������� ������
				continue;
			}
			if (this.goodAttributes[""][i] || (this.goodAttributes[tag] && this.goodAttributes[tag][i])) {
				// ���������� ����������� ���������
				continue;
			}
			if(!self.clean_colors && (i=="bgColor" || i=="color")) {
				// ���������� ��������� ������, ���� �� ������� �� ���������
				continue;
			} else if (tag == 'font' && self.clean_colors) {
				// ������� ��� FONT, ���� �� ������� ���������� ������.
				try{
					o.removeNode(false)
				} catch(e) {
					// ������, �� ��� �� �� �� ��������
				}
				return;
			}
			if(!self.clean_aligns && (i=="align" || i=="vAlign")) {
				// ���������� ��������� ������������, ���� �� ������� �� ���������
				continue;
			}
			if (i == 'class'){
				i = 'className';
			}
			o.removeAttribute(i)
		}
		
		/**
		* � 6-�� MSIE �� ������ ���������� ��������� � ������. ��. ����.
		*/
		if (o.tagName.toLowerCase()!="table") {
			for(i in this.mustDieAttributes){
				o.removeAttribute(this.mustDieAttributes[i])
			}
		}
		o.style.cssText = ""
	},
	cleanTree : function (o, mustClean){
		var c=o.children
		var i
		if(c){
			for(i=c.length-1;i>=0;i--){
				this.cleanTree(c[i],true)
			}
		}
		
		if(mustClean) this.cleanObject(o)
	},
	clean : function (){
		clean_a = document.getElementById("delete_a").checked
		clean_img = document.getElementById("delete_img").checked
		clean_colors = document.getElementById("delete_colors").checked
		clean_aligns = document.getElementById("delete_aligns").checked
		if(this.editor.innerHTML==""){
			this.buffer.innerHTML=this.output.value
		}else{
			this.buffer.innerHTML=this.editor.innerHTML
		}
		this.cleanTree(this.buffer,false)
		
		if (this.buffer.innerHTML.substr(0,6)=="&nbsp;"){
			this.buffer.innerHTML=this.buffer.innerHTML.substr(6)
		}
		
		this.editor.innerHTML = this.buffer.innerHTML
		this.output.value = this.editor.innerHTML
		
		if (document.getElementById('delete_a').checked) setCookie('clean_html_a', 1, 365); else setCookie('clean_html_a', 0, 365);
		if (document.getElementById('delete_img').checked) setCookie('clean_html_img', 1, 365); else setCookie('clean_html_img', 0, 365);
		if (document.getElementById('delete_colors').checked) setCookie('clean_html_colors', 1, 365); else setCookie('clean_html_colors', 0, 365);
		if (document.getElementById('delete_aligns').checked) setCookie('clean_html_aligns', 1, 365); else setCookie('clean_html_aligns', 0, 365);
		
		window.dialogArguments.frames.EditFrame.focus();
		var range = window.dialogArguments.frames.EditFrame.document.selection.createRange();
		range.pasteHTML(this.output.value);
		window.close();
	}
});