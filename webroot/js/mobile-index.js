$(document).ready(function () {
    var cache = {};
    var $productsContent = $('#products-content');
    var $seckill_product = $('#seckill_product');
    var menue = $('div.menue ul');
    var initTagId = menue.data('id');
    var currentTagId = 0;
    var recommendTagId = 23;
    var firstTag = $('div.menue ul li:first div');
    //format number
    Number.prototype.format = function (n, x) {
        var re = '(\\d)(?=(\\d{' + (x || 3) + '})+' + (n > 0 ? '\\.' : '$') + ')';
        return this.toFixed(Math.max(0, ~~n)).replace(new RegExp(re, 'g'), '$1,');
    };
    //date
    Date.prototype.yyyymmdd = function () {
        var yyyy = this.getFullYear().toString();
        var mm = (this.getMonth() + 1).toString(); // getMonth() is zero-based
        var dd = this.getDate().toString();
        return yyyy + (mm[1] ? mm : "0" + mm[0]) + (dd[1] ? dd : "0" + dd[0]); // padding
    };

    $('div.menue ul li').on('click', function () {
        var me = $(this);
        $('div.menue ul li.cur').removeClass('cur');
        var tagId = me.data('id');
        if (tagId != currentTagId) {
            currentTagId = tagId;
            if(tagId!=recommendTagId||tagId!=recommendTagId.toString()){
                $seckill_product.hide();
            }else{
                $seckill_product.show();
            }
            $('div.good',$productsContent).remove();
            loadDatas(tagId);
        }
        if(tagId!=recommendTagId||tagId!=recommendTagId.toString()){
            firstTag.css('background-color','#eeeeee');
            firstTag.css('color','#333333');
        }else{
            firstTag.css('background-color','');
            firstTag.css('color','');
        }
        me.addClass('cur');
    });

    function initView(){
        $('div.menue ul li[name="tag-'+initTagId+'"]').trigger('click');
    }

    //load tag products
    function loadDatas(tagId) {
        if (!cache[tagId]) {
            cache[tagId] = $.getJSON('/categories/getTagProducts/' + tagId).promise();
        }
        cache[tagId].done(drawToDOM);
    }

    var secKillTemplate = '<div class="tuandetail_seckill clearfix"> <p>秒杀</p> <span><a href="<%this.good_url%>"><img src="<%this.listimg%>"></a></span> <span> <h1><%this.name%></h1> <em>秒杀价：<strong>¥<%this.price%></strong></em> <div class="tuan_bar clearfix"> <div class="fl" style="width: 50%;margin-right: 5px;"> <div class="bar"><div class="bar_buy" style="width:<%this.sold_percent%>%;"></div> </div> <ul class="clearfix"> <li class="fl">已秒<span style=" display:inline;"><%this.TuanBuying.sold_num%></span>份</li> <li class="fr">共<%this.TuanBuying.target_num%>份</li> </ul> </div> <div> <a class="tuandetail_seckill_btn radius5 fr" href="<%this.good_url%>">去秒杀</a>  </div> </div> </span> </div>'
    var goodTemplate = '<div class="good"> <a href="<%this.good_url%>" class="xq"><%if(this.limit_area==1) {%><p>仅限<br>北京</p><%}%><img src="<%this.listimg%>"> </a> <div class="title clearfix"> <a href="<%this.brand_link%>" class="phead"><img src="<%this.brand_img%>"></a> <a href="<%this.brand_link%>" class="txt"><b><%this.name%></b></a> </div> <ul class="clearfix"> <li class="price fl"><strong>￥<%this.price%></strong><%if(this.original_price>0) {%>&nbsp;<label>￥<%this.original_price%></label><%}%></li><li class="fr"><a href="<%this.good_url%>" class="btn radius5">立即购买</a></li> </ul> </div>';
    //draw dom
    function drawToDOM(datas) {
        var data_list = datas['data_list'];
        $.each(data_list, function (index, item) {
            $productsContent.append(genGoodItemDom(item));
        });
    }

    function genGoodItemDom(good) {
        var price = good['price'];
        var tuanBuying = good['TuanBuying'];
        price = parseFloat(price).format(2);
        var originPrice = good['original_price'];
        var goodUrl = '';
        if (originPrice) {
            originPrice = parseFloat(originPrice).format(2);
        } else {
            originPrice = 0;
        }
        if(!tuanBuying){
            goodUrl = good['good_url']+'?history=/&amp;_sl=h5.cate.list&amp;tagId='+currentTagId;
            good['good_url'] = goodUrl;
            good['price'] = price;
            good['original_price'] = originPrice;
            return TemplateEngine(goodTemplate,good);
        }else{
            goodUrl = '/tuan_buyings/detail/'+tuanBuying['id']+'?history=/&amp;_sl=h5.cate.list&amp;tagId='+currentTagId;
            var sold_num = tuanBuying['sold_num'];
            var target_num = tuanBuying['target_num'];
            var sold_percent = (sold_num/target_num)*100;
            if(sold_percent>100){
                sold_percent=100;
            }
            good['sold_percent'] = sold_percent;
            if(tuanBuying['tuan_price']!=-1){
                price = tuanBuying['tuan_price'];
            }
            good['good_url'] = goodUrl;
            good['price'] = price;
            good['original_price'] = originPrice;
            return TemplateEngine(secKillTemplate,good);
        }
    }
    initView();
});