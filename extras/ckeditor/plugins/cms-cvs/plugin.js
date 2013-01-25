(function()
{
	var pluginName = 'cms-cvs';

	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			var id 			= window.parent.document.getElementById('editor_id').value;
			var table_name  = window.parent.document.getElementById('editor_table_name').value;
			var field_name  = window.parent.document.getElementById('editor_field_name').value;
			
			CKEDITOR.dialog.addIframe(
                pluginName,
                'История документа',
                this.path + 'dialogs/cvs-list.php?id='+id+'&table_name='+table_name+'&field_name='+field_name+'&t='+CKEDITOR.timestamp,
                600,
                400,
                function(){
                }
            );
			
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand(pluginName) );
			editor.ui.addButton( 'CmsCvs',
				{
					label : 'История документа',
					command : pluginName,
					icon: this.path + 'dialogs/cvs.png'
				});		
		}
	});
	
})();
