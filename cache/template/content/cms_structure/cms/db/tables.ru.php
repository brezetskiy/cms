<div style="text-align:right;padding:10px;">
	<span style="float:right;" class="button">
		<a href="/<?php echo LANGUAGE_URL; ?>action/admin/cms/db_<?php echo $this->vars['db_type']; ?>/?db_alias=<?php echo $_GET['db_alias']; ?>&_return_path=<?php echo CURRENT_URL_LINK; ?>"><img src="/img/sdk/reload.png" border="0" align="absmiddle"> Обновить структуру БД</a>
	</span>
</div>


<?php echo $this->vars['cms_view']; ?>
 
<div id="table_xml">
	<form action="/action/admin/sdk/table_xml_upload/" method="post" enctype="multipart/form-data">
		<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
		
		<h4 align="center">Импорт таблиц</h4> 
		XML файл таблиц:<br/><input type="file" name="table">
		<input type="submit" value="Импорт" >
	</form>
</div>