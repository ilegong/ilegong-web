var swfu_array =[];
var ckeditors = {};
// var BASEURL = '/index.php'; // 若项目处于二级目录，此处填写斜线加二级目录名称 ，如 '/subdir'
// var ADMIN_BASEURL = '/manage/index.php'; // 后台项目所在的二级目录
var swfu_array =[];
var last_open_dialog = null; // 记录最后一次打开的dialog
var jqgrid_scrollOffset = null; // 记录jqgrid的滚动条位置； // 触发更新事件时，自动滚动到先前滚动条所在的位置。
var form_submit_flag_for_swfupload = false;  // 表单提交标记，表单提交时，检测文件是否上传完。文件上传完时，自动提交表单
var form_submit_obj_for_swfupload = null;
//Pys common name space
PYS={};
PYS.storage = {
    save : function(key, jsonData, expirationHour){
        //if (!Modernizr.localstorage){return false;}
        var expirationMS = expirationHour * 60 * 60 * 1000;
        var record = {value: JSON.stringify(jsonData), timestamp: new Date().getTime() + expirationMS}
        localStorage.setItem(key, JSON.stringify(record));
        return jsonData;
    },
    load : function(key){
        //if (!Modernizr.localstorage){return false;}
        var record = JSON.parse(localStorage.getItem(key));
        if (!record){return false;}
        return (new Date().getTime() < record.timestamp && JSON.parse(record.value));
    }
}
// 进行Digg操作,单一选项投票提交
function singleSubmitDigg(model,data_id,question_id,option_id,callback)
{
	if(!sso.check_userlogin({"callback":singleSubmitDigg,"callback_args":arguments})){
		return false;
	}
	var postdata = {model:model,data_id:data_id};

	postdata['options['+question_id+']['+option_id+']']=1; // question 2,option
	// 4

	$.ajax({
		type:'post',
		url:BASEURL+'/appraiseresults/singlesubmit',
		data: postdata,
		success:function(data){
			if(data.error){
				alert(data.error);
				return false;
			}
			if(typeof(callback)=='function'){
				var obj = callback(data);
			}
		},
		dataType:'json'
	});
	return false;
}
function setDiggNum(data,total)
{
	var id = '#Digg-'+data.model+'-'+data.data_id+'-'+data.question_id+'-'+data.option_id;
	$(id).html('('+data.value+')');

	if(total){
		var bar = '#Diggbar-'+data.model+'-'+data.data_id+'-'+data.question_id+'-'+data.option_id;
		var p = data.value / total * 100;
		$(bar).attr("width", p );
	}
	else{
		// 有bar且没有传入参数total的时候，更新bar的高度，用在singelsubmit提交之后。
		// 有总数是加载时，会根据总数设置bar的高度
		// 适用于多项的单选。如心情，好评差评等。多选时不适用此处理。
		var bar = '#Diggbar-'+data.model+'-'+data.data_id+'-'+data.question_id+'-'+data.option_id;
		if($(bar).size()>0)
		{

			var total = 0;
			$("[id^='Digg-"+data.model+"-"+data.data_id+"-']").each(function(){
				var num = $(this).html();
				num = num.replace(/\(|\)/g,'');
				total+=parseInt(num);
			});
			$("[id^='Diggbar-"+data.model+"-"+data.data_id+"-']").each(function(){
				var numid = this.id.replace(/Diggbar-/,'Digg-');

				var num = $('#'+numid).html();
				num = num.replace(/\(|\)/g,'');

				var height = parseInt(num);
				var p = height / total * 100;
				$('span',this).css("width", p );
				$(this).next().html(p.toFixed(2)+'%');
			});
		}
	}
}


/**
 * 将CKEditor编辑器的内容，设置到textarea文本中，和表单提交时一起提交数据。
 *
 * @return
 */

function setCKEditorVal(form)
{
	$(form).each(function(i){
		$(this).find('textarea').each(function(){ // form .wygiswys
			var oEditor = CKEDITOR.instances[this.id];
			if(oEditor)	{
				var content = oEditor.getData();
				$(this).val(content);
			}
		});
	})
}

function updateCartItemCount()
{
	var itemCount = $('#item-count');
	var cartBtn = $('#card-btn');
	var cart_link = $('#cart_link');
	if (itemCount.length > 0 || cartBtn.length > 0 || cart_link.length > 0) {
		$.getJSON('/carts/cart_total.json', function (data) {
			if (data.count > 0) {
                itemCount.show();
				itemCount.text(data.count);
				cartBtn.addClass('cart_icon_not_empty');
			} else {
				itemCount.text('');
                itemCount.hide();
			}
		});
	}
}

/* ajax 操作表单交互,开始 */

// ajaxAction操作成功后，返回调用的函数
var rs_callbacks = {
	addtofavor:function(request){
		if(request.success){
			var num_selector = '.Stats-'+request.data.model+'-'+request.data.data_id+'-favor_nums';
			var num = $(num_selector).html();
			num = num.replace(/\(|\)/g,'');
			if(num=='') num = 0;
			num = parseInt(num);num++;
			$(num_selector).html(num);
		}
	},
	loginSucess:function(request, form){
		if(request.success){
			publishController.close_dialog();
			if(sso.form){
				$(sso.form).trigger("submit");
				sso.form=null;
			}
			if(sso.callback){
				sso.callback.apply(sso.callback,sso.callback_args);
//                if(request.userinfo && request.userinfo.session_flash){
//                    showSuccessMessage(request.success+request.userinfo.session_flash);
//                }
//                else if(request.userinfo){
//                    showSuccessMessage(request.success);
//                }
			} else {
				window.location.href = window.location.href;
			}
		} else {
			$("#loginMessage").html(request.error).show();
			if (form) {
				$(':submit',form).removeAttr('disabled');
			}
		}
	},
};

// 操作的提交，
/**
 * url，为ajax提交的url。 要求返回为json数据 postdata 为要提交的数据， form 提交的表单
 * callback_func_name,回调函数名，要在rs_callbacks中定义，回调函数的第一个参数为ajax返回的结果 moreags
 * 传给回调函数的更多参数，参数格式可自定义，字符串、数组、对象等
 */
function ajaxAction(url,postdata,form,callback_func_name, moreags, notShowMsg){
	if(url.search(/\?/)!=-1){
		url+='&inajax=1';
	}
	else{
		url+='?inajax=1';
	}
	if(form){
		var html = $(':submit',form).val();
		$(':submit',form).data('html',html).val('正在处理...').attr('disabled','disabled'); // 将按钮置为不可提交
	}
	$.ajax({
		// async:true,
		type:'post',
		url: url,
		data: postdata,
		complete:function (XMLHttpRequest, textStatus) {
			if(form){
				//var html = $(':submit',form).data('html');
				//$(':submit',form).val(html).removeAttr('disabled'); // 将按钮置为可提交
				$(':submit',form).val('已成功提交');
			}
		},
		success: function(request){

			if(typeof(callback_func_name)=='function'){
				callback_func_name(request);
				return;
			}
			else if(callback_func_name && rs_callbacks[callback_func_name]){
				var func = rs_callbacks[callback_func_name];
				if(moreags){
					func(request,moreags);
				}
				else{
					func(request);
				}
				return;
			}
			// tasks is a json object
			// tasks[i] is a json object that convert from a php array .
			// array('dotype','selector','content');

			// callback.apply(callback,callback_args);
			// //回调函数,callback_func_name为回调函数的函数名。如rs_callbacks.addtofavor()

			if(request.tasks){
				$(request.tasks).each(function(i){
					var task = request.tasks[i];

					if(task.dotype=="html"){
						$(task.selector).html(task.content).show();
					}
					else if(task.dotype=="value"){
						$(task.selector).val(task.content);
					}
					else if(task.dotype=="append"){
						$(task.content).appendTo(task.selector);
					}
					else if(task.dotype=="dialog"){
						$(task.content).appendTo(task.selector);
					}
					else if(task.dotype=="reload"){
						window.location.reload();
					}
					else if(task.dotype=="callback"){
						var callback = null,thisArg=null;
						eval( "callback= "+task.callback+";");
						eval( "thisArg= "+task.thisArg+";");
						var args = [];
						for(var i in task.callback_args){
							args[args.length]=task.callback_args[i];
						}
						if(callback){
							callback.apply(thisArg,args);
						}
					}
				});
				return;
			}
			else{
				if(!notShowMsg && request.success){
					showSuccessMessage(request.success);
				}
				else if(request.error){
					var errorinfo='';
					for(var i in request){
						errorinfo +="<span class='ui-state-error ui-corner-all'><span class='ui-icon ui-icon-alert'></span>"+request[i]+"</span>";
					}
					showErrorMessage(errorinfo);
				}
			}

		},
		dataType:"json"
	});
	return false;
}

// ajax 操作,获取html
function ajaxActionHtml(url,selector,callback){
	$.ajax({
		async:true,
		type:'get',
		url: url,
		success: function (data){
			$(selector).html(data);
			if(typeof(callback)=='function'){
				callback(selector);
			}
			else if(callback){
				eval(callback);
			}
		},
		dataType:"html"
	});
}
// ajax 操作,提交Form
function ajaxeSubmitForm(form,callback_func_name)
{
	setCKEditorVal();
	ajaxAction(form.action,$(form).serialize(),form,callback_func_name, form);	// 发出请求
	return false;
}


/* ajax 操作表单交互,结束 */


var sso = {
	usercookie:	$.cookie('SAECMS[Auth][User]'),
	form : null, // 登录成功后触发sso.form的提交，提交后置form为null，防止多次调用。登录的各种回调均在users/login模板中
	// callback:'',
	callback:null,   // 在登录成功后，自动调用 sso.callback(callback_args);
	callback_args:null,
	login_check : false, //just check result
	is_login : function(){
		if (this.login_check) { return true;}
		this.usercookie = $.cookie('SAECMS[Auth][User]');
		return !(this.usercookie == null || this.usercookie =="" || typeof(this.usercookie) =='undefined');
	},
	check_userlogin:function(params){

		if(!this.is_login()){

			if(params && params.callback){ this.callback = params.callback; }else{ this.callback = ''; }
			if(params && params.form){ this.form = params.form; }else{ this.form = null; }
			if(params && params.callback_args){ this.callback_args = params.callback_args; }else{ this.callback_args = '';}

			$.getJSON('/users/login_state', function(data){
				if (data.login == true) {
					sso.login_check = true;
					if(params.callback){
						params.callback.apply(params.callback, params.callback_args);
					}
				}  else {
					var baseUrl = BASEURL+'/users/login?';
					if(params && params.referer) {
						baseUrl += '&referer=' + encodeURIComponent(params.referer);
					}

					publishController.open_dialog(baseUrl,{'title':$.jslanguage.needlogin});  // 打开登录窗体
				}
			});

			return false;
		}
		return true;
	}
};


var publishController = {
	_crontroller:'questions',
	dialogid : null,
	overlays : {},
	open_dialog:function(url,options){ // 改成传入一个参数options数组。
		// {title:'title', width: 460}
		var $dialog = this;
		if($dialog.dialogid){
			$('#'+$dialog.dialogid).modal('hide')
		}
		$dialog.dialogid = url.replace(/\/|\.|:|,|\?|=|&|%/g,'_')+'-ajax—action';

		if($('#'+$dialog.dialogid).size()<1){
			$('<div  class="modal fade" id="'+$dialog.dialogid+'"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button><h3 id="myModalLabel">'+options.title+'</h3></div><div class="modal-body" style="padding-top: 0;"></div></div></div></div>').appendTo('body');

			var obj = $('#'+$dialog.dialogid).find('.modal-body').load(url,{},function(){
				$('.nav-tabs a','#'+$dialog.dialogid).click(function (e) {
					e.preventDefault();
					$(this).blur();
					$(this).tab('show');
				});  //初始化tab
				$('.nav-tabs a:first','#'+$dialog.dialogid).tab('show');//显示第一个tab
				$('.dropdown-toggle','#'+$dialog.dialogid).dropdown();
				$('button','#'+$dialog.dialogid).addClass('btn');
			});

		}
		//else{
		$('#'+$dialog.dialogid).modal('show');
		//}
		// modal的left，top距离都是50%，通过设置margin-left,margin-topd的值为modal宽高一半的负数，
		// 左右居中
		return false;
	},
	load_url:function(url){
		var $dialog = this;
		var re = /^#/;
		if(re.test(url) || url.substr(0,10).toLowerCase()=='javascript'){
			return false; // 当为锚点或javascript时，忽略动作
		}
		$('#'+$dialog.dialogid).load(url,function(){
			$('#'+$dialog.dialogid).find('a').click(function(){
				$dialog.load_url($(this).attr('href'));
				return false;
			});
			page_loaded();
		});
	},
	close_dialog:function(){
		if(this.dialogid){
			$('#'+this.dialogid).modal('hide');
		}
	},
	open_html_dialog:function(dialogid){
		this.dialogid = dialogid;
		$('#'+dialogid).modal('show');
	},
	invite_tabs:{}
};

/**
 * 将模块的数据添加到收藏夹
 *
 * @param model
 *            模块名
 * @param id
 *            模块数据编号
 * @return
 */
function addtofavor(model,id)
{

	var url=BASEURL+'/favorites/add/';
	var postdata = {'data[Favorite][model]':model,'data[Favorite][data_id]':id};
	if(!sso.check_userlogin({"callback":addtofavor,"callback_args":arguments}))
		return false;

	ajaxAction(url,postdata,null,'addtofavor');
	return false;
}

/*
 * page url hash listen
 */
var page_hash = {
	storedHash: '',
	currentTabHash: '', // The hash that's only stored on a tab switch
	cache: '',
	interval: null,
	listen: true, // listen to hash changes?

	// start listening again
	startListening: function() {
		setTimeout(function() {
			page_hash.listen = true;
		}, 600);
	},

	// stop listening to hash changes
	stopListening: function() {
		page_hash.listen = false;
	},

	// check if hash has changed
	checkHashChange: function() {

		var locStr = page_hash.currHash();
		if(page_hash.storedHash != locStr) {
			if(page_hash.listen == true) page_hash.refreshToHash(); // //update
																	// was made
																	// by back
																	// button
			page_hash.storedHash = locStr;
		}

		if(!page_hash.interval) page_hash.interval = setInterval(page_hash.checkHashChange, 500);

	},

	// refresh to a certain hash
	refreshToHash: function(locStr) {

		if(locStr) var newHash = true;
		locStr = locStr || page_hash.currHash();

		var hash_array = locStr.split('&');
		for(var i in hash_array ){
			var pageinfo = hash_array[i].split('=');
			if(pageinfo[0] && pageinfo[1] && pageinfo[0].substr(0,5)=='page_'){
				var portletid = pageinfo[0].replace('page_','');
				var page = pageinfo[1];
				$('.page_'+page,'#'+portletid).trigger('click');
			}
		}
		// if the hash is passed
		if(newHash){ page_hash.updateHash(locStr, true); }

	},

	updateHash: function(locStr, ignore) {

		if(ignore == true){ page_hash.stopListening(); }
		window.location.hash = locStr;
// if(bookmarklet){ window.parent.location.hash = locStr; }
		if(ignore == true){
			page_hash.storedHash = locStr;
			page_hash.startListening();
		}

	},

	clean: function(locStr){
		return locStr.replace(/%23/g, "").replace(/[\?#]+/g, "");
	},

	currHash: function() {
		return page_hash.clean(window.location.hash);
		// return page_hash.clean(encodeURIComponent(window.location.hash));
	},

	currSearch: function() {
		return page_hash.clean(window.location.search);
		// return page_hash.clean(encodeURIComponent(window.location.search));
	},

	init: function(){
		page_hash.storedHash = '';
		page_hash.checkHashChange();
	}
};



$(function(){
	$('.nav li.dropdown,.navbar-nav li.dropdown').hover( function(e){
		$(this).addClass('open');
	},function(){
		$(this).removeClass('open');
	}).click(function(e){
		e.stopPropagation();// 阻止事件冒泡，点击链接后，页面直接跳转，屏蔽click的dropdown事件。
	});

	// ajax操作时，显示一个进行时状态图标
	$("div#ajaxestatus").ajaxStart(function () {
		$(this).show();
	}).ajaxStop(function () {
		$(this).hide();
	});

	//loadDiggData();
	//loadStatsData();	
	//page_hash.init();
});

var stack_custom = {"dir1": "right", "dir2": "down"};
// 显示表单提交成功的信息
function showSuccessMessage(text, close_callback, timeout)
{
	if($('#showMessageModel').size()==0){
		$('<div id="showMessageModel" class="modal fade"><div class="modal-dialog">' +
		'<div class="modal-content">' +
		'<div class="modal-body"></div> <div class="modal-footer text-center">   <button id="show_msg_close_btn" type="button" class="btn btn-default" data-dismiss="modal">关闭</button></div>	</div>  </div></div>')
			.css({
				'top': '50%',
				'margin-top': function () {
					return -($(this).height() / 2);
				}
			})
			.appendTo('body');
	}
	var $msgDlg = $('#showMessageModel');
	if (typeof(close_callback) == 'function') {
		$msgDlg.on('hidden.bs.modal', function () {
			close_callback();
		});
	}
	$msgDlg.modal('hide');
	if (timeout) {
		var $showMsgCloseBtn = $('#show_msg_close_btn');
		$showMsgCloseBtn.html('关闭(<span>'+ (timeout/1000) +'</span>)');
		$timeSpan = $showMsgCloseBtn.find('span');
		var cal = function () {
			var t = parseInt($timeSpan.html());
			$timeSpan.html(t - 1 > 0 ? t - 1 : 0) ;
			setTimeout(cal, 1000);
		};
		setTimeout(cal,  1000);

		$msgDlg.on('shown.bs.modal', function () {
			clearTimeout($msgDlg.data('hideInteval'))
			var id = setTimeout(function(){
				$msgDlg.modal('hide');
			}, timeout);
			$msgDlg.data('hideInteval', id);
		});
	}
	$msgDlg.find('.modal-body').html(text);
	$msgDlg.modal('show');
	return true;
}
// 显示错误信息

function showErrorMessage(text)
{
	alert(text);
//	$.jGrowl(text, { 
//		theme: 'error' // danger
//	});
	return true;
}
/* ================form validate====================== */
/* jquery tools validate , and bootstrap css */
function find_container(input) {
	return input.parent().parent();
}
function remove_validation_markup(input) {
	var cont = find_container(input);
	cont.removeClass('error success warning');
	$('.help-inline.error, .help-inline.success, .help-inline.warning',cont).remove();
}
function add_validation_markup(input, cls, caption) {
	var cont = find_container(input);
	cont.addClass(cls);
	input.addClass(cls);
	if (caption) {
		var msg = $('<span class="help-inline"/>');
		msg.addClass(cls);
		msg.text(caption);
		input.after(msg);
	}
}
function remove_all_validation_markup(form) {
	$('.help-inline.error, .help-inline.success, .help-inline.warning',form).remove();
	$('.error, .success, .warning', form).removeClass('error success warning');
}

/* ================form validate====================== */
$.fn.preload = function () {
	this.each(function () {
		$('<img/>')[0].src = this;
	});
};
$(document).ready(function() {
	if (typeof _pys_notify_img_url != 'undefined' && _pys_notify_img_url) {
		$([_pys_notify_img_url]).preload();
	}
});

var editAmount = {
	min:1,
	max:999,
	reg:function(x){
		return new RegExp("^[1-9]\\d*$").test(x);
	},
	amount:function(obj,mode){
		var x=$(obj).val();
		if (this.reg(x)){
			if (mode){
				x++;
			}else{
				x--;
			}
		}else{
			alert("请输入正确的数量！");
			$(obj).val($this.min);
			$(obj).focus();
		}
		return x;
	},
	reduce: function(obj, callback){
		var x=this.amount(obj,false);
		if (x>=this.min){
			$(obj).val(x);
		}else{
			alert("商品数量最少为"+this.min);
			$(obj).val(this.min);
			$(obj).focus();
		}
		if (typeof callback == 'function') {
			callback(obj);
		}
	},
	add: function(obj, callback){
		var x=this.amount(obj,true);
		if (x<=this.max){
			$(obj).val(x);
		}else{
			alert("商品数量最多为"+this.max);
			$(obj).val(999);
			$(obj).focus();
		}
		if (typeof callback == 'function') {
			callback(obj);
		}
	},
	modify:function(obj){
		var x=$(obj).val();
		if (x<this.min||x>this.max||!this.reg(x)){
			alert("请输入正确的数量！");
			$(obj).val(this.min);
			$(obj).focus();
		}
	}
};

var utils = {
	notify_dialog : null,
	get_notify_img_url : function() {
		if (typeof '_pys_notify_img_url' != 'undefined' && _pys_notify_img_url) {
			return _pys_notify_img_url;
		} else {
			return '/img/progress_notify.gif';
		}
	},


	close_notify : function() {
		if (utils.notify_dialog) {
			utils.notify_dialog.modal('hide');
		}
	},

	progress_done : function(msg) {
		if (utils.notify_dialog) {
			utils.notify_dialog.find('div.bootbox-body').text(msg);
		}
	},

	progress_notify: function(msg) {
		if (msg) {
			msg = '<br/>' + msg;
		} else {
			msg = '';
		}
		utils.notify_dialog = bootbox.dialog({
			'closeButton': false,
			message: '<img src="'+ this.get_notify_img_url()+'"/>' + msg
		});

		var modal_dialog = utils.notify_dialog.find('.modal-dialog');
		modal_dialog.css('width', '200px').addClass('text-center');
		utils.notify_dialog.css({
			'overflow-y': 'auto',
			'padding-top': function () {
				return (($(this).height() - modal_dialog.height()) / 2);
			}
		})
	},

	alert:function(msg, callback, timeout, close_callback) {

		if (!callback) callback = function(){};

		var $dlg = bootbox.alert({'message':msg, 'callback':callback, 'closeButton':false});
		var modal_dialog = $dlg.find('.modal-dialog');
		$dlg.css({
			'overflow-y': 'auto'//,
//            'padding-top': function () {
//                return ( ($(this).height() - modal_dialog.height()) / 2);
//            }
		});

		utils.__auto_close(timeout, $dlg, close_callback);
	},

	__auto_close : function (timeout, $dlg, close_callback) {
		if (timeout && timeout > 0) {
			$dlg.on('shown.bs.modal', function () {
				clearTimeout($dlg.data('hideInteval'));
				var id = setTimeout(function(){
					$dlg.modal('hide');
				}, timeout);
				$dlg.data('hideInteval', id);
			});

			if (typeof close_callback == 'function') {
				$dlg.on('hidden.bs.modal', function () {
					close_callback();
				});
			}
		}
	},

	alert_one : function(msg, defLabel, defCallback, options) {
		return utils.alert_two(msg, defLabel, null, defCallback, null, options);
	},

	alert_two : function(msg, defLabel, impLabel, defCallback, impCallback, options){
		defLabel = defLabel || '知道了';
		var params = {
			message: msg,
			closeButton: false,
			buttons: {
				main: {
					label: defLabel,
					className: impLabel ? "btn-default" : 'btn-danger',
					callback: function () {
						if (defCallback) defCallback();
					}
				}
			}
		};

		if (impLabel) {
			params['buttons']['danger'] = {
				label: impLabel,
				className: "btn-danger",
				callback: function () {
					if (impCallback) impCallback();
				}
			};
		}

		var $dlg = bootbox.dialog(params).css({
			'top': options && options['top'] ? options['top'] : '50%',
			'margin-top': options && options['margin-top'] ? options['margin-top'] : function () {
				return -($(this).height() / 2);
			}
		}).find('div.modal-footer').css({'text-align': 'center'});

		if (options) {
			utils.__auto_close(options.timeout, $dlg, options.close_callback);
		}
	},

	wx_to_friend : function(appid, imgUrl, lineLink, descContent, shareTitle) {
		WeixinJSBridge.invoke('sendAppMessage',{
			"appid": appid,
			"img_url": imgUrl,
			"img_width": "640",
			"img_height": "640",
			"link": lineLink,
			"desc": descContent,
			"title": shareTitle
		}, function(res) {
			//_report('send_msg', res.err_msg);
		});
	},

	wx_to_timeline : function(appid, imgUrl, lineLink, descContent, shareTitle){
		WeixinJSBridge.invoke('shareTimeline',{
			"appid": appid,
			"img_url": imgUrl,
			"img_width": "640",
			"img_height": "640",
			"link": lineLink,
			"desc": descContent,
			"title": shareTitle
		}, function(res) {
			//_report('timeline', res.err_msg);
		});
	},

	is_weixin: function(){
		return (typeof '_pys_in_weixin' != 'undefined' && _pys_in_weixin);
	},

	toFixed: function (value, precision) {
		var precision = precision || 0,
			power = Math.pow(10, precision),
			absValue = Math.abs(Math.round(value * power)),
			result = (value < 0 ? '-' : '') + String(Math.floor(absValue / power));

		if (precision > 0) {
			var fraction = String(absValue % power),
				padding = new Array(Math.max(precision - fraction.length, 0) + 1).join('0');
			result += '.' + padding + fraction;
		}
		return result;
	},

	genSlug: function(val, callback) {
		var url = '/s/genSlug';
		ajaxAction(url,{'word':obj.value},null, function(data){
			callback(data);
		});
	},

	countDown: function(toTimer, initCallback, timeArrivedCallback) {
		if (toTimer.size() > 0) {

			var dif = (parseInt(toTimer.attr('data-start')) *1000 - new Date().getTime())/1000;
			if (dif <= 0) {
				if (typeof(timeArrivedCallback) == 'function') {
					timeArrivedCallback();
				}
				return;
			}

			var hour1 = $('<div class="countdown">0</div>');
			var hour2 = $('<div class="countdown">0</div>');
			var min1 = $('<div class="countdown">0</div>');
			var min2 = $('<div class="countdown">0</div>');
			var sec1 = $('<div class="countdown">0</div>');
			var sec2 = $('<div class="countdown">0</div>');

			toTimer.after(hour1, hour2, $('<div class="colon"><strong>:</strong></div>'),
				min1,min2, $('<div class="colon"><strong>:</strong></div>'), sec1, sec2);
			if (typeof(initCallback) == 'function') {
				initCallback();
			}

			toTimer.remove();

			var intvalId = setInterval(function(){
				if (dif <= 0) {
					clearInterval(intvalId);
					if (typeof(timeArrivedCallback) == 'function') {
						timeArrivedCallback();
					}
					return;
				}
				var h = Math.floor(dif/3600);
				var left = dif % 3600;
				var m = Math.floor(left / 60);
				var s = Math.floor(left % 60);

				hour1.text(Math.floor(h / 10));
				hour2.text(h % 10);

				min1.text(Math.floor(m / 10));
				min2.text(m % 10);

				sec1.text(Math.floor(s / 10));
				sec2.text(s % 10);
				dif--;
			}, 1000);
		}
	}
};



var TemplateEngine = function(html, options) {
    var re = /<%(.+?)%>/g,
        reExp = /(^( )?(var|if|for|else|switch|case|break|{|}|;))(.*)?/g,
        code = 'with(obj) { var r=[];\n',
        cursor = 0,
        result;
    var add = function(line, js) {
        js? (code += line.match(reExp) ? line + '\n' : 'r.push(' + line + ');\n') :
            (code += line != '' ? 'r.push("' + line.replace(/"/g, '\\"') + '");\n' : '');
        return add;
    }
    while(match = re.exec(html)) {
        add(html.slice(cursor, match.index))(match[1], true);
        cursor = match.index + match[0].length;
    }
    add(html.substr(cursor, html.length - cursor));
    code = (code + 'return r.join(""); }').replace(/[\r\t\n]/g, '');
    try { result = new Function('obj', code).apply(options, [options]); }
    catch(err) { console.error("'" + err.message + "'", " in \n\nCode:\n", code, "\n"); }
    return result;
};
function zitiAddress(){
    var beijingArea= {
        110101:{
            'name':"东城区"
        },
        110108:{
            'name':"海淀区"
        },
        110102:{
            'name':"西城区"
        },
        110105:{
            'name':"朝阳区"
        },
        110106:{
            'name':"丰台区"
        }

    };
    //崇文并入东城区， 宣武并入西城区
    var ship_address = {};
    var child_address = {};
    if(PYS.storage.load('offline_stores')){
        var data = PYS.storage.load('offline_stores');
        ship_address = data.address;
        child_address = data.child_address;
    }else{
        $.getJSON('/tuan_buyings/get_offline_address?type=-1',function(data){
            ship_address = data.address;
            child_address = data.child_address;
            PYS.storage.save('offline_stores',data,48);
        });
    }
    var getShipAddress = function(areaId){
        return ship_address[areaId];
    };
    var getShipChildAddress = function(areaId){
        return child_address[areaId];
    };
    return {
        getBeijingAreas: beijingArea,
        getShipAddress: getShipAddress,
        getShipChildAddress:getShipChildAddress
    }
}

var zitiObj = function(area,height, width){
    var conorder_url = '#TB_inline?inlineId=hiddenModalContent&modal=true&height=' + height + '&width=' + width;
    var choose_area='';
    return {
        generateZitiArea: function(){
            for(var addr in area){
                if (area[addr].children_area){
                    choose_area += '<ul><li><a href="#" class="child_area" area-id="' +addr + '">' + area[addr].name + '</a></li> </ul>';
                    $.each(area[addr].children_area,function(index,item){
                        choose_area += '<ul><li><a style="display: none" href="'+ conorder_url +'" class="thickbox children_area" parent-id ="'+addr+'" area-id="' +index + '">' + item.name + '</a></li> </ul>';
                    });
                }else{
                    choose_area += '<ul><li><a href="'+ conorder_url +'" class="thickbox parent_area" area-id="' +addr + '">' + area[addr].name + '</a></li> </ul>';
                }
            }
            return choose_area;
        },
        bindThickbox: function(){
            $("li a.thickbox").each(function(){
                var that = $(this);
                that.on("click", function(e){
                    $('.thickbox,.child_area,.parent_area').not(that).removeClass("cur");
                    var area_id = $(this).attr("area-id");
                    var parent_id = $(this).attr('parent-id');
                    setData(area_id,parent_id);
                    that.addClass("cur");
                })
            });
        },
        initChildAddress : function(){
            var self = this;
            $('.child_area').each(function () {
                var that = $(this);
                that.on('click', function () {
                    $('.parent_area.thickbox').not(that).removeClass('cur').hide();
                    $('.children_area').show();
                    that.html('其他市区').removeClass('child_area').addClass('cur').bind('click', function () {
                        $('.child_area,.thickbox,.children_area').not(that).removeClass('cur').hide();
                        that.html('昌平区').addClass('child_area');
                        $('.parent_area').show();
                        self.initChildAddress();

                    });
                });
            });
        },
        bindChildAddress:function(){
            this.initChildAddress();
        }
    }

};
function setData(area_id,parent_id){
    var remarkAddress = $('#remark_address');
    var chose_address = parent_id?zitiAddressData.getShipChildAddress(area_id):zitiAddressData.getShipAddress(area_id);
    chose_address = $.map(chose_address, function(value, index) {
        return [value];
    });
    chose_address = chose_address.sort(function(item1,item2){
        return item1['name'].localeCompare(item2['name']);
    });
    var $chose_item = '';
    $.each(chose_address,function(index,item){
        $chose_item+='<s data-shop-id="'+ item['id'] +'" data-can-remark-address="'+item['can_remark_address']+'" data-shop-name="'+item['alias']+'"> <h1>'+item['alias']+'</h1> <label>'+item['name']+'<br/>';
        if(item['owner_phone']){
            $chose_item+='联系电话: '+item['owner_phone']+'   ';
        }
        if(item['owner_name']){
            $chose_item+=' 联系人: '+item['owner_name'];
        }
        $chose_item+='</label> </s>'
    });
    $("#area_list").html($chose_item);
    $("#area_list s").each(function(){
        var that =$(this);
        that.on("click",function(){
            that.css("background-color","#eeeeee");
            var canRemarkAddress = that.data('can-remark-address');
            var shopId = that.data('shop-id');
            //should remark address
            if(canRemarkAddress==1){
                remarkAddress.show();
            }else{
                remarkAddress.hide();
            }
            $("#chose_address").html($('label',that).text()).data('shopId', shopId);
            tb_remove();
        })
    });
}
var zitiAddressData = zitiAddress($('#hiddenModalContent').data('zitiType'));

function isBlank(str) {
    return (!str || /^\s*$/.test(str));
}

function isPhoneNum(val){
    //var pattern=/(^(([0\+]\d{2,3}-)?(0\d{2,3})-)(\d{7,8})(-(\d{3,}))?$)|(^0{0,1}1[3|4|5|6|7|8|9][0-9]{9}$)/;
    var pattern = /^[0-9]+$/;
    if((val.length=11)&&pattern.test(val)) {
        return true;
    }else{
        return false;
    }
}

