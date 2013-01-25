<div id="adminbar_holder" <?php if($this->vars['adminbar_mode']=='hidden'): ?>style='width:140px;'<?php endif; ?>>

<script language="JavaScript">
var adminbar_mode = '<?php echo $this->vars['adminbar_mode']; ?>';
</script>

<div id="adminbar_logo"><a href='/Admin/'><img alt='Система управления сайтом "Пилот"' border=0 src="/design/cms/img/ui/delta_logo_small_color.gif"></a> <a href="#" onclick="return toggleAdminBar();"><img id="adminbar_toggle" src="/design/cms/img/ui/fam/control_<?php if($this->vars['adminbar_mode']=='hidden'): ?>play<?php else: ?>back<?php endif; ?>_blue.gif"></a></div>
<div id="adminbar_panel">
	<?php
			reset($this->vars['/button/'][$__key]);
			while(list($_button_key,) = each($this->vars['/button/'][$__key])):
			?>
		<?php if(($this->vars['/button/'][$__key][$_button_key]['type'] == 'editor')): ?>
			<a href="#" onclick="EditorWindow('event=editor/content&id=<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>&table_name=<?php echo $this->vars['/button/'][$__key][$_button_key]['table_name']; ?>&field_name=content_<?php echo LANGUAGE_CURRENT; ?>', 'editor<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>'); return false;" title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>"><img class="ico" align="absmiddle" src="/img/cms/adminbar/<?php echo $this->vars['/button/'][$__key][$_button_key]['img']; ?>" alt="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" hspace="10"><?php if(!$this->global_vars['short']): ?><?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?><?php endif; ?></a>&nbsp;&nbsp;
		<?php elseif($this->vars['/button/'][$__key][$_button_key]['type'] == 'cms_edit'): ?>
			<a href="#" onclick="EditWindow('<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>', '<?php echo $this->vars['/button/'][$__key][$_button_key]['table_name']; ?>', 'Structure/', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '');return false;" title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>"><img class="ico" align="absmiddle" src="/img/cms/adminbar/<?php echo $this->vars['/button/'][$__key][$_button_key]['img']; ?>" alt="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" hspace="10"><?php if(!$this->global_vars['short']): ?><?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?><?php endif; ?></a>&nbsp;&nbsp;
		<?php elseif($this->vars['/button/'][$__key][$_button_key]['type'] == 'link'): ?>
			<a title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" href="<?php echo $this->vars['/button/'][$__key][$_button_key]['url']; ?>" title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>"><img class="ico" align="absmiddle" src="/img/cms/adminbar/<?php echo $this->vars['/button/'][$__key][$_button_key]['img']; ?>" alt="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" hspace="10"><?php if(!$this->global_vars['short']): ?><?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?><?php endif; ?></a>&nbsp;&nbsp;
		<?php elseif($this->vars['/button/'][$__key][$_button_key]['type'] == 'cms_add'): ?>
			<a title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" href="#" onclick="EditWindow(0, '<?php echo $this->vars['/button/'][$__key][$_button_key]['table_name']; ?>', 'Structure/', '<?php echo CURRENT_URL_LINK; ?>', '<?php echo LANGUAGE_CURRENT; ?>', '<?php echo $this->vars['/button/'][$__key][$_button_key]['param']; ?>');return false;"><img class="ico" align="absmiddle" src="/img/cms/adminbar/<?php echo $this->vars['/button/'][$__key][$_button_key]['img']; ?>" alt="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" hspace="10"><?php if(!$this->global_vars['short']): ?><?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?><?php endif; ?></a>&nbsp;&nbsp;
		<?php elseif($this->vars['/button/'][$__key][$_button_key]['type'] == 'editor_php'): ?>
			<a title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" href="#" onclick="EditScript('id=<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>&extention=php&table_name=<?php echo $this->vars['/button/'][$__key][$_button_key]['table_name']; ?>&field_name=content_<?php echo LANGUAGE_CURRENT; ?>&file=1', 'editor<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>'); return false;"><img class="ico" align="absmiddle" src="/img/cms/adminbar/<?php echo $this->vars['/button/'][$__key][$_button_key]['img']; ?>" alt="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" hspace="10"><?php if(!$this->global_vars['short']): ?><?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?><?php endif; ?></a>&nbsp;&nbsp;
		<?php elseif($this->vars['/button/'][$__key][$_button_key]['type'] == 'editor_tmpl'): ?>
			<a title="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" href="#" onclick="EditScript('id=<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>&extention=tmpl&table_name=<?php echo $this->vars['/button/'][$__key][$_button_key]['table_name']; ?>&field_name=content_<?php echo LANGUAGE_CURRENT; ?>&file=1', 'editor<?php echo $this->vars['/button/'][$__key][$_button_key]['id']; ?>'); return false;"><img class="ico" align="absmiddle" src="/img/cms/adminbar/<?php echo $this->vars['/button/'][$__key][$_button_key]['img']; ?>" alt="<?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?>" hspace="10"><?php if(!$this->global_vars['short']): ?><?php echo $this->vars['/button/'][$__key][$_button_key]['name']; ?><?php endif; ?></a>&nbsp;&nbsp;
		<?php endif; ?>  
	<?php 
			endwhile;
			?>
	<?php if(!empty($this->vars['cvs_versions'])): ?>
		История: <form id="cvs_form" action="" method="get"><select onchange="byId('cvs_form').submit();" name="cvs_version"><option value=0></option><?php echo TemplateUDF::html_options(array('options'=>$this->vars['cvs_versions'],'selected'=>$this->vars['cvs_version'])); ?></select></form>
	<?php endif; ?> 
</div>
<div id="adminbar_user"><img class="ico" src="/design/cms/img/ui/fam/key.gif"><b><?php echo $_SESSION['auth']['login']; ?></b>&nbsp;&nbsp;<a href="/<?php echo LANGUAGE_URL; ?>action/cms/logout/?_return_path=<?php echo CURRENT_URL_LINK; ?>">Выход</a></div>
</div>