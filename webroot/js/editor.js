
/* CKEditor相关部分 */
// 当前激活的editor，用于insertHTML插入内容
var current_ckeditor_instance = null;

if(typeof(CKEDITOR)!='undefined'){
	CKEDITOR.on( 'currentInstance', function( e ){
		if ( CKEDITOR.currentInstance ){
			current_ckeditor_instance = CKEDITOR.currentInstance;
		}
	});
	CKEDITOR.on( 'focus', function( e ){
		current_ckeditor_instance = e.editor;
		alert('CKEDITOR focus');
	} )	
}

/*保存时，使用jquery触发表单的submit事件，是jquery绑定的submit能生效 */
(function(){
    var saveCmd = {
        modes : { wysiwyg:1, source:1 },
        exec : function( editor ){
            jQuery($form = editor.element.$.form).submit();
        }
    };

    var pluginName = 'safesave';
    // Register a plugin named "save".
    CKEDITOR.plugins.add(pluginName, {
        init : function( editor ){
            var command = editor.addCommand( pluginName, saveCmd );
            command.modes = { wysiwyg : !!( editor.element.$.form ) };

            editor.ui.addButton( 'SafeSave',{
                label     : editor.lang.save,
                command   : pluginName
            });
        }
    });
})();

function insertHTML(html){
	// Get the editor instance that we want to interact with.
	if(current_ckeditor_instance){
		var oEditor = current_ckeditor_instance;
		// Check the active editing mode.
		if (oEditor.mode == 'wysiwyg' ){
			// Insert the desired HTML.
			oEditor.insertHtml(html);
		}
		else{
			alert($.jslanguage.wysiswyg_mode );
		}
	}
	else{	
		alert($.jslanguage.select_editor);
	}	
}