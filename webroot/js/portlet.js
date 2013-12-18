var portlet_insert_container = null;
var add_portelt_toolbar_flag = false;
/*
 * setlayout('#container-part1','12_12');
 * 修改布局的比例，例数。如12_12,8_8_8. 用下划线隔开，数字和为外围的portlet的值。最外围的为24
 */
function setlayout(container,grids)
{
	var grids_a = grids.split('_');
	portletnum = grids_a.length;
	var contains = $(container).children('.ui-contain');
	contains.each(function(i){
		if(i>=grids_a.length){
			$(this).prev().append($(this).html());			
			$(this).remove();
			return ;
		}
		var classname = $(this).attr('class');		
		$(this).attr('class',classname.replace(/span\d+/,'span'+grids_a[i]));
	});
	
	var containsize = contains.size();
	if(containsize < grids_a.length){
		var i = contains.size();
		for(;i<grids_a.length;i++){
			$('<div class="ui-contain span'+grids_a[i]+'"></div>').appendTo(container).append(contains.eq(containsize-1).children(':last'));
		}
		
		$( ".ui-contain" ).sortable({
			connectWith: ".ui-contain",
			handle:'.ui-portlet-header',forceHelpSize:true
		});
	}	
}

/**
 *增加编辑模板时的样式及操作事件
 */
function addLayoutCssAndEvent(){
	if(add_portelt_toolbar_flag){
		return false;
	}
	add_portelt_toolbar_flag = true;
	
	$( "#maincontent" ).addClass('edit-maincontent'); // 通过edit-maincontent定义内部内容在编辑时的各个样式
	var rowcolors = ['blue','green','yellow','red']
	$( "#maincontent" ).find('.row:not(.row .row)').each(function(i){
		$(this).css('background-color',rowcolors[i]);
	})
	
	// portlet头部区域增加最大最小化按钮，内容区域增加css样式
	$( ".ui-portlet" ).find( ".ui-portlet-header" ).addClass( "ui-widget-header" )
			.prepend( "<ul class=\"portlet-edit-toolbar ui-float-right\"><li><i class=\"icon-edit\"></i></li><li><i class=\"icon-minus  tool-min\"></i></li><li><i class=\"icon-remove\"></i></li><ul>")
			.find('.icon-edit').click(function(){
				editPortlet( $(this).parents('.ui-portlet').attr('rel') );
				
			}).end().find('.icon-remove').click(function(){
				if(confirm('Are your sure to delete this?')){
					$(this).parents('.ui-portlet:first').remove();
				}
	});
	
	//隐藏头部的portlet鼠标移上去后，显示头部
	$( ".ui-portlet" ).find('.ui-portlet-header:hidden').each(function(){
		$(this).parent().hover(function(){				
			$( ".ui-portlet-header",this).show();//.css('z-index',200);
			$( ".ui-portlet-header",this).width($(this).width()-2);
		},function(){
			$( ".ui-portlet-header",this).hide();
		});
	});
	
	//最小化，及还原的折叠按钮
	$( ".ui-portlet-header:visible .tool-min" ).click(function() {
		$( this ).toggleClass( "icon-minus" ).toggleClass( "icon-plus" );
		$( this ).parents( ".ui-portlet:first" ).find( ".ui-portlet-content" ).toggle("fast");
	});
	
	$( ".ui-contain" ).addClass('ui-contain-active');
	
	/* row 外围容器工具栏，容纳内部ui-contain。 不包含嵌套的row  */
	$( "#maincontent .row:not(.row .row)" ).prepend( "<div class='ui-row-toolbar ui-widget-header ui-helper-clearfix ui-corner-top' style='line-height:32px;'><ul class='ui-float-right'><li><i class='icon-asterisk' title='配置行内分栏比例'></i></li><li><i class='icon-plus' title='新增一行'></i></li><li><i class='icon-remove' title='删除此行'></i></li></ul><h3>外部区域</h3></div>")
	$( ".row .ui-row-toolbar" ).find('.icon-plus').click(function(){
		var obj = $(this).parents('.row:first');
		add_layout_dialog(obj);
	}).end().find('.icon-remove').click(function(){
		if(confirm('你确认删除吗？')){			
			if($( "#maincontent .row" ).size()==1){
				alert('只剩最后一行了，不能删除');return false;
			}
			else{
				$(this).parents('.row:first').remove();
			}
		}
	}).end().find('.icon-asterisk').click(function(){
		var obj = $(this).parents('.row:first');
		setLayoutProp(obj,24);
	});
	
	/* ui-contain内部容器，容纳portlet与ui-horizontal-layout水平布局 */
	$( "#maincontent .ui-contain" ).prepend( "<div class='ui-inner-contain-toolbar ui-widget-header ui-helper-clearfix ui-corner-top' style='line-height:32px;'><ul class=\"portlet-edit-toolbar ui-float-right\"><li><i class='icon-resize-horizontal' title='split'></i></li><li><i class='icon-plus' title='insert region content'></i></li><li><i class='icon-remove' title='delete this'></i></li></ul><h3>内部区域</h3></div>")
	$( ".ui-contain .ui-inner-contain-toolbar" ).find('.icon-plus').click(function(){
		portlet_insert_container = $(this).parents('.ui-contain:first');
		publishController.open_dialog(BASEURL+'/regions/lists/1',{title:'Insert Region Area'});
		
	}).end().find('.icon-resize-horizontal').click(function(){
		// .ui-contain内部添加一个ui-horizontal-layout水平布局
		// Nesting columns,嵌套的row
		var contain_ui = $(this).parents('.ui-contain:first'); // 先获取contain，然后reset清空，再追加内容。(新追加的必须在清空后，因为新追加的无法调reset)
		resetPortletLayout();	// 清空后，$(this)对应的工具栏没有了
		var result = getGridCss(contain_ui.attr('class'));
		var html = '';
		if(result){
			html = '<div class="ui-contain span'+(result[1]/2)+'"></div>';
			html += '<div class="ui-contain span'+(result[1] - result[1]/2)+'"></div>';
			//alert(html);
		}
		contain_ui.append('<div class="ui-horizontal-layout row">'+html+'</div>');
		//alert($(this).parents('.ui-contain:first').html());
		// 先追加内容，然后再重新trigger，
		triggerPortletLayout();
		
	}).end().find('.icon-remove').click(function(){
		if(confirm('你确认删除ui-contain吗？')){
			var currentui= $(this).parents('.ui-contain:first');
			var uiclass = currentui.attr('class');			
			var result =  getGridCss(uiclass);
			if(result[1]){
				var siblobj;
				//alert(currentui.siblings('.ui-contain').size());
				if(currentui.siblings('.ui-contain').size()==0){ // 没有了兄弟节点，且不是ui-horizontal-layout
					alert('只剩最后一个了，不能删除。如要删除，请删除上层布局。');return false;
				}
				if(currentui.next().size()){
					siblobj = currentui.next();
				}
				else if(currentui.prev().size()){
					siblobj = currentui.prev();
				}
				else{
					alert('只剩最后一个了，不能删除');return false;
				}
				$(this).parents('.ui-contain:first').remove();
				var horizontal_layout = siblobj.parents('.ui-horizontal-layout');
				if(horizontal_layout.children().size()==1){ 
					// ui-horizontal-layout不存在只有一列的情况，只有一列时，自动删除，将内容放入上级中
					horizontal_layout.find('.ui-portlet').appendTo(horizontal_layout.parent());
					horizontal_layout.remove();
				}
				var uiclass = siblobj.attr('class');				
				var siblresult =  getGridCss(uiclass);;
				if(siblresult){
					siblobj.removeClass(siblresult[0]);
					var gridnum = parseInt(result[1])+parseInt(siblresult[1]);
					siblobj.addClass('span'+gridnum);
					if(siblobj.siblings('.alpha,.omega').size()>0){
						siblobj.parent().children().removeClass('alpha').removeClass('omega');
						siblobj.parent().children(':first').addClass('alpha');
						siblobj.parent().children(':last').addClass('omega');
					}
				}
				
			}
			
			
		}
	});
	/* ui-contain内部分栏容器（即.spanxx内部嵌套.spanxx），容纳portlet与ui-horizontal-layout水平布局 */
	$( ".ui-contain .ui-horizontal-layout" ).prepend( "<div class='ui-inner-horizontal-toolbar ui-widget-header ui-helper-clearfix ui-corner-top' style='line-height:32px;margin-left:10px;'><ul class=\"ui-float-right\"><li><i class=' icon-asterisk'></i></li><li><i class='icon-remove'></i></li></ul><h3>内部分栏区域</h3></div>")
	$( ".ui-horizontal-layout .ui-inner-horizontal-toolbar" ).find('.icon-asterisk').click(function(){
		var obj = $(this).parents('.ui-horizontal-layout:first');
		//add_layout_dialog(obj);
		var result = getGridCss($(obj).parent().attr('class'));
		setLayoutProp(obj,result[1]);
	}).end().find('.icon-remove').click(function(){
		if(confirm('你确认删除ui-horizontal-layout吗？')){
			$(this).parents('.ui-horizontal-layout:first').remove();
		}
	});
	
	$('.ui-icons li').addClass('ui-state-default ui-corner-all').hover(function(){
		$(this).addClass('ui-state-hover');
	},function(){
		$(this).removeClass('ui-state-hover');
	});
}

/**
 * 获取具有的grid css样式
 */
function getGridCss(classstring){	
	var reg = /span(\d+)/;
	var result =  reg.exec(classstring);
	return result;
}

/**
 *移除css与事件
 */
function removeAddedCssAndEvent(){
	add_portelt_toolbar_flag = false;
	$('#maincontent').removeClass('edit-maincontent');
	$( "#maincontent" ).find('.row').removeAttr('Style');
	$(".ui-portlet").unbind();
	//$('#maincontent .span24').removeClass('ui-state-default');
	$(".ui-contain").removeClass('ui-contain-active');
	$('.portlet-edit-toolbar').remove();
	$('.ui-contain-toolbar').remove();
	$('.ui-row-toolbar').remove();
	$('.ui-contain .ui-inner-contain-toolbar').remove();
	$('.ui-horizontal-layout .ui-inner-horizontal-toolbar').remove();
}

function triggerPortletLayout(){
	addLayoutCssAndEvent();	
	/*
	 * 对容器中的portlet的排序操作
	 * 每一个span类的class及为一个容器，都增加.ui-contain样式。 如.span2,.span24
	 **/
	$( ".ui-contain" ).sortable({
		connectWith: ".ui-contain", // span容器内部内容可跨区域移动，其他均不可跨区域移动。
		//handle:'.ui-portlet-header,.ui-horizontal-layout',
		forceHelpSize:true,
		forcePlaceholderSize: true,
		items: ".ui-portlet,.ui-horizontal-layout",
		opacity: 0.6,
		cursor: 'move',
		helper: 'clone'
	});
	/* 每一行内部的内容，可左中右进行调序，不可跨区域移动 */
	$( ".row" ).sortable({
		handle:'.ui-inner-contain-toolbar',
		forceHelpSize:true,
		forcePlaceholderSize: true,
		items: ".ui-contain:not(.ui-contain .ui-contain)",
		opacity: 0.6,
		cursor: 'move',
		helper: 'clone'
	});
	/*对主区域的每一行row的排序*/
	$('#maincontent').sortable({ //.row
		//helper: 'clone',
		handle:'.ui-row-toolbar',
		//placeholder:'ui-state-highlight',
		forceHelpSize:true,
		cursor: 'move',
		items: ".row",
		opacity: 0.6,
		// cancel: '.ui-contain-toolbar',
		forcePlaceholderSize: true	
	});
	$('.ui-horizontal-layout').sortable({
		//helper: 'clone',
		handle:'.ui-portlet-header,.ui-inner-contain-toolbar',
		placeholder:'ui-state-highlight',
		forceHelpSize:true,
		cursor: 'move',
		items: ".ui-contain:not(.ui-horizontal-layout .ui-contain .ui-contain)",
		opacity: 0.6,
		stop: function(event, ui) {
			$(this).children('.ui-contain');
			$(this).children('.ui-contain:first');
			$(this).children('.ui-contain:last');
		},
		// cancel: '.ui-contain-toolbar',
		forcePlaceholderSize: true	
	})
}
function insertPortlet(id){
	portlet_insert_container.append()
	publishController.close_dialog();
	$.get(BASEURL+'/regions/load/'+id,function(data){
            alert(data);
		resetPortletLayout();
		portlet_insert_container.append(data);		
		triggerPortletLayout();
	});
}
function editPortlet(id){	
	publishController.open_dialog(ADMIN_BASEURL+'/admin/regions/edit/'+id,{title:'Edit Region Info',width:960});
}
function copyPortlet(id){
	publishController.open_dialog(ADMIN_BASEURL+'/admin/regions/edit/'+id+'/copy',{title:'Copy Region Info',width:960});
}
function deletePortlet(id){
	ajaxAction(ADMIN_BASEURL+'/admin/regions/trash/'+id,'','','afterDeletePortlet');
}
function resetPortletLayout(){
	removeAddedCssAndEvent();
	/* 排序的事件sortable无需注销 */
//	$('#maincontent').sortable( "destroy" );
//	$('.row').sortable( "destroy" );
//	$('.ui-horizontal-layout').sortable( "destroy" );
//	$( ".ui-contain" ).sortable( "destroy" )
}

function saveTemplate(){
	if(!viewFileName){
		alert('模板文件名（viewFileName）为空，此页面不支持模板编辑');
		return false;
	}
	if(!confirm('确定要保存吗？保存后原有界面将不可恢复')){
		return false;
	}
	try{resetPortletLayout();}catch(err){};
	var data_obj = $('#maincontent').clone();	
	var html = '';	
	data_obj.find('.ui-sortable').each(function(){
		$(this).removeClass('ui-sortable');
	});
	data_obj.find('.ui-portlet').each(function(){
		$(this).removeAttr('style');
		$(this).removeAttr('class').addClass('ui-portlet');
		$(this).html('')
	})
	data_obj.find('style').remove();
	html = data_obj.html();	
	
	data_obj = null; // 释放 data_obj
	ajaxAction(ADMIN_BASEURL+'/admin/template_histories/edit',{'data[TemplateHistory][content]':html,'data[TemplateHistory][name]':viewFileName},null,'saveTemplate');
	//alert(html);
}

rs_callbacks.createAPortlet = function(request){
	publishController.close_dialog();
	$.get(BASEURL+'/regions/load/'+request.data.id,function(data){		
		$('#maincontent .ui-contain:last').append(data);
		resetPortletLayout();
		triggerPortletLayout();
	});
};
rs_callbacks.updatePortlet = function(request){
	publishController.close_dialog();
	//alert(request.data);
	if($('#portlet-'+request.data.id).size() > 0){
		$.get(BASEURL+'/regions/load/'+request.data.id+'.html',function(data){
			$('#portlet-'+request.data.id).html($("<div />").append(data).find('.ui-portlet').html());			
			resetPortletLayout();
			triggerPortletLayout();
		});
	}
};
rs_callbacks.saveTemplate = function(request){	
	alert('save over!');
};


function  add_layout_dialog(current_contain){
	$("#contain-24-edit-form input").unbind().each(function(i){
		$(this).keyup(function(){
			layoutwidthchange(i,24);
		});
		
	});
	$( "#contain-24-edit-form" ).dialog({
			autoOpen: true,
			height: 300,
			width: 350,
			modal: true,
			buttons: {
				"Create an Layout": function() {
					var bValid = true;
					var width1 = parseInt($('#layout-first-width').val());
					var width2 = parseInt($('#layout-second-width').val());
					var width3 = parseInt($('#layout-third-width').val());
					var width4 = parseInt($('#layout-fourth-width').val());
					if(width1+width2+width3+width4 !=24){
						alert('和必须为24')	
					}
					else{
						var widthinputs = ['#layout-first-width','#layout-second-width','#layout-third-width','#layout-fourth-width'];
						var contain_html = '';
						for(var i=0;i<4;i++){
							if(parseInt($(widthinputs[i]).val())>0){
								contain_html +='<div class="ui-contain span'+$(widthinputs[i]).val()+'"></div>';
							}
						}
						resetPortletLayout();//先清空layout的样式，然后再after添加内容
						current_contain.after('<div class="span24 clearfix">'+contain_html+'</div>');
						$( this ).dialog( "close" );						
						triggerPortletLayout(); // 追加layout操作的样式
					}
				},
				Cancel: function() {
					$( this ).dialog( "close" );
				}
			},
			close: function() {
				
			}
		});
}

/* 设置布局中内部元素的比例，针对span24,ui-horizontal-layout */
function setLayoutProp(obj,totalrate){
	$("#contain-24-edit-form input").unbind().each(function(i){
		$(this).keyup(function(){
			//alert(i+'--'+totalrate)
			layoutwidthchange(i,totalrate);
		});
		
	});
	$( "#contain-24-edit-form" ).dialog({
		autoOpen: true,
		height: 300,
		width: 350,
		modal: true,
		buttons: {
			"Create an Layout": function() {
				var bValid = true;
				var width1 = parseInt($('#layout-first-width').val());
				var width2 = parseInt($('#layout-second-width').val());
				var width3 = parseInt($('#layout-third-width').val());
				var width4 = parseInt($('#layout-fourth-width').val());
				if(width1+width2+width3+width4 !=totalrate){
					alert('和必须为'+totalrate)	
				}
				else{
					var widthinputs = ['#layout-first-width','#layout-second-width','#layout-third-width','#layout-fourth-width'];
					var contain_html = '';
					var j=0;
					resetPortletLayout();//先清空layout的样式，然后再after添加内容
					for(var i=0;i<4;i++){
						if(parseInt($(widthinputs[i]).val())>0){
							if($(obj).children('.ui-contain').size()>j){
								var current_ui = $(obj).children('.ui-contain').eq(j);
								
								var result = getGridCss(current_ui.attr('class'));
								if(result){
									current_ui.removeClass(result[0]).addClass('span'+$(widthinputs[i]).val());
								}
							}
							else{
								$(obj).append('<div class="ui-contain span'+$(widthinputs[i]).val()+'"></div>')
							}
							j++;
						}
					}					
					$(obj).children('.ui-contain').each(function(i){
						if(i+1>j){
							$(this).children().appendTo($(obj).children(':first'))
							$(this).remove();
						}
					})
					if(totalrate < 24){
						$(obj).children().removeClass('alpha').removeClass('omega');
						$(obj).children(':first').addClass('alpha');
						$(obj).children(':last').addClass('omega');
					}
					$( this ).dialog( "close" );						
					triggerPortletLayout(); // 追加layout操作的样式
				}
			},
			Cancel: function() {
				$( this ).dialog( "close" );
			}
		},
		close: function() {
			
		}
	});
}

function layoutwidthchange(num,totallayout){
	var widthinputs = ['#layout-first-width','#layout-second-width','#layout-third-width','#layout-fourth-width'];
	var total=0;
	if(!totallayout) 	totallayout = 24;
	
	if(parseInt($(widthinputs[num]).val())>totallayout){
		$(widthinputs[num]).val(totallayout)
	}
	
	for(var i=0;i<4;i++){
		if(isNaN(parseInt($(widthinputs[i]).val()))){
			$(widthinputs[i]).val('0')
		}
		total+=parseInt($(widthinputs[i]).val());
	}
	$('#text-width-total').html(total);
	var subval = 0;
	if(total > totallayout){
		subval = total - totallayout;
	}
	else{
		subval = totallayout - total;
	}
	$('#text-width-sub').html(subval);
	if(total!=totallayout){
		for(var i= num+1;i< 4+num;i++){
			var j = i%4;
			if(total > totallayout){
				if(parseInt($(widthinputs[j]).val())-subval>=0){
					$(widthinputs[j]).val(parseInt($(widthinputs[j]).val())-subval);
					break;
				}
				else{
					subval = subval - parseInt($(widthinputs[j]).val());
					$(widthinputs[j]).val(0);
				}
			}
			else{
				$(widthinputs[j]).val(parseInt($(widthinputs[j]).val()) + subval);
				break;
			}
		}
	}
	
}