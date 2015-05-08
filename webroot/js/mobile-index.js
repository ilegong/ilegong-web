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
            $productsContent.html('');
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
        if(tagId!=recommendTagId||tagId!=recommendTagId.toString()){
            $seckill_product.hide();
        }else{
            $seckill_product.show();
        }
        if (!cache[tagId]) {
            cache[tagId] = $.getJSON('/categories/getTagProducts/' + tagId).promise();
        }
        cache[tagId].done(drawToDOM);
    }

    //draw dom
    function drawToDOM(datas) {
        var data_list = datas['data_list'];
        $.each(data_list, function (index, item) {
            $productsContent.append(genGoodItemDom(item));
        });
    }

    function genGoodItemDom(good) {
        var price = good['price'];
        price = parseFloat(price).format(2);
        var originPrice = good['original_price'];
        if (originPrice) {
            originPrice = parseFloat(originPrice).format(2);
        } else {
            originPrice = 0;
        }
        var productDate = new Date(good['created'].replace(' ','T'));
        var goodUrl = '/products/' + productDate.yyyymmdd() + '/' + good['slug'] + '.html?history=/&amp;_sl=h5.cate.list&amp;tagId='+currentTagId;
        var goodHtml = '<div class="good"> <a href="'+goodUrl+'" class="xq">';
        if(good['limit_area']==1){
            goodHtml+='<p>仅限<br/>北京</p>';
        }
        goodHtml+='<img src="'+ good['listimg']+'"/> <div class="title">' + good['name'] + '</div> <ul class="clearfix" style="margin-bottom: 0;"> <li class="price fl"><strong>￥' + price + '</strong>';
        if (originPrice > 0) {
            goodHtml += '&nbsp;<label>￥' + originPrice + '</label>'
        }
        goodHtml+='</li> <li class="fr btn radius5">立即购买</li> </ul> </a> </div>';
        return goodHtml;
    }

    initView();
});