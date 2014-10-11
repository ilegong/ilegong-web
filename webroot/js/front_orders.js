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
	for(var i in request){
		$('#consignee_'+i).val(request[i])
	}	
}


//选择地址进行编辑
function editConsignee(obj,id){
	//$('#addr_'+id).attr("checked","checked"); 
	//var lastid = $("input[name='data[OrderConsignee][id]']:checked").val();
	//$('#addr_'+lastid).attr("checked",false);
	//$("input[name='data[OrderConsignee][id]']").each(function(){
	//	$(this).attr("checked",false).removeAttr("checked");
	//});
	$('#addr_'+id).get(0).checked = true;
	//$('#addr_'+id).attr("checked",true);
	$('#edit_type').val('edit');
	ajaxAction(BASEURL+"/orders/load_consignee/"+id,null,null,'editConsignee');	
	return false;
}

//新增地址
function use_NewConsignee(){
	$('#edit_type').val('new');
	$('#consignee_addr').show();
	
	$('#consignee_name').val('');
	$('#consignee_area').val('');	
	$('#consignee_mobilephone').val('');
	$('#consignee_telephone').val('');
	$('#consignee_email').val('');
	$('#consignee_postcode').val('');	
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
function orders_receive_3g(order_id){
    return ajaxAction(BASEURL+"/orders/confirm_receive/",{'order_id':order_id},null, function(){
        showSuccessMessage('您已确认收货', function(){
            $('.order_item_action_'+order_id).html('<a class="btn-sm btn-primary" href="/orders/detail/'+order_id+'">详细</a>');
            $('.order-status-'+order_id).html('已收货');
            $('#orders-wait_receive').find('.order_item_'+order_id).remove();
        }, 10000);
    });
}

function orders_undo(order_id) {
    return ajaxAction(BASEURL+"/orders/confirm_undo/",{'order_id':order_id},null, function(){
        showSuccessMessage('订单已取消', function(){
            $('.order_item_action_'+order_id).html('<a class="btn-sm btn-primary" href="/orders/detail/'+order_id+'">详细</a>');
            $('.order-status-'+order_id).html('已取消');
            $('#orders-wait_payment').find('.order_item_'+order_id).remove();
        }, 2000);
    });
}

function orders_remove(order_id) {
    return ajaxAction(BASEURL+"/orders/confirm_remove/",{'order_id':order_id}, null, function(){
        showSuccessMessage('订单已删除', function(){
            $('.order_item_'+order_id).remove();
    }, 2000);
    });
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