function gallery_create_swf_config(group_table_name, parent_id) {
	var swfu_param = {
		upload_url: "/action/admin/gallery/swf_upload/",
		post_params: {
			'group_table_name': group_table_name,
			'parent_id': parent_id,
			'auth_id': getCookie('auth_id'),
			'auth_code': getCookie('auth_code'),
			'max_height': max_height
		},
		file_size_limit : 0,	// 2MB
		file_types : "*.jpg;*.jpeg;*.gif;*.png;*.mp3;*.flv;*.mp4;*.aac",
		file_types_description : "Image Files",
		file_upload_limit : 0,
		file_queued_handler : gallery_fileQueued,
		file_queue_error_handler : gallery_fileQueueError,
		file_dialog_complete_handler : gallery_uploadDialogComplete,
		upload_start_handler : gallery_uploadStart,
		upload_progress_handler : gallery_uploadProgress,
		upload_error_handler : gallery_uploadError,
		upload_success_handler : gallery_uploadSuccess,
		upload_complete_handler : gallery_uploadComplete,
		flash_url : "/extras/swfupload/swfupload.2.2.0.beta3.swf",
		custom_settings : {
				'upload_target': 'gallery_container',
				'status_target': 'gallery_upload_status'
//				'field' : field
//				'table_name' : table_name,
		},
		debug: false,
			
		// Button settings
		button_image_url: "/design/cms/img/icons/swf_add_sprite.png",	// Relative to the Flash file
		button_width: "160",
		button_height: "20",
		button_placeholder_id: "spanSWFUploadButton",
		button_text: '<span class="theFont">Добавить картинки</span>',
		button_text_style: ".theFont { font-size: 13px; font-family: verdana; }",
		button_text_left_padding: 18,
		button_text_top_padding: -2
	}
	return swfu_param;
}

function gallery_swf_upload_delete(id, table_name, field_name, extension) {
	AjaxRequest.send('', '/action/admin/gallery/swf_upload_delete/', 'Удаление файла', true, {'return_path':'void', 'id':id, 'table_name':table_name, 'field_name':field_name, 'extension': extension});
}

/* Обработчики событий */
function gallery_uploadDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		this.addPostParam('max_height', max_height)
		if (numFilesQueued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function gallery_fileQueued(file) {
	try {
		var my_div = document.createElement('div');
		my_div.id = 'file_'+this.customSettings.table_name+'_'+this.customSettings.field+'_'+file.name;
		
		my_div.innerHTML = file.name+' ';
		my_div.className = 'file_upload';
		
		var my_status = document.createElement('span');
		my_status.id = 'status_'+file.id;
		my_status.innerHTML = 'в очереди...';
		my_status.className = 'file_status';
		
		my_div.appendChild(my_status);
		
		document.getElementById( this.customSettings.status_target ).insertBefore(my_div, document.getElementById( this.customSettings.status_target ).firstChild);
	} catch (ex) {
		this.debug(ex);
	}
}
function gallery_uploadStart(file) {
	try {
		var my_div = document.getElementById('file_'+this.customSettings.table_name+'_'+this.customSettings.field+'_'+file.name)
		my_div.className = 'file_upload_start';
	} catch (ex) {
		this.debug(ex);
	}
}
function gallery_uploadProgress(file, bytesLoaded) {
	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);
		var my_status = document.getElementById('status_'+file.id);
		if (percent==100) {
			my_status.parentNode.parentNode.removeChild(my_status.parentNode);
		} else {
			my_status.innerHTML = percent+'%';
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function gallery_uploadSuccess(file, serverData) {
	try {
		var script_fragment = '\\[script\\](.*)\\[\\/script\\]';
		var r = new RegExp(script_fragment, 'm');
		var script = serverData.match(r);
		serverData = serverData.replace(r, '');
		
		if (script!=null) {
			eval(script[1]);
		}
		
		this.addPostParam('max_height', max_height)
		
		byId('gallery_container').innerHTML = byId('gallery_container').innerHTML + serverData;
	} catch (ex) {
		this.debug(ex);
	}
}
function gallery_uploadComplete(file) {
	try {
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function gallery_fileQueueError(file, errorCode, message) {
	alert(message);
}
function gallery_uploadError(file, errorCode, message) {
	alert(message);
}
