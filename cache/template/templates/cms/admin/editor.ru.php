<html>
<head>
<title>Simple web based text editor.</title>
<meta http-equiv="content-type" content="text/html; charset=windows-1251">
<meta http-equiv="imagetoolbar" content="no">
<link href="/css/cms/text_editor.css" type="text/css" rel="stylesheet"> 
<script type="text/javascript" src="/extras/jquery/jquery-1.4.2.min.js"></script> 
	<SCRIPT type="text/javascript" src="/js/shared/jshttprequest.js"></SCRIPT>
	<SCRIPT type="text/javascript" src="/js/shared/global.js"></SCRIPT>
<script>
	function switchOnSave(){
		$("#save_button").removeAttr("disabled");
	}
	
	function send(){
		$("#text_editor_form").submit();
	}
	
	function refreshParent() {
		AjaxRequest.send(null, '/action/admin/cms/text_editor_close/', '', true);
		window.opener.location.href = window.opener.location.href;
		
		if (window.opener.progressWindow){
	    	window.opener.progressWindow.close()
	  	}
	  	window.close();
	  	window.opener.location.reload();
	}
</script>
</head>

<body style="margin:0; padding:0; overflow:hidden;">
	
<form id="text_editor_form" action="/action/admin/cms/text_editor_save" method="post">
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" name="id" id="editor_id" value="<?php echo $this->global_vars['id']; ?>">
	<input type="hidden" name="is_file" id="is_file" value="<?php echo $this->global_vars['is_file']; ?>">
	<input type="hidden" name="extention" id="extention" value="<?php echo $this->global_vars['extention']; ?>">
	<input type="hidden" name="table_name" id="editor_table_name" value="<?php echo $this->global_vars['table_name']; ?>">
	<input type="hidden" name="field_name" id="editor_field_name" value="<?php echo $this->global_vars['field_name']; ?>">
	
	<div class="se_menu" style="width:100%;">
		<table cellpadding="0" cellspacing="0" >
		<tr>
			<td width="75">
			<span class="se_toolbar">
				<span class="se_toolbar_start"></span>
				<span class="se_toolgroup">
					<span class="se_button"><a href="javascript:void(0);" onclick="send();" class="se_off se_button_save"><span class="se_icon" title="���������"></span></a></span> 
					<span class="se_button"> 
						<a href="javascript:void(0);" class="se_off se_button_cms-quit" onclick="refreshParent(); return false;">
							<span class="se_icon" title="������� ��������" style="background-image: url(/img/cms/text_editor/se_button_quit.png); background-position: 0pt 0px;"></span>
						</a>
					</span>  
				</span>
				<span class="se_toolbar_end"></span>
			</span> 
			</td>
			<td align="left" ><span class="se_toolbar"><span class="se_toolcomment" ><span class="se_comment" ><?php echo $this->vars['title']; ?></span></span></span></td>
			<?php if($this->global_vars['is_saved'] == 1 ): ?>
			<td align="left"><span class="se_toolbar" ><span class="se_toolcomment" ><span class="se_comment" >��������� � <b><?php echo $this->global_vars['save_dtime']; ?></b></span></span></span></td>
			<?php endif; ?>
		</tr>
		</table>
	</div>
	
	<div style="width:100%; height:100%;"><textarea id="content" name="content" style="width:100%; height:<?php echo $this->global_vars['window_height']; ?>px; font-size:11px;" onkeyup="switchOnSave();"><?php echo $this->vars['content']; ?></textarea></div>
</form>

</body>
</html>  