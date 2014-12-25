/**
 * Copyright (c) 2014 Leonardo Pinori - leodudedev(at)gmail(dot)com | https://github.com/leodudedev
 * Dual licensed under MIT and GPL.
 * @author Leonardo Pinori
 * @version 1.2.0
 */
(function($){
	$.fn.responsiveSlides = function(options){
		var _this=this;
		var _int=null;
		var im=' !important';
        var settings = $.extend({
			img:null,
			height:$(_this).height(),
			background:'#fff',
			loadingClassStyle:'',
			autoStart:true,
			startDelay:0,
			effectInterval:5000,
			effectTransition:1000,
			pagination:[{active:true, inner:true, position:'B_C', margin:10, dotStyle:'', dotStyleHover:'', dotStyleDisable:''}]
		},options);
		_this.RS_Start=function(){
			_this.RS_Stop();
			_int=setInterval(function(){
				var n=$('.current',$('.tgtimg',_this)).next('img');
				if(n.length==0){
					n=$('img:first',$('.tgtimg',_this));
				}
				_this.RS_ShowPhoto(n);
			},settings.effectInterval);
		};
		_this.RS_Stop=function(){clearInterval(_int); _int=null;}
		_this.RS_ShowPhoto=function(next){
			$('.current',$('.tgtimg',_this)).fadeOut(settings.effectTransition);
			next.fadeIn(settings.effectTransition,function(){
				$('.current',$('.tgtimg',_this)).removeClass('current');
				$(this).addClass('current');
				var std=settings.pagination[0].dotStyleDisable;
				if(std!=''){
					$('.'+std,$('.pagination',_this)).removeClass(std);
					$('div:eq('+$(this).index()+')',$('.pagination',_this)).addClass(std);
				}else{
					$('div',$('.pagination',_this)).css('opacity',1);
					$('div:eq('+$(this).index()+')',$('.pagination',_this)).css('opacity',0.5);
				}
				if(_int==null&&settings.autoStart){_this.RS_Start();}
			});
		};
		//var overstyle='position:absolute'+im+'; height:100%'+im+'; width:100%'+im+'; text-align:center'+im+'; line-height:'+settings.height+'px'+im+'; z-index:2'+im+'; background:'+settings.background+im+';';
		var overstyle='position:absolute; height:100%; width:100%; text-align:center; line-height:'+settings.height+'px; z-index:2; background-color:'+settings.background+';';
		if(settings.loadingClassStyle==''){
			overstyle+='font-family:Arial'+im+'; ';
			if(settings.background.toLowerCase()=='#fff'||settings.background.toLowerCase()=='#ffffff'){
				overstyle+='color:#000'+im+';';
			}else{
				overstyle+='color:#fff'+im+';';
			}
		}
		var tgtstyle='position:absolute'+im+'; height:'+settings.height+'px'+im+'; width:100%'+im+'; background:'+settings.background+im+'; z-index:1'+im+'; overflow:hidden'+im;
		$(_this).height(settings.height).prepend('<div class="overslide '+settings.loadingClassStyle+'" style="'+overstyle+'">loading..</div><div class="tgtimg" style="'+tgtstyle+'"></div>');

 		if(settings.img==null || settings.img==undefined || settings.img=='undefined'){
			$('img',_this).appendTo($('.tgtimg',_this));
			$('img',$('.tgtimg',_this)).each(function(i,e){
				$(this).attr('style','position:absolute; z-index:1; top:0px; left:0px; height:'+settings.height+'px; display:'+((i==0)?'block':'none'))
				if(i==0){
					$(this).addClass('current');
				}
            });
		}else{
			$.each(settings.img,function(i,e){
				$('.tgtimg',_this).append('<img src="'+e+'" style="position:absolute; z-index:1; top:0px; left:0px; height:'+settings.height+'px; display:'+((i==0)?'block':'none')+'" class="'+((i==0)?'current':'')+'">');
			});
		}
		$('img',$('.tgtimg',_this)).onImagesLoad(function(){
			// every images are loaded. can i init
			$(window).resize(function(){
				$('img',$('.tgtimg',_this)).each(function(i,e){
					var l=-(($(e).width()-$(_this).width())/2);
					if($(_this).width()>$(e).width()){
						l=($(_this).width()-$(e).width())/2;
					}
					$(e).css('left',l+'px');
				});
			}).trigger('resize');
			var p=settings.pagination[0];
			if(p.active){
				var pos=(p.position.substring(0,1)=='B')?'top:'+p.margin+'px; ':'bottom:'+p.margin+'px; ';
				pos+=(p.position.substring(2,3)=='L')?'left:'+(p.margin*2)+'px; ':(p.position.substring(2,3)=='R')?'right:'+(p.margin/2)+'px; ':'left:50%; ';
				var elm='';
				
				$('img',$('.tgtimg',_this)).each(function(i,e){elm+='<div'+((p.dotStyle=='')?' style="float:left; height:15px; width:15px; background:#fff; margin-left:2px; cursor:pointer"':' class="'+p.dotStyle+'"')+'></div>';});
				$(_this).append('<div class="pagination" style="position:absolute; z-index:10; '+pos+'">'+elm+'</div>');
				if(p.position.substring(2,3)=='C'){
					$('.pagination',_this).css('margin-left','-'+parseInt($('.pagination',_this).width()/2)+'px');
				}else if(p.position.substring(2,3)=='R'){
					$('.pagination',_this).css('margin-left','-'+($('.pagination',_this).width()+p.margin)+'px');
				}
				if(!p.inner){
					$('.pagination',_this).css('margin-top',-$('.pagination',_this).height()-(p.margin*2)+'px');
					$('.pagination',_this).css('margin-bottom',-$('.pagination',_this).height()-(p.margin*2)+'px');
				}
				$('div',$('.pagination',_this)).mouseover(function(){
					if(p.dotStyleHover!=''){$(this).addClass(p.dotStyleHover);}else{$(this).css('background','#CCC');}
				}).mouseout(function(){
					if(p.dotStyleHover!=''){$(this).removeClass(p.dotStyleHover);}else{$(this).css('background','#fff');}
				}).click(function(){
					_this.RS_Stop();
					_this.RS_ShowPhoto($('img:eq('+$(this).index()+')',$('.tgtimg',_this)));
				});
			}
			// init transition after delay and remove overlayer whit custom bg color
			$('.overslide',_this).delay(settings.startDelay).fadeOut(settings.effectTransition,function(){
				if(settings.autoStart){_this.RS_Start();}
				$('div:eq(0)',$('.pagination',_this)).css('opacity',0.5);
			});
		});
		return _this;
    };
 
}(jQuery));