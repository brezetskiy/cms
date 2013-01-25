function collab_create_swf_config(table_name, field, id) {
	var swfu_param = {
		upload_url: "/action/collab/swf_upload/",
		post_params: {
			'id': id,
			'table_name': table_name,
			'field':field,
			'auth_id': getCookie('auth_id'),
			'auth_code': getCookie('auth_code')
		},
		file_size_limit : 0,	// 2MB
		file_types : "*.*",
		file_types_description : "Files",
		file_upload_limit : 0,
		file_queued_handler : collab_fileQueued,
		file_queue_error_handler : collab_fileQueueError,
		file_dialog_complete_handler : collab_uploadDialogComplete,
		upload_start_handler : collab_uploadStart,
		upload_progress_handler : collab_uploadProgress,
		upload_error_handler : collab_uploadError,
		upload_success_handler : collab_uploadSuccess,
		upload_complete_handler : collab_uploadComplete,
		flash_url : "/extras/swfupload/swfupload.2.2.0.beta3.swf",
		custom_settings : {
				'upload_target': 'upload_'+table_name+'_'+field,
				'table_name' : table_name,
				'field' : field,
				'id' : id
		},
		debug: false,
		
		// Button settings
		button_image_url: "/design/cms/img/icons/swf_add_sprite.png",	// Relative to the Flash file
		button_width: "160",
		button_height: "20",
		button_placeholder_id: "spanSWFUploadButton",
		button_text: '<span class="theFont">Добавить файлы</span>',
		button_text_style: ".theFont { font-size: 13px; font-family: verdana; }",
		button_text_left_padding: 18,
		button_text_top_padding: -2
	}
	return swfu_param;
}

function collab_swf_upload_delete(id, table_name, field, file_name) {
	AjaxRequest.send('', '/action/collab/swf_upload_delete/', 'Удаление файла', true, {'id':id, 'table_name':table_name, 'file_name':file_name, 'field':field});
}

/* Обработчики событий */
function collab_uploadDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function collab_fileQueued(file) {
	try {
		var my_div = document.createElement('div');
		my_div.id = 'file_'+this.customSettings.table_name+'_'+this.customSettings.field+'_'+file.name;
		
		my_div.innerHTML = file.name;
		my_div.className = 'file_upload';
		
		var my_status = document.createElement('div');
		my_status.id = 'status_'+file.id;
		my_status.innerHTML = 'в очереди...';
		my_status.className = 'file_status';
		
		my_div.appendChild(my_status);
		
		document.getElementById( this.customSettings.upload_target ).insertBefore(my_div, document.getElementById( this.customSettings.upload_target ).firstChild);
	} catch (ex) {
		this.debug(ex);
	}
}
function collab_uploadStart(file) {
	try {
		var my_div = document.getElementById('file_'+this.customSettings.table_name+'_'+this.customSettings.field+'_'+file.name)
		my_div.className = 'file_upload_start';
	} catch (ex) {
		this.debug(ex);
	}
}
function collab_uploadProgress(file, bytesLoaded) {
	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);
		var my_status = document.getElementById('status_'+file.id);
		my_status.innerHTML = (percent == 100) ? 'подождите...' : percent+'%';
	} catch (ex) {
		this.debug(ex);
	}
}
function collab_uploadSuccess(file, serverData) {
	try {
		document.getElementById('file_'+this.customSettings.table_name+'_'+this.customSettings.field+'_'+file.name).innerHTML = serverData;
	} catch (ex) {
		this.debug(ex);
	}
}
function collab_uploadComplete(file) {
	try {
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function collab_fileQueueError(file, errorCode, message) {
	alert(message);
}
function collab_uploadError(file, errorCode, message) {
	alert(message);
}
