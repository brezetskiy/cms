<?php echo $this->vars['cmsTable']; ?>

<br/>

<?php if(IS_DEVELOPER): ?>
<div id="structure_xml">
	<form action="/action/admin/sdk/structure_xml_upload/" method="post" enctype="multipart/form-data">
		<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
		<input type="hidden" name="structure_id" value="<?php echo $this->vars['structure_id']; ?>"> 
		<input type="hidden" name="table" value="cms_structure"> 
		
		<h4 align="center">Импорт структуры</h4>
		XML файл структуры:<br/><input type="file" name="structure">
		<input type="submit" value="Импорт" >
	</form>
</div>

<?php endif; ?>