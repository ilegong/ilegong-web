var formChecker = null;
var progress_list = {};

function preLoad() {
	if (!this.support.loading) {
		alert("You need the Flash Player 9.028 or above to use SWFUpload.");
		return false;
	}
}
function loadFailed() {
	alert("Something went wrong while loading SWFUpload. If this were a real application we'd clean up and then give you an alternative");
}
function loadSuccess()
{
}

// Called by the submit button to start the upload
function doSubmit(e) {
	if (formChecker != null) {
		clearInterval(formChecker);
		formChecker = null;
	}
	
	e = e || window.event;
	if (e.stopPropagation) {
		e.stopPropagation();
	}
	e.cancelBubble = true;
	
	try {
		swfu.startUpload();
	} catch (ex) {

	}
	return false;
}

function allUploadComplete()
{
	// 判断文件是否全部提交完，多个上传控件全部完成时，提交表单 。
	var stop_auto_submit = false;
	for(var i=0;i<swfu_array.length;i++){		
		var stats = swfu_array[i].getStats();
		if(stats && stats.files_queued ==0){
			continue;
		}
		else if(stats){
			stop_auto_submit = true;
			swfu_array[i].startUpload();
		}
	}
	if(!stop_auto_submit && form_submit_flag_for_swfupload){
		// 已上传完，表单已提交		
		if(form_submit_obj_for_swfupload){
			$(form_submit_obj_for_swfupload).trigger("submit");
		}
	}
}

function fileQueueError(file, errorCode, message)  {
	try {
		// Handle this error separately because we don't want to create a FileProgress element for it.
		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
			alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
			return;
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			alert("The file you selected is too big.");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			return;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			alert("The file you selected is empty.  Please select another file.");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			return;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			alert("The file you choose is not an allowed file type.");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			return;
		default:
			alert("An error occurred in file queue. Try again later.");
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			return;
		}
	} catch (e) {
	}
}
/* 文件加入上传队列   */
function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progress_target);
		//alert(file.id);alert(progress);
		progress_list[file.id] = progress;
		progress.toggleCancel(true, this);		
	} catch (ex) {
		this.debug(ex);
	}
}

/* 选择好了文件，判断是否开始上传  */
function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		// 是否自动开始上传
		if(this.customSettings.auto_start){
			this.startUpload();
		}
	} catch (ex)  {
        this.debug(ex);
	}
}

/* 上传过程修改进度  */
function uploadProgress(file, bytesLoaded, bytesTotal) {

	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);
		var progress = progress_list[file.id] ;		
		progress.setProgress(percent);
	} catch (e) {
		
	}
}
/* 单文件上传完成  */
function uploadSuccess(file, serverData) {
	try {
		var progress = progress_list[file.id] ;
		progress.setComplete();
		if (serverData === " ") {
			this.customSettings.upload_successful = false;
		} else {
			var data=eval('(' + serverData + ')'); 
			if(data.status==1){
				this.customSettings.upload_successful = true;
				var html = $("#fileuploadinfo_"+data.fieldname).html();
				$("#fileuploadinfo_"+data.fieldname).html(html+data.message);
			}
		}		
	} catch (e) {
		alert(serverData);
	}
}
/** 开始上传  **/
function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		//var progress = new FileProgress(file, this.customSettings.progress_target);
		var progress = progress_list[file.id] ;
		progress.toggleCancel(true, this);
	}
	catch (ex) {}
	
	return true;
}
/** 上传完成  **/
function uploadComplete(file) {
	try {
		if (this.customSettings.upload_successful) {
			
			var stats = this.getStats();
			if(stats && stats.files_queued ==0){
				//this.setButtonDisabled(true);
				// 判断文件是否全部提交完，多个上传控件 。
				allUploadComplete();
			}
			else if(stats){
				this.startUpload();
			}
		} else {
			//var progress = new FileProgress(file, this.customSettings.progress_target);
			var progress = progress_list[file.id] ;
			progress.setError();
			alert("There was a problem with the upload.\nThe server did not accept it.");
		}
	} catch (e) {
	}
}
/** 上传错误  **/
function uploadError(file, errorCode, message) {
	//alert(error);
	try {
		
		if (errorCode === SWFUpload.UPLOAD_ERROR.FILE_CANCELLED) {
			// Don't show cancelled error boxes
			return;
		}
		
		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
			alert("There was a configuration error.  You will not be able to upload a resume at this time.");
			this.debug("Error Code: No backend file, File name: " + file.name + ", Message: " + message);
			return;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			alert("You may only upload 1 file.");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			return;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			break;
		default:
			alert("An error occurred in the upload. Try again later.");
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			return;
		}

		file.id = "singlefile";	
		//var progress = new FileProgress(file, this.customSettings.progress_target);
		var progress = progress_list[file.id] ;
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			this.debug("Error Code: Upload Cancelled, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			this.debug("Error Code: Upload Stopped, File name: " + file.name + ", Message: " + message);
			break;
		}
	} catch (ex) {
	}
}
