/**
* ������ ������ ����� �������������� ���������
*/

(function()
{

	CKEDITOR.plugins.add('cms-language',
	{
		requires : [ 'richcombo' ],

		init : function( editor )
		{
			var config = editor.config;
			
			// ������ ��������� ������
			var names = config.cmslanguage_languages.split( ';' ),
				values = [];
	
			// ������������ ������ ������
			var language_names = {};
			var language_codes = {};
			var current_langugage_name = '';
			
			for ( var i = 0 ; i < names.length ; i++ ) {
				var parts = names[ i ];
				if ( parts )
				{
					parts = parts.split( '/' );
	
					var name  = names[ i ] = parts[ 0 ];
					var value = parts[ 1 ] || name;
	
					language_codes[ i ]  = name;
					language_names[ i ] = value;
					
					if (language_codes[i] == config.cmslanguage_current_language) {
						current_langugage_name = language_names[i]
					}
					
				}
				else
					names.splice( i--, 1 );
			}
	
			// ���������� ����������� ������ � ��������� ���������
			editor.ui.addRichCombo( 'CmsLanguage',
				{
					label : '',
					title : '�������� ����',
					className : 'cke_format',
					panel :
					{
						css : editor.skin.editor.css.concat( editor.config.contentsCss ),
						multiSelect : false,
						attributes : { 'aria-label' : '�������� ����3' }
					},
	
					init : function()
					{
						this.startGroup( '�������� ����' );
						for ( var i = 0 ; i < names.length ; i++ ) {
							// ���������� ����� � ������
							this.add( language_codes[i], language_names[i], language_names[i] );
						}
						this.setValue(config.cmslanguage_current_language, current_langugage_name)
					},
	
					onClick : function( value )
					{
						if (value == config.cmslanguage_current_language) {
							return;
						}
						
						if (editor.checkDirty()) {
							if (!confirm("��������!\n\n������� �������� �� ���������. �������� ��� ��������� �� ���� �������� � ������� ��� �������������� �������� �� ������ �����?")) {
								return;
							}
						}
						var id = document.getElementById('editor_id').value;
						var table_name = document.getElementById('editor_table_name').value;
						var field_name = document.getElementById('editor_field_name').value;
						location.href = '/tools/ckeditor/ckeditor.php?event=editor/content&id='+id+'&table_name='+table_name+'&field_name='+field_name.replace(/_[a-z]{2}/, '_'+value);
					},

				});
		}
	});
})();

CKEDITOR.config.cmslanguage_languages = 'ru/�������;en/English;';
CKEDITOR.config.cmslanguage_current_language = 'ru';