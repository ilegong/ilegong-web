$(document).ready(function(){
	//themes, change CSS with JS
	//default theme(CSS) is cerulean, change it if needed
	//var current_csstheme = $.cookie('current_csstheme')==null ? 'united' :$.cookie('current_csstheme');
	if(typeof current_csstheme!='undefined'){
		$('#themes a[data-value="'+current_csstheme+'"]').find('i').addClass('icon-ok');
	}
				 
	$('#themes a').click(function(e){
		var current_csstheme=$(this).attr('data-value');
		 // ajax提交保存 
		ajaxAction(ADMIN_BASEURL+'/admin/settings/ajaxesave',{'setting[Site][csstheme]':current_csstheme});
		if(typeof(current_csstheme)!='undefined'){
			e.preventDefault();			
			switch_theme(current_csstheme);
			$('#themes i').removeClass('icon-ok');
			$(this).find('i').addClass('icon-ok');
		}
	});
	
	
	function switch_theme(theme_name){
		if($('#append-css').size()<1){
			var cssLink = '<link id="append-css" href="'+ADMIN_BASEURL+'/css/charisma/bootstrap-'+theme_name+'.css" type="text/css" rel="Stylesheet" />';
			$("head").append(cssLink);
		}
		else{
			$('#append-css').attr('href',ADMIN_BASEURL+'/css/charisma/bootstrap-'+theme_name+'.css');
		}
	}
	;
	//ajax menu checkbox
	$('#is-ajax').click(function(e){
		$.cookie('is-ajax',$(this).prop('checked'),{expires:365});
	});
	if($.cookie('is-ajax')!=null){
		$('#is-ajax').prop('checked',$.cookie('is-ajax')==='true' ? true : false);
	}
	
	//disbaling some functions for Internet Explorer
	if($.browser.msie)
	{
		$('#is-ajax').prop('checked',false);
		$('#for-is-ajax').hide();
		$('#toggle-fullscreen').hide();
		$('.login-box').find('.input-large').removeClass('span10');
		
	}
	/*$(window).resize(function(){
		var content_height = $(window).height()-80;
		$('#ui-layout-west').addClass('ui-layout-west').height(content_height);
		$('#mainContent').addClass('ui-layout-main').height(content_height);
		
	}).trigger('resize');*/
	
	//highlight current / active link
	$('ul.main-menu li a').each(function(){
		if($($(this))[0].href==String(window.location))
			$(this).parent().addClass('active');
	});
	
	//establish history variables
	var	History = window.History, // Note: We are using a capital H instead of a lower h
		State = History.getState(),
		$log = $('#log');

	//bind to State Change
	History.Adapter.bind(window,'statechange',function(){ // Note: We are using statechange instead of popstate
		var State = History.getState(); // Note: We are using History.getState() instead of event.state
		$.ajax({
			url:State.url,
			success:function(msg){
				//$('#mainContent').html($(msg).find('#mainContent').html());
				$('#mainContent').html(msg);
				$('#loading').remove();
				$('#mainContent').fadeIn();
				docReady();
			}
		});
	});
	
	//ajaxify menus
	//$('a.ajax-link').click
	
	$('a.ajax-link,.ui-grid-actions a').live('click',function(e){
		if($(this).data('url')){
			this.href=$(this).data('url');
		}
		if($(this).attr('href').substr(0,1)=='#' || this.href.match(/javascript:/) || typeof($(this).attr('onclick'))!='undefined'){
			return true;
		}
		
		if($.browser.msie) e.which=1;
		if(e.which!=1 || !$('#is-ajax').prop('checked') || $(this).parent().hasClass('active')) return;
		e.preventDefault();
		if($('.btn-navbar').is(':visible')){
			$('.btn-navbar').click();
		}
		$('#loading').remove();
		$('#mainContent').fadeOut().parent().append('<div id="loading" class="center">Loading...<div class="center"></div></div>');
		var $clink=$(this);
		History.pushState(null, null, $clink.attr('href'));
		$('ul.nav li.active').removeClass('active');
		$clink.parent('li').addClass('active');	
	});
	
	//animating menus on hover
	$('ul.main-menu li:not(.nav-header)').hover(function(){
		$(this).animate({'margin-left':'+=5'},300);
	},
	function(){
		$(this).animate({'margin-left':'-=5'},300);
	});
	
	//other things to do on document ready, seperated for ajax calls
	docReady();
});
 
function docReady(){
	page_loaded(); /* admin.js page_loaded */
	//prevent # links from moving to top
	$('a[href="#"][data-top!=true]').click(function(e){
		e.preventDefault();
	});	
	
	//datepicker
	$('.datepicker').datepicker();
	

	//uniform - styler for checkbox, radio and file input
	//$("input:checkbox, input:radio, input:file").not('[data-no-uniform="true"],#uniform-is-ajax').uniform();

	//chosen - improves select
	$('[data-rel="chosen"],[rel="chosen"]').each(function(){
		if($(this).find('option').size()>10){ // 选项大于10个时，启用ajax搜索
			$(this).ajaxChosen({
				minTermLength: 0,
				afterTypeDelay: 100,
				lookingForMsg: "正在查找",
				type: 'GET',
				jsonTermKey: "txt_filter",
				dataType: 'json'
			}, function (data) {
				var terms = {};		
				$.each(data, function (i, val) {
					terms[i] = val;
				});		
				return terms;
			});
		}
	});

	//makes elements soratble, elements that sort need to have id attribute to save the result
	//slider
//	$('.slider').slider({range:true,values:[10,65]});

	//tooltip
	//$('[rel="tooltip"],[data-rel="tooltip"]').tooltip({"placement":"bottom",delay: { show: 400, hide: 200 }});

	//auto grow textarea
	$('textarea').autogrow();

	//popover
	//$('[rel="popover"],[data-rel="popover"]').popover();

	//star rating
	//$('.raty').raty({
	//	score : 4 //default stars
	//});

	
	//tour
	if($('.tour').length && typeof(tour)=='undefined')
	{
		var tour = new Tour();
		tour.addStep({
			element: ".span10:first", /* html element next to which the step popover should be shown */
			placement: "top",
			title: "Custom Tour", /* title of the popover */
			content: "You can create tour like this. Click Next." /* content of the popover */
		});
		tour.addStep({
			element: ".theme-container",
			placement: "left",
			title: "Themes",
			content: "You change your theme from here."
		});
		tour.addStep({
			element: "ul.main-menu a:first",
			title: "Dashboard",
			content: "This is your dashboard from here you will find highlights."
		});
		tour.addStep({
			element: "#for-is-ajax",
			title: "Ajax",
			content: "You can change if pages load with Ajax or not."
		});
		tour.addStep({
			element: ".top-nav a:first",
			placement: "bottom",
			title: "Visit Site",
			content: "Visit your front end from here."
		});
		
		tour.restart();
	}

}
