<style>
	table.user_info_container tr td{ vertical-align:top;}
</style>
 
<script type="text/javascript" src="/js/user/phone.js"></script>
<script>   

 
	function set_user_group(id, params) {
		$("#current_params").val(params);  
		
		$("div[id^='div_user_group_']").css("background-color", "#eee");
		$("#div_user_group_"+id).css("background-color", "#d6e8fe");
		 
		$("div[id^='user_group_comment_']").fadeOut(1);
		if($("#user_group_comment_"+id).html() != ""){
			$("#user_group_comment_"+id).fadeIn(1);
		}
	
		$("div[id^='devider_']").fadeOut(1, function(){ 
			for(var i=0; i<params.length; i++){ 
				$("#devider_"+params[i]).fadeIn(1);   
			}
		});
	}
</script>


<div style="padding-top:10px;">

	<table width="100%" cellpadding="0" cellspacing="0" class="user_info_container">
		<tr>
			<td width="50%">
				<h2>Основная информация</h2>
				<table  border="0" cellpadding="0" cellspacing="10" width="100%"  class="form">		
					<tr>
						<td class="title">Полное имя:</td>
						<td><?php echo $this->vars['user']['name']; ?></td>
					</tr>
					<tr>
						<td class="title">Email:</td>
						<td><?php echo $this->vars['user']['email']; ?></td>
					</tr>
					<tr>
						<td class="title">Ник имя:</td>
						<td><?php echo $this->vars['user']['nickname']; ?></td>
					</tr>
					<tr>
						<td class="title">&nbsp;</td>
						<td style="text-align: left;"><a href="../Change/">Изменить информацию</a></td>
					</tr>
				</table>
				<br/>
				
				<h2>Телефонные номера</h2>
				<div id="phones_block"></div>
			
			</td>
			
			<td  width="50%"> 
			<h2>Дополнительная информация</h2>
				<?php
			reset($this->vars['/category/'][$__key]);
			while(list($_category_key,) = each($this->vars['/category/'][$__key])):
			?>
					<div id="devider_<?php echo $this->vars['/category/'][$__key][$_category_key]['param_id']; ?>" <?php if(AUTH_USER_GROUP_EDITABLE || empty($this->global_vars['user_group'])): ?>style="display:none;"<?php endif; ?>> 
						<table  border="0" cellpadding="0" cellspacing="10" width="100%"  class="form">
						<?php
			reset($this->vars['/category/row/'][$_category_key]);
			while(list($_category_row_key,) = each($this->vars['/category/row/'][$_category_key])):
			?>
							<tr> 
								<td class="title"><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['name']; ?><?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['required']): ?><?php endif; ?>:</td>
								<td>
									<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='char' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='decimal' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='text' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='html' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='date'): ?>
										<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['value']; ?>
						
									<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='file_list'): ?>
										<div id="upload_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['table_name']; ?>_<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['uniq_name']; ?>">
											<?php
			reset($this->vars['/category/row/uploads/'][$_category_row_key]);
			while(list($_category_row_uploads_key,) = each($this->vars['/category/row/uploads/'][$_category_row_key])):
			?>
											<div id="file_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['table_name']; ?>_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['field']; ?>_<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?>">
								  				<img src="/img/shared/ico/<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['icon']; ?>.gif" border="0" align="absmiddle">
												<a target=_blank href="<?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['file_url']; ?>"><?php echo $this->vars['/category/row/uploads/'][$_category_row_key][$_category_row_uploads_key]['filename']; ?></a>
											</div>
											<?php 
			endwhile;
			?>
										</div>
									<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='file'): ?>
										<?php if(!empty($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'])): ?>
											<a target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['file']; ?>">Посмотреть файл</a>
										<?php endif; ?>
									<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='image'): ?>
										<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'] != '#' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb'] != '#'): ?>
											Посмотреть картинку [
												<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['file'] != '#'): ?><a rel="lightbox" target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['file']; ?>">оригинальная: <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['width']; ?> x <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['height']; ?></a>;<?php endif; ?>
												<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb'] != '#'): ?><a rel="lightbox" target="_blank" href="<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb']; ?>">пиктограмма: <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb_width']; ?> x <?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['thumb_height']; ?></a><?php endif; ?>
											]
										<?php endif; ?>
									<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='bool'): ?>
										<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['value']!=0): ?>Да<?php else: ?>Нет<?php endif; ?>
									<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey' || $this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='multiple'): ?>
										<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['option']; ?>
									<?php elseif($this->vars['/category/row/'][$_category_key][$_category_row_key]['data_type']=='fkey_table'): ?>
										<select name="param[<?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['param_id']; ?>]" disabled>
											<option value="0">Сделайте выбор</option>
											<?php if($this->vars['/category/row/'][$_category_key][$_category_row_key]['is_tree']): ?><?php echo $this->vars['/category/row/'][$_category_key][$_category_row_key]['options']; ?><?php else: ?><?php echo TemplateUDF::html_options(array('options'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['options'],'selected'=>$this->vars['/category/row/'][$_category_key][$_category_row_key]['value'])); ?><?php endif; ?>
										</select>
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
			</td>
		</tr>
		<tr><td colspan="2">&nbsp;</td></tr>
	</table>
</div>
