$(function(){
    var tuanTeams = $('.tuan-teams');
    $.getJSON('/manage/admin/tuan/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
    });
    var tuanProducts = $('.tuan-products');
    $.getJSON('/manage/admin/tuan/api_tuan_products',function(data){
        tuanProducts.data('tuan-products', data);
        $.each(data,function(index,item){
            $('<option value="' + index + '">' + item + '</option>').appendTo(tuanProducts);
        });
    });

    $('#tuan_down,#tuan_product_down').click(function(){
        var id = $(this).attr('data-id');
        var val=$(this).attr("value");

        if(confirm('确定编辑吗？')){
            if($(this).attr('id')== 'tuan_product_down'){
                confirm('团购取消后请及时退款哦');
            }
            var data={"id":id,"val":val};
            $.ajax({
                type:'post',
                success:function(data){
                    window.location.reload();
                    alert('状态修改成功');
                },
                error:function(e){alert(e);},
                url:"{{$this->Html->url(array('controller'=>'tuan','action'=>'admin_tuan_buying_set'))}}",
                data:data,
                dataType:'json'
            })
        }
        else{
            return false;
        }
    });
});