if (!window.jquitr) {
	var jquitr = {};
}

$(function () {
	//add dev tool to page
	jquitr.addThemeRoller = function(url){
		if($('#inline_themeroller').size() > 0){
			$('#inline_themeroller').fadeIn();
		}
		else {
			$('<div id="inline_themeroller" style="display: none; position: fixed; background: #111; top: 25px; right: 25px; padding: 22px 0 15px 4px;width: 265px;height:400px; -webkit-border-radius: 6px; -moz-border-radius: 6px; z-index: 9999999;">'+
				'<a href="#" class="closeTR" style="font-family: Verdana, sans-serif; font-size: 10px; display: block; position: absolute; right: 0; top: 2px; text-align: right; background: url(http://jqueryui.com/themeroller/developertool/icon_bookmarklet_close.gif) 0 2px no-repeat; width: 16px;height: 16px; color: #fff; text-decoration: none;" title="Close ThemeRoller"></a>'+
				'<iframe name="trApp" src="'+url+'" style="background: transparent; overflow: auto; width: 260px;height:100%;border: 0;" frameborder="0" ></iframe>'+
				'</div>')
				.appendTo('body')
				.draggable({
					start: function(){
						$('<div id="div_cover" />').appendTo('#inline_themeroller').css({width: $(this).width(), height: $(this).height(), position: 'absolute', top: 0, left:0});
					},
					stop: function(){
						$('#div_cover').remove();
					},
					opacity: 0.6,
					cursor: 'move'
				})
				.resizable({
					start: function(){
						$(this).find('iframe').hide();
					},
					stop: function(){
						$(this).find('iframe').show();
					},
					handles: 's'
				})
				.find('a.closeTR').click(function(){
					jquitr.closeThemeRoller();
				})
				.end()
				.find('.ui-resizable-s').css({
					background: 'url(http://jqueryui.com/themeroller/developertool/icon_bookmarklet_dragger.gif) 50% 50% no-repeat',
					border: 'none',
					height: '14px',
					dipslay: 'block',
					cursor: 'resize-s',
					bottom: '-3px'
				})
				.end()
				.css('cursor', 'move')
				.fadeIn();
			}
			jquitr.reloadCSS();		
	};
	//close dev tool
	jquitr.closeThemeRoller = function () {
		$('#inline_themeroller').fadeOut();
	};
	//get current url hash
	jquitr.getHash = function () {
		var currSrc = window.location.hash;
		if (currSrc.indexOf('#') > -1) {
			currSrc = currSrc.split('#')[1];
		}
		return currSrc;
	};
	//recursive reload call
	jquitr.reloadCSS = function(){
		var currSrc = jquitr.getHash(), cssLink;
		//alert(currSrc);
		if(jquitr.trString !== currSrc && currSrc !== ''){
			jquitr.trString = currSrc;
			cssLink = '<link flag="themeroller" href="/themeroller/css/parseTheme.css.php?'+ currSrc +'" type="text/css" rel="Stylesheet" />';
			//works for both 1.6 final and early rc's
			//if( $("link[href*=parseTheme.css.php], link[href=ui.theme.css]").size() > 0){
			//	$("link[href*=parseTheme.css.php]:last, link[href=ui.theme.css]:last").eq(0).after(cssLink);
			//} else {
				$("head").append(cssLink);
			//}
			if( $("link[flag=themeroller]").size() > 1){
				$("link[flag=themeroller]:first").remove();
			}
		}
		window.setTimeout(jquitr.reloadCSS, 1000);
	};
	// Actually add the roller
	
});