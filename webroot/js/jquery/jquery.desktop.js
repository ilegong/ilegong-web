var open_window;

$.tools.validator.localize("zh", {
	'*'			: '请检查输入格式是否正确',
	':email'  	: '请输入有效的邮箱地址。',
	':number' 	: '请输入有效的数字。',
	':url' 		: '请输入有效的网址。',
	'[max]'	 	: '最大值不大于$1',
	'[min]'		: '最小值不小于$1',
	'[required]'	: '此项必填，不允许为空。'
});

var JQD = (function($, window, document, undefined) {
	
	/**
	 * 记录当前最顶层可见的窗口，在顶层窗口切换时，隐藏显示时会用到。
	 */
	var topwin = null;
	
	/**
	 * 当链接的target为_self时，在本窗口打开链接的内容. 如分页链接.
	 * obj 可绑定onclosing时间，在关闭窗口时调用 
	*/
	function open_window(url,obj)
	{
		var window_task_id = url.replace(/http:\/\/|\/|\.|\?|:|=|#|&/g,'_');
		
		if($(obj).attr('target')=='_self'){
			window_task_id = $(obj).closest('.ui-dialog-content').attr('id');
			window_task_id = window_task_id.substr(7);
		}
		if(window_task_id==''|| url == '#'){
			return false;
		}
		var window_id='window_'+window_task_id;
		var task_id = 'task_'+window_task_id;
		var resultObj = $("#desktop");
		
		topwin = '#'+window_id; /*顶层窗口*/
		
		if($(obj).html()){ // is dom element
			var window_pic = $(obj).find('img').attr('src');
			var window_title = $(obj).html();
			var window_type = $(obj).data('type');
			var window_width = $(obj).data('width');
			var window_height = $(obj).data('height');
			var window_resizable = $(obj).data('resizable');
			var window_callback = $(obj).data('callback');
			var window_callbackargs;
			eval( "window_callbackargs= "+$(obj).data('callbackargs')+";");
		}
		else{ // is obj like {title:'',icon:'',type:''}
			var window_title = obj.title;
			var window_type = obj.type;
			var window_pic = obj.icon;
			var window_width = obj.width;
			var window_height = obj.height;
			var window_resizable = obj.resizable;
			var window_callback = obj.callback;
			var window_callbackargs = obj.callbackargs;
		}
		if(window_pic==undefined){
			window_pic='images/deskicons/nofile.png';
		}
		if(!window_width || !window_height){
			window_width = 960;
			window_height = $(window).height()-120;
		}
		if(window_resizable!=false){
			window_resizable = true;
		}
		
		if(url.search(/\?/)!=-1){
			url+='&inajax=1';
		}
		else{
			url+='?inajax=1';
		}
		
		if(window_type=='ajax'){
			
			//ajaxAction(url,postdata,form,callback_func_name,moreags)
			return ajaxAction(url,null,null,window_callback,window_callbackargs);
		}
		
		//create bar_bottom task item.
		if(window_task_id!=''){
			if ($('#'+task_id).length > 0) {
				$('#'+task_id).show();
				$('.task_icon').removeClass('taskicon_active');
				$('#tasklist_'+window_task_id).addClass('taskicon_active');	
				$('#startNav').hide();
			}else{
				var dock_li_html='<li id="'+task_id+'" style="display: inline;" class="dock_window">'+
				'<span id="#'+task_id+ '" class="task_thumb abs task_close"></span>'+
				'<a id="tasklist_'+window_task_id+'" href="#'+window_id+'" class="icon task_icon">'+window_title+'</a></li>';
				if ($('#'+task_id).length <= 0) {
					//$('div.window').removeClass('window_stack');
					$('ul#dock').append(dock_li_html);
					$('.task_icon').removeClass('taskicon_active');
					$('#tasklist_'+window_task_id).addClass('taskicon_active');
					$('#startNav').hide();
				}
			}
		}
		window.open_window = open_window;
		
		function loadWindowContent(url){
			$.ajax({
				url:url,
				data:{},
				complete: function (result, stat) {
					//alert(result);alert(stat);
				},
				success:function(data,textStatus){
					$('#'+window_id).data('url',url);
					$('#'+window_id).data('resizable',window_resizable);
					$('#'+window_id).css('overflow-x','hidden');
					$('#'+window_id).css('overflow-y','auto');
					$('#'+window_id).dialog( "option", {
						width: window_width,
						height: window_height
					});
					$('#'+window_id).dialog( "option","position",['center','center']);
					
					// 防止窗口高度超出window可见区域。超出时减小高度，并设置滚动条
					$('#'+window_id).html(data);
					page_loaded();
					
					/**
					 * TODO.submitcallback,提交后回调事件，刷新父窗口数据，关闭新窗口等，
					 */
					
					if($('#'+window_id).find('form').size()>0){
						var form = $('#'+window_id).find('form').eq(0);
						form.bind("onSubmitSucess", function (e) {
							$('#'+window_id).dialog('close');
		                });
		                
						$('#'+window_id).dialog( "option", "buttons",{
							"提交": function() {
								//使用eq获取jquery对象，触发bind的submit事件
								var dialog = this;
								form.submit();//提交，完成后，关闭窗口
								return false;
							},
							"重置": function() {
								//使用get获取dom对象，触发form对象的reset事件
								$( this ).dialog( "widget" ).find('form').get(0).reset();
							}
						}).dialog( "widget" ).find('button').addClass('btn btn-primary');
					}
					
					
					$('.nav-tabs a','#'+window_id).click(function (e) {
				        e.preventDefault();
				        $(this).blur();
				        $(this).tab('show');
				    });  //初始化tab
					$('.nav-tabs a:first','#'+window_id).tab('show');//显示第一个tab
				    $('.dropdown-toggle','#'+window_id).dropdown();
				},
				dataType:"html",
			    cache:false
			});
		}
		
		//如果页面中已经存在id名为window_id的则不添加data内容到desktop里
		if($('#'+window_id).length>0){
			$('#'+window_id).dialog( "option", "position", ['center','center']);
			$('#'+window_id).dialog('open').dialog( "moveToTop" );
			loadWindowContent(url);
			return false;
			$('div.window').removeClass('window_stack');
			$('#'+window_id).addClass('window_stack window_visible').show();
			$('.task_icon').removeClass('taskicon_active');
			$('#tasklist_'+window_task_id).addClass('taskicon_active');
			$('#startNav').hide();
		}else{
			//create window
			if($('#'+window_id).size()<1){
				var options = {
						title:window_title,
						closeOnEscape:false,
						autoOpen: true,
						width: 200,
						open:function(event, ui){
							/* 替换关闭按钮 */
		                    $(".ui-dialog-titlebar-close").replaceWith('<span class="float_right"><a class="window_min" href="#"></a><a class="window_resize" href="#"></a><a class="window_close" href="#icon_dock_drive"></a></span>');
						},
						close: function(event, ui) {
							$(this).find('.cke_skin_kama').each(function(i){
								/* 注销ckeditor，防止重复打开时，对象已存在，无法再次初始化相同id的编辑器 */
								var ckid = this.id.replace(/cke_/,'');
								if(CKEDITOR.instances[ckid]){
									CKEDITOR.instances[ckid].destroy();
								}
							});
							$(obj).trigger('onclosing');
							$('#'+task_id).remove();
							$(this).dialog("destroy"); 
							$(this).remove();
						},
						dragStart:function(event, ui){
							$('#'+window_id).dialog( "widget" ).css('opacity','0.8');
							//$('#'+window_id).hide();
							$('.iframe_dialog').hide();
						},
						dragStop:function(event, ui){
							$('#'+window_id).dialog( "widget" ).css('opacity','1');
							//$('#'+window_id).show();
							$('.iframe_dialog').show();
						}
				};
				if(window_resizable==false){
					options.resizable = false;
				}
				else{
					options.resizeStop=function(event, ui) {
						var win = $('#'+window_id).dialog( "widget" );
						var areaHeight = win.height()-160;
					    var areaWidth = win.width()-10;
				        win.find('.jqgrid-list').jqGrid('setGridHeight',areaHeight).jqGrid('setGridWidth',areaWidth);
					};
				}
				$('<div id="'+window_id+'">loading...</div>').appendTo('#desktop');				
				$('#'+window_id).dialog(options).dialog( "widget" ).appendTo('#desktop');
				
				if(window_type=='iframe'){
					$('#'+window_id).dialog( "option", {
						width: 960,
						height:500
					});
					$('#'+window_id).html('<iframe width="100%" border="0" height="100%" src="'+url+'"></iframe>');
					$('#'+window_id).dialog( "option","position",['center','center']);
					$('#'+window_id).addClass('iframe_dialog');
				}
				else{
					loadWindowContent(url);
				}			
			}
			else{
				$('#'+window_id).dialog( "option", "position", ['center','center']);
			}
			$('#'+window_id).dialog('open');
		}
	}
	
  // Expose innards of JQD.
  return {
    go: function() {
      for (var i in JQD.init) {
        JQD.init[i]();
      }
    },
    init: {
      frame_breaker: function() {
        if (window.location !== window.top.location) {
          window.top.location = window.location;
        }
      },
      //
      // Initialize the clock.
      //
      clock: function() {
        var clock = $('#clock');

        if (!clock.length) {
          return;
        }

        // Date variables.
        var date_obj = new Date();
        var hour = date_obj.getHours();
        var minute = date_obj.getMinutes();
        var day = date_obj.getDate();
        var year = date_obj.getFullYear();
        var second = date_obj.getSeconds();
        var suffix = 'AM';

        // Array for weekday.
        var weekday = [
          '周日',
          '周一',
          '周二',
          '周三',
          '周四',
          '周五',
          '周六'
        ];
        
      //格式化日期  
        function getFormat(time){  
            if(time.toString().length == 1){  
                time = "0"+time  
            }  
            return time;  
        }
        day = getFormat(day);hour = getFormat(hour);minute = getFormat(minute);
        second = getFormat(second);
        

        // Array for month.
        var month = [
          'Jan',
          'Feb',
          'Mar',
          'Apr',
          'May',
          'Jun',
          'Jul',
          'Aug',
          'Sep',
          'Oct',
          'Nov',
          'Dec'
        ];

        // Assign weekday, month, date, year.
        weekday = weekday[date_obj.getDay()];
        month = month[date_obj.getMonth()];

        // Build two HTML strings.
        var clock_time = weekday + ' ' + hour + ':' + minute +':'+ second;
        var clock_date = month + ' ' + day + ', ' + year;

        // Shove in the HTML.
        clock.html(clock_time).attr('title', clock_date);

        // Update every 60 seconds.
        setTimeout(JQD.init.clock, 1000);
      },
      //
      // Initialize the desktop.
      //
      desktop: function() {
        // Alias to document.
        var d = $(document);
        
        
        $.contextMenu({
            selector: 'ul#dock li', 
            zIndex:1000000,
            build: function($trigger, ev) {
            	var winid = '#window_'+$trigger.attr('id').substr(5); // task_
                // this callback is executed every time the menu is to be shown
                // its results are destroyed every time the menu is hidden
                // e is the original contextmenu event, containing e.pageX and e.pageY (amongst other data)
                return {
                    callback: function(key, options) {
                        if(key =='min'){
                        	$(winid).closest('div.ui-dialog').hide();
                        }
                        else if(key =='max'){
                        	$(winid).closest('div.ui-dialog').show();
                        	JQD.util.window_resize(winid);
                        }
                        else if(key =='close'){
                        	$(winid).closest('.ui-dialog').find('.ui-dialog-content').dialog('close');
                        }
                    },
                    items: {
                    	"min": {name: "最小化", icon: "edit"},
                    	"max": {name: "最大化", icon: "edit"},
                    	"close": {name: "关闭", icon: "edit"},
                    }
                };
            }
        });
        
        $.contextMenu({
            selector: '#desktop', 
            zIndex:1000000,
            build: function($trigger, ev) {
            	if($(ev.target).closest('.shortcut').length || $(ev.target).closest('.ui-dialog').length){
            		return false;
            	}
                // this callback is executed every time the menu is to be shown
                // its results are destroyed every time the menu is hidden
                // e is the original contextmenu event, containing e.pageX and e.pageY (amongst other data)
                return {
                    callback: function(key, options) {
                        if(key =='new_category'){
                        	open_window(ADMIN_BASEURL+'/admin/categories/add',{'title':'新建栏目'});
                        }
                        else if(key =='new_article'){
                        	open_window(ADMIN_BASEURL+'/admin/articles/add',{'title':'新建文章'});
                        }
                        else if(key =='new_product'){
                        	open_window(ADMIN_BASEURL+'/admin/products/add',{'title':'新建产品'});
                        }
                        else if(key =='showdesktop'){
                        	$('#show_desktop').trigger('mousedown');
                        }
                    },
                    items: {
                    	"new": {
                            "name": "新建", 
                            "items": {
                                "new_category": {"name": "新建栏目"},
                                "new_article": {
                                    "name": "新建新闻",
                                },
                                "new_product": {"name": "新建产品"}
                            }, icon: "edit"
                        },
                        "showdesktop": {name: "显示桌面", icon: "edit"}	                       
                    }
                };
            }
        });

        // Cancel mousedown.
        d.mousedown(function(ev) {
          var tags = ['a', 'button', 'input', 'select','textarea', 'tr'];
          
          /* hide start menu */
          if (!$(ev.target).closest(['#startmenu','#Start-Menu-Button']).length) {
	          $('#Start-Menu-Button.active').removeClass('active');
	          $('#startmenu').hide();
          }

          if (!$(ev.target).closest(tags).length) {
            JQD.util.clear_active();
            ev.preventDefault();
            ev.stopPropagation();
          }
        });

        // Cancel right-click.
        d.on('contextmenu', function() {
          return false;
        });

        /**
         * 带#的链接点击取消事件
         */
        d.on('click', 'a', function(ev) {
          var url = $(this).attr('href');
          this.blur();

          if (url && url.match(/^#/)) {
            ev.preventDefault();
            ev.stopPropagation();
          }
        });
        
        /**
		 * // 窗口内部链接(非锚点，且不包含onclick事件，无target属性)
         * 窗口内的链接，点击后，打开新窗口。
         *  当target为_self时，替换本窗体的内容（在open_window函数中处理）。
         */
		
		d.on('click', '.ui-dialog-content a', function() {
			var url = null;
			if($(this).data('url')){
				url = $(this).data('url');
			}
			//javascript:
			if(!this.href.match(/#/) && !this.href.match(/javascript:/) && typeof($(this).attr('onclick'))=='undefined' && (typeof($(this).attr('target'))=='undefined' || $(this).attr('target')=='_self')){ 
				url = this.href;
			}
			if(url && !url.match(/#/)){
				open_window(url,this);
				return false;
			}								
	    });

        // Make top menus active.
        d.on('mousedown', 'a.menu_trigger', function() {
          if ($(this).next('ul.menu').is(':hidden')) {
            JQD.util.clear_active();
            $(this).addClass('active').next('ul.menu').show();
          }
          else {
            JQD.util.clear_active();
          }
        });

        // Transfer focus, if already open.
        d.on('mouseenter', 'a.menu_trigger', function() {
          if ($('ul.menu').is(':visible')) {
            JQD.util.clear_active();
            $(this).addClass('active').next('ul.menu').show();
          }
        });

        // Cancel single-click.
        d.on('mousedown', 'a.icon', function() {
          // Highlight the icon.
          JQD.util.clear_active();
          $(this).addClass('active');
          return false;
        });

        // Respond to double-click.
        d.on('dblclick', 'a.shortcut', function() {
          var url;
          if(!this.href.match(/#/)){
        	  url = this.href;
          }
          else{
        	  url = $(this).data('url');
          }
    	  open_window(url,this);
    	  JQD.util.clear_active();
        });
        
        $('#programs > li > a').hover( function(){
            $(this).tab('show');
        });
        
        d.on('click', '.startmenu #links a,', function() {
	        var url = $(this).data('url');
	        //javascript:
			if(typeof(url)!='undefined' && !url.match(/#/) && !url.match(/javascript:/) && typeof($(this).attr('onclick'))=='undefined'){ 
				open_window(url,this);
			}	    	  
	    	JQD.util.clear_active();
	    	return false;
	   });

        // Make icons draggable.
        d.on('mouseenter', 'a.shortcut', function() {
          $(this).off('mouseenter').draggable({
            revert: true,
            containment: 'parent'
          });
        });

        // Taskbar buttons.
        d.on('click', '#dock a', function() {
          // Get the link's target.
          var x = $($(this).attr('href')).closest('.ui-dialog');
          var cur_win = $(this).attr('href');
          // Hide, if visible.
          if (cur_win==topwin && x.is(':visible')) {
            x.hide();
            var zIndex=0;
            $('.ui-dialog:visible').each(function(){
            	if(zIndex<$(this).css('z-index')){
            		zIndex = $(this).css('z-index');
            		topwin = '#'+$(this).find('.ui-dialog-content').attr('id');
            	}
            });
          }
          else {
        	topwin = cur_win;
        	$(cur_win).dialog( "moveToTop" );
            // Bring window to front.
            JQD.util.window_flat();
            x.show().addClass('window_stack');
          }
          return false;
        });
        // Double-click top bar to resize, ala Windows OS.
        d.on('dblclick', 'div.ui-dialog-titlebar', function() {
          JQD.util.window_resize(this);
        });

        

        // Minimize the window.
        d.on('click', 'a.window_min', function() {
          $(this).closest('div.ui-dialog').hide();
        });

        // Maximize or restore the window.
        d.on('click', 'a.window_resize', function() {
          JQD.util.window_resize(this);
        });

        // Close the window.
        d.on('click', 'a.window_close', function() {
          $(this).closest('.ui-dialog').find('.ui-dialog-content').dialog('close');
          //$($(this).attr('href')).hide('fast');
        });
        // Double click top bar icon to close, ala Windows OS.
        d.on('dblclick', 'div.ui-dialog-titlebar img', function() {
          $(this).closest('.ui-dialog').find('.ui-dialog-content').dialog('close');
          return false;
        });

        // Show desktop button, ala Windows OS.
        d.on('mousedown', '#show_desktop', function() {
          // If any windows are visible, hide all.
          if ($('div.ui-dialog:visible').length) {
            $('div.ui-dialog').hide();
          }
          else {
            // Otherwise, reveal hidden windows that are open.
            $('#dock li:visible a').each(function() {
            	$($(this).attr('href')).closest('.ui-dialog').show();            	         
            });
          }
        });
        
        d.on('mousedown', '#Start-Menu-Button', function() {
            if ($(this).hasClass('active')) {
              $('#startmenu').hide();
              $(this).removeClass('active');
            }
            else {
            	$(this).addClass('active');
              // Otherwise, reveal hidden windows that are open.
            	$('#startmenu').show();
            }
          });

        $('table.data').each(function() {
          // Add zebra striping, ala Mac OS X.
          $(this).find('tr:odd').addClass('zebra');
        });

        d.on('mousedown', 'table.data tr', function() {
          // Clear active state.
          JQD.util.clear_active();

          // Highlight row, ala Mac OS X.
          $(this).closest('tr').addClass('active');
        });
      },
      wallpaper: function() {
        // Add wallpaper last, to prevent blocking.
        if ($('#desktop').length) {
          $('body').prepend('<img id="wallpaper" class="abs" src="'+ADMIN_BASEURL+'/img/desktop/misc/win7-desktop-bg.jpg" />');
        }
      }
    },
    util: {
      //
      // Clear active states, hide menus.
      //
      clear_active: function() {
        $('a.active, tr.active').removeClass('active');       
        $('ul.menu').hide();
      },
      //
      // Zero out window z-index.
      //
      window_flat: function() {
        $('div.window').removeClass('window_stack');
      },
      //
      // Resize modal window.
      //
      window_resize: function(el) {
        // Nearest parent window.
        var win = $(el).closest('div.ui-dialog');
        // Is it maximized already?
        if (win.hasClass('window_full')) {
          // Restore window position.
          win.removeClass('window_full').css({
            'top': win.attr('data-t'),
            'left': win.attr('data-l'),
            'right': win.attr('data-r'),
            'bottom': win.attr('data-b'),
            'width': win.attr('data-w'),
            'height': win.attr('data-h')
          });
          
        }
        else {
          win.attr({
            // Save window position.
            'data-t': win.css('top'),
            'data-l': win.css('left'),
            'data-r': win.css('right'),
            'data-b': win.css('bottom'),
            'data-w': win.css('width'),
            'data-h': win.css('height')
          }).addClass('window_full').css({
            // Maximize dimensions.
            'top': '0',
            'left': '0',
            'right': '0',
            'bottom': '0',
            'width': '100%',
            'height': '100%'
          });
	    }
        areaHeight = win.height();
	    areaWidth = win.width();
        if( win.find('.ui-dialog-buttonpane').size()>0){
        	  win.find('.ui-dialog-content').height(win.height()-85);
        	  win.find('.jqgrid-list').jqGrid('setGridHeight',areaHeight-200).jqGrid('setGridWidth',areaWidth-10);
        }
        else{
        	// dialog标题栏 40，工具栏 40，jqdrid标题栏40，jqgrid底部40，dialog底部按钮40
        	  win.find('.ui-dialog-content').height(win.height()-45);
        	  win.find('.jqgrid-list').jqGrid('setGridHeight',areaHeight-160).jqGrid('setGridWidth',areaWidth-10);
        }
        // Bring window to front.
        JQD.util.window_flat();
        win.addClass('window_stack');
      }
    }
  };
// Pass in jQuery.
})(jQuery, this, this.document);
	
//
// Kick things off.
//
jQuery(document).ready(function() {
  JQD.go();
});
	
	
	
