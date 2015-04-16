/**
 * Created by ldy on 15/4/10.
 */
var priceDom = $(".conordertuan_total strong");
var totalPriceDom = $(".cart_pay .fl strong");
var CartDomName = "input[name='shopCart']";
var shopName = '';
function editCartNum(id, num) {
    $('.shop_jifen_used').html("");
    var cartPrice = $('#pamount-' + id).data('price') * num;
    priceDom.data("goodsPrice", cartPrice);
    var ship_fee = $(".ship_fee").data("shipFee") || 0;
    var goodsPrice = priceDom.data("goodsPrice");
    totalPriceDom.data("totalPrice", (goodsPrice + ship_fee) || cartPrice);
    var totalPrice = totalPriceDom.data("totalPrice");
    $("[data-count]").text(num);
    priceDom.text("￥"+ utils.toFixed(goodsPrice, 2));
    totalPriceDom.text("￥"+ utils.toFixed(totalPrice, 2));
    var url = BASEURL + '/carts/editCartNum/' + id + '/' + num;
    if (!sso.check_userlogin({"callback": editCartNum, "callback_args": arguments}))
        return false;
    ajaxAction(url, null, null, function (data) {
        if (data && data.success) {
            if (typeof(updateCartItemCount) === 'function') {
                updateCartItemCount();
            }
        }
    }, {'id': id, 'num': num});
    return false;
}
$(".cartnumreduce").on('click', function(){
    editAmount.reduce(CartDomName, function (CartDomName) {
        editCartNum($(CartDomName).data('id'), $(CartDomName).val());
    });
});
$(".cartnumadd").on('click', function(){
    editAmount.add(CartDomName, function (CartDomName) {
        editCartNum($(CartDomName).data('id'), $(CartDomName).val());
    });
});
$('.shop_jifen_used').click(function(){
    var that = $(this);
    if(that.html()=="<i></i>"){
        that.html("");
    }else{
        that.html("<i></i>");
    }
    var balance_use_score = $(".balance_use_score");
    $.post('/orders/apply_score.json', {'use' : that.html()=="<i></i>", 'score':totalPriceDom.data("totalPrice")*100/2}, function(data){
        if (data && data.success) {
            console.log(data);
            var scoreMoney = data.score_money;
            if(data.score_used){
                scoreMoney = - data.score_money;
            }
            var goodsPrice = priceDom.data("goodsPrice");
            var totalPrice = totalPriceDom.data("totalPrice");
            priceDom.data("goodsPrice", goodsPrice + scoreMoney);
            totalPriceDom.data("totalPrice", totalPrice + scoreMoney);
            priceDom.text("￥"+ utils.toFixed(priceDom.data("goodsPrice"), 2));
            totalPriceDom.text("￥"+ utils.toFixed(totalPriceDom.data("totalPrice"), 2));
            balance_use_score.text(data.score_usable);
            balance_use_score.next('span').text(utils.toFixed(data.score_money,2));
        } else {
            utils.alert('使用积分失败', function(){}, 1000);
        }
    }, 'json');
});
$('#confirm_next').on('click',function(e){
    if($("#confirm_next").data("disable") == 'true') {
        return false;
    }
    var balanceDom = $(".tuan_balance");
    var way = balanceDom.data("shipWay") || "";
    var addressInput = $("input[name='consignee_address']").length || "not";
    var zitiChoice = $("#chose_address").length ? $("#chose_address").text(): "not";
    var remarkAddress = $("input[name='consignee_remark_address']").val()||"";
    var address = $("input[name='consignee_address']").val() || $("#chose_address").text();
    if(address.trim()){
        address = address + '['+remarkAddress+']';
    }else{
        if(remarkAddress.trim()){
            address = remarkAddress;
        }else{
            address = '';
        }
    }
    var name = $("input[name='consignee_name']").val();
    var mobile = $("input[name='consignee_mobilephone']").val();
    if (addressInput != "not" && address == "") {
        utils.alert("请输入地址");
        e.preventDefault();
        return false;
    }
    if(zitiChoice != "not" && zitiChoice.length <= 0){
        utils.alert("请输入自提地址");
        e.preventDefault();
        return false;
    }
    if (name == "") {
        utils.alert("请输入你的姓名");
        e.preventDefault();
        return false;
    }
    if (mobile.length != 11) {
        utils.alert("联系电话格式不正确");
        e.preventDefault();
        return false;
    }

    var cart_id = $("input[name='shopCart']").data("id") || false;
    var tuan_buy_id = balanceDom.data("tuanbuyId") || false;
    var tuan_id = balanceDom.data("tuanteamId") || false;
    if(!(cart_id && tuan_buy_id && tuan_id)){
        return false;
    }
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: "/tuan_buyings/pre_order",
        data: {name: name, mobile: mobile, cart_id: cart_id, tuan_buy_id: tuan_buy_id, tuan_id: tuan_id, address:address, way:way ,shop_name:shopName},
        success: function (a) {
            if (a.success) {
                $("#confirm_next").attr('data-disable', 'true');
                window.location.href = '/tuan_buyings/tuan_pay/' + a.order_id;
            } else {
                if(a.info){
                    utils.alert(a.info);
                }else{
                    utils.alert("结算出错，请刷新重试");
                }
            }
        }
    });
});

var zitiObj = function(area,height, width){
    var conorder_url = '#TB_inline?inlineId=hiddenModalContent&modal=true&height=' + height + '&width=' + width;
    var choose_area='';
    return {
        generateZitiArea: function(){
            for(var i=0; i< area.length; i++){
                choose_area += '<ul><li><a href="'+ conorder_url +'" class="thickbox" area-id="' +area[i].id + '">' + area[i].name + '</a></li> </ul>';
            }
            return choose_area;
        },
        bindThickbox: function(){
            $(".thickbox").each(function(){
                var that = $(this);
                that.on("click", function(e){
                    $('.thickbox').not(that).removeClass("cur");
                    var area_id = $(this).attr("area-id");
                    setData(area_id);
                    that.addClass("cur");
                })
            });
        }
    }
};
function setData(area_id){
    var chose_address = zitiAddress.getShipAddress(area_id);
    var $chose_item = '';
    $.each(chose_address,function(index,item){
        if(item['not_shop']){
            $chose_item +=' <p data-shop-name="'+item['shop_name']+'">'+item['address']+'</p>';
        }else{
            $chose_item +=' <p>'+item['address']+' 好邻居便利店</p>';
        }
    });
    $("#area_list").html($chose_item);
    $("#area_list p").each(function(){
        var that =$(this);
        that.on("click",function(){
            that.css("background-color","#eeeeee");
            $("#chose_address").html(that.text());
            shopName = that.data('shop-name');
            tb_remove();
        })
    });
}
