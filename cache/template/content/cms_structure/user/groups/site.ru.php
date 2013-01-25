<script language="JavaScript">
function save() {
	AjaxRequest.send('form', '/action/admin/auth/group_structure/', 'Идёт сохранение', true, {});
}
</script>

<h1>Доступ к структуре сайта для группы "<?php echo $this->vars['group']; ?>"</h1>
<a href="../"><img src="/design/cms/img/button/up.gif" border="0" align="absmiddle"> Вернуться к списку групп</a><br>
<p>
<div class="context_help">Право редактировать распространяетсяна все подразделы.</div>
<p>

<input type="checkbox" onclick="(this.checked) ? $('#structure input').attr('checked', 'checked'):$('#structure input').removeAttr('checked');" id="all">
<label for="all"><b>Выделить/снять выделене</b></label>

<form id="form" action="/action/admin/cms/auth_group_structure" onsubmit="save(); return false;">
	<input type="hidden" name="group_id" value="<?php echo $this->vars['group_id']; ?>">
	<pre id="structure" style="font-family:Verdana;">
	<?php echo $this->vars['structure']; ?>
	</pre>
	<input type="submit" value="Сохранить">
</form>