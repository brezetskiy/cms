(function()
{
	var pluginName = 'cms-image';

	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			CKEDITOR.dialog.addIframe(
                pluginName,
                'Вставка картинки',
                this.path + 'dialogs/image.php?t='+CKEDITOR.timestamp,
                420,
                360,
                function(){
                }
            );
			
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand(pluginName) );
			editor.ui.addButton( 'CmsImage',
				{
					label : 'Вставка картинки',
					click: function(editor) {
						CKEDITOR.config.activeEditorName = editor.name
						editor.execCommand( pluginName );
					},
					icon: this.path + 'dialogs/image.png'
				});
		}
	});
	
})();
