
CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'zh-cn';
	// config.uiColor = '#AADC6E';
	
	config.skin = 'moono';
	config.toolbarCanCollapse = false;
	config.autoUpdateElement = true;
	config.autoGrow_onStartup = false;
	config.resize_enabled = false;
	config.extraPlugins = 'safesave,flvPlayer,portlet,ajax'; //autogrow,
	//removePlugins : 'resize';
	
	config.filebrowserBrowseUrl = ADMIN_BASEURL+'/admin/uploadfiles/filemanage';  
    config.filebrowserImageBrowseUrl = ADMIN_BASEURL+'/admin/uploadfiles/filemanage?path=images';  
    config.filebrowserFlashBrowseUrl = ADMIN_BASEURL+'/admin/uploadfiles/filemanage?path=flashes';  
    config.filebrowserFlvPlayerBrowseUrl = ADMIN_BASEURL+'/admin/uploadfiles/filemanage?path=videos';  
    
    config.filebrowserUploadUrl = ADMIN_BASEURL+'/admin/uploadfiles/upload?no_db=1&no_thumb=1&return=ckeditor';  
    config.filebrowserImageUploadUrl = ADMIN_BASEURL+'/admin/uploadfiles/upload?type=images&no_db=1&no_thumb=1&return=ckeditor';  
    config.filebrowserFlashUploadUrl = ADMIN_BASEURL+'/admin/uploadfiles/upload?type=flashes&no_db=1&no_thumb=1&return=ckeditor';
    config.filebrowserFlvPlayerUploadUrl = ADMIN_BASEURL+'/admin/uploadfiles/upload?type=videos&no_db=1&no_thumb=1&return=ckeditor';
    
    config.filebrowserWindowWidth = '900';  
    config.filebrowserWindowHeight = '600';  
		
	config.toolbar = 'CMS';
	config.toolbar_Full =
	[
	    ['Source','-','Save','NewPage','Preview','-','Templates'],
	    ['Cut','Copy','Paste','PasteText','PasteFromWord','-','Print', 'SpellChecker', 'Scayt'],
	    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	    ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField'],
	    '/',
	    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote','CreateDiv'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['BidiLtr', 'BidiRtl'],
	    ['Link','Unlink','Anchor'],
	    ['Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe'],
	    '/',
	    ['Styles','Format','Font','FontSize'],
	    ['TextColor','BGColor'],
	    ['Maximize', 'ShowBlocks','-','About']
	];
	
	
	config.toolbar_CMS =
	[
	    ['Source','-','SafeSave','Bold','Italic','Underline','Strike','Font','-','TextColor','BGColor','-','Subscript','Superscript'],
	    ['NumberedList','BulletedList','-'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'], 
	    ['Undo','Redo','-','Find','Replace','-','Maximize','SelectAll','RemoveFormat'],
	    [],
	    '/',
	    ['Styles','Format','FontSize'],
	    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
	    ['Link','Unlink','Anchor'],
	    ['Image','Flash','Portlet','flvPlayer','Table','HorizontalRule','SpecialChar','PageBreak'],	    
	    ['-','SpellChecker', 'Scayt','-', 'ShowBlocks','Preview','About']	    
	];
	
};


