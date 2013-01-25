// Список файлов, которые стоят в очереди на закачку. Этот список нужен для того, чтоб
// файлы загружались В обратном порядке
var file_upload_queue = new Array();

function imageDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		Editor.ImageSaveParam();
		this.addPostParam('auth_id', getCookie('auth_id'));
		this.addPostParam('auth_code', getCookie('auth_code'));
		this.addPostParam('thumb_width', document.getElementById('img_thumb_width').value);
		this.addPostParam('thumb_height', document.getElementById('img_thumb_height').value);
		this.addPostParam('watermark', document.getElementById('img_watermark').checked);
		Editor.hideDialog('dialog_image');
		if (numFilesQueued > 0) {
			Editor.openDialog('dialog_upload');
			this.startUpload(file_upload_queue.pop());
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function attachDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesQueued > 0) {
			Editor.openDialog('dialog_upload', '');
			this.startUpload(file_upload_queue.pop());
		}
	} catch (ex) {
		this.debug(ex);
	}
}
function fileQueued(file) {
	try {
		var my_div = document.createElement('div');
		my_div.id = 'div_'+file.id;
		my_div.innerHTML = file.name;
		my_div.className = 'file_upload';
		
		var my_status = document.createElement('div');
		my_status.id = 'status_'+file.id;
		my_status.innerHTML = 'в очереди...';
		my_status.className = 'file_status';
		
		my_div.appendChild(my_status);
		
		file_upload_queue[file_upload_queue.length] = file.id; 

		document.getElementById( this.customSettings.upload_target ).insertBefore(my_div, document.getElementById( this.customSettings.upload_target ).firstChild);
	} catch (ex) {
		this.debug(ex);
	}
}
function uploadStart(file) {
	try {
		var my_div = document.getElementById('div_'+file.id)
		my_div.className = 'file_upload_start';
	} catch (ex) {
		this.debug(ex);
	}
}
function uploadProgress(file, bytesLoaded) {
	try {
		var percent = Math.ceil((bytesLoaded / file.size) * 100);
		var my_status = document.getElementById('status_'+file.id);
		my_status.innerHTML = (percent == 100) ? 'создаём пиктограмму...' : percent+'%';
	} catch (ex) {
		this.debug(ex);
	}
}
function imageUploadSuccess(file, serverData) {
	try {
		// Удаляем слой со статусом закачки картинки
		var my_div = document.getElementById('div_'+file.id);
		my_div.parentNode.removeChild(my_div);
		
		if (serverData.indexOf('<object') > 0) { 
			// Flash
			Editor.insertNode(serverData);
		} else {
			var serverData = serverData.split(';');
			
			var my_img = document.createElement('img');
			my_img.src = serverData[0];
			my_img.hspace = document.getElementById('img_hspace').value;
			my_img.vspace = document.getElementById('img_vspace').value;
			my_img.border = (document.getElementById('img_border').checked) ? 1 : 0;
			my_img.alt = file.name;
			
			if (serverData.length > 1) {
				var my_link = document.createElement('a');
				my_link.href = 'javascript:showImage(\'' + serverData[1] + '\');';
				my_link.appendChild(my_img);
				Editor.insertNode(my_link);
			} else {
				Editor.insertNode(my_img);
			}
		}
		
	} catch (ex) {
		this.debug(ex);
	}
}
function attachUploadSuccess(file, serverData) {
	try {
		var my_div = document.getElementById('div_'+file.id);
		my_div.parentNode.removeChild(my_div);
		
		var my_link = document.createElement('a');
		my_link.href = '/tools/cms/site/download.php?url='+serverData+'&name='+encodeURIComponent(file.name);
		my_link.innerHTML = Editor.GetSelectionText();
		Editor.insertNode(my_link);
		
	} catch (ex) {
		this.debug(ex);
	}
}
function uploadComplete(file) {
	try {
		var files_queued = this.getStats().files_queued;
		if (files_queued > 0) {
			this.startUpload(file_upload_queue.pop());
		} else {
			Editor.hideDialog('dialog_upload');
		}
	} catch (ex) {
		this.debug(ex);
	}
}


function fileQueueError(file, errorCode, message) {
	alert(message + '. File: ' + file.name);
}
function uploadError(file, errorCode, message) {
	alert(message + '. File: ' + file.name);
}
