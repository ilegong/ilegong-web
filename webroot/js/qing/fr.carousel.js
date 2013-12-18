/*
* FrUI v1.0
* Author:River Zhang(zhang_hechuan@hotmail.com)
* Lisence:MIT Lisence
*/
FR={
	Version:'1.0.0',
	Author:'River Zhang(zhang_hechuan@hotmail.com)',
	Lisence:'MIT Lisence'
};
FR.Util={
	//Replace document.getElementById.
	$:function(id){
		return document.getElementById(id);
	},
	//Replace getElementsByTagName.
	$$:function(node, tag){
		return node.getElementsByTagName(tag);
	},
	creat:function(node,name){
		var element=document.createElement(name);
		node.appendChild(element);
		return element;
	},
	//Event Binding functions.
	addEvent:function(eventType,eventFunc,eventObj){
		eventObj = eventObj || document;
		if(window.attachEvent)eventObj.attachEvent("on"+eventType,eventFunc);
		if(window.addEventListener) eventObj.addEventListener(eventType,eventFunc,false);
	},
	setOpacity:function(obj, value) {
		if (document.all)
		{
			obj.style.filter = "alpha(opacity=" + value + ")";
			if(value>0){
				obj.style.zIndex = "50";
			}else{
				obj.style.zIndex = "0";
			}
		}
		else
		{
			obj.style.opacity = value / 100;
			if(value>0){
				obj.style.zIndex = "50";
			}else{
				obj.style.zIndex = "0";
			}
		}
	},
	setPosition:function(obj, x, y){
		var curx=parseInt(obj.style.left);
		var cury=parseInt(obj.style.top);
		if(isNaN(curx)) curx=cury=0;
		var newx=curx+x;
		var newy=cury+y;
		obj.style.left=newx+'px';
		obj.style.top=newy+'px';
	}
};

/*
* FR.Carousel v1.1
* Author:River Zhang(zhang_hechuan@hotmail.com)
* Lisence:MIT Lisence
* Usage:
*	<script>FR.Carousel.start(mode,steps,period,width,height,autoSwitch,delay);</script>
*       1: 交替切换； 2、闪光切换； 3、淡出淡入切换； 4、滚动模式（纵向）； 5、爬行模式（横向）
*/
FR.Carousel={
	version:'1.1',
	mode:1,
	steps:20,
	period:25,
	width:300,
	height:200,
	bgColor:'#000000',
	autoSwitch:true,
	delay:5000,
	_semaphore:0,/* DO NOT try to modify this value */
	start:function(args){
		if(typeof(args)!='undefined'){
			FR.Carousel.mode=args.mode||FR.Carousel.mode;
			FR.Carousel.steps=args.steps||FR.Carousel.steps;
			FR.Carousel.period=args.period||FR.Carousel.period;
			FR.Carousel.width=args.width||FR.Carousel.width;
			FR.Carousel.height=args.height||FR.Carousel.height;
			FR.Carousel.bgColor=args.bgColor||FR.Carousel.bgColor;
			FR.Carousel.autoSwitch=args.autoSwitch||FR.Carousel.autoSwitch;
			FR.Carousel.delay=args.delay||FR.Carousel.delay;
		}
		FR.Util.addEvent("load",FR.Carousel.run,window);
	},
	run:function(){
		FR.Carousel.initialCSS();
		FR.Carousel.counter='frimg0';
		var carouselimg=FR.Util.$('carouselimg');
		var img=FR.Util.$$(carouselimg, 'img');
		for(var i=0;i!=img.length;++i){
			img[i].id='frimg'+i;
			if(FR.Carousel.mode==4 || FR.Carousel.mode==5) continue;
			img[i].style.position="absolute";
			img[i].style.left="0 px";
			img[i].style.top="0 px";
			FR.Util.setOpacity(img[i], 0);
		}
		if(FR.Carousel.mode!=4) FR.Util.setOpacity(img[0], 100);
		if(FR.Carousel.mode==1) bindFunction=function(name){FR.Carousel.fade(FR.Util.$(name), FR.Carousel.steps, FR.Carousel.period);};
		else if(FR.Carousel.mode==2) bindFunction=function(name){FR.Carousel.flash(FR.Util.$(name), FR.Carousel.steps, FR.Carousel.period);};
		else if(FR.Carousel.mode==3) bindFunction=function(name){FR.Carousel.fadeIntoColor(FR.Util.$(name), FR.Carousel.steps, FR.Carousel.period);};
		else if(FR.Carousel.mode==4) bindFunction=function(name){FR.Carousel.scroll(name, FR.Carousel.steps, FR.Carousel.period);};
		else if(FR.Carousel.mode==5) bindFunction=function(name){FR.Carousel.crawl(name, FR.Carousel.steps, FR.Carousel.period);};
		var carouseltitle=FR.Util.$('carouseltitle');
		var li=FR.Util.$$(carouseltitle, 'li');
		li[0].className='#carousel #carouseltitle active';
		FR.Carousel.autoCarousel(img.length);
		for(var i=0;i!=li.length;++i){
			(function(){
				var name='frimg'+i;
				li[i].onmouseover=function(){
					clearInterval(FR.Carousel.s);
					if(!FR.Carousel._semaphore){
						li[FR.Carousel.counter.substring(5)].className='';
						this.className='#carousel #carouseltitle active';
						bindFunction(name);
					}
				};
				li[i].onmouseout=function(){
					FR.Carousel.autoCarousel(img.length);
				}
			})();
		}
	},
	autoCarousel:function(length){
		if(FR.Carousel.autoSwitch){
			FR.Carousel.s=setInterval(function(){
				var carouseltitle=FR.Util.$('carouseltitle');
				var li=FR.Util.$$(carouseltitle, 'li');
				li[FR.Carousel.counter.substring(5)].className='';
//				li[FR.Carousel.counter.split('')[FR.Carousel.counter.length-1]].className='';
				var next=(parseInt(FR.Carousel.counter.substring(5))+1)%length;
				li[next].className='#carousel #carouseltitle active';
				name='frimg'+next;
				bindFunction(name);
			},FR.Carousel.delay);
		}
	},
	initialCSS:function(){
		var carouselimg=FR.Util.$('carouselimg');
		var carousel=FR.Util.$('carousel');
		carouselimg.style.width=FR.Carousel.width+"px";
		carouselimg.style.height=FR.Carousel.height+"px";
		carousel.style.width=FR.Carousel.width+"px";
		carousel.style.height=FR.Carousel.height+"px";
		if(FR.Carousel.mode==5){
			var imgcontainer=FR.Util.$('imgcontainer');
			var img=FR.Util.$$(carouselimg, 'img');
			var size=img.length*FR.Carousel.width;
			imgcontainer.style.width=size+"px";
		}
	},
	fade:function(obj, steps, speed) {
		FR.Carousel._semaphore=1;
		var value1=0;
		var value2=100;
		if(obj.id!=FR.Carousel.counter){
			var carouselimg=FR.Util.$('carouselimg');
			var img=FR.Util.$$(carouselimg, 'img');
			for(var i=0;i!=img.length;++i){
				if(i!=FR.Carousel.counter.substring(5))
				FR.Util.setOpacity(img[i], 0);
			}
			temp=FR.Carousel.counter;
			FR.Carousel.counter=obj.id;
			tempobj=FR.Util.$(temp);
			var increment=100/steps;
			FR.Carousel.i=setInterval(function(){
				if(value1<=100){
					FR.Util.setOpacity(obj,value1);
					FR.Util.setOpacity(tempobj,value2);
					value1+=increment;
					value2-=increment;
				} else {
					clearInterval(FR.Carousel.i);
					FR.Carousel._semaphore=0;
				}
			},speed);
		}else {
			FR.Carousel._semaphore=0;
			return;
		}
	},
	flash:function(obj, steps, speed) {
		FR.Carousel._semaphore=1;
		var value1=0;
		if(obj.id!=FR.Carousel.counter){
			var carouselimg=FR.Util.$('carouselimg');
			var img=FR.Util.$$(carouselimg, 'img');
			for(var i=0;i!=img.length;++i){
				FR.Util.setOpacity(img[i], 0);
			}
			FR.Carousel.counter=obj.id;
			var increment=100/steps;
			FR.Carousel.i=setInterval(function(){
				if(value1<=100){
					FR.Util.setOpacity(obj,value1);
					value1+=increment;
				} else {
					clearInterval(FR.Carousel.i);
					FR.Carousel._semaphore=0;
				}
			},speed);
		}else {
			FR.Carousel._semaphore=0;
			return;
		}
	},
	fadeIntoColor:function(obj, steps, speed){
		FR.Carousel._semaphore=1;
		var value1=100;
		var value2=0;
		if(obj.id!=FR.Carousel.counter){
			var carouselimg=FR.Util.$('carouselimg');
			carouselimg.style.backgroundColor=FR.Carousel.bgColor;
			var img=FR.Util.$$(carouselimg, 'img');
			for(var i=0;i!=img.length;++i){
				if(i!=FR.Carousel.counter.substring(5))
				FR.Util.setOpacity(img[i], 0);
			}
			temp=FR.Carousel.counter;
			FR.Carousel.counter=obj.id;
			tempobj=FR.Util.$(temp);
			var increment=100/steps;
			FR.Carousel.i=setInterval(function(){
				if(value1>=0){
					FR.Util.setOpacity(tempobj,value1);
					value1-=increment;
				}
				else if(value1<0 && value2<=100){
					FR.Util.setOpacity(obj,value2);
					value2+=increment;
				} else {
					clearInterval(FR.Carousel.i);
					FR.Carousel._semaphore=0;
				}
			},speed);
		} else {
			FR.Carousel._semaphore=0;
			return;
		}
	},
	scroll:function(curno, steps, speed){
		FR.Carousel._semaphore=1;
		var ic=FR.Util.$('imgcontainer');
		var count=(curno.substring(5)-FR.Carousel.counter.substring(5))*FR.Carousel.height;
		FR.Carousel.counter=curno;
		var value1=0;
		var increment=count/steps;
		FR.Carousel.i=setInterval(function(){
			if(Math.abs(value1)<Math.abs(count)){
				if(count>0){
					FR.Util.setPosition(ic,0,-increment);
					value1-=increment;
				}
				else{
					FR.Util.setPosition(ic,0,-increment);
					value1+=increment;
				}
			} else {
				clearInterval(FR.Carousel.i);
				FR.Carousel._semaphore=0;
			}
		},speed);
	},
	crawl:function(curno, steps, speed){
		FR.Carousel._semaphore=1;
		var ic=FR.Util.$('imgcontainer');
		var count=(curno.substring(5)-FR.Carousel.counter.substring(5))*FR.Carousel.width;
		FR.Carousel.counter=curno;
		var value1=0;
		var increment=count/steps;
		FR.Carousel.i=setInterval(function(){
			if(Math.abs(value1)<Math.abs(count)){
				if(count>0){
					FR.Util.setPosition(ic,-increment,0);
					value1-=increment;
				}
				else{
					FR.Util.setPosition(ic,-increment,0);
					value1+=increment;
				}
			} else {
				clearInterval(FR.Carousel.i);
				FR.Carousel._semaphore=0;
			}
		},speed);
	}
};
