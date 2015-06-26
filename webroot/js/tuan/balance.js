/**
 * Created by ldy on 15/4/10.
 */
var priceDom = $(".conordertuan_total strong");
var totalPriceDom = $(".cart_pay .fl strong");
var CartDomName = "input[name='shopCart']";
var balanceShipFeeDom = $('#balance_ship_fee');
var balanceShipFee = balanceShipFeeDom.val()||0;
var orginTotalPrice = totalPriceDom.data('totalPrice');
function editCartNum(id, num) {
    $('.shop_jifen_used').html("");
    $('#promotion_code').val("");
    var cartPrice = $('#pamount-' + id).data('price') * num;
    priceDom.data("goodsPrice", cartPrice);
    var ship_fee = $(".ship_fee").data("shipFee") || 0;
    var goodsPrice = priceDom.data("goodsPrice");
    totalPriceDom.data("totalPrice", (goodsPrice + ship_fee) || cartPrice);
    var totalPrice = totalPriceDom.data("totalPrice");
    $("[data-count]").text(num);
    priceDom.text("￥"+ utils.toFixed(goodsPrice, 2));
    totalPriceDom.text("￥"+ utils.toFixed(totalPrice, 2));
    orginTotalPrice = totalPriceDom.data('totalPrice');
    var url = BASEURL + '/carts/editCartNum/' + id + '/' + num;
    if (!sso.check_userlogin({"callback": editCartNum, "callback_args": arguments}))
        return false;
    ajaxAction(url, null, null, function (data) {
        if (data && data.success) {
            if (typeof(updateCartItemCount) === 'function') {
                updateCartItemCount();
                $('li > a.coupon > input[type=checkbox]').each(function(index,item){
                    $(item).prop("checked", false);
                });
            }
        }
    }, {'id': id, 'num': num});
    return false;
}
($(CartDomName).prev("a")).on('click', function(){
    editAmount.reduce(CartDomName, function (CartDomName) {
        editCartNum($(CartDomName).data('id'), $(CartDomName).val());
    });
});
($(CartDomName).next("a")).on('click', function(){
    editAmount.add(CartDomName, function (CartDomName) {
        editCartNum($(CartDomName).data('id'), $(CartDomName).val());
    });
});

$('#use_promotion_code').on('click',function(){
    var $promotionCode = $('#promotion_code');
    var promotionCode = $promotionCode.val();
    if(promotionCode){
        $.post('/orders/apply_promotion_code/'+promotionCode,{},function(data){
            //console.log(data);
            if(data && data['success']){
                if(data['reducePrice']>0){
                    var totalPrice = parseFloat(orginTotalPrice)-parseFloat(data['reducePrice']);
                    priceDom.data("goodsPrice", totalPrice);
                    totalPriceDom.data("totalPrice", totalPrice);
                    priceDom.text("￥"+ utils.toFixed(priceDom.data("goodsPrice"), 2));
                    totalPriceDom.text("￥"+ utils.toFixed(totalPriceDom.data("totalPrice"), 2));
                    $('.shop_jifen_used').html('');
                    var checkbox = $("[data-coupon_item_id] > input[type=checkbox]");
                    checkbox.prop("checked", false);
                }else{
                    utils.alert('优惠码使用失败');
                }
            }else{
                if(data['reason']=='code_error'){
                    utils.alert('优惠码有误,请重新输入');
                }else if(data['reason']=='not_login'){
                    utils.alert('请登录',function(){window.location.href = '/users/login.html?referer=' + encodeURIComponent("/");},1000);
                }else if(data['reason']=='cart_empty'){
                    utils.alert('优惠码使用失败,请重新购买');
                }else if(data['reason']=='has_used'){
                    utils.alert('你已经使用过优惠码');
                }else{
                    utils.alert('优惠码使用失败');
                }
            }
        },'json');
    }else{
        utils.alert('请输入优惠码');
    }
});
//use score
$('.shop_jifen_used').click(function(){
    var that = $(this);
    if(that.html()=="<i></i>"){
        that.html("");
    }else{
        that.html("<i></i>");
    }
    var balance_use_score = $(".balance_use_score");
    $('#promotion_code').val("");
    $.post('/orders/apply_score.json', {
        'use': that.html() == "<i></i>",
        'score': totalPriceDom.data("totalPrice") * 100 / 2,
        'ship_fee': balanceShipFee
    }, function (data) {
        if (data && data.success) {
            //console.log(data);
            $('#promotion_code').val();
            var scoreMoney = data.score_money;
            if(data.score_used){
                scoreMoney = - data.score_money;
            }
            var goodsPrice = parseFloat(priceDom.data("goodsPrice"));
            var totalPrice = parseFloat(totalPriceDom.data("totalPrice"));
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

$('div.usecoupon > a').on('click',function (e) {
    e.preventDefault();
    $('#promotion_code').val("");
    var that = $(this);
    var brandId = that.attr('data-brandId');
    var coupon_item_id = that.attr('data-coupon_item_id');
    var checkbox = $('input[type=radio]',that);
    checkbox.prop("checked", !checkbox.prop("checked"));
    var action = (checkbox.prop("checked") == false )? 'unapply' : 'apply';
    $.post('/orders/apply_coupon.json', {
        'brand_id': brandId,
        'coupon_item_id': coupon_item_id,
        'action': action,
        'ship_fee': balanceShipFee
    }, function (data) {
        if (data) {
            //console.log(data);
            $('#promotion_code').val();
            if (data.changed) {
                var totalPrice = utils.toFixed(parseFloat(data.total_price), 2);
                totalPriceDom.text("￥"+totalPrice);
                totalPriceDom.data("totalPrice", totalPrice);
                tb_remove();
            } else {
                if (data.reason == 'not_login') {
                    utils.alert('您长时间未操作，请重新登录', function () {
                        window.location.href = '/users/login?refer=' + encodeURIComponent('/carts/listcart');
                    });
                } else if (data.reason == 'share_type_coupon_exceed') {
                    checkbox.prop("checked", !checkbox.prop("checked"));
                    utils.alert('优惠券使用超出限制',function(){
                        tb_remove();
                    });
                }
            }
        }
    }, 'json');
});

$('#confirm_next').on('click',function(e){
    if($("#confirm_next").data("disable") == 'true') {
        return false;
    }
    var is_try = false;
    var balanceDom = $(".tuan_balance");
    var choseAddress = $("#chose_address");
    var way_id = balanceDom.data('wayId')||0;
    var addressInput = $("input[name='consignee_address']").length || "not";
    var zitiChoice =choseAddress.length ? choseAddress.text(): "not";
    var remarkAddress = $("input[name='consignee_remark_address']").val()||"";
    var address = $("input[name='consignee_address']").val() || choseAddress.text();
    var $remark_address = $('#remark_address');
    var name = $("input[name='consignee_name']").val();
    var mobile = $("input[name='consignee_mobilephone']").val().replace(/\s+$/,"");
    if (addressInput != "not" && address == "") {
        utils.alert("请输入地址");
        e.preventDefault();
        return false;
    }
    if(zitiChoice != "not" && zitiChoice.length <= 0){
        utils.alert("请选择自提地址");
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

    if ($remark_address.length > 0 && ($remark_address.css('display') != 'none')) {
        if (!remarkAddress.trim()) {
            utils.alert("请填写详细地址");
            e.preventDefault();
            return false;
        }
    } else {
        remarkAddress = '';
    }

    var cart_id = $("input[name='shopCart']").data("id") || false;
    var tuan_buy_id = balanceDom.data("tuanbuyId") || false;
    var try_id = balanceDom.data('tryId')||false;
    var tuan_id = balanceDom.data("tuanteamId") || false;
    var tuan_sec = balanceDom.data("tuanSec")||false;
    var global_sec = balanceDom.data('globalSec')||false;
    var member_id = '';
    var shop_id =  choseAddress.data('shopId')||0;
    if(tuan_buy_id){
       member_id = tuan_buy_id;
    }
    if(try_id){
        member_id = try_id;
        is_try = true;
    }
    if(global_sec){
        if(!(cart_id && member_id)){
            utils.alert('订单有误,请重新下单');
            return false;
        }
    }else{
        if(!(cart_id && member_id && tuan_id)){
            utils.alert('订单有误,请重新下单');
            return false;
        }
    }
    jQuery.ajax({
        type: "POST",
        dataType: "json",
        url: "/tuan_buyings/pre_order",
        data: {name: name, mobile: mobile, cart_id: cart_id, member_id: member_id, tuan_id: tuan_id, address:address,remark_address:remarkAddress, way_id:way_id , tuan_sec:tuan_sec , shop_id: shop_id,global_sec:global_sec},
        success: function (a) {
            if (a.success) {
                $("#confirm_next").attr('data-disable', 'true');
                if(is_try){
                    window.location.href = '/orders/detail/'+ a.order_id+'/pay';
                }else{
                    window.location.href = '/tuan_buyings/tuan_pay/' + a.order_id;
                }
            } else {
                if(a.info){
                    if(a.url){
                        utils.alert(a.info,function(){
                            window.location.href = a.url;
                        });
                    }else{
                        utils.alert(a.info);
                    }
                }else{
                    utils.alert("结算出错，请刷新重试");
                }
            }
        }
    });
});


