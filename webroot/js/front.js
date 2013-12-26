
function page_loaded()
{
	$('.ui-portlet-content').each(function(){
		if(jQuery.trim($(this).html())==''){
			$(this).parent('.ui-portlet').hide();
		}
	});
	$(".alert").alert().append('<a class="close" data-dismiss="alert" href="#">&times;</a>');
	
	//$('.tabs').tabs(); // jquery ui.
	//$( "button, input:submit, input:button").button();
	//$(".tabs .ui-tabs-nav").tabs(".tabs .ui-tab-panes > div",{event: 'mouseover',effect: 'slide'}); // jquery tools
}

$(document).ready(function() {
	page_loaded();	
});

function detectCapsLock(e, obj) {
	var valueCapsLock = e.keyCode ? e.keyCode : e.which;
	var valueShift = e.shiftKey ? e.shiftKey : (valueCapsLock == 16 ? true : false);
	this.clearDetect = function () {
		obj.className = 'txt';
	};	
	obj.className = (valueCapsLock >= 65 && valueCapsLock <= 90 && !valueShift || valueCapsLock >= 97 && valueCapsLock <= 122 && valueShift) ? 'clck txt' : 'txt';
	
	if($.browser.msie) {
		event.srcElement.onblur = this.clearDetect;
	} else {
		e.target.onblur = this.clearDetect;
	}
}

/**
 * 将CKEditor编辑器的内容，设置到textarea文本中，和表单提交时一起提交数据。
 * @return
 */
function setCKEditorVal()
{
	$('form .wygiswys').find('textarea').each(function(){
		var oEditor = CKEDITOR.instances[this.id];	
		if(oEditor)
		{
			var content = oEditor.getData();
			//alert( content );
			$(this).val(content);
		}
	});
}

// 显示表单验证错误的信息
function showValidateErrors(request,model,suffix)
{
	var tempmodel = model;
	var field_name = '';
	var error_message = '';
	var firsterror = true;
	for(var i in request)
	{
		//alert(i);
		tempmodel = model;
		field_name =i;
		var split_str=i.split('.');
		if(split_str.length>1)
		{
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
		$('#'+tempmodel+field+suffix).parent('div:first').append("<span id='error_"+tempmodel+field+suffix+"' name='error_"+tempmodel+field+suffix+"' class='ui-state-error ui-corner-all' style='position: absolute;'><span class='ui-icon ui-icon-alert'></span>"+request[i]+"</span>");
		var txt = $('label[for="'+tempmodel+field+suffix+'"]').html();
		//alert(txt);
		error_message +=txt+':'+ request[i]+"<br/>";
	}
	if(error_message!='')
	{
		show_message(error_message,8);
	}
}
function addNewCrawlRegular()
{
	var field = $('.model-schema-list').val();
	$('.model-schema-area').before($('<div class="regexp-add"><label for="CrawlRegexp'+field+'">Regexp '+field+'</label><textarea id="CrawlRegexp'+field+'" cols="60" rows="2" name="data[Crawl][regexp_'+field+']"></textarea></div>'));
}
var AjaxHelper={
	dialog_open:false,
	open_help: function(){
		$('#ajax_doing_help').html('<img src="/img/ajax/circle_ball.gif" /> 正在提交...');
		$('#ajax_doing_help').dialog({width: 650,
			close: function(event, ui) {
				$('#invite-user-html').hide().appendTo('body');
			}
		});
		this.dialog_open=true;
	},	
	has_init_tab:false,
	friends_tab:null		
}


/* 订单ajax操作提示信息  开始*/
//显示提示信息
function showAlert(info,obj,infoSign)
{
   if($('#'+infoSign).size()>0){return;}
   var newd=document.createElement("span");
   newd.id=infoSign;
   newd.className='ui-state-error';
   newd.innerHTML=info;
   $(obj).append($(newd));
}
//删除提示信息
function removeAlert(infoSign)
{
   $(infoSign).remove();
}

function clearSubmitError(obj){
	$(obj).parent().find('.errorInfo').remove();
}
function clearWaitInfo(obj){
	if(obj){
		$(obj).parent().find('.waitInfo').remove();
	}
	else{
		$(".waitInfo").remove();
	}
}

function showWaitInfo(info,obj){
	try{
		if(obj==null)return;
		clearWaitInfo();
		var newd=document.createElement("span");
		newd.className='waitInfo';
		newd.id='waitInfo';
		newd.innerHTML=info;
		obj.parentNode.appendChild(newd);
	}catch(e){}
}

function showWaitInfoOnInner(info,obj){
	try{
		if(obj==null)return;
		clearWaitInfo();
		var newd=document.createElement("span");
		newd.className='waitInfo';
		newd.id='waitInfo';
		newd.innerHTML=info;
		obj.innerHTML='';
		obj.appendChild(newd);
	}catch(e){}
}

/* 订单ajax操作提示信息  结束 */


$(function(){
	//站内查询
//	$("input[type=text]").focusin(function(){$( this ).removeClass( "ui-state-default");
//		$( this ).addClass( "ui-state-focus");
//	});
//	$("input[type=text]").focusout(function(){$( this ).removeClass( "ui-state-focus");
//		$( this ).addClass( "ui-state-default");
//	});
	/* 菜单开始 */
	$('.ui-navi li').hover(function(){
		$(this).children(".ui-drop-menu:first").show();
	},function(){
		$(this).children(".ui-drop-menu:first").hide();
	});
	//二级菜单显示隐藏	
	$(".ui-sidemenu li").hover(
            function () {
                var li_width = $(this).width();
                var li_offset = $(this).offset();
                $(this).children("a").addClass( "ui-state-default");
                var submenu = $(this).children(".ui-secondmenu");
                if(li_offset.left>$(window).width()/2){
                    submenu.css('left',-submenu.width()+2).show();
                }
                else{
                    submenu.css('left',li_width-2).show();
                }
                 var offset = submenu.offset();
                if(li_offset.top-$(window).scrollTop()+submenu.height()>$(window).height()){
                    if(submenu.height() < $(window).height()){
                        // 子菜单的高度小于window的高度时，子菜单从window的底部开始显示
                        submenu.css('top',$(window).height()-2-li_offset.top -submenu.height()+$(window).scrollTop() );
                    }
                    else{
                        // 子菜单的高度大于window的高度时，从window顶部开始显示
                        submenu.css('top',-li_offset.top+$(window).scrollTop()+2);
                    }
                }
            },
            function(){
                $(this).children("a").removeClass( "ui-state-default");
                $(".ui-secondmenu",this).hide();
            });
	/* 菜单结束 */
	/* 列表的新闻鼠标移上高亮显示 */
	$(".regionlist").hover(function () {
		$(".regionlist").css({"background":""});
		$(this).css({"background":"#f2f2f2"});
	});

	
	/*
	$('.pagelink a').live('click',function(){
		$linkobj = this;
		
		var region_obj = $(this).parents('.ui-portlet-content').eq(0);
		// 加载region分页的内容
		var page_url = this.href;
		var portletid = region_obj.parents('.ui-portlet').eq(0).attr('id');
		
		if(portletid){
			region_obj.load(page_url+' #' + portletid,{},function(){			
				var offset = region_obj.offset();
				$('html,body').animate({ scrollTop: offset.top },1000);			
				
				var html = region_obj.find('.ui-portlet-content').html();
				region_obj.html(html);
				
				if(!$.browser.msie){
					region_obj.find('img[original]').lazyload({
			            placeholder : BASEURL+"/img/grey.gif",
			            effect      : "fadeIn" ,threshold : 200                 
			        });
				}
				loadStatsData();
				
				
				var url_info = page_url.split('/');	
				var page = $($linkobj).html();
				var hash = window.location.hash;
				hash = hash.replace('page_'+portletid+'=','page=').replace(/page=\d+/,'');								
				if(hash=='#'||hash=='')	{
					hash='#page_'+portletid+'='+page;
				}
				else{
					hash = hash+'&page_'+portletid+'='+page;
					hash=hash.replace('&&','&');
				}
				page_hash.updateHash(hash, false);
				
			});	
		}
		return false;
	});*/
});
