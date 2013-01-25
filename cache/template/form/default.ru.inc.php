<form id="form_<?php echo $this->vars['uniq_name']; ?>" action="/actions_site.php" enctype="multipart/form-data" method="POST" onsubmit="AjaxRequest.form('form_<?php echo $this->vars['uniq_name']; ?>', 'Отправка данных...', {});return false;">
<input type="hidden" name="_event" value="form/send">
<input type="hidden" name="_return_path" value="<?php echo SITE_STRUCTURE_URL; ?>/Ok/">
<input type="hidden" name="form_name" value="<?php echo $this->vars['uniq_name']; ?>">
<input type="hidden" name="current_path" value="<?php echo CURRENT_URL_FORM; ?>">
<?php
			reset($this->vars['/hidden/'][$__key]);
			while(list($_hidden_key,) = each($this->vars['/hidden/'][$__key])):
			?>
	<input type="hidden" name="form[<?php echo $this->vars['/hidden/'][$__key][$_hidden_key]['uniq_name']; ?>]" value="<?php echo $this->vars['/hidden/'][$__key][$_hidden_key]['default_value']; ?>">
<?php 
			endwhile;
			?>
<table class="form">
<?php if(Auth::isAdmin()): ?>
	<tr>
		<td></td>
		<td><a href="/Admin/Site/Forms/Fields/?form_id=<?php echo $this->vars['form_id']; ?>" target="_blank">Редактировать форму</a></td>
	</tr>
<?php endif; ?>
	<?php
			reset($this->vars['/row/'][$__key]);
			while(list($_row_key,) = each($this->vars['/row/'][$__key])):
			?>
		<tr>
		<?php if($this->vars['/row/'][$__key][$_row_key]['type'] == 'devider'): ?>
			<td></td>
			<td class="devider"><?php echo $this->vars['/row/'][$__key][$_row_key]['title']; ?></td>
		<?php else: ?>
			<td class="title"><?php if($this->vars['/row/'][$__key][$_row_key]['required']): ?><span style="color:red;">*</span><?php endif; ?><?php echo $this->vars['/row/'][$__key][$_row_key]['title']; ?>:</td>
			<td>
			<?php if($this->vars['/row/'][$__key][$_row_key]['type'] == 'text'): ?>
				<input class="text" type="text" name="form[<?php echo $this->vars['/row/'][$__key][$_row_key]['uniq_name']; ?>]" value="<?php echo $this->vars['/row/'][$__key][$_row_key]['default_value']; ?>">
			<?php elseif($this->vars['/row/'][$__key][$_row_key]['type'] == 'integer'): ?>
				<input class="text" type="text" name="form[<?php echo $this->vars['/row/'][$__key][$_row_key]['uniq_name']; ?>]" value="<?php echo $this->vars['/row/'][$__key][$_row_key]['default_value']; ?>">
			<?php elseif($this->vars['/row/'][$__key][$_row_key]['type'] == 'file'): ?>
				<input type="file" name="form[<?php echo $this->vars['/row/'][$__key][$_row_key]['uniq_name']; ?>]">
			<?php elseif($this->vars['/row/'][$__key][$_row_key]['type'] == 'enum'): ?>
				<select name="form[<?php echo $this->vars['/row/'][$__key][$_row_key]['uniq_name']; ?>]">
					<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/row/'][$__key][$_row_key]['info'])); ?>
				</select>
			<?php elseif($this->vars['/row/'][$__key][$_row_key]['type'] == 'set'): ?>
				<?php
			reset($this->vars['/row/info/'][$_row_key]);
			while(list($_row_info_key,) = each($this->vars['/row/info/'][$_row_key])):
			?>
				<input type="checkbox" name="form[<?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['uniq_name']; ?>][]" value="<?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['value']; ?>" id="<?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['uniq_name']; ?>_<?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['key']; ?>"><label for="<?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['uniq_name']; ?>_<?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['key']; ?>"><?php echo $this->vars['/row/info/'][$_row_key][$_row_info_key]['value']; ?></label><br>
				<?php 
			endwhile;
			?>
			<?php elseif($this->vars['/row/'][$__key][$_row_key]['type'] == 'textarea'): ?>
				<textarea style="width:80%;height:100px;" name="form[<?php echo $this->vars['/row/'][$__key][$_row_key]['uniq_name']; ?>]"><?php echo $this->vars['/row/'][$__key][$_row_key]['default_value']; ?></textarea>
			<?php endif; ?>
				</td>
		<?php endif; ?>
		</tr>
	<?php 
			endwhile;
			?>
	<?php if(FORM_CAPTCHA && !Auth::isLoggedIn()): ?> 
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
		<td></td>
		<td><?php if(!empty($this->vars['image_button'])): ?> <input type="image" src="<?php echo $this->vars['image_button']; ?>"> <?php else: ?><input type="submit" value="<?php echo $this->vars['button']; ?>"><?php endif; ?></td>
<!--		<td><input type="submit" value="<?php echo $this->vars['button']; ?>"></td>-->
	</tr>
</table>
</form>