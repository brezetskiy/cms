<script language="JavaScript">
	function load(path, page, is_zip) { 
		$('#content_log').html('');
		AjaxRequest.form('logs', 'Подождите...', {'path':path, 'page':page, 'is_zip':is_zip});  
		return false; 
	}
	
	function log_display(path, sort, destination, page){
		AjaxRequest.send(null, '/<?php echo LANGUAGE_URL; ?>action/admin/cms/logs/view/', 'Загружаю...', true, {'path':path, 'sort':sort, 'destination':destination, 'page':page});
		return false;
	}
</script>

<form id="logs" action="/<?php echo LANGUAGE_URL; ?>action/admin/cms/logs/navigate/" method="POST" enctype="multipart/form-data">
	<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
</form>  


<h1>Логи событий</h1>
<!--<div style="margin:20px;">
	<?php
			reset($this->vars['/archives/'][$__key]);
			while(list($_archives_key,) = each($this->vars['/archives/'][$__key])):
			?>
		<a href="javascript:void(0);" <?php if($this->global_vars['current_archive'] == $this->vars['/archives/'][$__key][$_archives_key]['name']): ?>class="selected"<?php endif; ?>><?php echo $this->vars['/archives/'][$__key][$_archives_key]['name']; ?></a>
	<?php 
			endwhile;
			?>
</div>   -->

<div id="content_navi"></div>
<div class="context_help">
	* Чтобы открыть лог события за определенную дату, пожалуйста, 
	найдите его с помощью навигационного меню выше, и нажмите на соответствующий файл.
</div>


<div id="content_log"></div>