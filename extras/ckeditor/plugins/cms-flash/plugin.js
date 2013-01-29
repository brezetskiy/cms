(function()
{
	var pluginName = 'cms-flash';

	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			CKEDITOR.dialog.addIframe(
                pluginName,
                '������� flash ������',
                this.path + 'dialogs/flash.php?t='+CKEDITOR.timestamp,
                300,
                10,
                function(){
                }
            );
			
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand(pluginName) );
			editor.ui.addButton( 'CmsFlash',
				{
					label : '������� flash ������',
					click: function(editor) {
						CKEDITOR.config.activeEditorName = editor.name
						editor.execCommand( pluginName );
					},
					icon: this.path + 'dialogs/flash.png'
				});		
		}
	});
	
})();
