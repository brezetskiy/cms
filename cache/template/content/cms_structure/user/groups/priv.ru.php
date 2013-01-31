<script language="JavaScript">
function saveActions() {
	AjaxRequest.send('form_actions', '/<?php echo LANGUAGE_URL; ?>action/admin/cms/group_actions/', 'Идёт сохранение данных', true, {});
}
</script>
<h1>Привилегии пользователей группы "<?php echo $this->vars['group']; ?>"</h1>
<a href="../"><img src="/design/cms/img/button/up.gif" border="0" align="absmiddle"> Вернуться к списку групп</a><br>
<form action="/<?php echo LANGUAGE_URL; ?>action/admin/cms/group_actions/" id="form_actions" onsubmit="saveActions();return false;">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<input type="hidden" name="group_id" value="<?php echo $this->vars['group_id']; ?>">
	<table class="vertical" width="100%">
		<?php
			reset($this->vars['/action/'][$__key]);
			while(list($_action_key,) = each($this->vars['/action/'][$__key])):
			?>
			<tr>
				<?php if(!empty($this->vars['/action/'][$__key][$_action_key]['module'])): ?>
					<td class="title"><b><?php echo $this->vars['/action/'][$__key][$_action_key]['module']; ?>:</b></td>
				<?php else: ?>
					<td></td>
				<?php endif; ?>
				<td><input type="checkbox" <?php echo $this->vars['/action/'][$__key][$_action_key]['checked']; ?> name="action[]" value="<?php echo $this->vars['/action/'][$__key][$_action_key]['id']; ?>" id="action_<?php echo $this->vars['/action/'][$__key][$_action_key]['id']; ?>"><label for="action_<?php echo $this->vars['/action/'][$__key][$_action_key]['id']; ?>"><?php echo $this->vars['/action/'][$__key][$_action_key]['title']; ?></label></td>
			</tr>
		<?php 
			endwhile;
			?>
		<tr>
			<td></td>
			<td><input align="right" type="submit" value="Сохранить"></td>
		</tr>
	</table>
</form>