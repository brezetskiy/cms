<div id="idTabs" >
	<h1>Настройка параметров модуля <?php echo $this->vars['description']; ?> (<?php echo $this->vars['name']; ?>)</h1>
	<a href="../"><img height="16" width="16" border="0" alt="На уровень вверх" src="/design/cms/img/button/up.gif"> К списку модулей</a>
	<form name="Form" id="Form" enctype="multipart/form-data" action="/<?php echo LANGUAGE_URL; ?>action/admin/cms/cms_settings/" method="POST">
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
	<input type="hidden" name="module_id" value="<?php echo $this->vars['module_id']; ?>">
	<br /> 
	<ul style="padding:0; margin:0;">
	<?php
			reset($this->vars['/capture/'][$__key]);
			while(list($_capture_key,) = each($this->vars['/capture/'][$__key])):
			?>
		<li class="<?php echo $this->vars['/capture/'][$__key][$_capture_key]['divclass']; ?>">
			<a class="<?php echo $this->vars['/capture/'][$__key][$_capture_key]['divclass']; ?>" href="#<?php echo $this->vars['/capture/'][$__key][$_capture_key]['id']; ?>"><?php echo $this->vars['/capture/'][$__key][$_capture_key]['divname']; ?></a> 
		</li>
	<?php 
			endwhile;
			?>
	</ul>
	<div class="delfloat"></div>
	<?php
			reset($this->vars['/capture/'][$__key]);
			while(list($_capture_key,) = each($this->vars['/capture/'][$__key])):
			?>
		<table id="<?php echo $this->vars['/capture/'][$__key][$_capture_key]['id']; ?>" class=cms_view>
			<thead>
			<tr><td colspan=2><?php echo $this->vars['/capture/'][$__key][$_capture_key]['module']; ?> - <?php echo $this->vars['/capture/'][$__key][$_capture_key]['divname']; ?></td></tr>
			</thead>
			<?php
			reset($this->vars['/capture/row/'][$_capture_key]);
			while(list($_capture_row_key,) = each($this->vars['/capture/row/'][$_capture_key])):
			?>
			<tr class="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['class']; ?>">
				<td width="50%"><?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['description']; ?></td>
				<td>
					<?php if($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='file'): ?>
						<input type="file" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" class="wide"><br>
						<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['download_file']; ?>
						<input type="checkbox" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>_del" value="1" id="delete_file_<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>"><label for="delete_file_<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>">Удалить</label><br>
					<?php elseif($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='bool'): ?>
						<input type="checkbox" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" value=1 <?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['checked']; ?>><br>
					<?php elseif($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='fkey'): ?>
						<select name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" class="wide">
							<option value="0">Сделайте выбор</option>
							<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['fkey'],'selected'=>$this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['value'])); ?><br>
						</select>
					<?php elseif($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='enum'): ?>
						<select name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" class="wide">
							<?php echo TemplateUDF::html_options(array('options'=>$this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['enum_values'],'selected'=>$this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['value'])); ?><br>
						</select>
					<?php elseif($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='passwd'): ?>
						<input type="password" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" value="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['value']; ?>" class="wide">
					<?php elseif($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='time'): ?>
						<input type="text" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" value="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['value']; ?>">
						<select name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>_unit"><?php echo TemplateUDF::html_options(array('options'=>$this->global_vars['time_unit'],'selected'=>$this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['time_unit'])); ?></select>
					<?php elseif($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['type']=='byte'): ?>
						<input type="text" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" value="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['value']; ?>">
						<select name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>_unit"><?php echo TemplateUDF::html_options(array('options'=>$this->global_vars['byte_unit'],'selected'=>$this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['byte_unit'])); ?></select>
					<?php else: ?>
						<input type="text" name="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['id']; ?>" value="<?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['value']; ?>" class="wide">
					<?php endif; ?>
					<span class=comment><?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['name']; ?><?php if(!empty($this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['unit'])): ?>, <?php echo $this->vars['/capture/row/'][$_capture_key][$_capture_row_key]['unit']; ?><?php endif; ?></span>
				</td>
			</tr>
			<?php 
			endwhile;
			?>
			<tfoot>
			<tr class="odd">
				<td colspan="2" align="right"><input type="submit" value="Сохранить"></td>
			</tr>
			</tfoot>
		</table>
	<?php 
			endwhile;
			?>
	</form>
</div>
<script type="text/javascript"> 
	$("#idTabs ul").idTabs(); 
</script>
