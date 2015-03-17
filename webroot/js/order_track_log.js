$(document).ready(function(){
    var $track_date = $('#track_date');
    var $product_id = $('#product_id');
    var $order_data = $('#order-table-data');
    var $track_message_list = $('#track_message_list');
    var $track_message = $('#track_message');
    var $add_track_message = $('#add_track_message');
    var $add_order = $('#add_order');
    var $order_ids = $('#order_ids');
    var $track_id = $('#track_id');
    var $select_date = $('#select_date');
    var postLogs = [];
    var orderIds = [];
    if(!$track_date.attr('value')){
        $track_date.val(formatDate(new Date()));
    }
    function loadOrder(url,param){
        $.getJSON(url,param,function(data){
            var tbody = '';
            $.each(data,function(index,item){
                tbody +='<tr><td name="order-id">'+item['id']+'</td><td>'+item['consignee_name']+'</td><td>'+item['consignee_mobilephone']+'</td><td>'+item['consignee_address']+'</td><td name="rm-row"><a class="btn btn-primary btn-xs pull-right">删除</a></td></tr>'
            });
            $order_data.prepend($(tbody));
            $('td[name="rm-row"]').on('click',function(){
                $(this).closest('tr').remove();
            });
        });
    }
    //todo 排期功能上了之后  动态加载
    function getOrder(){
        var date_val = $select_date.val();
        var p_id = $product_id.val();
        var url = '/stores/get_product_orders_by_date.json';
        var param = {'date_id':date_val,'product_id':p_id};
        loadOrder(url,param);
    }

    $select_date.on('change',function(){
        var thisvalue = $select_date.find("option:selected").text();
        $track_date.val(thisvalue);
        getOrder();
    });

    $add_track_message.on('click',function(){
        var msg = $.trim($track_message.val());
        if(msg){
            var $item = $('<a href="#" class="list-group-item"><span>'+msg+'</span><button type="button" class="btn btn-primary btn-xs pull-right">删除</button></a>');
            $('button',$item).on('click',function(){
                $(this).closest('a').remove();
            });
            $track_message_list.prepend($item);
            $track_message.val('');
        }
    });

    $('td[name="rm-row-remote"]').on('click',function(){
        var me = $(this);
        var id = me.data('id');
        var track_id = $track_id.val();
        var url = '/stores/delete_order_track_map';
        var params = {
            'order_id':id,
            'track_id':track_id
        };
        $.getJSON(url,params,function(data){
            if(data['success']){
                me.closest('tr').remove()
            }else{
                alert('删除失败');
            }
        });
    });

    $add_order.on('click',function(){
        var ids = $.trim($order_ids.val());
        var url = '/stores/get_order_by_ids';
        var param = {'ids':ids};
        loadOrder(url,param);
    });

    //todo 排期功能上了之后改造
    //getOrder();
    //$track_date.on('change',function(){
    //    //load order
    //    //console.log($track_date.val())
    //    getOrder();
    //});
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    function getPostOrderIds(){
        //回显回来的数据 不选择也 不能删除
        var $tds = $('td[name="order-id"]');
        $.each($tds,function(index,item){
            orderIds.push($.trim($(item).text()));
        });
        $('#post_order_ids').val(JSON.stringify(orderIds));

    }
    function getPostLogs(){
        var $log_items = $('a.list-group-item:has(button)');
        $.each($log_items,function(index,item){
            var log = $.trim($('span',$(item)).text());
            postLogs.push(log);
        });
        $('#post_logs').val(JSON.stringify(postLogs));
    }

    if($select_date){
        getOrder();
    }

    $('#post_form').on('submit',function(){
        //set data
        getPostOrderIds();
        getPostLogs();
    });
});