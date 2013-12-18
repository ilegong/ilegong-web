/*
	A simple class for displaying file information and progress
	Note: This is a demonstration only and not part of SWFUpload.
	Note: Some have had problems adapting this class in IE7. It may not be suitable for your application.
*/

// Constructor
// file is a SWFUpload file object
// targetID is the HTML element id attribute that the FileProgress HTML structure will be added to.
// Instantiating a new FileProgress object with an existing file will reuse/update the existing DOM elements

function FileProgress(file, targetID) {	
	this.fileProgressID = file.id;
	this.opacity = 100;	
	this.fileProgressWrapper = $('#'+this.fileProgressID,top.document);	
	if (!this.fileProgressWrapper.size()>0) {
		var wrapper_html = '<div id='+this.fileProgressID+' class="progressWrapper  ui-widget-content"><div class="progressContainer">'+
		'<ul style="float:left;width:16px;margin:0;"><li title=".ui-icon-closethick" class="ui-corner-all"><span class="ui-icon ui-icon-closethick"></span></li></ul>'+
		'<div class="progressName">'+file.name+'</div>'+
		'<div class="progress progress-striped"><div class="bar" style="width: 1%;"></div></div>'+
		'</div></div>';	
		
		this.fileProgressWrapper = $(wrapper_html);
		this.progressBar = this.fileProgressWrapper.find('.bar');
		this.fileProgressElement = this.fileProgressWrapper.find('.progressContainer');
		//document.getElementById(targetID).appendChild(this.fileProgressWrapper);
		$('#'+targetID,top.document).append(this.fileProgressWrapper);		
	} else {
		this.fileProgressElement =  this.fileProgressWrapper.find('.progressContainer');	
		this.fileProgressWrapper.find('.progressName').html(file.name);
	}
	
}
FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressElement.removeClass();
	this.fileProgressElement.addClass("progressContainer green");
	this.progressBar.width(percentage+'%');
};
FileProgress.prototype.setComplete = function () {
	this.appear();	
	this.fileProgressElement.removeClass();
	this.fileProgressElement.addClass("progressContainer ui-state-highlight");
	var oSelf = this;
	
	this.fileProgressWrapper.find('.ui-icon-closethick').removeClass('ui-icon-closethick').addClass('ui-icon-check').unbind('click');
	
	setTimeout(function () {
		oSelf.disappear();
	}, 3000);
};
FileProgress.prototype.setError = function () {
	
	this.fileProgressElement.removeClass();
	this.fileProgressElement.addClass("progressContainer red");
	var oSelf = this;	
	this.appear();	
	setTimeout(function () {
		oSelf.disappear();
	}, 2000);
};
FileProgress.prototype.setCancelled = function () {
	this.fileProgressElement.removeClass();
	this.fileProgressElement.addClass("progressContainer");
	var oSelf = this;	
	this.appear();
	
	setTimeout(function () {
		oSelf.disappear();
	}, 2000);
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	if(show){
		this.fileProgressWrapper.find('.ui-icon-closethick').show();
	}
	else{
		this.fileProgressWrapper.find('.ui-icon-closethick').hide();
	}
	var oSelf = this;	
	//this.fileProgressElement.childNodes[0].style.visibility = show ? "visible" : "hidden";
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressWrapper.find('.ui-icon-closethick').click(function () {
			swfUploadInstance.cancelUpload(fileID,true);
			setTimeout(function () {
				oSelf.disappear();
			}, 3000);
			return false;
		});
	}
};

// Makes sure the FileProgress box is visible
FileProgress.prototype.appear = function () {
	this.fileProgressWrapper.fadeIn('fast');
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {
	this.fileProgressWrapper.fadeOut(2000);
};