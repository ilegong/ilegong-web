<?php
class CkeditorHelper extends AppHelper {
	
	function load($id, $toolbar = 'kama') {
        return "<script type=\"text/javascript\">        
$(function(){
	CKEditorAddUploadImage = function(thisDialog){
	    var uploadUrl = '".$this->url('/admin/uploadfiles/listfile/'.$this->_View->getVar('current_model'))."'; //处理文件/图片上传的页面URL	
	    var obj = window.showModalDialog(uploadUrl); 
		thisDialog.getContentElement('info', 'txtUrl').setValue(obj.txtUrl);	   
	    thisDialog.getContentElement('info', 'txtAlt').setValue(obj.txtAlt);	   
	}
	if(CKEDITOR.instances['$id']){
		//CKEDITOR.instances['$id'].destroy(); // 取消编辑器效果，替换为之前的textarea
		CKEDITOR.remove(CKEDITOR.instances['$id']); // 销毁删除对象
	}
	
    CKEDITOR.on( 'instanceReady', function( e ){
		e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/960_24_col.css')."' );
		e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/saecms/jquery-ui-themes.css')."' );
		e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/base.css')."' );
		e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/ui-customer.css')."' );
		//e.editor.setMode( 'source' );
		e.editor.on( 'mode', function( e ){
			if(e.editor.mode == 'wysiwyg'){
				e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/960_24_col.css')."' );
				e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/saecms/jquery-ui-themes.css')."' );
				e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/base.css')."' );
				e.editor.document.appendStyleSheet( '".$this->webroot(CSS_URL.'/ui-customer.css')."' );
			}
		});		
	});
	
    ckeditors['$id'] = CKEDITOR.replace( '$id',{
    	skin : '$toolbar'
	});	
	ckeditors['$id'].on('focus',function(e){
		current_ckeditor_instance = e.editor;
	});
//	addUploadButton(ckeditors['$id']);
});
</script>
"; 
    }
/*
 *  ckeditors['$id'] = CKEDITOR.replace( '$id',{
    	skin : '$toolbar',
    	extraPlugins : 'autogrow',
		removePlugins : 'resize'
	});	
 * */

}
?>