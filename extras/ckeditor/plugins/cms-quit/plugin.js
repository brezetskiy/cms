/**
 * @file Обработчик кнопки выхода из редактора
 */

(function()
{
	var quitCmd =
	{
		exec : function( editor )
		{
			if (editor.checkDirty()) {
				if (!confirm("ВНИМАНИЕ!\n\nТекущая страница не сохранена. Отменить все изменения и закрыть редактор?")) {
					return;
				}
			}
			var id = document.getElementById('editor_id').value;
			var table_name = document.getElementById('editor_table_name').value;
			var field_name = document.getElementById('editor_field_name').value;
			location.href = '/tools/editor/close.php?id='+id+'&table_name='+table_name+'&field_name='+field_name;
		}
	};

	var pluginName = 'cms-quit';

	CKEDITOR.plugins.add( pluginName,
	{
		init : function( editor )
		{
			editor.addCommand( pluginName, quitCmd );
			editor.ui.addButton( 'CmsQuit',
				{
					label : 'Закрыть редактор',
					command : pluginName,
					icon: this.path+'quit.png'
				});
		}
	});
})();
