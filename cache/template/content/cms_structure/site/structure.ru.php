<script language="JavaScript" type="text/javascript">
/*
function cw(id) {
	EditorWindow('event=editor/content&id=' + id + '&table_name=site_structure&field_name=content_ru', 'editor'+ id);
	return false;
}
*/
function copy_structure() {
	var id = cmsView.getChecked('form_1');
	AjaxRequest.send('form_copy', '/action/admin/site/structure_copy/', 'Идёт копирование', true, {'id' : id});
}
</script>
<style>
#structure_copy {
	margin-top: -100px;
	margin-left: -200px;
	width: 400px;
	height: 120px;
	border: 4px solid #888;
	padding: 20px;
	position: absolute;
	top: 50%;
	left: 50%;
	z-index: 300;
	background-color: #c4dded;
	display: none;
}
</style>
<div id="structure_copy">
	<form id="form_copy">
		Скопировать выбранные разделы в:<br>
		<select name="to_id" class="tree"><?php echo $this->vars['tree']; ?></select><p>
		Установить шаблон дизайна:<br>
		<select name="template_id"><option value="0">Без изменений</option><?php echo TemplateUDF::html_options(array('options'=>$this->vars['template'])); ?></select><p>
		<input type="button" value="Копировать" style="float:right;" onclick="copy_structure();return false;">
		<input type="button" value="Отмена" style="float:right;" onclick="$('#structure_copy').jqmHide();">
	</form>
</div>

<?php echo $this->vars['cms_view']; ?>


<?php if($this->vars['links']>0): ?>
	<DIV class="context_help"><b>Вы можете редактировать разделы и вложенные в них страницы:</b>
	<?php
			reset($this->vars['/links/'][$__key]);
			while(list($_links_key,) = each($this->vars['/links/'][$__key])):
			?>
		<li><a href="/Admin/Structure/?structure_id=<?php echo $this->vars['/links/'][$__key][$_links_key]['structure_id']; ?>&last_inserted_id=<?php echo $this->vars['/links/'][$__key][$_links_key]['id']; ?>"><?php echo $this->vars['/links/'][$__key][$_links_key]['name']; ?></a>
	<?php 
			endwhile;
			?>
	</DIV>
<?php endif; ?>
<div class="context_help">
	<b>Изменить порядок сортировки страниц по полю:</b>
	<li>Название в меню - 
		<a href="/<?php echo LANGUAGE_URL; ?>action/admin/site/structure_order/?order=name&structure_id=<?php echo $this->vars['structure_id']; ?>&direction=asc&_language=<?php echo LANGUAGE_CURRENT; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">в алфавитном порядке</a>
		| <a href="/<?php echo LANGUAGE_URL; ?>action/admin/site/structure_order/?order=name&structure_id=<?php echo $this->vars['structure_id']; ?>&direction=desc&_language=<?php echo LANGUAGE_CURRENT; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">в обратном порядке</a>
	</li>
	<li>Имя файла - 
		<a href="/<?php echo LANGUAGE_URL; ?>action/admin/site/structure_order/?order=uniq_name&direction=asc&structure_id=<?php echo $this->vars['structure_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">в алфавитном порядке</a>
		| <a href="/<?php echo LANGUAGE_URL; ?>action/admin/site/structure_order/?order=uniq_name&direction=desc&structure_id=<?php echo $this->vars['structure_id']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>">в обратном порядке</a>
	</li>
</div>

<?php echo $this->vars['cms_gallery']; ?>


<?php if(IS_DEVELOPER): ?>
	<form action="/action/admin/sdk/structure_xml_upload/" method="post" enctype="multipart/form-data">
		<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input type="hidden" name="structure_id" value="<?php echo $this->vars['structure_id']; ?>"> 
		<div id="structure_xml" style="border:1px solid silver;background-color:#F0F0F0;padding:5px 5px 5px 20px;">
				XML файл со структурой сайта:<input type="file" name="structure">
				<input type="submit" value="Импортировать" >
		</div>
	</form>
<?php endif; ?>