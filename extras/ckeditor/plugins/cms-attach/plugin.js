(function()
{
	var pluginName = 'cms-attach';

	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			CKEDITOR.dialog.addIframe(
                'cms-attach',
                'Прикрепить файл',
                this.path + 'dialogs/attach.php?t='+CKEDITOR.timestamp,
                300,
                60,
                function(){
                }
            );
			
			editor.addCommand( pluginName, new CKEDITOR.dialogCommand(pluginName) );
			editor.ui.addButton( 'CmsAttach',
				{
					label : 'Прикрепить файл',
					click: function(editor) {
						CKEDITOR.config.activeEditorName = editor.name
						editor.execCommand( pluginName );
					},
					icon: '/design/cms/img/editor/attach.gif'
				});		
		}
	});
	
})();
