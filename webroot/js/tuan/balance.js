/**
 * Created by ldy on 15/4/10.
 */
var priceDom = $(".conordertuan_total strong");
var totalPriceDom = $(".cart_pay .fl strong");
var CartDomName = "input[name='shopCart']";
var remarkAddress = $('#remark_address');
function zitiAddress(type){
    var beijingArea= {
        110101:"东城区",
        110108:"海淀区",
        110102:"西城区",
        110105:"朝阳区",
        110106:"丰台区",
        110114:"昌平区",
        110113:"顺义区",
        110115:"大兴区",
        110112:"通州区"
    };
    //崇文并入东城区， 宣武并入西城区
    var ship_address = {};
    var area = [];
    $.getJSON('/tuan_buyings/get_offline_address?type='+type,function(data){
        ship_address = data;
        $.each(data,function(index,item){
            $("[area-id="+index+"]").show();
        });
    });
    var getShipAddress = function(areaId){
        return ship_address[areaId];
    };
    return {
        getBeijingAreas: beijingArea,
        getShipAddress: getShipAddress
    }
};
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
    var is_try = false;
    var balanceDom = $(".tuan_balance");
    var choseAddress = $("#chose_address");
    var way_id = balanceDom.data('wayId')||0;
    var addressInput = $("input[name='consignee_address']").length || "not";
    var zitiChoice =choseAddress.length ? choseAddress.text(): "not";
    var remarkAddress = $("input[name='consignee_remark_address']").val()||"";
    var address = $("input[name='consignee_address']").val() || choseAddress.text();
    var $remark_address = $('#remark_address');
    if(address.trim()){
        if(remarkAddress){
            address = address + '['+remarkAddress+']';
        }
    }
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

    if($remark_address.css('display') != 'none'){
        if(!remarkAddress.trim()){
            utils.alert("请填写备注地址");
            e.preventDefault();
            return false;
        }
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

var zitiObj = function(area,height, width){
    var conorder_url = '#TB_inline?inlineId=hiddenModalContent&modal=true&height=' + height + '&width=' + width;
    var choose_area='';
    return {
        generateZitiArea: function(){
            for(var addr in area){
                choose_area += '<ul><li><a style="display: none" href="'+ conorder_url +'" class="thickbox" area-id="' +addr + '">' + area[addr] + '</a></li> </ul>';
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
    var chose_address = zitiAddressData.getShipAddress(area_id);
    chose_address = $.map(chose_address, function(value, index) {
        return [value];
    });
    chose_address = chose_address.sort(function(item1,item2){
        return item1['name'].localeCompare(item2['name']);
    });
    var $chose_item = '';
    $.each(chose_address,function(index,item){
        $chose_item +=' <p data-shop-id="'+ item['id'] +'" data-can-remark-address="'+item['can_remark_address']+'" data-shop-name="'+item['alias']+'">'+item['name']+'<br/>';
        if(item['owner_phone']){
            $chose_item+='联系电话:'+item['owner_phone'];
        }
        if(item['owner_name']){
            $chose_item+=' 联系人: '+item['owner_name'];
        }
        $chose_item+='</p>';
    });
    $("#area_list").html($chose_item);
    $("#area_list p").each(function(){
        var that =$(this);
        that.on("click",function(){
            that.css("background-color","#eeeeee");
            var canRemarkAddress = that.data('can-remark-address');
            shopId = that.data('shop-id');
            //should remark address
            if(canRemarkAddress==1){
                remarkAddress.show();
            }else{
                remarkAddress.hide();
            }
            $("#chose_address").html(that.text()).data('shopId', shopId);
            tb_remove();
        })
    });
}
