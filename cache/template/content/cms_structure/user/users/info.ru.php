
<h1>Информация о пользовтеле</h1>
Тут выводится основная информация о пользователе
<br>
<br>

<h3>Заголовки дополнительной информации</h3>

<script language="JavaScript" type="text/javascript" src="/extras/swfupload/swfupload.2.2.0.beta3.js"></script>
<script language="JavaScript" type="text/javascript" src="/extras/swfupload/handlers_cms.js"></script>
<script>
	function integrateCkEditor(textarea, object_id, object_table, object_field, object_temp_id) {
		CKEDITOR.timestamp = ( new Date() ).valueOf();
	//	alert(textarea);
		CKEDITOR.on('currentInstance', function(e) {
	//		alert('activated '+e.sender.currentInstance.name)
		});
		CKEDITOR.replace(textarea, {
		        customConfig : '',
		        resize_enabled: false,
		        skin: 'office2003',
		        contentsCss: '/design/cms/css/editor/src.css',
		        //,cms-cvs
		        extraPlugins: 'iframedialog,cms-quit,cms-language,cms-image,cms-attach,cms-flash,cms-structure-link',
		        cmslanguage_languages: 'ru/Русский;',
		        cmslanguage_current_language: 'ru',
		        
		        object_id: object_id,
		        object_temp_id: object_temp_id,
		        object_table: object_table,
		        object_field: object_field,
		        
		      
		        stylesSet: [
					{ name : 'Заголовок 1'		, element : 'h1' },
					{ name : 'Заголовок 2'		, element : 'h2' },
					{ name : 'Заголовок 3'		, element : 'h3' },
				],
				fontSize_sizes: '8/8px;9/9px;10/10px;11/11px;12/12px;13/13px;14/14px;15/15px;16/16px;17/17px;18/18px;19/19px;20/20px;21/21px;22/22px;23/23px;24/24px;25/25px;26/26px;27/27px;28/28px;36/36px;48/48px;72/72px;',
		        toolbar_CMS:
				    [
					    ['Cut','Copy','Paste','PasteText','PasteFromWord','CmsCvs'],
					    
					    ['Link','Unlink','Anchor','CmsAttach','CmsStructureLink'],
					    ['CmsImage','CmsFlash','Table','HorizontalRule','SpecialChar'],
					    
					    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
					    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
					    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat','ShowBlocks'],
					    '/',
					 
					    ['Styles','Font','FontSize','TextColor','BGColor'],
					    ['Bold','Italic','Underline','Strike','Subscript','Superscript'],
					    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField','Source'],
				    ],
		        toolbar: 'CMS'		       
		    }
		);
	}
</script>

<p>

<a href="../?user_id=<?php echo $this->vars['user_id']; ?>"><img src="/design/cms/img/button/up.gif" border="0" align="absmiddle"> На уровень вверх</a>&nbsp;&nbsp;      
<a href="javascript:void(0);" onclick="EditWindow(0, 'auth_user_group_param', '<?php echo $this->vars['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['LANGUAGE_CURRENT']; ?>', 'user_id=<?php echo $this->vars['user_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" align="absmiddle"> Новое свойство</a><br>

<h1><?php echo $this->vars['user']; ?></h1>

<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/auth/save_info/" enctype="multipart/form-data">
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" name="_error_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" name="user_id" value="<?php echo $this->vars['user_id']; ?>">

	<div id="idTabs">
		<ul><?php
			reset($this->vars['/category/'][$__key]);
			while(list($_category_key,) = each($this->vars['/category/'][$__key])):
			?><li><a href="#p<?php echo $this->vars['/category/'][$__key][$_category_key]['param_id']; ?>"><?php echo $this->vars['/category/'][$__key][$_category_key]['name']; ?></a></li><?php 
			endwhile;
			?></ul>
		
		<br style="clear: left;"/>
		<div class="bottom"></div>
		
		<?php
			reset($this->vars['/category/'][$__key]);
			while(list($_category_key,) = each($this->vars['/category/'][$__key])):
			?>
			<div id="p<?php echo $this->vars['/category/'][$__key][$_category_key]['param_id']; ?>">
			
				<table class="vertical" width="100%">
					<?php
			reset($this->vars['/category/row/'][$_category_key]);
			while(list($_category_row_key,) = each($this->vars['/category/row/'][$_category_key])):
			?>
						<tr> 
							<td class="title">
								<a href="javascript:void(0);" onclick="EditWindow(<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>, 'auth_user_param', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', '');"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['name']; ?></a>
								<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['required']): ?><span class="asterix">*</span><?php endif; ?>:
							</td>
							<td>
								<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='char'): ?>
									<input class="wide" type="text" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?>">
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='decimal'): ?>
									<input class="wide" type="text" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?>">
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='file_list'): ?>
									<div id="upload_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['table_name']; ?>_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['uniq_name']; ?>">
										<?php
			reset($this->vars['/category/row/uploads/'][$_category_row_key]);
			while(list($_category_row_uploads_key,) = each($this->vars['/category/row/uploads/'][$_category_row_key])):
			?>
										<div id="file_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['table_name']; ?>_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['field']; ?>_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?>">
											<a href="javascript:void(0);" onclick="cms_swf_upload_delete('<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['product_id']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['table_name']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['field']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['tmp_dir']; ?>')"><img src="/design/cms/img/icons/swf_del.png" border="0" align="absmiddle"></a>
											<img src="/img/shared/ico/<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['icon']; ?>.gif" border="0" align="absmiddle">
											<a target=_blank href="<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['file_url']; ?>"><?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?></a>
										</div>
										<?php 
			endwhile;
			?>
									</div>
									<span id="spanSWFUploadButton_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['uniq_name']; ?>"></span>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='file'): ?>
									<input type="file" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]"><br>
									<input type="checkbox" name="delete[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" id="delete_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>"><label for="delete_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>">Удалить картинку</label><br>
									<?php if(!empty($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'])): ?>
										<a target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['file']; ?>">Посмотреть файл</a>
									<?php endif; ?>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='image'): ?>
									<input type="file" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]"><br>
									<input type="checkbox" name="delete[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" id="delete_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>"><label for="delete_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>">Удалить картинку</label><br>
									<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'] != '#' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb'] != '#'): ?>
										Посмотреть картинку [
											<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'] != '#'): ?><a rel="lightbox" target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['file']; ?>">оригинальная: <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['width']; ?> x <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['height']; ?></a>;<?php endif; ?>
											<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb'] != '#'): ?><a rel="lightbox" target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb']; ?>">пиктограмма: <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb_width']; ?> x <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb_height']; ?></a><?php endif; ?>
										]
									<?php endif; ?>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='bool'): ?>
									<input type="checkbox" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="1" <?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['value']!=0): ?>checked<?php endif; ?>>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey'): ?>
									<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]">
										<option value="0">Сделайте выбор</option>
										<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?>
									</select>
									<a href="javascript:void(0);" onclick="EditWindow(0, 'auth_user_info_data', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', 'info_id=<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['info_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" alt="Добавить значение"></a>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey_table'): ?>
									<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]">
										<option value="0">Сделайте выбор</option>
										<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['is_tree']): ?><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['options']; ?><?php else: ?><?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?><?php endif; ?>
									</select>
									<a href="javascript:void(0);" onclick="EditWindow(0, '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['table_name']; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', 'info_id=<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['info_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" alt="Добавить значение"></a>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='multiple'): ?>
									<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>][]" multiple>
									<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?>
									</select>
									<a href="javascript:void(0);" onclick="EditWindow(0, 'auth_user_info_data', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', 'info_id=<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['info_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" alt="Добавить значение"></a>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='text'): ?>
									<textarea name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" class="wide"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?></textarea>
								<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='html'): ?>
									<input type="hidden" name="temp_id_param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" id="temp_id_param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['temp_field_id']; ?>" />
									<textarea name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" id="param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['uniq_name']; ?>"  cols="80" rows="10"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?></textarea>
								<?php endif; ?>
								<br><span class="comment"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['description']; ?></span>
							</td>
						</tr>
					<?php 
			endwhile;
			?>
					<tr><td></td><td><input type="submit" value="Сохранить"></td></tr>
				</table>
			</div>
		<?php 
			endwhile;
			?>
	</div>
</form>

<?php echo $this->vars['cms_phones']; ?>
<?php echo $this->vars['gallery']; ?>