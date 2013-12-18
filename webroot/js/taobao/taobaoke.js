$(".itemimg").live({
        mouseenter: function() {
            $(this).children(".itemtitle").fadeIn();
        },
        mouseleave: function() {
        	$(this).children(".itemtitle").fadeOut();
        }
});
function search_taobaoke(keyword){
	window.location.href= BASEURL+"/taobao/taobaokes/index?name="+keyword;
}

$(function(){
 /* 
    var $container = $('.portlet-region-list');  
    //$container.imagesLoaded( function(){
      $container.masonry({
        itemSelector: '.taobao-list-item',
        animate: true,       
        animationOptions: {
            duration: 400,
            easing: 'linear',
            queue: false
        }
      });
    //});

  $container.infinitescroll({
  		
        bufferPx     : 200, //离分页底部还有bufferPx像素时就开始加载下一页的内容 ，设置大一些体验效果会好一些
	    
	    loading : {img:"/img/pbar-ani.gif",msgText:"正在加载新的宝贝...",finishedMsg:"没有更多的啦。。。"},
	    
	    //animate: true,extraScrollPx:0;
	    navSelector  : "div.pagelink",
	                   // selector for the paged navigation (it will be hidden)
	    nextSelector : "div.pagelink a:first",    
	                   // selector for the NEXT link (to page 2)
	    itemSelector : ".portlet-region-list div.taobao-list-item"          
                   // selector for all items you'll retrieve
  	},
    // call masonry as a callback.
    function(newElements) {
        var $newElems = $(newElements);
        
//        $newElems.find('img[original]').lazyload({
//		            placeholder : BASEURL+"/img/grey.gif",
//		            effect      : "fadeIn"                  
//		        });
        // ensure that images load before adding to masonry layout
        // 每一个加载完后，即调用masonry；而不是等所有的都加载完后再调（时间长，影响体验，特别是在网速不够快的时候）
        $newElems.hide().each(function(){
        	var $item = $(this);
        	$item.imagesLoaded(function() {        		
                $container.masonry('appended', $item, true);
                $item.show();
            });
        }); 
//        $newElems.imagesLoaded(function() {
//            $container.masonry('appended', $newElems, true);
//        });
    });
*/  
});
