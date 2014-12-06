
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
	
    config.filebrowserUploadUrl = BASEURL+'/uploadfiles/upload?no_db=1&no_thumb=1&return=ckeditor';  
    config.filebrowserImageUploadUrl = BASEURL+'/uploadfiles/upload?type=images&no_db=1&no_thumb=1&return=ckeditor';  
    config.filebrowserFlashUploadUrl = BASEURL+'/uploadfiles/upload?type=flashes&no_db=1&no_thumb=1&return=ckeditor';
    config.filebrowserFlvPlayerUploadUrl = BASEURL+'/uploadfiles/upload?type=videos&no_db=1&no_thumb=1&return=ckeditor';
    
	config.toolbar = 'FRONT';
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
	
	config.toolbar_FRONT =
	[
	    ['Source','-','Bold','Italic','Underline','Strike','Font','-','TextColor','BGColor','-',],
	    ['NumberedList','BulletedList','-'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'], 
	    ['Undo','Redo','-','Find','Replace','-','Maximize','RemoveFormat'],
	    [],
	    '/',
	    ['Styles','Format','FontSize'],
	    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
	    ['Link','Unlink','Anchor'],
	    ['Image','Table','HorizontalRule','SpecialChar','PageBreak'],	    
	    ['-','ShowBlocks','Preview']	    
	];
	
};

CKEDITOR.on('instanceReady', function (ev) {
    ev.editor.dataProcessor.htmlFilter.addRules(
        {
            elements:
            {
                $: function (element) {
                    // Remove width and height style rules from images
                    if (element.name == 'img') {
                        var style = element.attributes.style;

                        if (style) {
                            // Get the width from the style.
                            var match = /(?:^|\s)width\s*:\s*(\d+)px/i.exec(style),
                                width = match && match[1];

                            // Get the height from the style.
                            match = /(?:^|\s)height\s*:\s*(\d+)px/i.exec(style);
                            var height = match && match[1];

                            if (width) {
                                element.attributes.style = element.attributes.style.replace(/(?:^|\s)width\s*:\s*(\d+)px;?/i, '');
                            }

                            if (height) {
                                element.attributes.style = element.attributes.style.replace(/(?:^|\s)height\s*:\s*(\d+)px;?/i, '');
                            }
                        }
                    }

                    if (!element.attributes.style)
                        delete element.attributes.style;

                    return element;
                }
            }
        });
});



