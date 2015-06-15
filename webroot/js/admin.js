
var last_open_dialog = null; // 记录最后一次打开的dialog
var jqgrid_scrollOffset = null; //记录jqgrid的滚动条位置； // 触发更新事件时，自动滚动到先前滚动条所在的位置。

$(document).ready(function() {
	page_loaded();
	//alert($.jslanguage.selectAll);
	$('.checkbox input:checkbox').each(function(){
		if(this.value==""){
			$(this).next().html($.jslanguage.selectAll); // 设置值为空的多选项后的label的文字为全选
		}
	});
	
	$('.checkbox input:checkbox').live('click',function(){
		//alert($(this).parents('div').eq(1).find('input:checkbox').size());
		// 取得当前父级第二级，并将内部的checkbox全选		
		if(this.value==""){
			if(this.checked){
				$(this).parents('div:first').find('input:checkbox').attr('checked',true);
			}
			else{
				$(this).parents('div:first').find('input:checkbox').removeAttr('checked');
			}
		}
	});
try{
  $('.datetime').datetimepicker({
    language:  'zh-CN',
    weekStart: 1,
    todayBtn:  1,
    autoclose: 1,
    todayHighlight: 1,
    startView: 2,
    forceParse: 0,
    showMeridian: 1
  });
}catch(e){

}
});
function page_loaded()
{
    $('button').addClass('btn btn-default');
    $('input[type=submit]').addClass('btn btn-primary');    
    /**
     * 绑定上传文件的点击删除事件
     */
    $('.upload-filelist').off('click.delete').on('click.delete','a.upload-file-delete',function(){
    	var obj = this;
    	ajaxAction($(obj).data('url'),null,null,function(){
    		$('#upload-file-'+$(obj).attr('rel')).remove();
    	});
    	return false;
    })
    /*参数设置模块，设置项排序。/admin/settings/prefix/site */
    $('.settings-sortable').sortable({
		revert:true,
		cancel:':input,:radio,:image,:button',
		update:function(event,ui){
			//console.log($(this).sortable('toArray'));serialize
			ajaxAction(ADMIN_BASEURL+'/admin/settings/sort.json',$(".settings-sortable").sortable( "serialize" ));
		}
	});
    /**
     * 绑定form的ajax提交
     */
    $('form').each(function(){
		var form = $(this); 
		if(typeof(form.data('noajax'))!='undefined' || typeof(form.attr('target'))!='undefined' || form.attr('method')=='get'|| form.attr('method')=='GET'){
			return true; // 不需要绑定ajax提交事件，则跳过
		}
		if(typeof(form.data('validator'))!='undefined'){
			return true; // 已绑定的form则跳过。需要return true。false时，each后续循环的会跳过
		}
		form.bind("onSuccess", function (e, ok) {
	        $.each(ok, function() {
	            var input = $(this);
	            remove_validation_markup(input);
	            // uncomment next line to highlight successfully
	            // validated fields in green
	            add_validation_markup(input, "success");
	            return true;
	        });
	    }).bind("onFail", function (e, errors) {
	    	var msg = "";
	    	remove_all_validation_markup(form);
	        $.each(errors, function() {
	            var err = this;
	            var input = $(err.input);
	            add_validation_markup(input, "error",err.messages.join(" "));
	        });
	        return false;
	    });
	    form.validator({errorInputEvent:'keyup.v change.v blur',lang: 'zh'});// 校验错误的，在keyup，change，blur时，会再次校验
	    form.data('validator').checkValidity();
	    // 必须用on绑定事件。.bind(), .delegate(), 和 .live(). 要删除的.on()绑定的事件，validator的事件由on绑定
		form.on('submit',function(e){
			setCKEditorVal(form);
			var validator = form.data('validator');
			var ret = validator.checkValidity(null, e);
			if(ret==true){ // 返回true时，才ajax提交
				$.ajax({
					type: "POST",		
					url:form.attr('action'),
					data:form.serialize(),
					success:function(request, textStatus){
						if(request.success){
							/* 触发表单绑定的提交成功的事件 */
							showSuccessMessage(request.success); /* 提示成功消息 */
							if(request.actions){ // 进行后续操作
								if(request.actions.nexturl){ // 转至另一网址
									if(History.pushState){ /* ajax动态加载效果 */
										form.trigger('onSubmitSucess');
										History.pushState(null, null, request.actions.nexturl);
									}
									else if(form.closest('.ui-dialog-content').size()>0){ // 在弹出的dialog窗口中
										//窗体可能关闭了，desktop模式时，忽略nexturl。在onSubmitSucess中，关闭窗体，刷新list列表
										//form.closest('.ui-dialog-content').load(request.actions.nexturl);
										form.trigger('onSubmitSucess');// 可能将窗体关闭了，需要在if判断后执行
									}
									else{
										form.trigger('onSubmitSucess');
										window.location.href=nexturl;
									}
								}
							}
							
						}
						else{
							form.trigger('onSubmitFail');
							validator.invalidate(request);
							showErrorMessage('请检查输入是否正确！');
						}
					},
					error:function (XMLHttpRequest, textStatus, errorThrown) {
					    alert('ajax error please retry');
					},
					dataType:"json"
				});
			}
			return false; // 必须返回false
		});
		return true;
	});
    /**
     * 绑定使用、注销可见即可得编辑器
     */
	$('.use_editor').off( "click").on('click',function(){
		var id = $(this).parent().find("textarea:first").attr('id');
		if(id && CKEDITOR){
			if(CKEDITOR.instances[id]){
				CKEDITOR.instances[id].destroy();
			}
			if($(this).html()==$.jslanguage.use_editor){
				$(this).html($.jslanguage.destory_editor);
				CKEDITOR.replace(id);
			}
			else{
				$(this).html($.jslanguage.use_editor);
				//$(this).addClass('hidden');
			}
		}
	})
}



function open_content_dialog(id){
	$("#"+id).dialog({ width: 650,modal: true});
	return false;
}

function getEvent() {
    if (document.all) {
        return window.event; //如果ie
    }
    func = getEvent.caller;
    while (func != null) {
        var arg0 = func.arguments[0];
        if (arg0) {
            if ((arg0.constructor == Event || arg0.constructor == MouseEvent) || (typeof (arg0) == "object" && arg0.preventDefault && arg0.stopPropagation)) {
//                alert(arg0);
            	return arg0;
            }
        }
        func = func.caller;
    }
    return null;
}

/**
 * dialog方式打开一个url地址,根据url地址生成dialog的id
 * 
 * @param options 为dialog的options参数,为对象形式 ‘{}’
 * @param url 为dialog打开对话框的内容url，通过ajax获取内容
 * @param callback ajax获取完内容后的回调事件
 * @param params 为回调函数的传参，为对象形式 ‘{}’
 * @param event 为触发的事件，可通过它找到对应触发的元素
 * */
function open_dialog(options,url,callback,params,event){
	event = event? event:getEvent();
	var src_obj = event.srcElement ? event.srcElement:event.target;
	
	if(typeof(options.title)=='undefined' && typeof($(src_obj).attr('title'))!='undefined'){
		options.title = $(src_obj).attr('title');
	}
	//options.dialogtype = 'iframe';
	
	if(url.search(/\?/)!=-1){
		url+='&inajax=1';
	}
	else{
		url+='?inajax=1';
	}
	var dialogid = "dialog_"+url.replace(/\.html/g,'').replace(/http:/g,'').replace(/\/|\.|:|,|\?|=|&| /g,'_');
	
	if(window.open_window){ //desktop
		return window.open_window(url,{title:options.title});
	}
	
	last_open_dialog = dialogid;
	var dialog_tpl = '<div id="'+dialogid+'" class="modal fade">\
  <div class="modal-dialog">\
    <div class="modal-content">\
      <div class="modal-header">\
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>\
        <h4 class="modal-title"></h4>\
      </div>\
      <div class="modal-body">loading...\
      </div>\
    </div><!-- /.modal-content -->\
  </div><!-- /.modal-dialog -->\
</div><!-- /.modal -->\
';
	
	if($('#'+dialogid).size()<1){
		$(dialog_tpl).appendTo('body');
		//$('<div id="'+dialogid+'">loading...</div>').appendTo('body');
	}
	
	$('#'+dialogid).modal().on('hidden.bs.modal', function () {
		  // do something…
		for(var i in ckeditors)	{
			CKEDITOR.remove(ckeditors[i]);
		}
		$(this).remove();
	});
	


	
	if(options.dialogtype && options.dialogtype=='iframe'){
		url = url.replace(/inajax=1/g,'');
		$('#'+dialogid).find('.modal-body').html('<iframe width="100%" border="0" height="100%" src="'+url+'"></iframe>');
	}
	else{
		$.ajax({
			url:url,
			data:{},
			success:function(data,textStatus){  
				try{
					var obj=eval("("+data+")"); // 返回的数据为json格式，表示操作成功，弹出提示语
					if(obj.success && typeof(obj.success)=='xml'){
						throw "error";
					}
					if(obj.success && obj.success!=""){
						var content = '<div class="ui-widget">'+
						'<div style="margin: 0px;padding: 5px 10px;" class="ui-state-highlight ui-corner-all"> '+
							'<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-check"></span>'+
							obj.success +'.</p>'+
						'</div></div>';
						$('#'+dialogid).find('.modal-body').html(content);
					}
					else if(obj.error && obj.error!=""){
						var content = '<div class="ui-widget">'+
						'<div style="margin: 0px;padding: 5px 10px;" class="ui-state-error ui-corner-all"> '+
							'<p><span style="float: left; margin-right: 0.3em;" class="ui-icon ui-icon-alert"></span>'+
							obj.error +'.</p>'+
						'</div></div>';
						$('#'+dialogid).find('.modal-body').html(content);
					}
					else{
						throw "error";
					}
					//$('#'+dialogid).dialog( "option", "width", 350 );
					//$('#'+dialogid).dialog( "option", "buttons", { "Ok": function() { $(this).dialog("close"); } } );
					//$('#'+dialogid).show('pulsate',{times:2},200,function(){
						//$(this).dialog('close');
					//});
					
					//$( "#remote-dialog" ).fadeOut('slow');  
					if(typeof(callback)=='function'){
						if(typeof(params)=='undefined'){
							var obj = callback();
						}
						else{
							var obj = callback(params);
						}
					}
				}
				catch(err){
					$('#'+dialogid).find('.modal-body').html(data);
					
					var form = $('#'+dialogid).find('form').eq(0);
					form.bind("onSubmitSucess", function (e) {
						$('#'+dialogid).modal('hide');
						if(src_obj.tagName=='A'){
							$(src_obj).trigger('onDialogSubmitSucess');
						}
						else{
							$(src_obj).closest('a').trigger('onDialogSubmitSucess');
						}						
	                });
			    		    	
			    	if(typeof(callback)=='function'){			    		
			    		// dialog关闭时，刷新列表内容。dialog中表单是否提交都会刷新，选择条件区分无条件时不刷新
			    		$('#'+dialogid).on('hidden.bs.modal', function () {
			    			  // do something…
			    			if(typeof(params)=='undefined'){
								var obj = callback();
							}
							else{
								var obj = callback(params);
							};
			    		});
					}
			    	
			    	
			    	$dialog = $('#'+dialogid);				
					function load_url(){
						page_loaded();
						$dialog.find('a').click(function(){	
							var url = $(this).attr('href');
							var re = /^#/;
							if( typeof($(this).attr('onclick')) != "undefined" || re.test(url) || url.substr(0,10).toLowerCase()=='javascript'){
								return true; // 当为锚点，javascript,或者定义了onclick 时，忽略动作
								// 可以使用onclick="return true;"，来忽略dialog中链接的绑定事件
							}							
							$dialog.find('.modal-body').load(url,function(){ load_url();});							
							return false;
						});						
					}
					load_url();
				}
				$('body').css('overflowY','auto');
				//$('#'+dialogid).dialog( "option", "position", ['center','center']);
				
		    },
		    dataType:"html",
		    cache:false
		});
	}
	return false;
}

// 弹出消息对话框，自动关闭
function show_message(txt,times)
{
	if(!times) times=5;
	if($('#show_message_dialog').size()<1) $('<div id=\"show_message_dialog\" title=\"Result\"></div>').appendTo('body');
	
	$('#show_message_dialog').dialog({
		height:120,
		modal: false
	});
	$('#show_message_dialog').html(txt);
	$( '#show_message_dialog' ).show('pulsate',{times:times},300,function(){
		$( '#show_message_dialog' ).dialog('close');
	});
}
//弹出内容展示，不自动关闭
function show_content(txt)
{
	if($('#show_content_dialog_message').size()<1) $('<div id=\"show_content_dialog_message\" ></div>').appendTo('body');	
	$('#show_content_dialog_message').dialog({
		height:420,
		width:600,
		modal:false
	});
	$('#show_content_dialog_message').html(txt);
	$('#show_content_dialog_message' ).show();
}
/* dialog方式打开一个url地址*/
function close_dialog(){
	$('#remote-dialog').dialog('close');
	return false;
}
// 显示表单提交成功的信息
function showDialogMessage(request)
{
	return showSuccessMessage(request.success);
	
	if(last_open_dialog){
		$('#'+last_open_dialog).dialog('close');
	}
	if($('#ajax_action_result').size()<1) $('<div id="ajax_action_result" title="Result"></div>').appendTo('body');
	$('#ajax_action_result').html(request.success);
	var dbuttons = {};
	if(request.actions){
		for(var i in request.actions){
			if(request.actions[i]=='closedialog'){
				dbuttons[i] = function(){
					$(this).dialog('close');
				};
			}
			else if(request.actions[i]=='resetform'){							
				dbuttons[i] = function(){
					$(document.forms[0]).trigger('reset');
					$('.fileuploadinfo').html('');
					$(this).dialog('close');
				};
			}
		}
	}
	
	$('#ajax_action_result').dialog({
		height:160,
		modal: true,
		buttons: dbuttons
	});  
	$( '#ajax_action_result' ).show('pulsate',{times:5},500,function(){
		$( '#ajax_action_result' ).dialog('close');
	});
}
// 显示表单验证错误的信息
function showValidateErrors(request,model,suffix)
{
	var tempmodel = model;
	var field_name = '';
	var error_message = '';
	var firsterror = true;
	for(var i in request){
//		alert(i);
		tempmodel = model;
		field_name =i;
		var split_str=i.split('.');
		if(split_str.length>1){
			tempmodel = split_str[0];
			field_name = split_str[1];
		}
		// 首字母大写，如将task_id,替换成Task_id
		var field = field_name.replace(/\b\w+\b/g, function(word) {
           return word.substring(0,1).toUpperCase( ) +
                  word.substring(1);
         });
         // 替换下划线 ，并使字符串为驼峰型。如将Task_id,替换成TaskId
         field = field.replace(/\_\w/g, function(word) {
           return word.substring(1,2).toUpperCase( );
         });
         //alert('#error_".$options['model']."'+field);
		if(firsterror)
		{
			window.location.hash = '#'+tempmodel+field+suffix;
			firsterror = false;
		}
		$("#error_"+tempmodel+field+suffix).remove();
		$('#'+tempmodel+field+suffix).parent('div:first').append("<span id='error_"+tempmodel+field+suffix+"' id='error_"+tempmodel+field+suffix+"' class='ui-state-error ui-corner-all' style='position: absolute;'><span class='ui-icon ui-icon-alert'></span>"+request[i]+"</span>");
		var txt = $('label[for="'+tempmodel+field+suffix+'"]').html();
		//alert(txt);
		error_message +=txt+':'+ request[i]+"<br/>";
	}
	if(error_message!=''){
		show_message(error_message,8);
	}
}

function setCoverImg(model,imgsrc){
	$('#'+model+'Coverimg').val(imgsrc);	
	$('#'+model+'CoverimgPreview').attr('src',imgsrc);
}

function setListImg(model,imgsrc){
    $('#'+model+'Listimg').val(imgsrc);
    $('#'+model+'ListimgPreview').attr('src',imgsrc);
}

function addNewCrawlRegular()
{
	var field = $('.model-schema-list').val();
	$('.model-schema-area').before($('<div class="regexp-add"><label for="CrawlRegexp'+field+'">Regexp '+field+'</label><textarea id="CrawlRegexp'+field+'" cols="60" rows="2" name="data[Crawl][regexp_'+field+']"></textarea></div>'));
}

/**
 * ajaxAction设置栏目为首页，成功后的回调函数
 * @param request ajax返回的内容
 * @param obj	  obj为点击的元素 <a class="btn">...</a>
 */
rs_callbacks.set_index_page = function(request,obj){
	$(obj).closest('.ui-dialog-content').find('.btn-success').removeClass('btn-success').html('<i class="icon-home"></i>');
	$(obj).addClass('btn-success').html('<i class="icon-ok icon-white"></i>');
}

/**
 * 生成slug链接的函数及回调
 * @param request
 * @param selector
 */
rs_callbacks.generateSlug = function(request,selector){
	$(selector).val(request.slug); 
}
function generateSlug(obj,selector){
	if(obj.value){
		var url = ADMIN_BASEURL+'/admin/tools/genSlug';
		ajaxAction(url,{'word':obj.value},null,'generateSlug',selector);
	}
}

var iUtils = function () {
    var initSelectBox = function (selectBox) {
        var value = selectBox.data('value');
        $("option", selectBox).each(function(){
            if($(this).val() == value){
                $(this).attr('selected', 'selected');
            }
            else{
                $(this).removeAttr('selected');
            }
        });
    }
    var isMobileValid = function(mobile){
        return /^1\d{10}$/.test(mobile);
    }

    return {
        initSelectBox: initSelectBox,
        isMobileValid: isMobileValid
    }
}();

