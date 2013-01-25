function blog_create_swf_config(parent_id) {
	var swfu_param = {
		upload_url: "/action/blog/swf_upload/",
		post_params: {
			'parent_id': parent_id,
			'auth_id': getCookie('auth_id'),
			'auth_code': getCookie('auth_code')
		},
		file_size_limit : 0,	// 2MB
		file_types : "*.jpg;*.jpeg;*.gif;*.png",
		file_types_description : "Image Files",
		file_upload_limit : 0,
		file_queued_handler : blog_fileQueued,
		file_queue_error_handler : blog_fileQueueError,
		file_dialog_complete_handler : blog_uploadDialogComplete,
		upload_start_handler : blog_uploadStart,
		upload_progress_handler : blog_uploadProgress,
		upload_error_handler : blog_uploadError,
		upload_success_handler : blog_uploadSuccess,
		upload_complete_handler : blog_uploadComplete,
		flash_url : "/extras/swfupload/swfupload.2.2.0.beta3.swf",
		custom_settings : {
				'upload_target': 'gallery_container',
				'status_target': 'gallery_upload_status'
		},
		debug: false,
			
		// Button settings
		button_image_url: "/design/cms/img/icons/swf_add_sprite.png",	// Relative to the Flash file
		button_width: "175",
		button_height: "20",
		button_placeholder_id: "spanSWFUploadButton",
		button_text: '<span class="theFont">Добавить фотографии</span>',
		button_text_style: ".theFont { font-size: 13px; font-family: verdana; }",
		button_text_left_padding: 18,
		button_text_top_padding: -2
	}
	return swfu_param;
}

function blog_swf_upload_delete(id, extension) {
	AjaxRequest.send('', '/action/blog/swf_upload_delete/', 'Удаление файла', true, {'return_path':'void', 'id':id, 'extension': extension});
}

/* Обработчики событий */
function blog_uploadDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function blog_fileQueued(file) {
	try {
		var my_div = document.createElement('div');
		my_div.id = 'file_'+file.name;
		
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
function blog_uploadStart(file) {
	try {
		var my_div = document.getElementById('file_'+file.name)
		my_div.className = 'file_upload_start';
	} catch (ex) {
		this.debug(ex);
	}
}
function blog_uploadProgress(file, bytesLoaded) {
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
function blog_uploadSuccess(file, serverData) {
	try {
		var script_fragment = '\\[script\\](.*)\\[\\/script\\]';
		var r = new RegExp(script_fragment, 'm');
		var script = serverData.match(r);
		serverData = serverData.replace(r, '');
		
		if (script!=null) {
			eval(script[1]);
		}
		
		byId('gallery_container').innerHTML = byId('gallery_container').innerHTML + serverData;
	} catch (ex) {
		this.debug(ex);
	}
}
function blog_uploadComplete(file) {
	try {
		if (this.getStats().files_queued > 0) {
			this.startUpload();
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function blog_fileQueueError(file, errorCode, message) {
	alert(message);
}
function blog_uploadError(file, errorCode, message) {
	alert(message);
}
