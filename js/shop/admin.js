function integrateFckEditor(textarea, height, toolbar_set, table, language, object_id ) {
	
	var oFCKeditor = new FCKeditor( textarea ) ;

	oFCKeditor.BasePath	= '/js/shared/fckeditor/';
	oFCKeditor.Config["CustomConfigurationsPath"] = "/js/shared/fck-config/shop.js?v2"  ;
	
//	oFCKeditor.ToolbarSet = toolbar_set ;
	oFCKeditor.Height = height ;
	
	oFCKeditor.Config['CmsObjectId'] = object_id ;
	oFCKeditor.Config['CmsObjectTempId'] = createUniqCode();
	byId('temp_id_'+textarea).value = oFCKeditor.Config['CmsObjectTempId'];
	oFCKeditor.Config['CmsObjectTable'] = table ;
	oFCKeditor.Config['CmsObjectEditLanguage'] = language ;
	oFCKeditor.Config['CmsObjectImageUploader'] = '/action/admin/editor/fck/image_upload/';
	oFCKeditor.Config['CmsObjectFlashUploader'] = '/action/admin/editor/fck/flash_upload/';
	
	oFCKeditor.ReplaceTextarea() ;
}