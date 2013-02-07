<html>
<head>
<title><?php echo $this->vars['title']; ?></title>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<meta http-equiv="imagetoolbar" content="no">
<script type="text/javascript" src="/extras/ckeditor/ckeditor.js"></script>
<script type="text/javascript">
	window.onload = function()
	{		
		CKEDITOR.timestamp = ( new Date() ).valueOf();
		
		CKEDITOR.replace('editor1', {
			
			object_id: '<?php echo $this->global_vars['id']; ?>',
		        object_temp_id: '',
		        object_table: '<?php echo $this->global_vars['table_name']; ?>',
		        object_field: '<?php echo $this->global_vars['field_name']; ?>',
			
		        customConfig : '',
		        resize_enabled: true,
		        cmslanguage_languages: '<?php
			reset($this->vars['/language/'][$__key]);
			while(list($_language_key,) = each($this->vars['/language/'][$__key])):
			?><?php echo $this->vars['/language/'][$__key][$_language_key]['code']; ?>/<?php echo $this->vars['/language/'][$__key][$_language_key]['name']; ?>;<?php 
			endwhile;
			?>',
		        cmslanguage_current_language: '<?php echo $this->vars['current_language']; ?>',
		        stylesSet: [
					{ name : 'Заголовок 1'		, element : 'h1' },
					{ name : 'Заголовок 2'		, element : 'h2' },
					{ name : 'Заголовок 3'		, element : 'h3' },
				],
			fontSize_sizes: '8/8px;9/9px;10/10px;11/11px;12/12px;13/13px;14/14px;15/15px;16/16px;17/17px;18/18px;19/19px;20/20px;21/21px;22/22px;23/23px;24/24px;25/25px;26/26px;27/27px;28/28px;36/36px;48/48px;72/72px;',
	
		        on :
				   {
				      'instanceReady' : function( evt )
				      {
				         evt.editor.execCommand( 'maximize' );
				      }
				   } 
		    }
    	);
	};
</script>
</head>
<body style="margin:0; padding:0">
<form action="/action/admin/<?php echo $this->global_vars['event']; ?>" method="post">
<input type="hidden" name="_return_path" value="<?php echo CURRENT_URL_FORM; ?>">
<input type="hidden" name="id" id="editor_id" value="<?php echo $this->global_vars['id']; ?>">
<input type="hidden" name="table_name" id="editor_table_name" value="<?php echo $this->global_vars['table_name']; ?>">
<input type="hidden" name="field_name" id="editor_field_name" value="<?php echo $this->global_vars['field_name']; ?>">
<textarea name="content" id="editor1"><?php echo $this->vars['content']; ?></textarea>
</form>
</body>
</html>