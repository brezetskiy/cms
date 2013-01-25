<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=windows-1251">
	<TITLE><?php echo $this->vars['title']; ?></TITLE>
	<base href="<?php echo HTTP_SCHEME; ?>://<?php echo CMS_HOST; ?><?php echo $this->global_vars['base_url']; ?>">
	
	<link rel="stylesheet" href="/design/cms/css/admin.css" type="text/css">
	<link rel="stylesheet" href="/design/cms/css/cms_edit.css" type="text/css">
	<link rel="stylesheet" href="/design/cms/css/scw.css" type="text/css">
	<link rel="stylesheet" href="/design/cms/css/jquery.autocomplete.css" type="text/css">
	
 	<link rel="stylesheet" href="/extras/jquery/css/chosen/style.css" />
	
	<?php
			reset($this->vars['/css/'][$__key]);
			while(list($_css_key,) = each($this->vars['/css/'][$__key])):
			?>
		<link rel="stylesheet" href="<?php echo $this->vars['/css/'][$__key][$_css_key]['url']; ?>" type="text/css">
	<?php 
			endwhile;
			?>
	
	<script language="JavaScript" type="text/javascript" src="/design/cms/js/cms.js"></script>
	<script language="JavaScript" type="text/javascript" src="/js/shared/global.js"></script>
	<script language="JavaScript" type="text/javascript" src="/js/shared/jshttprequest.js"></script>
	<script language="JavaScript" type="text/javascript" src="/js/shared/scw.js"></script> 
	
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery-1.4.2.min.js"></script>
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.autocomplete.js"></script>
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.idtabs.min.js"></script>
	<script language="JavaScript" type="text/javascript" src="/extras/jquery/jquery.chosen.min.js"></script>
	
	<script language="JavaScript" type="text/javascript">
		<?php
			reset($this->vars['/swf_upload_var/'][$__key]);
			while(list($_swf_upload_var_key,) = each($this->vars['/swf_upload_var/'][$__key])):
			?>
			var swf_upload_<?php echo $this->vars['/swf_upload_var/'][$__key][$_swf_upload_var_key]['field']; ?>;
		<?php 
			endwhile;
			?>

		
		$(function() {
			Hotkey.Init();
			
			for(var i = 0; i < document.links.length; i++){
				document.links.hidefocus = true;
			}
			
			// используется для открытия нужных разделов в полях типа ext_multiple
			<?php
			reset($this->vars['/onload/'][$__key]);
			while(list($_onload_key,) = each($this->vars['/onload/'][$__key])):
			?>
				<?php echo $this->vars['/onload/'][$__key][$_onload_key]['function']; ?> 
			<?php 
			endwhile;
			?>
			
			FormFocus();
			$("#tabs").idTabs();
			 
			<?php
			reset($this->vars['/swf_upload_constructor/'][$__key]);
			while(list($_swf_upload_constructor_key,) = each($this->vars['/swf_upload_constructor/'][$__key])):
			?>
				swf_upload_<?php echo $this->vars['/swf_upload_constructor/'][$__key][$_swf_upload_constructor_key]['field']; ?> = new SWFUpload(cms_create_swf_config('<?php echo $this->global_vars['table_name']; ?>', '<?php echo $this->vars['/swf_upload_constructor/'][$__key][$_swf_upload_constructor_key]['field']; ?>', '<?php echo $this->global_vars['id']; ?>', '<?php echo $this->global_vars['tmp_dir']; ?>')); 
			<?php 
			endwhile;
			?>
			
			// Размер окна
			var max_height = 0;
			var max_width = 0;
			
			$(".cms_edit").each(function(i) {
				if ($(this).height() > max_height) {
					max_height = $(this).height();  
				}
			});
			
			$("ul li").each(function(i) {
				max_width += $(this).width() + 30;
			});
			
			if(max_width < 650) max_width = 650;
			resizeDialog(max_width, max_height + 210);
			 
		  	$(".chzn-select").chosen({allow_single_deselect:true});   
		});
	</script>
	
	<script type="text/javascript" src="/extras/swfupload/swfupload.2.2.0.beta3.js"></script>
	<script type="text/javascript" src="/extras/swfupload/handlers_cms.js"></script>
	
</head>

<body>
	<div id="ajaxPreloader"></div>
	
	<?php
			reset($this->vars['/error/'][$__key]);
			while(list($_error_key,) = each($this->vars['/error/'][$__key])):
			?><div class="delta_error"><?php echo $this->vars['/error/'][$__key][$_error_key]['message']; ?></div><?php 
			endwhile;
			?>
	<?php
			reset($this->vars['/success/'][$__key]);
			while(list($_success_key,) = each($this->vars['/success/'][$__key])):
			?><div class="delta_success"><?php echo $this->vars['/success/'][$__key][$_success_key]['message']; ?></div><?php 
			endwhile;
			?>
	<?php
			reset($this->vars['/info/'][$__key]);
			while(list($_info_key,) = each($this->vars['/info/'][$__key])):
			?><div class="delta_info"><?php echo $this->vars['/info/'][$__key][$_info_key]['message']; ?></div><?php 
			endwhile;
			?>
	<?php
			reset($this->vars['/warning/'][$__key]);
			while(list($_warning_key,) = each($this->vars['/warning/'][$__key])):
			?><div class="delta_warning"><?php echo $this->vars['/warning/'][$__key][$_warning_key]['message']; ?></div><?php 
			endwhile;
			?>
	
	<form name="Form" id="Form" enctype="multipart/form-data" action="/<?php echo LANGUAGE_URL; ?>action/admin/cms/table_add/" method="POST">
		<input type="hidden" name="_return_path" value="<?php echo $this->global_vars['return_path']; ?>">
		<input type="hidden" name="_return_anchor" value="<?php echo $this->global_vars['return_anchor']; ?>">

		<?php if(isset($_GET['_update_field_name'])): ?>
			<input type="hidden" name="_return_type" value="update_foreign_key">
		<?php else: ?>
			<input type="hidden" name="_return_type" value="<?php echo $this->global_vars['return_type']; ?>">
		<?php endif; ?>
		
		<input type="hidden" name="_error_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input type="hidden" name="_table_id" id="_table_id" value="<?php echo $this->global_vars['table_id']; ?>">
		<input type="hidden" name="tmp_dir" value="<?php echo $this->global_vars['tmp_dir']; ?>">
		<input type="hidden" name="_update_field_id" value="<?php echo $_GET['_update_field_id']; ?>">
		<input type="hidden" name="_update_field_name" value="<?php echo $_GET['_update_field_name']; ?>">
		<input type="hidden" name="_update_form_id" value="<?php echo $_GET['_update_form_id']; ?>">
	
		<?php
			reset($this->vars['/hidden/'][$__key]);
			while(list($_hidden_key,) = each($this->vars['/hidden/'][$__key])):
			?>
			<input type="hidden" name="<?php echo $this->vars['/hidden/'][$__key][$_hidden_key]['name']; ?>" value="<?php echo $this->vars['/hidden/'][$__key][$_hidden_key]['value']; ?>">
			<?php if(is_null($this->vars['/hidden/'][$__key][$_hidden_key]['value'])): ?>
				<input type="hidden" value="true" name="<?php echo $this->global_vars['table_id']; ?>[_null_][<?php echo $this->vars['/hidden/'][$__key][$_hidden_key]['name']; ?>]">
			<?php endif; ?>
		<?php 
			endwhile;
			?>
	
		<div style="background-color:white;height:25px;">
			<ul id="tabs">
				<?php
			reset($this->vars['/devider/'][$__key]);
			while(list($_devider_key,) = each($this->vars['/devider/'][$__key])):
			?>
				<li><a class="<?php echo $this->vars['/devider/'][$__key][$_devider_key]['class']; ?>" href="#<?php echo $this->vars['/devider/'][$__key][$_devider_key]['name']; ?>"><?php echo $this->vars['/devider/'][$__key][$_devider_key]['title']; ?></a></li>
				<?php 
			endwhile;
			?>
			</ul>
		</div>
		
		<?php
			reset($this->vars['/devider/'][$__key]);
			while(list($_devider_key,) = each($this->vars['/devider/'][$__key])):
			?>
			<table id="<?php echo $this->vars['/devider/'][$__key][$_devider_key]['name']; ?>" class="cms_edit">
			<?php
			reset($this->vars['/devider/row/'][$_devider_key]);
			while(list($_devider_row_key,) = each($this->vars['/devider/row/'][$_devider_key])):
			?>
			<tr class="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['class']; ?>">
				<td class="title">  
					
					<label for="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['label']; ?>"><?php if(IS_DEVELOPER): ?><acronym title="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?> (<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']; ?>)"><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['title']; ?></acronym><?php else: ?><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['title']; ?><?php endif; ?></label> 
				</td>
				<td class="null">
					<?php if($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['is_nullable']): ?>
						<input type="checkbox" <?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['null_checked']; ?> value="true" name="<?php echo $this->global_vars['table_id']; ?>[_null_][<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>]" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_null" onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', this.checked);">
					<?php else: ?>
						<input type="checkbox" disabled>
					<?php endif; ?>
				</td>
				<td class="text">
				<div id="div_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>">
					<?php if($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='dummie'): ?>
						<input type="hidden" name="<?php echo $this->global_vars['table_id']; ?>[_dummie_fields_][<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>]" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>">
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='text'): ?>
						<input onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);" type="text" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>" maxLength="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['max_length']; ?>" style="width:<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['size']; ?>;"><br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='password'): ?>
						<input type="hidden" name="<?php echo $this->global_vars['table_id']; ?>[<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>_old_password]" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['old_password']; ?>">
						<input onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);" type="password" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>" maxLength="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['max_length']; ?>" style="width:<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['size']; ?>;">
						<?php if(IS_DEVELOPER): ?>
							<input type="checkbox" onclick="byId('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_show').innerHTML = (this.checked) ? byId('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value : '';"><br>
							<span class="comment" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_show"></span>
						<?php else: ?>
							<br>
						<?php endif; ?>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='file'): ?>
						<input type="hidden" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>[extension]" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>">
						<input type="file" onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);"  name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>[file]"><br>
						<?php if($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['file_exists']==true): ?>
							<input type="checkbox" disabled>
							<?php if($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['file_type'] == 'image'): ?>
								<a href="javascript: void(0);" onclick="CenterWindow('/tools/cms/admin/preview.php?id=<?php echo $this->global_vars['id']; ?>&table_name=<?php echo $this->global_vars['table_name']; ?>&field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>&extension=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>', 'Image', <?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['width']; ?>, <?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['height']; ?>, 1, 'center'); return false;">Просмотреть картинку</a>
							<?php else: ?>
								<a href="/tools/cms/admin/preview.php?id=<?php echo $this->global_vars['id']; ?>&table_name=<?php echo $this->global_vars['table_name']; ?>&field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>&extension=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>">Скачать файл</a>
							<?php endif; ?>
							<br><input type="checkbox" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>[del]" id="del_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" value="true">
							<?php if($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['file_type'] == 'image'): ?>
								<label for="del_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>">Удалить картинку</label><br>
							<?php else: ?>
								<label for="del_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>">Удалить файл</label><br>
							<?php endif; ?>
						<?php endif; ?>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='swf_upload'): ?>
						<div id="upload_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>">
							<?php
			reset($this->vars['/devider/row/uploads/'][$_devider_row_key]);
			while(list($_devider_row_uploads_key,) = each($this->vars['/devider/row/uploads/'][$_devider_row_key])):
			?>
							<div id="file_<?php echo $this->global_vars['table_name']; ?>_<?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['field']; ?>_<?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['filename']; ?>">
								<a href="javascript:void(0);" onclick="cms_swf_upload_delete('<?php echo $this->global_vars['id']; ?>', '<?php echo $this->global_vars['table_name']; ?>', '<?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['field']; ?>', '<?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['filename']; ?>', '<?php echo $this->global_vars['tmp_dir']; ?>')"><img src="/design/cms/img/icons/swf_del.png" border="0" align="absmiddle"></a>
								<img src="/img/shared/ico/<?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['icon']; ?>.gif" border="0" align="absmiddle">
								<a target=_blank href="<?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['file_url']; ?>"><?php echo $this->vars['/devider/row/uploads/'][$_devider_row_key][$_devider_row_uploads_key]['filename']; ?></a>
							</div>
							<?php 
			endwhile;
			?>
						</div>
						<span id="spanSWFUploadButton_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>"></span>
						<br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='textarea'): ?>
					<script>
					
					function shrink(id) {
						//$('#'+id).style.height=120;
						setTimeout('document.getElementById("'+id+'").style.height=120;', 100);
					}
					</script>
					
						<textarea onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);" onfocusin="this.style.height=240;" onfocusout="shrink('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>')" onKeyDown="return countTextField(this, event, <?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['max_length']; ?>);"  style="width:350px;height:120px;" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>"><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?></textarea><BR>
						<DIV class="countTextField" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_ctf">
							<DIV class="countTextField_bar"  id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_ctf_bar">
								<DIV id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_ctf_filler"></DIV>
							</DIV>
							<DIV id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_ctf_counter_div" class="countTextField_counter">
								<input disabled type="text" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_ctf_counter">
							</DIV>
						</DIV> 
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='fk_nn'): ?> 
						<select style="width:325px;" align="left" class="tree chzn-select" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>[]" multiple>
							<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['tree']; ?>
							<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['options'],'selected'=>$this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['selected'])); ?>
						</select> 
						<a href="javascript:void(0);" onclick="select_options_chzn('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', true);return false;"><img src="/design/cms/img/icons/fk_add.gif" width="15" height="15" alt="Выделить все" border="0" align="top" style="margin-top:8px;"></a>&nbsp;
						<a href="javascript:void(0);" onclick="select_options_chzn('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);return false;"><img src="/design/cms/img/icons/fk_minus.gif" width="15" height="15" alt="Снять выделение" border="0" align="top" style="margin-top:8px;"></a>&nbsp;
						<a href="javascript:void(0);" onclick="select_options_chzn('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', 'invert');return false;"><img src="/design/cms/img/icons/fk_asterix.gif" width="15" height="15" alt="Инвертировать выделение все" border="0" align="top" style="margin-top:8px;"></a>
						
						<br clear="all">
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='fk'): ?> 
						<select style="width:325px;"class="tree chzn-select" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);">
							<option value=""></option> 
							<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['options'],'selected'=>$this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['selected'])); ?> 
							<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['tree']; ?>
						</select>
						
						<a href="javascript:void(0);" onclick="EditWindow(0, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/add.png" border="0" align="top" style="margin-top:5px;"></a>&nbsp;
						<a href="javascript:void(0);" onclick="EditWindow(byId('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/edit.png" border="0" align="top" style="margin-top:5px;"></a>
						
						<br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='ext_select'): ?>
						<input style="width:325px;color:blue;" type="text" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_text" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['text_value']; ?>" 
							onkeydown="var ignore=ignoreKey(); if(!ignore) {CenterWindow('/tools/cms/admin/ext_select.php?field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&table_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field_fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, 'tree', 400, 500, 1, 1);};return ignore;" 
							onclick="CenterWindow('/tools/cms/admin/ext_select.php?field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&table_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field_fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, 'tree', 400, 500, 1, 1);"
						>
						<!-- a href="javascript:void(0);" onclick="CenterWindow('/tools/cms/admin/ext_select.php?field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&table_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field_fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, 'tree', 400, 500, 1, 1);"><img src="/design/cms/img/ui/search.png" border="0" align="absmiddle"></a -->
						<a href="javascript:void(0);" onclick="EditWindow(0, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/add.png" border="0" align="absmiddle"></a>
						<a href="javascript:void(0);" onclick="EditWindow(byId('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/edit.png" border="0" align="absmiddle"></a>
						<input type="hidden" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>"><br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='ext_list'): ?>
						<input style="width:325px;color:#00B;" type="text" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_text" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['text_value']; ?>" 
							onkeydown="var ignore=ignoreKey(); if(!ignore) {CenterWindow('/tools/cms/admin/ext_list.php?field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&table_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field_fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, 'tree', 800, 600, 1, 1);};return ignore;" 
							onclick="CenterWindow('/tools/cms/admin/ext_list.php?field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&table_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field_fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, 'tree', 800, 600, 1, 1);"
						>
						<!-- a href="javascript:void(0);" onclick="CenterWindow('/tools/cms/admin/ext_list.php?field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&table_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field_fk_table_id']; ?>&open_id='+document.getElementById('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, 'tree', 800, 600, 1, 1);"><img src="/design/cms/img/ui/search.png" border="0" align="absmiddle"></a -->
						<a href="javascript:void(0);" onclick="EditWindow(0, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/add.png" border="0" align="absmiddle"></a>
						<a href="javascript:void(0);" onclick="EditWindow(byId('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>').value, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/edit.png" border="0" align="absmiddle"></a>
						<input type="hidden" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>"><br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='ext_multiple'): ?>
						<DIV style="border:1px solid #7F9DB9;width:325px;height:500px;overflow:scroll;background-color:white;padding:5px;">
							<?php
			reset($this->vars['/devider/row/ext_multiple/'][$_devider_row_key]);
			while(list($_devider_row_ext_multiple_key,) = each($this->vars['/devider/row/ext_multiple/'][$_devider_row_key])):
			?>
							<?php if($this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['recursive'] === 'true'): ?>
								<input type="checkbox" name="<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['master_table_id']; ?>[<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['field']; ?>][]" value="<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>" <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['checked']; ?>> <a href="javascript:void();" onclick="extMultiple('<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['master_table_id']; ?>[<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['field']; ?>][]', <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['fk_table_id']; ?>, 2, <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>, '<?php echo $this->global_vars['id']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['relation_table_name']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['relation_select_field']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['relation_parent_field']; ?>');return false;"><img src="/img/shared/toc/plus.png" border="0" width="11" height="11" id="img_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>"> <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['name']; ?></a><br><div style="display:none;" id="<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>"></div>
							<?php else: ?>
								<a href="javascript:void();" onclick="extMultiple('<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['master_table_id']; ?>[<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['field']; ?>][]', <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['fk_table_id']; ?>, 2, <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>, '<?php echo $this->global_vars['id']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['relation_table_name']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['relation_select_field']; ?>', '<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['relation_parent_field']; ?>');return false;"><img src="/img/shared/toc/plus.png" border="0" width="11" height="11" id="img_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>"> <?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['name']; ?></a><br><div style="display:none;" id="<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/ext_multiple/'][$_devider_row_key][$_devider_row_ext_multiple_key]['id']; ?>"></div>
							<?php endif; ?>
							<?php 
			endwhile;
			?>
						</DIV>
						<script language="JavaScript">
						function extMultipleOpen_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['field']; ?>() {
						<?php
			reset($this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key]);
			while(list($_devider_row_open_ext_multiple_key,) = each($this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key])):
			?>
							extMultiple('<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['code']; ?>_<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['id']; ?>', '<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['master_table_id']; ?>[<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['field']; ?>][]', <?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['fk_table_id']; ?>, 2, <?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['id']; ?>, '<?php echo $this->global_vars['id']; ?>', '<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['relation_table_name']; ?>', '<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['relation_select_field']; ?>', '<?php echo $this->vars['/devider/row/open_ext_multiple/'][$_devider_row_key][$_devider_row_open_ext_multiple_key]['relation_parent_field']; ?>');
						<?php 
			endwhile;
			?>
						}
						</script>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='money'): ?>
						<input type="text" id="<?php echo $this->global_vars['table_name']; ?>_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['text_field']; ?>" name="<?php echo $this->global_vars['table_id']; ?>[<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['text_field']; ?>]" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['text_value']; ?>" maxLength="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['max_length']; ?>" style="width:<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['size']; ?>;"> 
						<select class="tree" id="<?php echo $this->global_vars['table_name']; ?>_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['currency_field']; ?>" name="<?php echo $this->global_vars['table_id']; ?>[<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['currency_field']; ?>]" size="1">
							<option value="0"><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['null_text']; ?></option>
							<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['currency_data'],'selected'=>$this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['currency_id'])); ?>
						</select><br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='ajax_select'): ?>
						<input type="hidden" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_value" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>">
						<input class="ac_input" type="text" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" name="ajax_select[<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>][<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['uniqid']; ?>]" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['ajax_value']; ?>" style="width:<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['size']; ?>;color:#00B;" onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);" />
						<input type="checkbox" disabled id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_fixed" <?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value_fixed']; ?>>
						
						<a href="javascript:void(0);" onclick="EditWindow(0, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/add.png" border="0" align="absmiddle"></a>
						<a href="javascript:void(0);" onclick="EditWindow(byId('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>_value').value, '<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['fk_table_id']; ?>', '', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '_update_field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>&_update_field_id=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>&_update_form_id=Form');return false;"><img src="/design/cms/img/ui/edit.png" border="0" align="absmiddle"></a>
						<br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='html'): ?>
						<!--<iframe style="width:90%;height:300px;" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" src="/tools/editor/frame/blank.php"></iframe> -->
						<textarea style="width:90%; height:300px;" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" src="/tools/editor/frame/blank.php"><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?></textarea> 	
						<br>
						<img src="/design/cms/img/editor/html_editor.png" border="0" align="absmiddle">
						<a href="/tools/ckeditor/ckeditor.php?event=editor/content&id=<?php echo $_GET['id']; ?>&table_name=<?php echo $this->global_vars['table_name']; ?>&field_name=<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['field']; ?>" target="_self">редактор</a>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='fixed_open'): ?>
						<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='date'): ?>
						<input type="text" class="date" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>" onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);scwShow(scwID('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>'),event);" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>"><br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='time'): ?>
						<input onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);" type="text" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" style="width:80px;"><br>
					<?php elseif($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['type']=='datetime'): ?>
						<input onclick="set_null('<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>', false);" type="text" name="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['value']; ?>" id="<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>"><br>
					<?php endif; ?>
					
					
					
					<?php
			reset($this->vars['/devider/row/radio/'][$_devider_row_key]);
			while(list($_devider_row_radio_key,) = each($this->vars['/devider/row/radio/'][$_devider_row_key])):
			?>
						<input name="<?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['row']['input_name']; ?>" type="radio" <?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['checked']; ?> value="<?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['value']; ?>" id="<?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['row']['input_id']; ?>_<?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['value']; ?>"><label for="<?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['row']['input_id']; ?>_<?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['value']; ?>"><?php echo $this->vars['/devider/row/radio/'][$_devider_row_key][$_devider_row_radio_key]['description']; ?></label><br>
					<?php 
			endwhile;
			?>
					<?php
			reset($this->vars['/devider/row/checkbox/'][$_devider_row_key]);
			while(list($_devider_row_checkbox_key,) = each($this->vars['/devider/row/checkbox/'][$_devider_row_key])):
			?>
						<input type="checkbox" id="<?php echo $this->vars['/devider/row/checkbox/'][$_devider_row_key][$_devider_row_checkbox_key]['row']['input_id']; ?>" name="<?php echo $this->vars['/devider/row/checkbox/'][$_devider_row_key][$_devider_row_checkbox_key]['row']['input_name']; ?>" value="<?php echo $this->vars['/devider/row/checkbox/'][$_devider_row_key][$_devider_row_checkbox_key]['value']; ?>" <?php echo $this->vars['/devider/row/checkbox/'][$_devider_row_key][$_devider_row_checkbox_key]['checked']; ?>><label for="<?php echo $this->vars['/devider/row/checkbox/'][$_devider_row_key][$_devider_row_checkbox_key]['row']['input_id']; ?>"><?php echo $this->vars['/devider/row/checkbox/'][$_devider_row_key][$_devider_row_checkbox_key]['description']; ?></label><br>
					<?php 
			endwhile;
			?>
					<?php
			reset($this->vars['/devider/row/checkboxset/'][$_devider_row_key]);
			while(list($_devider_row_checkboxset_key,) = each($this->vars['/devider/row/checkboxset/'][$_devider_row_key])):
			?>
						<input type="checkbox" id="<?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['row']['input_id']; ?>_<?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['value']; ?>" name="<?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['row']['input_name']; ?>[]" value="<?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['value']; ?>" <?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['checked']; ?>><label for="<?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['row']['input_id']; ?>_<?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['value']; ?>"><?php echo $this->vars['/devider/row/checkboxset/'][$_devider_row_key][$_devider_row_checkboxset_key]['description']; ?></label><br>
					<?php 
			endwhile;
			?>
				</div>
				<!-- div id="null_<?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['input_id']; ?>" style="display:none;"><i>Неопределённое значение</i></div -->
				<?php if(!empty($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['error'])): ?>
					<span class="error"><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['error']; ?></span><br>
				<?php endif; ?>
				<?php if(!empty($this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['comment'])): ?><span class="comment"><?php echo $this->vars['/devider/row/'][$_devider_key][$_devider_row_key]['row']['comment']; ?></span><?php endif; ?>
				</td>
			</tr>
			<?php 
			endwhile;
			?>
			</table>
		<?php 
			endwhile;
			?>
	
		<div class="footer">
			<input accesskey="13" type="submit" value="Сохранить" class="submit"><p>
			<input type="checkbox" <?php if($this->global_vars['no_refresh']): ?>checked<?php endif; ?> name="no_refresh" value="1" id="no_refresh"> <label for="no_refresh">Не обновлять</label><p>
			<input <?php if(!empty($this->global_vars['id'])): ?>disabled<?php endif; ?> type="checkbox" <?php if(isset($_COOKIE['add_more']) && $_COOKIE['add_more']): ?> checked <?php endif; ?> name="_add_more" value="1" id="add_more"> <label for="add_more">Добавить еще</label> &nbsp;
		</div>
	</form>
</body>
</html>