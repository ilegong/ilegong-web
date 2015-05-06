$(document).ready(function () {
    var cache = {};
    var $productsContent = $('#products-content');
    var currentTagId = -1;
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
        me.addClass('cur');
        var tagId = me.data('id');
        if (tagId != currentTagId) {
            currentTagId = tagId;
            $productsContent.html('');
            loadDatas(tagId);
        }
    });
    //load tag products
    function loadDatas(tagId) {
        if (!cache[tagId]) {
            cache[tagId] = $.getJSON('/categories/getTagProducts/' + tagId).promise();
        }
        cache[tagId].done(drawToDOM);
    }

    //draw dom
    function drawToDOM(datas) {
        //console.log(datas);
        var data_list = datas['data_list'];
        var mapBrands = datas['mapBrands'];
        console.log(data_list);
        $.each(data_list, function (index, item) {
            var brand = mapBrands[item['brand_id']];
            $productsContent.append(genGoodItemDom(item, brand));
        });
    }

    function genGoodItemDom(good, brand) {
        var price = good['price'];
        price = parseFloat(price).format(2);
        var originPrice = good['original_price'];
        if (originPrice) {
            originPrice = parseFloat(originPrice).format(2);
        } else {
            originPrice = 0;
        }
        var productDate = new Date(good['created']);
        var goodUrl = '/products/' + productDate.yyyymmdd() + '/' + good['slug'] + '.html?history=/&amp;_sl=h5.cate.list';
        var brandDate = new Date(brand['Brand']['created']);
        var brandUrl = '/brands/' + brandDate.yyyymmdd() + '/' + brand['Brand']['slug'] + '.html';
        var goodHtml = '<div class="good"> <a href="' + goodUrl + '" class="xq">';
        if(good['limit_area']==1){
            goodHtml+='< p > 仅限 < br / > 北京 < / p > ';
        }
        goodHtml += '<img src="' + good['listimg'] + '"/> <div class="title">' + good['name'] + '</div> <div class="price"><strong>' + price + '</strong>';
        if (originPrice > 0) {
            goodHtml += '&nbsp;<label>' + originPrice + '</label>'
        }
        goodHtml += '</div> </a> <s class="clearfix"> <a href="' + brandUrl + '" class="fl"><span class="phead fl"><img src="' + brand['Brand']['coverimg'] + '"/></span><span class="txt fl">' + brand['Brand']['name'] + '</span></a> <a href="' + goodUrl + '" class="fr btn radius5">立即购买</a> </s> </div>';
        return goodHtml;
    }
});