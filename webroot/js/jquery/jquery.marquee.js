/*
 * jQuery JavaScript Library Marquee Plus 1.0
 * http://mzoe.com/
 *
 * Copyright (c) 2009 MZOE
 * Dual licensed under the MIT and GPL licenses.
 *
 * Date: 2009-05-13 18:54:21
 */
(function($) {
	$.fn.marquee = function(o) {
		//获取滚动内容内各元素相关信息
		o = $.extend({
			speed:		parseInt($(this).attr('speed')) || 500, // 滚动速度
			step:		parseInt($(this).attr('step')) || 1, // 滚动步长
			direction:	$(this).attr('direction') || 'up', // 滚动方向
			pause:		parseInt($(this).attr('pause')) || 5000, // 停顿时长
                        btn_pre:'',
                        btn_next:'',
                        selector:'',
                        column:1        //列数，一个一列或多个一列
		}, o || {});
		var dIndex = jQuery.inArray(o.direction, ['right', 'down']);
		if (dIndex > -1) {
			o.direction = ['left', 'up'][dIndex];
			o.step = -o.step;
		}
		var mid,one_times,marquee_item,marquee_contain;
		var div 		= $(this); // 容器对象
		var divWidth 	= div.innerWidth(); // 容器宽
		var divHeight 	= div.innerHeight(); // 容器高
                
                if(o.selector){
                    marquee_item = $(o.selector,this);
                    marquee_contain = $(marquee_item).eq(0).parent();
                }
                else{
                    var marquee_contain = $(div).find('ul:first'); // 容器对象           
                    var marquee_item = marquee_contain.children('li'); //$("li", ul);
                }
                
		
		var liSize 		= marquee_item.size(); // 初始元素个数
		var liWidth 	= marquee_item.eq(0).width(); // 元素宽
		var liHeight 	= marquee_item.eq(0).height(); // 元素高
                if(liSize%o.column==0){
                    one_times = liSize/o.column;
                }
                else{
                    one_times = parseInt(liSize/o.column) + 1;
                }             
                div.height(parseInt(divHeight/one_times)-3*one_times).css('overflow',"hidden");

                divWidth 	= div.innerWidth(); // 容器宽
		divHeight 	= div.innerHeight(); // 容器高
                
		var width 		= liWidth * one_times;
		var height 		= liHeight * one_times;
		if ((o.direction == 'left' && width > divWidth) || 
			(o.direction == 'up' && height > divHeight)) {
			// 元素超出可显示范围才滚动
			if (o.direction == 'left') {
				marquee_contain.width(2 * one_times * liWidth);
				if (o.step < 0) marquee_contain.scrollLeft(width);
			} else {
				marquee_contain.height(2 * one_times * liHeight);
				if (o.step < 0) marquee_contain.scrollTop(height);
			}
			marquee_contain.append(marquee_item.clone()); // 复制元素，参与辅助显示，滚动时轮到clone内的li时，则跳到起始位开始滚动。left,top的scroll位置都不超过一个width（height）的长度
			mid = setInterval(_marquee, o.pause);
                        if(o.btn_pre!=''){
                            $(o.btn_pre).click(function(){
                                o.step = -o.step;_marquee();o.step = -o.step;
                            });
                        }
                        if(o.btn_next!=''){
                            $(o.btn_next).click(function(){
                                _marquee();
                            });
                        }
                        marquee_item.hover(
				function(){clearInterval(mid);},
				function(){mid = setInterval(_marquee, o.pause);}
			);
//			div.parents('#spec-thumb-list').hover(
//				function(){clearInterval(mid);},
//				function(){mid = setInterval(_marquee, o.pause);}
//			);
		}
                var cur_times = 0;
		function _marquee() {
                    cur_times++;
			// 滚动
                        if (o.direction == 'left') {
                            var l = div.scrollLeft();
                            var tl;
                            if(o.step<0){
                                if(l <= 0) div.scrollLeft(width); //往左，超过了一个ul的长度，回到正中位置，重新开始滚动
                                tl=(l <= 0 ? width : l) + o.step*liWidth
                            } else {
                                if(l >= width) div.scrollLeft(0); //往右，超过了一个ul的长度，回到最左位置，重新开始滚动
                                tl=(l >= width ? 0 : l) + o.step*liWidth;
                            }
                            div.animate({scrollLeft: tl}, o.speed );
                        }
                        else{
                            var tl;
                            if(o.step<0){
                                if(cur_times > one_times){div.scrollTop(height);cur_times=1;}
                                //tl= (t <= 0 ? height : t) + o.step*liHeight
                                tl = marquee_item.eq((cur_times-1)*o.column+1).position().top;
                            } else {
                                if(cur_times > one_times){ div.scrollTop(0);cur_times=1;} // 从零开始
                                tl = marquee_item.eq((cur_times-1)*o.column+1).position().top;
                            }                            
                            div.animate({scrollTop: tl}, o.speed );
                        }
                        return;
		}		
	};
})(jQuery);