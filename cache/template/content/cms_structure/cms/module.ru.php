<?php if($this->vars['show_admin_error']): ?>
	<h1>Ошибки в определении связи "модуль - страница админ. интерфейса"</h1>
	<table class="cms_view">
		<thead>
			<tr>
				<td width="30%">Модули</td>
				<td>Страница</td>
			</tr>
		</thead>
		<?php
			reset($this->vars['/row/'][$__key]);
			while(list($_row_key,) = each($this->vars['/row/'][$__key])):
			?>
		<tr class="<?php echo $this->vars['/row/'][$__key][$_row_key]['class']; ?>">
			<td><?php echo $this->vars['/row/'][$__key][$_row_key]['modules']; ?></td>
			<td><?php echo $this->vars['/row/'][$__key][$_row_key]['name']; ?></td>
		</tr>
		<?php 
			endwhile;
			?>
	</table>
<?php endif; ?>

<?php if($this->vars['show_admin_wo_module']): ?>
	<h1>Страницы админ интерфейса не привязанные к модулям</h1>
	<table class="cms_view">
		<thead>
			<tr>
				<td>URL</td>
			</tr>
		</thead>
		<?php
			reset($this->vars['/row/'][$__key]);
			while(list($_row_key,) = each($this->vars['/row/'][$__key])):
			?>
		<tr class="<?php echo $this->vars['/row/'][$__key][$_row_key]['class']; ?>">
			<td><a href="/Admin/CMS/Structure/?structure_id=<?php echo $this->vars['/row/'][$__key][$_row_key]['structure_id']; ?>&_event_insert_id=<?php echo $this->vars['/row/'][$__key][$_row_key]['id']; ?>"><?php echo $this->vars['/row/'][$__key][$_row_key]['url']; ?></a></td>
		</tr>
		<?php 
			endwhile;
			?>
	</table>
<?php endif; ?>
