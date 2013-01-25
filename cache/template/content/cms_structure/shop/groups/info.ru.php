<h1><?php echo $this->vars['name']; ?></h1>
<?php
			reset($this->vars['/path/'][$__key]);
			while(list($_path_key,) = each($this->vars['/path/'][$__key])):
			?><a href="/Admin/Shop/Groups/?group_id=<?php echo $this->vars['/path/'][$__key][$_path_key]['id']; ?>"><?php echo $this->vars['/path/'][$__key][$_path_key]['name']; ?></a> <img src="/design/cms/img/ui/selector.gif" border="0"> <?php 
			endwhile;
			?> <?php echo $this->vars['name']; ?>

<script language="JavaScript" type="text/javascript" src="/extras/swfupload/swfupload.2.2.0.beta3.js"></script>
<script language="JavaScript" type="text/javascript" src="/extras/swfupload/handlers_cms.js"></script>
<script type="text/javascript" src="/js/shared/fckeditor/fckeditor.js"></script>
<script type="text/javascript" src="/js/shop/admin.js?v2"></script>
<p>
<a href="../?group_id=<?php echo $this->vars['group_id']; ?>"><img src="/design/cms/img/button/up.gif" border="0" align="absmiddle"> На уровень вверх</a>&nbsp;&nbsp;
<a href="./?group_id=<?php echo $this->vars['group_id']; ?>"><img src="/design/cms/img/icons/fk_add.gif" border="0" align="absmiddle"> Добавить товар</a>&nbsp;&nbsp;
<a href="javascript:void(0);" onclick="EditWindow(0, 'shop_group_param', '<?php echo $this->vars['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['LANGUAGE_CURRENT']; ?>', 'group_id=<?php echo $this->vars['group_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" align="absmiddle"> Новое свойство</a>
<?php if(isset($this->vars['del_return_path'])): ?>
	<a href="/action/admin/cms/table_delete/?_return_path=<?php echo $this->vars['del_return_path']; ?>&_language=<?php echo LANGUAGE_CURRENT; ?>&_table_id=946&946[id][]=<?php echo $this->vars['product_id']; ?>" title="Удалить" onclick="return confirm('Удалить?')"><img src="/design/cms/img/icons/del.gif" width="15" height="15" border="0" align="absmiddle" alt="Удалить"> Удалить товар</a>
<?php endif; ?>
<br>

<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/admin/shop/product/" enctype="multipart/form-data">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<input type="hidden" name="_error_path" value="<?php echo CURRENT_URL_FORM; ?>">
<input type="hidden" name="product_id" value="<?php echo $this->vars['product_id']; ?>">
<!-- input type="hidden" name="group_id" value="<?php echo $this->vars['group_id']; ?>" -->
<input type="hidden" name="tmp_dir" value="<?php echo $this->vars['tmp_dir']; ?>">
<input type="hidden" name="table_name" value="<?php echo $this->vars['table_name']; ?>">

<div id="idTabs">
	<ul>
	<?php
			reset($this->vars['/category/'][$__key]);
			while(list($_category_key,) = each($this->vars['/category/'][$__key])):
			?>
		<li><a href="#p<?php echo $this->vars['/category/'][$__key][$_category_key]['param_id']; ?>"><?php echo $this->vars['/category/'][$__key][$_category_key]['name']; ?></a></li>
	<?php 
			endwhile;
			?>
	</ul>
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
			<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id'] == 0): ?>
				<tr>
					<td class="title">Название<span class="asterix">*</span>:</td>
					<td><input type="text" name="name" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['product']; ?>" class="wide"></td>
				</tr>
				<tr>
					<td class="title">Цена:</td>
					<td><input type="text" name="price" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['price']; ?>" class="wide"></td>
				</tr>
				
				<tr>
					<td class="title">Категория:</td>
					<td>
						<select name="group_id">
						<?php echo $this->global_vars['group_options']; ?>
						</select>
					</td>
				</tr>
			<?php else: ?>
				<tr>
					<td class="title"><a href="javascript:void(0);" onclick="EditWindow(<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>, 'shop_group_param', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', '');"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['name']; ?></a><?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['required']): ?><span class="asterix">*</span><?php endif; ?>:</td>
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
							<input type="checkbox" name="delete[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" id="delete_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>"> <label for="delete_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>">Удалить картинку</label><br>
							<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'] != '#'): ?>
								<a rel="lightbox" target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['file']; ?>"><img hspace="4" src="/design/cms/img/icons/photo.png" border="0">Посмотреть картинку</a>
							<?php endif; ?>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='bool'): ?>
							<input type="checkbox" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="1" <?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['value']!=0): ?>checked<?php endif; ?>>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey'): ?>
							<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]">
								<option value="0">Сделайте выбор</option>
								<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?>
							</select>
							<a href="javascript:void(0);" onclick="EditWindow(0, 'shop_info_data', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', 'info_id=<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['info_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" alt="Добавить значение"></a>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey_table'): ?>
							<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]">
								<option value="0">Сделайте выбор</option>
								<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['options']; ?>
							</select>
							<a href="javascript:void(0);" onclick="EditWindow(0, '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['table_name']; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', 'info_id=<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['info_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" alt="Добавить значение"></a>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='multiple'): ?>
							<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>][]" multiple>
							<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?>
							</select>
							<a href="javascript:void(0);" onclick="EditWindow(0, 'shop_info_data', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['STRUCTURE_URL']; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['LANGUAGE_CURRENT']; ?>', 'info_id=<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['info_id']; ?>');"><img src="/design/cms/img/icons/fk_add.gif" border="0" alt="Добавить значение"></a>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='text'): ?>
							<textarea name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" class="wide"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?></textarea>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='html'): ?>
							<input type="hidden" name="temp_id_param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" id="temp_id_param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" value="" />
							<textarea name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" id="param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" class="wide"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?></textarea>
						<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='date'): ?>
							<input type="text" class="date" id="param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?>" onclick="set_null('param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>', false);scwShow(scwID('param_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>'),event);" />							
						<?php endif; ?>
						<br><span class="comment"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['description']; ?></span>
					</td>
				</tr>
			<?php endif; ?>
			<?php 
			endwhile;
			?>
			<tr>
				<td></td>
				<td><input type="submit" value="Сохранить"></td>
			</tr>
			</table>
		</div>
	<?php 
			endwhile;
			?>
</div>
</form>

<table width="100%"><tr>
<?php if(!empty($this->vars['previous'])): ?>
	<td width="50%"><a href="./?product_id=<?php echo $this->vars['previous']; ?>">&larr; Назад</a></td>
<?php else: ?>
	<td width="50%" style="color:silver;">&larr; Назад</td>
<?php endif; ?>
<?php if(!empty($this->vars['next'])): ?>
	<td align="right" width="50%"><a href="./?product_id=<?php echo $this->vars['next']; ?>">Вперёд &rarr;</a></td>
<?php else: ?>
	<td align="right" width="50%" style="color:silver;">Вперёд &rarr;</td>
<?php endif; ?>
</tr></table>

<?php echo $this->vars['gallery']; ?>


<!-- textarea style="width:100%;height:300px;"></textarea -->