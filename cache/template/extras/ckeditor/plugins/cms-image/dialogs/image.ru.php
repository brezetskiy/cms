<html>
<head>
<title>������� �����������</title>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; CHARSET=Windows-1251">
<META HTTP-EQUIV="imagetoolbar" CONTENT="no">
<LINK href="/design/cms/css/editor/dialog.css" type="text/css" rel="stylesheet">
<link href="/extras/ckeditor/plugins/cms-shared/dialog.css?2" rel="stylesheet" type="text/css" />
<script language="JavaScript" src="/js/shared/global.js"></script>
<script language="JavaScript">
// �������� ���������������� ���������
var previewImage = new Image();

function checkForm() {
	var imgRegExp = /\.(jpg|gif|png)$/i;
	var currentForm = document.all.Form;
	
	currentForm.normalImage.focus();
	
	if (currentForm.normalImage.value == "") {
		alert("������� ����� �����������");
		return false;
	} else if(null == currentForm.normalImage.value.match(imgRegExp)) {
		alert ("��������� ���� �� �������� ������������")
		return false;
	} else {
		document.getElementById("submitButton").disabled = true;
		return true;
	}
	return false;
}

/**
* ����������� ��������� ��������
*/
function position (pos) {
	document.getElementById('default').style.border = '1px solid silver';
	document.getElementById('left').style.border = '1px solid silver';
	document.getElementById('right').style.border = '1px solid silver';
	document.getElementById('top').style.border = '1px solid silver';
	document.getElementById('bottom').style.border = '1px solid silver';
	document.getElementById('center').style.border = '1px solid silver';
	
	document.getElementById(pos).style.border = '1px solid red';
	
	if (pos != 'default') {
		document.getElementById('img_align').value = ' align="' + pos +'"';
	} else {
		document.getElementById('img_align').value = '';
	}
}

var CKEDITOR = window.parent.CKEDITOR;

var okListener = function(ev) {
	document.getElementById('editor_name').value = CKEDITOR.config.activeEditorName
	document.getElementById('id').value = CKEDITOR.instances[CKEDITOR.config.activeEditorName].config.object_id
	document.getElementById('temp_id').value = CKEDITOR.instances[CKEDITOR.config.activeEditorName].config.object_temp_id
	document.getElementById('field_name').value = CKEDITOR.instances[CKEDITOR.config.activeEditorName].config.object_field
	document.getElementById('table_name').value = CKEDITOR.instances[CKEDITOR.config.activeEditorName].config.object_table
	document.getElementById('dialog_form').submit();
	CKEDITOR.dialog.getCurrent().removeListener("ok", okListener);
};

CKEDITOR.dialog.getCurrent().on("ok", okListener);


</script>
</head>
<body>
	<form id="dialog_form" action="/action/admin/ckeditor/image_upload/" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="id" id="id">
	<input type="hidden" name="temp_id" id="temp_id">
	<input type="hidden" name="field_name" id="field_name">
	<input type="hidden" name="table_name" id="table_name">
	<input type="hidden" name="editor_name" id="editor_name">
	
	<fieldset><LEGEND>��������</LEGEND>
	<table class="cms_cke_dialog">
	<tr>
		<td class="title">������������:</td>
		<td>
			<input id="img_align" type="hidden" name="imgAlign" value="">
			<a href="javascript:void(0);" onclick="position('default');"><img id="default" src="/design/cms/img/editor/image/default.gif" border="0" hspace="2" alt="�� ���������"></a>
			<a href="javascript:void(0);" onclick="position('left');"><img id="left" src="/design/cms/img/editor/image/left.gif" border="0" hspace="2" alt="�� ������ ����"></a>
			<a href="javascript:void(0);" onclick="position('right');"><img id="right" src="/design/cms/img/editor/image/right.gif" border="0" hspace="2" alt="�� ������� ����"></a>
			<a href="javascript:void(0);" onclick="position('top');"><img id="top" src="/design/cms/img/editor/image/top.gif" border="0" hspace="2" alt="�� �������� ����"></a>
			<a href="javascript:void(0);" onclick="position('bottom');"><img id="bottom" src="/design/cms/img/editor/image/bottom.gif" border="0" hspace="2" alt="�� ������� ����"></a>
			<a href="javascript:void(0);" onclick="position('center');"><img id="center" src="/design/cms/img/editor/image/center.gif" border="0" hspace="2" alt="�� ������"></a>
		</td>
	</tr>
	<tr>
		<td class="title">��������:</td>
		<td><input type="file" name="normalImage" id="normalImage" size="20" onChange="imagesOnly(this);" class="input_file"></td>
	</tr>
	<tr>
		<td class="title">���������:</td>
		<td><input name="alt" value="" size="32"></td>
	</tr>
	<tr>
		<td></td>
		<td><input type="checkbox" id="border" name="border" value="1" <?php echo $this->vars['image_border']; ?>><label for="border">�����</label></td>
	</tr>
	<tr>
		<td></td>
		<td><input id="watermark" type="checkbox" name="watermark" value="true" <?php echo $this->vars['watermark']; ?>><label for="watermark">������� ����</label></td>
	</tr>
	
	<tr>
		<td class="title">������ �� ��� X:</td>
		<td><input name="hspace" size="15" maxLength="3" value="<?php echo $this->vars['hspace']; ?>" onKeyPress="return digitsOnly(event);"> px</td>
	</tr>
	<tr>
		<td class="title">������ �� ��� Y:</td>
		<td><input name="vspace" size="15" maxLength="3" value="<?php echo $this->vars['vspace']; ?>" onKeyPress="return digitsOnly(event);"> px</td>
	</tr>
	</table>
	</fieldset>
	
	<DIV id="div_thumb_make">
		<fieldset><LEGEND>������ �����������</LEGEND>
		<table>
		<tr>
			<td class="title">������:</td>
			<td><input name="thumb_width" size="15" value="<?php echo $this->vars['thumb_width']; ?>" onKeyPress="return digitsOnly(event);"> px</td>
		</tr>
		<tr>
			<td class="title">������:</td>
			<td><input name="thumb_height" size="15" value="<?php echo $this->vars['thumb_height']; ?>" onKeyPress="return digitsOnly(event);"> px</td>
		</tr>
		</table>
		</fieldset>
	</DIV>
	
</form>
<script language="JavaScript">
	position('default');
</script>
</body>
</html>