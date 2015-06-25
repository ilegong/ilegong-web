//显示地址信息表单
var label_consignee = null;
function showForm_consignee(obj){
   selAddressId=0;
   label_consignee=$("#part_consignee").html();
   showWaitInfo('正在读取收货人信息，请等待！',obj);
   ajaxActionHtml(BASEURL+"/orders/edit_consignee","#part_consignee","");
}
function close_consignee(obj){
  if(label_consignee==null){
     showWaitInfo('正在关闭收货人信息，请等待！',obj);
     ajaxActionHtml(BASEURL+"/orders/info_consignee","#part_consignee","");
  }
  else{
	  $("#part_consignee").html(label_consignee);
  }
}
rs_callbacks.deleteConsignee = function(request){
	$('#consignee_'+request.id).remove();
}
//取消常用地址
function DelAddress(obj,id){
	if(confirm('确认要删除吗？')){
		ajaxAction(BASEURL+"/orders/delete_consignee/"+id,null,null,'deleteConsignee');
	}
}
rs_callbacks.defaultConsignee = function(request){
	$('.consignee_row').removeClass('danger');
	$('#consignee_'+request.id).addClass('danger');
}
//设为常用地址
function SetDefaultAddress(obj,id){
	ajaxAction(BASEURL+"/orders/default_consignee/"+id,null,null,'defaultConsignee');
	return true;
}
rs_callbacks.editConsignee = function(request){
	$('#consignee_addr').show();
	$('#consignee_address').show();
    var id_group = {province_id:0, city_id:0, county_id:0, town_id:0 };
	for(var i in request) {
        $('#consignee_'+i).val(request[i]);
        if(i == 'province_id'||i == 'city_id'|| i == 'county_id'||i == 'town_id'){
            id_group[i] = request[i];
        }
    }
    $.ajax({type: "GET", dataType: "json", url: "/locations/get_address", data: id_group, success: function(a) {
        if (a) {
            var b = 0;
            for (var f in a.histories){
                b++;
                if(b == 1){
                    $("#provinceDiv").val(f);
                    var g = "<option value='0'>\u8bf7\u9009\u62e9</option>";
                    for (var d in a.city_list){
                        g += "<option value='" + d + "'>" + a.city_list[d] + "</option>";
                    }
                    $("#cityDiv").html(g);
                }else if(b == 2){
                    $("#cityDiv").val(f);
                    var g = "<option value='0'>\u8bf7\u9009\u62e9</option>";
                    g += "<option value='0'>---</option>";
                    for (var d in a.county_list){
                        g += "<option value='" + d + "'>" + a.county_list[d] + "</option>";
                    }
                    $("#countyDiv").html(g);
                }else if(b == 3){
                    $("#countyDiv").val(f);
                    if(a.town_list){
                        var g = "<option value='0'>\u8bf7\u9009\u62e9</option>";
                        g += "<option value='0'>---</option>";
                        for (var d in a.town_list){
                            g += "<option value='" + d + "'>" + a.town_list[d] + "</option>";
                        }
                        $("#townDiv").html(g);
                    }
                }else if(b == 4){
                    $("#townDiv").val(f);
                }
            }
            if (b > 3) {
                $("#townDiv").show();
            }else{
                $("#townDiv").hide();
            }

        }
        fillTownName();
    }});
};


//选择地址进行编辑
function editConsignee(obj,id){
	//$('#addr_'+id).attr("checked","checked"); 
	//var lastid = $("input[name='data[OrderConsignee][id]']:checked").val();
	//$('#addr_'+lastid).attr("checked",false);
	//$("input[name='data[OrderConsignee][id]']").each(function(){
	//	$(this).attr("checked",false).removeAttr("checked");
	//});
    $("#provinceDiv").val('');
    $("#cityDiv").val('');
    $("#countyDiv").val('');
    $("#townDiv").val('');
	$('#addr_'+id).get(0).checked = true;
	//$('#addr_'+id).attr("checked",true);
	$('#edit_type').val('edit');
	ajaxAction(BASEURL+"/orders/load_consignee/"+id,null,null,'editConsignee');
    //$("html, body").animate({scrollTop:$("#consignee_addr").offset().top},1000);
	return false;
}

//新增地址
function use_NewConsignee(){
	$('#edit_type').val('new');
	$('#consignee_addr').val('').show();
	$('#consignee_name').val('');
	$('#consignee_area').val('');	
    $('#consignee_address').val('');
	$('#consignee_mobilephone').val('');
	$('#consignee_telephone').val('');
	$('#consignee_email').val('');
	$('#consignee_postcode').val('');	
    $('#areaHide').val('');
    $('#areaName').text('');
    $("#provinceDiv").val('');
    $("#cityDiv").val('');
    $("#countyDiv").val('');
    $("#townDiv").val('');

}
/**********************************************************************/

//选择发票地址
rs_callbacks.changeInvoice = function(request){	
	if(request.customtype == 'company'){
		$('#invoince_ct_company').trigger('click');
	}
	else{
		$('#invoince_ct_personal').trigger('click');
	}
	$('#invoice_name').val(request.name);
	$('input[@type=radio][name="data[OrderInvoice][content]"][value="'+request.content+'"]').attr('checked',true);
}
function changeInv(obj,addIndex){
	ajaxAction(BASEURL+"/orders/load_invoice/"+addIndex,null,null,'changeInvoice');
	$(obj).parents('li:first').siblings().removeClass('xz');
	$(obj).parents('li:first').addClass('xz');	
}
//----------------------------发票--start-------------------------
var label_invoice;
function showForm_invoice(obj){
  label_invoice=$("#part_invoice").html();
  showWaitInfo('正在读取发票信息，请等待！',obj);
  ajaxActionHtml(BASEURL+"/orders/edit_invoice","#part_invoice");
  //setAjax_getResAndRunCode("action=showForm_invoice","part_invoice","GetInvoiceList();isInvoiceOpen=true;"+radioList);
}
rs_callbacks.deleteInvoice = function(request){
	$('#Invoiceli_'+request.id).remove();
}
function DelInv(obj,id){
	if(confirm('确认要删除吗？')){
		ajaxAction(BASEURL+"/orders/delete_invoice/"+id,null,null,'deleteInvoice');
	}
}
function close_invoice(obj){
	$("#part_invoice").html(label_invoice);
}
//----------------------------发票--end-------------------------
//显示支付方式和配送方式表单
function showForm_payTypeAndShipType(obj){
   //showWaitInfo('正在读取支付方式及配送方式信息，请等待！',obj);
   var runCode="isPayTypeAndShipTypeOpen=true;";
   runCode+="setPayShipRadioDefault();";
   runCode+="if(isShowUpdateInfo){";
//   runCode+="isShowUpdateInfo=false;";
   runCode+="$('#updateInfo_payType').html(\"<span class='payTypeChangeAlert'>由于您更改了收货人信息，请重新填写支付方式和配送方式！</span>\");";
   runCode+="}";
   runCode+="setPayRemarkShow();ShowShipTimeRemark();";
   $('#part_payTypeAndShipType .o_show').hide();
   $('#part_payTypeAndShipType .o_write').show();
   // ajaxActionHtml(BASEURL+"/orders/edit_consignee","#part_consignee","");
   //setAjax_getResAndRunCode("action=showForm_payTypeAndShipType","part_payTypeAndShipType",runCode);
}

//关闭支配方式
function close_payTypeAndShipType(obj){
	$('#part_payTypeAndShipType .o_show').show();
	$('#part_payTypeAndShipType .o_write').hide();
//  showWaitInfo('正在关闭表单，请等待！',obj);
//  isShowUpdateInfo=false;
//  showLabel_payTypeAndShipType();
}
//选择支付方式
function changePayType(payType){
   $('#payType_IdPaymentType').val(payType);
   showWaitInfoOnInner('正在加载配送方式信息，请等待。。。',g('part_shipType'));
   

   setAjax_getResAndRunCode("action=changePayType&payType="+payType,"part_shipType",runCode);
   
   setPayRemarkShow();
}

/********** 备注信息 ***************/

//选择发票地址
rs_callbacks.saveOrderRemark = function(request){	
	$('#order_remark_show').html(request.Order.remark);
	close_remark();
}
function saveOrder_remark(content){
	ajaxAction(BASEURL+"/orders/edit_remark/",{'data[Order][remark]':content},null,'saveOrderRemark');
}
function showForm_remark(obj){
	$('#part_remark .o_show').hide();
	$('#part_remark .o_write').show();
//   label_remark=g('part_remark').innerHTML;
//   showWaitInfo('正在读取订单备注信息，请等待！',obj);
//   setAjax_getResAndRunCode("action=showForm_remark","part_remark","isRemarkOpen=true");
}

function close_remark(){
   $('#part_remark .o_show').show();
   $('#part_remark .o_write').hide();
}

//选择发票地址
rs_callbacks.confirm_order_status = function(request){
//	$('.order-status-'+request.order_id, '#order-status-'+request.order_id).html('<font color="red">'+request.msg+"</font>");
	var element= $('#order-status-'+request.order_id);
    var element2= $('.order-status-'+request.order_id);
    element.html('<font color="red">'+request.msg+"</font>");
    element2.html('<font color="red">'+request.msg+"</font>");
}

//设置订单的快递类型与快递单号
function ship_order(order_id, creator){
	if($('#ship-type-'+order_id).val()=="" || $('#ship-code-'+order_id).val()==""){
		alert("请选择快递类型与快递单号！");
		return false;
	}
    creator = creator || 0;
	return ajaxAction(BASEURL+"/orders/set_status/"+creator,{'order_id':order_id,'status':2,'ship_code':$('#ship-code-'+order_id).val(),'ship_type':$('#ship-type-'+order_id).val()},null,'confirm_order_status');
}
//标记订单的发货日期与发货方式
function mark_order(order_id){
    if($('#mark-date-'+order_id).val()=="" || $('#mark-tip-'+order_id).val()==""){
        alert("请输入预发货日期和发货方式！");
        return false;
    }
    function callback(request){
        console.log(request);
        alert(request.msg);
    }
    return ajaxAction(BASEURL+"/orders/set_mark_order",{'order_id':order_id, 'mark_date':$('#mark-date-'+order_id).val(),'mark_tip':$('#mark-tip-'+order_id).val()},null,'callback');
}

//用户确认收货
function confirm_receive(order_id){
	return ajaxAction(BASEURL+"/orders/confirm_receive/",{'order_id':order_id},null,'confirm_order_status');
}
//商家确认订单
function confirm_order(order_id, status, creator){
    creator = creator || 0;
	return ajaxAction(BASEURL+"/orders/set_status/"+creator,{'order_id':order_id,'status':status},null,'confirm_order_status');
}

//用户确认收货
function orders_receive_3g(order_id,is_try,is_mobile){
    return ajaxAction(BASEURL+"/orders/confirm_receive/",{'order_id':order_id},null, function(){
        showSuccessMessage('您已确认收货', function(){
            if(is_try){
                if(is_mobile){
                    //$('.order_item_action_'+order_id).html('<a href="/orders/detail/"'+order_id+' class="btn_skin_gary">查看订单</a>');
                }else{
                    $('.order_item_action_'+order_id).html('<a class="btn-sm btn-primary" href="/orders/detail/'+order_id+'">详细</a>');
                }
            }else{
                if(is_mobile){
                    $('.order_item_action_'+order_id).html('<a class="btn_skin_orange" href="/comments/add_comment/'+order_id+'.html?history=/orders/mine.html?tab=comment">评论赢积分</a>');
                }else{
                    $('.order_item_action_'+order_id).html('<a class="btn-sm btn-primary" href="/orders/detail/'+order_id+'">详细</a><a class="btn-sm btn-warning" href="/comments/add_comment/'+order_id+'">评论赢积分</a>');
                }
            }
            $('.order-status-'+order_id).html('已收货');
            $('#orders-wait_receive').find('.order_item_'+order_id).remove();
        }, 10000);
    });
}
//用户确认收货
function orders_receive_3g_detail(order_id,is_try, callback){
    return ajaxAction(BASEURL+"/orders/confirm_receive/",{'order_id':order_id},null, function(data){
        callback(data);
    });
}

function orders_undo(order_id,is_mobile) {
    return ajaxAction(BASEURL+"/orders/confirm_undo/",{'order_id':order_id},null, function(){
        showSuccessMessage('订单已取消', function(){
            if(is_mobile){
                $('.order_item_action_'+order_id).html('<a class="btn_skin_gary" href="javascript:;" onclick="bootbox.confirm(\'您确认要删除吗?\', function(result) {if(result){orders_remove('+order_id+');}})">删除订单</a>');
            }else{
                $('.order_item_action_'+order_id).html('<a class="btn-sm btn-primary" href="/orders/detail/'+order_id+'">详细</a>');
            }
            $('.order-status-'+order_id).html('已取消');
            $('#orders-wait_payment').find('.order_item_'+order_id).remove();
        }, 2000);
    });
}

function orders_undo_3g(order_id,callback){
    return ajaxAction(BASEURL+"/orders/confirm_undo/",{'order_id':order_id},null,function(data){
        callback(data);
    });
}

function orders_remove(order_id) {
    return ajaxAction(BASEURL+"/orders/confirm_remove/",{'order_id':order_id}, null, function(){
        showSuccessMessage('订单已删除', function(){
            $('.order_item_'+order_id).remove();
    }, 2000);
    });
}

function orders_remove_3g(order_id,callback){
    return ajaxAction(BASEURL+"/orders/confirm_remove/",{'order_id':order_id}, null, callback);
}


//检查联系电话
function check_telephone()
{
   removeAlert('#phone_ff');   
   var pNode=$('#consignee_telephone').parent();
   //var myReg=/((\d+)|^((\d+)|(\d+)-(\d+)|(\d+)-(\d+)-(\d+)-(\d+))$)/;
   var myReg=/(\d{11})|^((\d{7,8})|(\d{4}|\d{3})-(\d{7,8})|(\d{4}|\d{3})-(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1})|(\d{7,8})-(\d{4}|\d{3}|\d{2}|\d{1}))$/;
   if($('#consignee_telephone').val()!='' && !myReg.test($('#consignee_telephone').val())){showAlert('固定电话格式不正确',pNode,'phone_ff');return false;}
   if($('#consignee_telephone').val()!='' && $('#consignee_telephone').val().length > 20 ){showAlert('固定电话格式不正确',pNode,'phone_ff');return false;}
   return true;
}
//检查手机号
function check_mobile()
{
   removeAlert('#mobile_ff');
   if($('#consignee_mobilephone').val()!=''){
	   var pNode=$('#consignee_mobilephone').parent();
	   var myReg=/(^\s*)(((\(\d{3}\))|(\d{3}\-))?13\d{9}|1\d{10})(\s*$)/;
	   if(!myReg.test($('#consignee_mobilephone').val())){showAlert('手机号格式不正确',pNode,'mobile_ff');return false;}
   }
   return true;
}
//检查Email
function check_email()
{  
   var iSign='email';
   removeAlert('#'+iSign+'_ff');
   if($('#consignee_'+iSign).val()!=''){
	   var pNode=$('#consignee_'+iSign).parent();
	   var myReg=/(^\s*)\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*(\s*$)/;
	   if(!myReg.test($('#consignee_'+iSign).val())){showAlert('电子邮件格式不正确',pNode,iSign+'_ff');return false;}
   }
   return true;
}
//检查邮政编码
function check_postcode()
{  
   removeAlert('postcode_ff');
   if($('#consignee_postcode').val()!=''){
	   var pNode=$('#consignee_postcode').parent();
	   var myReg=/(^\s*)\d{6}(\s*$)/;
	   if(!myReg.test($('#consignee_postcode').val())){showAlert('邮编格式不正确',pNode,'postcode_ff');return false;}
   }
   return true;
}

/*
 * Created by ldy on 14/11/4.
 */
// 请求省份列表
var appDomain = "/locations";
var ohtml = "<option value='0'>\u8bf7\u9009\u62e9</option>";
function loadProvince() {
    var province_lists = [["340000", "\u5b89\u5fbd"], ["110100", "\u5317\u4eac"], ["500100", "\u91cd\u5e86"], ["350000", "\u798f\u5efa"], ["620000", "\u7518\u8083"], ["440000", "\u5e7f\u4e1c"], ["450000", "\u5e7f\u897f"], ["520000", "\u8d35\u5dde"], ["460000", "\u6d77\u5357"], ["130000", "\u6cb3\u5317"], ["230000", "\u9ed1\u9f99\u6c5f"], ["410000", "\u6cb3\u5357"], ["420000", "\u6e56\u5317"], ["430000", "\u6e56\u5357"], ["320000", "\u6c5f\u82cf"], ["360000", "\u6c5f\u897f"], ["220000", "\u5409\u6797"],["210000", "\u8fbd\u5b81"], ["150000", "\u5185\u8499\u53e4"], ["640000", "\u5b81\u590f"], ["630000", "\u9752\u6d77"], ["370000", "\u5c71\u4e1c"], ["310100", "\u4e0a\u6d77"], ["140000", "\u5c71\u897f"], ["610000", "\u9655\u897f"], ["510000", "\u56db\u5ddd"],["120100", "\u5929\u6d25"], ["650000", "\u65b0\u7586"], ["540000", "\u897f\u85cf"], ["530000", "\u4e91\u5357"], ["330000", "\u6d59\u6c5f"]];
    var b = ohtml;
    for (var i = 0, len = province_lists.length; i < len; i++){
        b += "<option value='" + province_lists[i][0] + "'>" + province_lists[i][1] + "</option>";
    }
    $("#provinceDiv").html(b)
}
function loadProvince_get() {
    var a = appDomain + "/get_province";
    jQuery.ajax({type: "POST",dataType: "json",url: a,data: "",success: function(a) {
        if (a) {
            var b = "<option value= '0'>\u8bf7\u9009\u62e9</option>";
            for (var c in a){
                b += "<option value='" + c + "'>" + a[c] + "</option>";
            }
            $("#provinceDiv").html(b)
        }
    },error: function() {
    }})
}
// 请求城市列表
function loadCity() {
    var a = $("#provinceDiv option:selected"), b = a.val(), c = a.text();
    if (b > 0 && 84 != b) {
        var d = appDomain +"/get_city";
        jQuery.ajax({type: "GET",dataType: "json",url: d,data: "provinceId=" + b,cache: !1,success: function(a) {
            var b = ohtml;
            if (a)
                for (var d in a)
                    b += "<option value='" + d + "'>" + a[d] + "</option>";
            $("#cityDiv").html(b), $("#countyDiv").html(ohtml), $("#townDiv").hide().empty(), $("#areaName").text(c), $("#areaHide").val(c);
        },error: function() {
        }})
    } else
        $("#cityDiv").html(ohtml), $("#countyDiv").html(ohtml), $("#townDiv").hide().empty(), $("#areaName").text(""), $("#areaHide").val("");
}
// 请求县镇列表
function loadCounty() {
    var a = $("#cityDiv option:selected"), b = a.val(), c = $("#provinceDiv option:selected").text(), d = a.text();
    if (b > 0) {
        var e = appDomain + "/get_county";
        jQuery.ajax({type: "GET",dataType: "json",url: e,data: "cityId=" + b,cache: !1,success: function(a) {

            if (a) {
                var b = ohtml;
                b += "<option value='0'>---</option>";
                for (var e in a){
                    b += "<option value='" + e + "'>" + a[e] + "</option>";
                }
                $("#countyDiv").html(b), $("#townDiv").hide().empty(), $("#areaName").text(c + d);
                $("#areaHide").val(c + d);
            }
        },error: function() {
        }})
    } else
        $("#countyDiv").html(ohtml), $("#townDiv").hide().empty(), $("#areaName").text(c),$("#areaHide").val(c);
}
// 请求镇乡列表
function loadTown() {
    var a = $("#countyDiv option:selected"), b = a.val(), c = $("#provinceDiv option:selected").text(), d = $("#cityDiv option:selected").text(), e = a.text();
    if (b > 0) {
        var f = appDomain + "/get_town";
        jQuery.ajax({type: "GET",dataType: "json",url: f,data: "countyId=" + b,cache: !1,success: function(a) {

            if (a) {
                var b = 0;
                for (var f in a)
                    b++;
                if (b > 0) {
                    $("#townDiv").show();
                    var g = ohtml;
                    g += "<option value='0'>---</option>";
                    for (var f in a){
                        g += "<option value='" + f + "'>" + a[f] + "</option>";
                    }
                    $("#townDiv").html(g);
                }
            }
            $("#areaName").text(c + d + e);
            $("#areaHide").val(c + d + e);
        },error: function() {
        }})
    } else
        $("#townDiv").hide().empty(), $("#areaName").text(c + d), $("#areaHide").val(c+d)
}
// js显示所选，传值到input，等待submit
function fillTownName() {
    var a = $("#townDiv option:selected"), b = a.val(), c = $("#countyDiv option:selected"), d = c.val(), e = $("#provinceDiv option:selected").text(), f = $("#cityDiv option:selected").text(), g = d > 0 ? c.text() : "", h = b > 0 ? a.text() : "";
    $("#areaName").text(e + f + g + h);
    $("#areaHide").val(e + f + g + h);
}
//改价回调函数
rs_callbacks.modify_order_price = function(request){
//	$('.order-status-'+request.order_id, '#order-status-'+request.order_id).html('<font color="red">'+request.msg+"</font>");
    var element= $('#order-status-'+request.order_id);
    element.prepend('<p style="color:red;">'+request.msg+ " 当前实际价格：￥"+request.modify_price +" 刷新可见</p>");
};

// 修改订单价格
function modify_price(order_id, status, creator,obj){
    var a =$("#order-price-" + order_id).val();
    if(!$.isNumeric(a)){
        bootbox.alert('输入的价格必须是数字');
        return false;
    }else{
        creator = creator || 0;
        ajaxAction(BASEURL+"/orders/set_status/"+creator,{'order_id':order_id,'status':status, 'price':a},null,'modify_order_price');
        $(obj).parent().prev().val('');
        return true;
    }
}
//修改商家备注
function submit_remark(id,obj){
    var a = $(obj).parent().prev().val();
    $.getJSON('/orders/remark_submit',{remark: a,order_id: id}, function(data){
        if(data.content){
            $('#business_remark_' + id).html('');
            $('#business_remark_' + id).html(data.content);
            $('#remark_' + id).val('');
        }
    });

}
//edit_type choose select
function chose_Consignee(){
    $('#edit_type').val('select');
    $('#consignee_addr').hide();
}
var infoToBalance = function(){
    var setRemark = function(self){
        var link = self.attr("href");
        var params = (link.indexOf("?")>-1)?"&":"?";
        $("input[name^='remark']").each(function(){
            var input_name=$(this).attr("name");
            var input_value=$(this).val();
            if(input_value!=""){
                params = params+input_name+"="+input_value+"&";
            }
        });
        params += 'ship_type=' + $('.address:visible').data('ship');
        self.attr("href",link+params);
    };
    var checkAddress = function(){
        var $addressDom = $('.address:visible');
        if($addressDom.data("on")!=1){
            utils.alert("请编辑收货信息");
            return false;
        }
        return true;
    };
    var totalPriceDom= $(".total_price_info");
    var editCartNum = function(id, num) {
        var cartDom = $("input[data-id="+ id +"]");
        var brandId = cartDom.data("brandId");
        var cartNum = cartDom.data("num");
        var goodsPrice = cartDom.data("price");
        var intnum =parseInt(num);
        var proNum = cartDom.data("num");
        cartDom.data("num",num);
        var brandTotal = $(".conordertuan_total[data-brand-id="+ brandId + "]");
        var originPrice = brandTotal.children('strong').data("brandPrice");
        var originNum = brandTotal.children('span').data("brandNum");
        var brandPriceDom = brandTotal.children('strong');
        var brandNumDom = brandTotal.children("span");
        brandPriceDom.data("brandPrice", originPrice + goodsPrice*(intnum - cartNum));
        brandNumDom.data("brandNum", originNum - cartNum + intnum);
        brandPriceDom.text("￥"+ utils.toFixed(brandPriceDom.data("brandPrice"),2));
        brandNumDom.text(brandNumDom.data("brandNum"));
        var totalNum = parseInt($(".total_num_info").text());
        $(".total_num_info").text(totalNum - cartNum + intnum);
        var totalPrice = parseFloat(totalPriceDom.text());
        totalPriceDom.text(utils.toFixed(totalPrice + goodsPrice*(intnum - cartNum), 2));
        var url = BASEURL + '/carts/editCartNum/' + id + '/' + intnum;
        if (!sso.check_userlogin({"callback": editCartNum, "callback_args": arguments}))
            return false;
        ajaxAction(url, null, null, function (data) {
            if (data && data.success) {
                if (typeof(updateCartItemCount) === 'function') {
                    updateCartItemCount();
                }
            }
        }, {'id': id, 'num': num});
        if(proNum > intnum){
            var couponDom = $("div.usecoupon input:checked").first().parent('a');
            if(couponDom.length <= 0){
                return false;
            }
            var couponItemId = couponDom.data("coupon_item_id");
            $.post('/orders/apply_coupon.json', {'brand_id': brandId, 'coupon_item_id': couponItemId, 'action': 'unapply'}, function (data){
                if (data.changed) {
                    totalPriceDom.text(utils.toFixed(data.total_price, 2));
                    couponDom.children(':checkbox').prop("checked", !couponDom.children(':checkbox').prop("checked"));
                }
            }, 'json');
        }
    };
    return{
        submitOrder : function(self){
            if(checkAddress()){
                setRemark(self);
                return true;
            }
            return false;
        },
        cartEdit: function(){
            $(".cart-num-input").each(function(){
                var that = this;
                ($(that).prev("a")).on('click', function(){
                    editAmount.reduce(that, function (that) {
                        editCartNum($(that).data('id'), $(that).val());
                    });

                });
                ($(that).next("a")).on('click', function(){
                    editAmount.add(that, function (that) {
                        editCartNum($(that).data('id'), $(that).val());
                    });
                });
            });
        },
        scoreUse: function(){
            $('.shop_jifen_used').click(function(){
                var that = $(this);
                if(that.html()=="<i></i>"){
                    that.html("");
                }else{
                    that.html("<i></i>");
                }
                var balance_use_score = $(".balance_use_score");
                var totalPrice = parseFloat(totalPriceDom.data('totalPrice')) || 0;
                $.post('/orders/apply_score.json', {'use' : that.html()=="<i></i>", 'score':totalPrice*100/2}, function(data){
                    if (data && data.success) {
                        var scoreMoney = data.score_money;
                        if(data.score_used){
                            scoreMoney = - data.score_money;
                        }
                        balance_use_score.text(data.score_usable);
                        balance_use_score.next('span').text(utils.toFixed(data.score_money,2));
                        var afterChangePrice = utils.toFixed(totalPrice + scoreMoney, 2);
                        totalPriceDom.text('￥'+afterChangePrice);
                    } else {
                        utils.alert('使用积分失败', function(){}, 1000);
                    }
                }, 'json');
            });
        },
        couponUse: function(){
            $('div.usecoupon > a').click(function () {
                var that = $(this);
                var brandId = that.attr('data-brandId');
                var coupon_item_id = that.attr('data-coupon_item_id');
                var checkbox = $('input[type=radio]',that);
                var couponName = that.data('coupon-name');
                var couponTipInfo = $('#coupon-tip-info');
                var useCouponCount = couponTipInfo.data('coupon-count');
                checkbox.prop("checked", !checkbox.prop("checked"));
                var action = (checkbox.prop("checked") == false )? 'unapply' : 'apply';
                var totalPrice = parseFloat(totalPriceDom.data('totalPrice')) || 0;
                $.post('/orders/apply_coupon.json', {'brand_id': brandId, 'coupon_item_id': coupon_item_id, 'action': action}, function (data) {
                    if (data) {
                        //console.log(data);
                        if (data.changed) {
                            var reducedPrice = data.total_reduced;
                            if(reducedPrice>0){
                                var afterChangePrice = utils.toFixed(totalPrice-reducedPrice,2);
                                totalPriceDom.text('￥'+afterChangePrice);
                                couponTipInfo.text(couponName);
                            }else{
                                couponTipInfo.text("您有"+useCouponCount+"张可使用优惠券");
                            }
                            tb_remove();
                        } else {
                            if (data.reason == 'not_login') {
                                utils.alert('您长时间未操作，请重新登录', function () {
                                    window.location.href = '/users/login?refer=' + encodeURIComponent('/carts/listcart');
                                });
                            } else if (data.reason == 'share_type_coupon_exceed') {
                                checkbox.prop("checked", !checkbox.prop("checked"));
                                utils.alert('优惠券使用超出限制,取消后重新选择',function(){
                                    tb_remove();
                                });
                            }
                        }
                    }
                }, 'json');
            });
        }
    }
};