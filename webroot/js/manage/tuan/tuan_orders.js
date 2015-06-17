$(document).ready(function () {
    var products = $('.products');
    var start_stat_date = $('input[name="start_stat_datetime"]');
    var end_stat_date = $('input[name="end_stat_datetime"]');
    var tuan_con_date = $('input[name="tuan_con_date"]');
    var product_con_date = $('input[name="product_con_date"]');
    var $exportBtn = $('button.export-excel');
    var mainContent = $('#mainContent');
    var $currentOperateOrder = null;
    var sendOrderMsg = $('#send_order_msg');
    var refundLog = $('#refund_logs');
    mainContent.height(250);

    start_stat_date.datetimepicker({
        format: 'yyyy-mm-dd hh:ii'
    });
    end_stat_date.datetimepicker({
        format: 'yyyy-mm-dd hh:ii'
    });
    tuan_con_date.datetimepicker({
        format: 'yyyy-mm-dd'
    });
    product_con_date.datetimepicker({
        format: 'yyyy-mm-dd'
    });
    $.getJSON('/manage/admin/tuanTeams/api_tuan_teams', function (data) {
        var tuanTeamsBox = $('.tuan-teams');

        var values = [{'val': -1, 'name': '请选择团队'}];
        $.each(data, function (teamId, item) {
            var tuanTeam = item['TuanTeam'];
            var ele = $('<option value="' + teamId + '">' + tuanTeam['tuan_name'] + '</option>');
            ele.appendTo(tuanTeamsBox);
            ele.data('TuanBuyings', item['TuanBuyings']);
            values.push({'val': teamId, 'name': tuanTeam['tuan_name']});
        });

        iUtils.initSelectBox(tuanTeamsBox);
        initSearchBox($('.tuan-team-search'), values);
        tuanTeamsBox.each(function () {
            updateTuanBuyingSelectBox($("option:selected", $(this)));
        })
    });
    $.getJSON('/manage/admin/tuanProducts/api_tuan_products', function (data) {
        var values = [{'val': -1, 'name': '请选择商品'}];
        $.each(data, function (index, item) {
            var tuan_product = item['TuanProduct'];
            $('<option value="' + tuan_product['product_id'] + '">' + tuan_product['alias'] + '</option>').appendTo($('.tuan-products'));
            values.push({'val': tuan_product['product_id'], 'name': tuan_product['alias']});
        });

        iUtils.initSelectBox($('.tuan-products'));
        initSearchBox($('.tuan-product-search'), values);
    });
    $.getJSON('/manage/admin/tuanProducts/api_products', function (data) {
        var values = [{'val': -1, 'name': '请选择商品'}];
        $.each(data, function (productId, item) {
            var name = item['isTuanProduct'] ? item['TuanProduct']['alias'] : item['ProductTry']['product_name'] + '(' + item['ProductTry']['spec'] + ')';
            if (item['isTuanProduct'] && item['isProductTry']) {
                name = name + "[团，秒]";
            }
            else if (item['isTuanProduct']) {
                name = name + "[团]";
            }
            else if (item['isProductTry']) {
                name = name + "[秒]";
            }

            $('<option value="' + productId + '">' + name + '</option>').appendTo(products);
            values.push({'val': productId, 'name': name});
        });

        iUtils.initSelectBox(products);
        initSearchBox($('.product-search'), values);
    });
    $.getJSON('/manage/admin/offline_stores/api_offline_stores', function (data) {
        var values = [{'val': -1, 'name': '请选择自提点'}];
        var menu = {0: '所有好邻居自提点', 1: '所有自有自提点'};
        var offlineStoreBox = $('.offline_store');
        for (var category in data) {
            $('<option value="" class="store_' + category + '">' + menu[category] + '</option>').appendTo(offlineStoreBox);
            var chose_address = $.map(data[category], function (value) {
                return [value];
            });
            chose_address = chose_address.sort(function (item1, item2) {
                return item1['name'].localeCompare(item2['name']);
            });
            for (var i in chose_address) {
                $('<option value="' + chose_address[i].id + '">' + chose_address[i].name + '</option>').appendTo(offlineStoreBox);
                values.push({'val': chose_address[i].id, 'name': chose_address[i].name});
                var val = $('.store_' + category).val();
                $('.store_' + category).val(chose_address[i].id + ',' + val);
            }
            values.push({'val': $('.store_' + category).val(), 'name': menu[category]});
        }
        iUtils.initSelectBox(offlineStoreBox);
        initSearchBox($('.offline-store-search'), values);
    });

    String.prototype.Trim = function () {
        return this.replace(/(^\s*)|(\s*$)/g, "");
    };

    function initSearchBox(searchBox, values) {
        if (navigator.userAgent.indexOf("MSIE") > 0) {
            searchBox.on('onpropertychange', function () {
                onSearchBoxChanged($(this), values)
            });
        } else {
            searchBox.on('input', function () {
                onSearchBoxChanged($(this), values)
            });
        }
    }

    function onSearchBoxChanged(searchBox, values) {
        var content = searchBox.val().Trim();
        var selector = $("#" + searchBox.data('search-for'));
        selector.empty();
        if (content == '') {
            $.each(values, function (index, value) {
                selector.append('<option value="' + value['val'] + '">' + value['name'] + '</option>');
            });
        } else {
            var reg = new RegExp(content, 'i');
            $.each(values, function (index, val) {
                if (reg.test(val['name'])) {
                    selector.append('<option selected="selected" value="' + val['val'] + '">' + val['name'] + '</option>');
                }
            })
        }
        selector.change();
    }

    $(".nav-tabs a").click(function () {
        var tab = $(this).data('tab');
        activateTab(tab);
    });

    function activateTab(tab) {
        $(".nav-tabs a").parents('li').removeClass('active');
        $(".nav-tabs a[data-tab=" + tab + "]").parents('li').addClass('active');

        $('.tab-pane').removeClass('active');
        $('#' + tab).addClass('active');
    };

    function updateTuanBuyingSelectBox(selectedTuanTeam) {
        var tuanBuyingsBox = $('.tuan-buyings');
        tuanBuyingsBox.empty();
        $('<option value="-1">请选择团购</option>').appendTo(tuanBuyingsBox);
        if (selectedTuanTeam.length <= 0 || typeof(selectedTuanTeam.data('TuanBuyings')) == 'undefined') {
            return;
        }

        $.each(selectedTuanTeam.data('TuanBuyings'), function (index, item) {
            var tuanBuying = item['TuanBuying'];
            var tuanProduct = item['TuanProduct'];
            if (tuanBuying['status'] == 11 || tuanBuying['status'] == 21) {
                return;
            }
            var consignmentType = tuanProduct['consignment_type'] == 0 ? '团满发货' : (tuanProduct['consignment_type'] == 1 ? '团满准备发货' : '排期')
            var status = '进行中'
            if (tuanBuying['status'] == 1) {
                status = '已截单';
            }
            else if (tuanBuying['status'] == 2) {
                status = '已取消';
            }
            var name = tuanProduct['alias'] + "(" + consignmentType + ", " + status + ")";

            $('<option value="' + tuanBuying['id'] + '">' + name + '</option>').appendTo(tuanBuyingsBox);
        });
        iUtils.initSelectBox(tuanBuyingsBox);
    }

    function setupByTuanTeamForm() {
        var form = $('.tab-pane.active');
        var tuanBuyingsbox = $('.tuan-buyings', form);
        var sendDateStart = $('.send-date-start', form);
        var sendDateEnd = $('.send-date-end', form);
        sendDateEnd.attr('disabled', 'disabled');
        $('.search-label', form).on('change', function () {
            if (tuanBuyingsbox.length > 0) {
                updateTuanBuyingSelectBox($("option:selected", $(this)));
            }
            updateSendDateInput($(this).val() || '');
        });
        tuanBuyingsbox.on('change', function () {
            updateSendDateInput($(this).val() || '');
        });
        function updateSendDateInput(commonBoxVal) {
            if (commonBoxVal == -1 || commonBoxVal.indexOf(',') > 0) {
                sendDateStart.removeAttr('disabled');
                sendDateEnd.val('');
                sendDateEnd.attr('disabled', 'disabled');
            }
            else {
                if (tuanBuyingsbox.val() == -1 || tuanBuyingsbox.length == 0) {
                    sendDateStart.removeAttr('disabled');
                    sendDateEnd.removeAttr('disabled');
                }
                else {
                    sendDateStart.attr('disabled', 'disabled');
                    sendDateEnd.attr('disabled', 'disabled');
                }
            }
        }

        setTimeout(updateSendDateInput, 3000);
    }

    function setupHaolinjuStoreDialog(orderId) {
        var sendWeixinMessageCheckBox = $(".send-weixin-message");
        var haolinjuCodeInput = $(".haolinju-code");
        var haolinjuOrderIdInput = $(".haolinju-order-id");
        var shipToHaolinjuStoreForm = $('.ship-to-haolinju-store-form');
        var confirmShipping = function () {
            if (!sendWeixinMessageCheckBox.is(':checked')) {
                if (!confirm("修改为发货，但是不发送到货提醒，确认吗?")) {
                    return;
                }
                shipToHaolinju(haolinjuOrderIdInput.val(), false, '');
            }
            else {
                if (haolinjuCodeInput.val() == '') {
                    utils.alert("修改为发货，并发送到货提醒，请输入提货码");
                    return;
                }
                if (!confirm("修改为发货，并发送到货提醒，提货码为" + haolinjuCodeInput.val() + ", 确认吗？")) {
                    return;
                }
                shipToHaolinju(haolinjuOrderIdInput.val(), true, haolinjuCodeInput.val());
            }
        };
        var shipToHaolinju = function (orderId, sendWeixinMessage, haolinjuCode) {
            $.post('/manage/admin/tuan_orders/ship_to_haolinju_store', {
                orderId: orderId,
                sendWeixinMessage: sendWeixinMessage,
                haolinjuCode: haolinjuCode
            }, function (data) {
                if (data.success) {
                    var returnedOrderId = data.res;
                    if (orderId = returnedOrderId) {
                        utils.alert('订单状态修改成功，并发送了到货提醒');
                    }
                    else {
                        utils.alert('订单状态修改成功，但是没有发送到货提醒');
                    }
                    shipToHaolinjuStoreDialog.dialog("close");
                }
                else {
                    utils.alert('订单修改失败: ' + data.res);

                }
                $('.table-bordered tbody tr').remove('[data-order-id=' + orderId + ']');
            }, 'json')
        };

        var shipToHaolinjuStoreDialog = $(".ship-to-haolinju-store-dialog").dialog({
            autoOpen: false,
            height: 300,
            width: 350,
            modal: true,
            buttons: {
                "取消": function () {
                    shipToHaolinjuStoreDialog.dialog("close");
                },
                "确认发货": function () {
                    confirmShipping(orderId);
                }
            },
            close: function () {
                sendWeixinMessageCheckBox.attr("checked", "checked");
                haolinjuCodeInput.val("");
            }
        });
        sendWeixinMessageCheckBox.on('click', function () {
            if ($(this).is(":checked")) {
                haolinjuCodeInput.removeAttr('disabled');
            }
            else {
                haolinjuCodeInput.attr('disabled', 'disabled');
            }
        });
        $(".ship-to-haolinju-store").click(function () {
            var orderId = $(this).parents('tr').data('order-id');
            haolinjuOrderIdInput.val(orderId);
            shipToHaolinjuStoreDialog.dialog("open");
        });
    }

    (function () {
        var shipTypeSelect = $('.ship-type-select');
        var shipCodeInput = $("input[name=order-ship-code]");
        var orderId = 0;
        var inputshipCode = function (orderId, type, code) {
            $.post('/manage/admin/tuan_orders/input_ordinary_ship_code', {
                orderId: orderId,
                shipType: type,
                shipCode: code
            }, function (data) {
                if (data.success) {
                    utils.alert(data.res);
                    inputShipCodeDialog.dialog("close");
                }
                else {
                    utils.alert('订单修改失败: ' + data.res);
                }
                $('.table-bordered tbody tr').remove('[data-order-id=' + orderId + ']');
            }, 'json')
        };
        var inputShipCodeDialog = $(".input-ship-code-dialog").dialog({
            autoOpen: false,
            height: 200,
            width: 350,
            modal: true,
            buttons: {
                "取消": function () {
                    inputShipCodeDialog.dialog("close");
                },
                "确认发货": function () {
                    var shipCode = shipCodeInput.val();
                    var shipType = shipTypeSelect.val();
                    if (shipCode.length > 0 && shipType != -1) {
                        inputshipCode(orderId, shipType, shipCode);
                    } else {
                        alert('请检查回填信息');
                    }
                }
            }
        });
        $(".input-ship-code").click(function () {
            orderId = $(this).parents('tr').data('order-id');
            inputShipCodeDialog.dialog("open");
        });
    })();
    activateTab($('.nav-tabs').data('query-type'));
    iUtils.initSelectBox($('.cart-status'));
    iUtils.initSelectBox($('.order-types'));
    setupByTuanTeamForm();
    setupHaolinjuStoreDialog();
    $('#check_all_tb').click(function (e) {
        var table = $(e.target).closest('table');
        var checked = $(this).is(':checked');
        $('.order input:checkbox', $('.orders')).each(function () {
            if (checked && !$(this).is(':disabled')) {
                $(this).attr('checked', 'checked');
            }
            else {
                $(this).removeAttr('checked');
            }
        })
    });
    function getCheckedOrderIds() {
        var $tb_ids = [];
        $.each($('.order input:checkbox:checked', $('.orders')), function (index, item) {
            $tb_ids.push($(this).val());
        });
        return $tb_ids;
    }

    function setCheckedOrderStatus(){
        $.each($('.order input:checkbox:checked', $('.orders')), function (index, item) {
           var that = $(this);
            that.parents('tr').children('td').eq(3).html('已发货');
        });
    }
    function setSuccessArrivedOrder(orderIds,type){
        $.each(orderIds,function(index,item){
           var that = $('.order input:checkbox[value='+item+']');
            console.log(that);
            that.parents('tr').children('td').eq(13).children('span').eq(type).removeClass('hidden');
        });
    }
    function initSetOrder(){
        var sendOutIds = sendOrderMsg.data('send_out').split(',');
        var reachIds = sendOrderMsg.data('reach').split(',');
        $.each(sendOutIds,function(index,item){
            var that = $('.order input:checkbox[value='+item+']');
            that.parents('tr').children('td').eq(13).children('span').eq(0).removeClass('hidden');
        });
        $.each(reachIds,function(index,item){
            var that = $('.order input:checkbox[value='+item+']');
            that.parents('tr').children('td').eq(13).children('span').eq(1).removeClass('hidden');
        });
    }
    var ourAddressSend = function () {
        var orderIds = getCheckedOrderIds();
        var val = $('input:radio[name="optionsRadios"]:checked').val();
        if (val == '1') {
            $.post('/manage/admin/tuan_orders/ship_to_pys_stores', {"ids": orderIds}, function (data) {
                if (data.success) {
                    var msg;
                    if (orderIds.length == (data.res.length + data.already.length)) {
                        msg = '订单状态修改成功，并全部发送了到达提醒';
                        setSuccessArrivedOrder(orderIds,1);
                    }
                    else {
                        msg = '订单状态修改成功，但有' + (orderIds.length - data.res.length - data.already.length) + '个未发送到达提醒';
                        var orderId = $.merge(data.res,data.already);
                        setSuccessArrivedOrder($.unique(orderId),1);
                    }
                    utils.alert(msg);
//                    location.reload();
                    setCheckedOrderStatus();
                    shipToOurStoreDialog.dialog('close');
                }
                else {
                    utils.alert(data.res);
                }
            }, 'json');
        } else {
            $.post('/manage/admin/tuan_orders/send_by_pys_stores', {"ids": orderIds}, function (data) {
                if (data.success) {
                    var msg;
                    if (orderIds.length == data.res.length) {
                        msg = '订单状态修改成功，并全部发送了发货提醒';
                        setSuccessArrivedOrder(orderIds,0);
                    }
                    else {
                        msg = '订单状态修改成功，但有' + data.fail.length + '个未发送发货提醒';
                        setSuccessArrivedOrder(data.res,0);
                    }
                    utils.alert(msg);
//                    location.reload();
                    setCheckedOrderStatus();
                    shipToOurStoreDialog.dialog('close');
                }
                else {
                    utils.alert(data.res);
                }
            }, 'json');
        }

    };
    var shipToOurStoreDialog = $(".ship-to-our-store-dialog").dialog({
        autoOpen: false,
        height: 300,
        width: 350,
        modal: true,
        buttons: {
            "取消": function() {shipToOurStoreDialog.dialog( "close" );},
            "确认": function(){ourAddressSend();}
        },
        close:function(){}
    });
    $('.ship-to-pys-stores').click(function () {
        var orderIds = getCheckedOrderIds();
        if (orderIds.length == 0) {
            utils.alert('请先选择自有自提点的待发货订单');
            return;
        }
        shipToOurStoreDialog.dialog("open");
    });

    function sendRefundOrderDialog() {
        var refundMoney = $('#refund_money');
        var refundOrder = $('#refund_order');
        var orderId = $('#order-id');
        var orderTotalPrice = $('#order-totalprice');
        var orderCreator = $('#order-creator');
        var orderScores = $('#refund_scores');
        var send_refund_message = function (order_id, refund_money, creator, refund_mark, total_price, order_status,order_scores) {
            $.getJSON('/manage/admin/orders/compute_refund_money', {'orderId': order_id}, function (data) {
                var res = data;
                if (order_status == 4) {
                    if (refund_money == '') {
                        refundMoney.focus().val('');
                        refundOrder.find('p.error-message').empty().append('退款金额不能为空哦');
                        return false;
                    } else if(total_price <res){
                        refundOrder.find('p.error-message').empty().append('已退款金额超过订单金额！');
                        return false;
                    }else if (refund_money > Math.round((total_price - res) * 100) / 100 || parseInt(refund_money) <= 0) {
                        refundMoney.focus().val('').val(refund_money);
                        refundOrder.find('p.error-message').empty().append('退款金额在0~' + Math.round((total_price - res) * 100) / 100 + '之间');
                        return false;
                    }
                }
                $.post('/manage/admin/tuan/update_order_status_to_refunded', {
                    'orderId': order_id,
                    'orderStatus': order_status
                }, function (data) {
                    var res = JSON.parse(data);
                    if (res.success) {
//                        if (order_status == 4) {
                            $.post('/manage/admin/orders/send_refund_notify', {
                                'orderId': order_id,
                                'refundMoney': refund_money,
                                'creator': creator,
                                'refundMark': refund_mark,
                                'orderStatus':order_status,
                                'orderScores':order_scores,
                                'orderTotalAllPrice':total_price
                            }, function (data) {
                                var result = JSON.parse(data);
                                if (result.success) {
                                    bootbox.alert(res.msg + ' ' + result.msg);
                                    //location.reload();
                                    if(order_status == 4){
                                        $currentOperateOrder.parents('tr').children('td').eq(3).html('已退款');
                                    }else{
                                        $currentOperateOrder.parents('tr').children('td').eq(3).html('退款中');
                                    }
                                    refundOrderDialog.dialog('close');
                                } else {
                                    bootbox.alert(result.msg);
                                }
                            });
//                        } else {
//                            bootbox.alert(res.msg);
////                            location.reload();
//                            refundOrderDialog.dialog('close');
//                        }
                    } else {
                        bootbox.alert(res.msg);
                    }
                });
            });
        };
        refundOrder.focusout(function () {
            $(this).find('p.error-message').empty();
        });
        var refundOrderDialog = $(".refund-order-dialog").dialog({
            autoOpen: false,
            height: 500,
            width: 600,
            modal: true,
            buttons: {
                "取消": function () {
                    refundOrderDialog.dialog("close");
                },
                "发送": function () {
                    var refund_money = $('#refund_money').val();
                    var refund_remark = $('#refund_remark').val();
                    var order_status = $('input[name=status]:checked', '#refund-form').val();
                    var order_scores = $('#refund_scores').val();
                    send_refund_message(orderId.val(), refund_money, orderCreator.val(), refund_remark, orderTotalPrice.val(), order_status,order_scores);
                }
            },
            close: function () {
                $('#refund_money').val('');
                $('#refund_remark').val('');
            }
        });
        $(".refund-button").click(function () {
            var $order = $(this).parents('tr.order');
            var refundOrderId = $order.data('order-id');
            var refundOrderPrice = $order.data('total-price');
            var href = '/manage/admin/orders/get_refund_log/'+ refundOrderId+'/'+refundOrderPrice;
            orderId.val(refundOrderId);
            orderTotalPrice.val(refundOrderPrice);
            orderCreator.val($order.data('order-creator'));
            orderScores.val($order.data('order-scores'));
            $currentOperateOrder = $(this);
            refundLog.attr('href',href);
            refundOrderDialog.dialog("open");
        });
    }

    sendRefundOrderDialog();
    $('.collapse').collapse();

    $exportBtn.on('click', function (e) {
        e.preventDefault();
        var tableIds = [];
        var tableNames = [];
        var ignoreRows = [0, 2, 3, 4, 5, 6, 7, 8, 9, 14, 18, 19, 20, 21, 22];
        $('table.orders').each(function (index, item) {
            tableIds.push($(item).attr('id'));
            tableNames.push($(item).data('table-name'));
        });
        tablesToExcel(tableIds, tableNames, 'order-export.xls', 'Excel', ignoreRows);
    });

    $('.toggle-orders').on('click', function(e){
        var showAll = $(this).data('show-all');
        if(showAll == 1){
            $(this).data('show-all', 0);
            $(this).text('只显示统计');
            $('.orders').removeClass("hidden");
            $('h3.ship-type').addClass("new-page");
            $('.table-collect-data').css('display', '');
        }
        else{
            $(this).data('show-all', 1);
            $(this).text('显示全部');
            $('.orders').addClass("hidden");
            $('h3.ship-type').removeClass("new-page");
            $('.table-collect-data').css('display', 'block');
        }
    });
    $('.print-orders').on('click', function(e){
        window.print();
    });
    initSetOrder();
});