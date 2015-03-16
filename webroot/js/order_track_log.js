$(document).ready(function(){
    var $track_date = $('#track_date');
    var $product_id = $('#product_id');
    var $order_data = $('#order-table-data');
    var $track_message_list = $('#track_message_list');
    var $track_message = $('#track_message');
    var $add_track_message = $('#add_track_message');
    if(!$track_date.attr('value')){
        $track_date.val(formatDate(new Date()));
    }
    function getOrder(){
        var date_val = $track_date.val();
        var p_id = $product_id.val();
        $.getJSON('/stores/get_product_orders_by_date.json',{'date':date_val,'product_id':p_id},function(data){
            var tbody = '';
            $.each(data,function(index,item){
                tbody +='<tr><td>'+item['id']+'</td><td>'+item['consignee_name']+'</td><td>'+item['consignee_mobilephone']+'</td><td>'+item['consignee_address']+'</td><td name="rm-row"><a>删除</a></td></tr>'
            });
            $order_data.html(tbody);
            $('td[name="rm-row"]').on('click',function(){
                $(this).closest('tr').remove();
            });
        });
    }
    $add_track_message.on('click',function(){
        var msg = $.trim($track_message.val());
        if(msg){
            var $item = $('<a href="#" class="list-group-item">'+msg+'<button type="button" class="btn btn-primary btn-xs pull-right">删除</button></a>');
            $('button',$item).on('click',function(){
                $(this).closest('a').remove();
            });
            $track_message_list.prepend($item);
            $track_message.val('');
        }
    });
    getOrder();
    $track_date.on('change',function(){
        //load order
        //console.log($track_date.val())
        getOrder();
    });
    function formatDate(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }
});