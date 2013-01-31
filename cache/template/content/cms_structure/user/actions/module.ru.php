
<h1>Использование доступных свойств модуля</h1>


<?php if($this->vars['show_change']>0): ?>
	<h3>Таблицы</h3>
	<?php
			reset($this->vars['/table/'][$__key]);
			while(list($_table_key,) = each($this->vars['/table/'][$__key])):
			?>
		<?php if(!empty($this->vars['/table/'][$__key][$_table_key]['db_name'])): ?>
			<b><?php echo $this->vars['/table/'][$__key][$_table_key]['db_name']; ?></b><br>
		<?php endif; ?>
		<input disabled type="checkbox" <?php echo $this->vars['/table/'][$__key][$_table_key]['checked_select']; ?>><input disabled type="checkbox" <?php echo $this->vars['/table/'][$__key][$_table_key]['checked_update']; ?>><?php echo $this->vars['/table/'][$__key][$_table_key]['name']; ?><br>
	<?php 
			endwhile;
			?>
<?php endif; ?>

<?php if($this->vars['show_event']>0): ?>
	<h3>События</h3>
	<?php
			reset($this->vars['/event/'][$__key]);
			while(list($_event_key,) = each($this->vars['/event/'][$__key])):
			?>
		<input disabled type="checkbox" <?php echo $this->vars['/event/'][$__key][$_event_key]['checked']; ?>><?php echo $this->vars['/event/'][$__key][$_event_key]['name']; ?><br>
	<?php 
			endwhile;
			?>
<?php endif; ?>

<?php if($this->vars['show_view']): ?>
<h3>Доступ к разделам</h3>
	<?php echo $this->vars['view']; ?>
	<?php
			reset($this->vars['/structure/'][$__key]);
			while(list($_structure_key,) = each($this->vars['/structure/'][$__key])):
			?>
	<?php echo $this->vars['/structure/'][$__key][$_structure_key]['name']; ?> [<?php echo $this->vars['/structure/'][$__key][$_structure_key]['url']; ?>]<br>
	<?php 
			endwhile;
			?>
<?php endif; ?>