(function()
{
	var pluginName = 'cms-structure-link';

	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			var id 		   = editor.config.object_id
			var table_name = editor.config.object_table
			var field_name = editor.config.object_field
			var temp_id    = editor.config.object_temp_id
			
			CKEDITOR.dialog.addIframe(
                pluginName,
                '¬нутренн€€ ссылка',
                this.path + 'dialogs/structure-link.php?id='+id+'&table_name='+table_name+'&field_name='+field_name+'&temp_id='+temp_id+'&editor_name='+editor.name+'&t='+CKEDITOR.timestamp,
                300,
                400,
                function(){
                }
            );
			
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand(pluginName) );
			editor.ui.addButton( 'CmsStructureLink',
				{
					label : '¬нутренн€€ ссылка',
//					command : pluginName,
					click: function(editor) {
						CKEDITOR.config.activeEditorName = editor.name
						editor.execCommand( pluginName );
					},
					icon: this.path + 'dialogs/structure-link.png'
				});		
		}
	});
	
})();