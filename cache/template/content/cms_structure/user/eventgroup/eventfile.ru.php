<?php echo $this->vars['cms_view']; ?>

<DIV class="context_help">
	� ������� "��������" ������� ��� ����� - �������.
	���� ����� � ���� ������� ����� ����� ����, �� ������ ����� �� �������� - �� ����������.
</DIV>

<?php if(!empty($this->vars['action_files'])): ?>
	<H2>����� �������, ������� �� �������� � �������:</H2>
	
	<?php
			reset($this->vars['/file/'][$__key]);
			while(list($_file_key,) = each($this->vars['/file/'][$__key])):
			?>
	<li><a onclick="EditWindow(0, <?php echo $this->global_vars['table_id']; ?>, '<?php echo CMS_STRUCTURE_URL; ?>', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', 'name=<?php echo $this->vars['/file/'][$__key][$_file_key]['file']; ?>&module_id=<?php echo $this->global_vars['module_id']; ?>&description_ru=<?php echo $this->vars['/file/'][$__key][$_file_key]['description']; ?>')" href="javascript:void();"><?php echo $this->vars['/file/'][$__key][$_file_key]['file']; ?></a></li>
	<?php 
			endwhile;
			?>
<?php endif; ?>
