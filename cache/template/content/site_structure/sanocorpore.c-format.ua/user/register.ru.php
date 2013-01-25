
<script type="text/javascript" src="/js/user/phone.js"></script>
<script>
	function set_user_group(id, params) {
		window.global_params = params;
		
		$("div[id^='devider_']").fadeOut(1);
		if(typeof(window.global_params)=="undefined" || window.global_params == null || window.global_params == ""){
			alert("Укажите группу пользователей.");
			return false;
		}
		
		$("#current_params").val(window.global_params);  
		$("div[id^='devider_']").hide(); 
		for(var i=0; i<window.global_params.length; i++){ 
			$("#devider_"+window.global_params[i]).fadeIn(1);   
		}
	}
	
	jQuery(function($) {
		$.mask.definitions['~']='[+-]';
		$('#phone_block_input_phone').mask('+38(999) 999-99-99');
	});
</script>

<form method="POST" action="/<?php echo LANGUAGE_URL; ?>action/user/register/" enctype="multipart/form-data">
	<input type="hidden" name="_return_path" value="/User/Info/">
	<input type="hidden" name="_error_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" id="current_params" name="current_params" value="">

	<table  border="0" cellpadding="0" cellspacing="10" width="100%"  class="form">
		<tr>
			<td class="title"><span class="asterix">*</span>Email:</td>
			<td><input id="email" class="wide" type="text" name="user_email" value="<?php echo $this->vars['user_email']; ?>" onkeyup="setCheckIdentityTimeout('email');">
				<div id="email_check_ok" class="identity_check_ok"></div>
				<div id="email_check_failed" class="identity_check_failed"></div>
			</td>
		</tr>		
		<tr>
			<td class="title"><span class="asterix">*</span>Пароль:</td>
			<td><input id="user_password" class="wide" type="password" name="user_password" value="<?php echo $this->vars['user_password']; ?>"></td>
		</tr>
		<tr>
			<td class="title"><span class="asterix">*</span>Пароль повторно:</td>
			<td><input id="user_password_confirm" class="wide" type="password" name="user_password_confirm" value="<?php echo $this->vars['user_password_confirm']; ?>"></td>
		</tr>
		<?php if($this->vars['user_group_count'] > 1): ?>
			<tr>
				<td class="title"></td>
				<td>
				<?php
			reset($this->vars['/user_group/'][$__key]);
			while(list($_user_group_key,) = each($this->vars['/user_group/'][$__key])):
			?>
						<input <?php if($this->global_vars['group_id'] == $this->vars['/user_group/'][$__key][$_user_group_key]['id']): ?>checked<?php endif; ?> onclick="set_user_group(<?php echo $this->vars['/user_group/'][$__key][$_user_group_key]['id']; ?>, new Array(<?php echo $this->vars['/user_group/'][$__key][$_user_group_key]['params']; ?>));" type="radio" name="user_group" value="<?php echo $this->vars['/user_group/'][$__key][$_user_group_key]['id']; ?>" id="user_group_<?php echo $this->vars['/user_group/'][$__key][$_user_group_key]['id']; ?>">
						<label for="user_group_<?php echo $this->vars['/user_group/'][$__key][$_user_group_key]['id']; ?>"> <?php echo $this->vars['/user_group/'][$__key][$_user_group_key]['name']; ?></label><br/>
				<?php 
			endwhile;
			?> 
				</td>
			</tr>
			
		<?php elseif($this->vars['user_group_count'] == 1): ?>
			<input type="hidden" name="user_group" value="<?php echo $this->global_vars['group_id']; ?>">
		<?php endif; ?> 
	</table>

	<?php
			reset($this->vars['/category/'][$__key]);
			while(list($_category_key,) = each($this->vars['/category/'][$__key])):
			?>
		<div id="devider_<?php echo $this->vars['/category/'][$__key][$_category_key]['param_id']; ?>" style="display:none;"> 
			<table  border="0" cellpadding="0" cellspacing="10" width="100%"  class="form">
				<?php
			reset($this->vars['/category/row/'][$_category_key]);
			while(list($_category_row_key,) = each($this->vars['/category/row/'][$_category_key])):
			?>
					<tr> 
						<td class="title"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['name']; ?><?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['required']): ?><span class="asterix">*</span><?php endif; ?>:</td>
						<td>
							<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='char'): ?>
								<input class="wide" type="text" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?>" >
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='decimal'): ?>
								<input class="wide" type="text" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?>">
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='file_list'): ?>
								<div id="upload_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['table_name']; ?>_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['uniq_name']; ?>">
									<?php
			reset($this->vars['/category/row/uploads/'][$_category_row_key]);
			while(list($_category_row_uploads_key,) = each($this->vars['/category/row/uploads/'][$_category_row_key])):
			?>
										<div id="file_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['table_name']; ?>_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['field']; ?>_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?>">
											<a href="javascript:void(0);" onclick="swf_upload_delete('<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['product_id']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['table_name']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['field']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?>', '<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['tmp_dir']; ?>')"><img src="/design/cms/img/icons/swf_del.png" border="0" align="absmiddle"></a>
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
								<?php if(!empty($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'])): ?>
									<a target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['file']; ?>">Посмотреть файл</a>
								<?php endif; ?>
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='image'): ?>
								<input type="file" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]"><br>
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
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey_table'): ?>
								<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]">
									<option value="0">Сделайте выбор</option>
									<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['is_tree']): ?><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['options']; ?><?php else: ?><?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?><?php endif; ?>
								</select>
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='multiple'): ?> 
								<select  name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>][]" multiple>
									<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?>
								</select>
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='text'): ?>
								<textarea  name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" class="wide"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?></textarea>
								
							<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='date'): ?>
								<input class="date" type="text" name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" id="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" value="" onclick="scwShow(scwID('param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]'),event);">
													
							<?php endif; ?>
							
							<br><span class="comment"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['description']; ?></span>
						</td>
					</tr>
				<?php 
			endwhile;
			?>  
			</table>
		</div>
	<?php 
			endwhile;
			?>

	<table id="register_submit_table"  border="0" cellpadding="0" cellspacing="10" width="100%"  class="form" >
		<tr>
			<td class="title">Номер мобильного:</td>
			<td>
				<input type="text" id="phone_block_input_phone" name="user_phone" value="<?php echo $this->vars['user_phone']; ?>">
				<div id="phone_block_country_control">
					<span id="phone_block_country_title" style="font-size:11px; color:#777; margin-top:5px;">Ваша страна:</span> 
					<a id="phone_block_country_ukraine" href="javascript:void(0);" onclick="phone_country('ukraine');" style="font-size:10px; color:#ff9d02;" class="country_mask_button">Украина</a>, 
					<a id="phone_block_country_russia" href="javascript:void(0);" onclick="phone_country('russia');" style="font-size:10px;" class="country_mask_button">Россия</a>,
					<a id="phone_block_country_other" href="javascript:void(0);" onclick="phone_country('other');" style="font-size:10px;" class="country_mask_button">другая</a>
				</div>
			</td>
		</tr>
	</table>

	<table id="register_submit_table"  border="0" cellpadding="0" cellspacing="10" width="100%"  class="form" >
		<?php if(CMS_USE_CAPTCHA && !Auth::isLoggedIn()): ?>
			<tr>
				<td class="title"><span class="asterix">*</span>Число на картинке:</td>
				<td> 
					<table cellspacing="0" cellpadding="0"> 
						<tr>
							<td><?php echo $this->vars['captcha_html']; ?></td>
							<td><input type="text" maxlength="6" size="6" name="captcha_value"></td> 
						</tr>
					</table>
					<span class="comment">Введите число, показанное на картинке</span>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<td>&nbsp;</td> 
			<td><input type="submit" value="Зарегистрироваться"></td>
		</tr>
	</table>

</form>
