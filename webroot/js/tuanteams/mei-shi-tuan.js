/**
 * Created by algdev on 15/4/9.
 */
$(function(){
    var $tipInfoPanel = $('<div class="comment_tip_layer radius10" style="width:60%; left:50%; top:30%; margin-left:-30%; display: none;"></div>');
    $('body').append($tipInfoPanel);
    var area = $('.conordertuan');
    var areaIds = $('.tuan-teams');
    var $join_tuan = $('a.alltuan_addbtn');
    var $area_id = $('#area_id',areaIds);
    var area_id = $area_id.val();

    $join_tuan.on('click',function(){
        var me = $(this);
        var tuan_id = me.data('tuan-id');
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: "/tuan_teams/join",
            data: {tuan_id: tuan_id},
            success: function (a) {
                if (a['success']) {
                    $tipInfoPanel.text('加入团队成功').fadeIn(300).fadeOut(1000,function(){
                        location.reload();
                    });
                } else {
                    if(a['type'] == 'error'){
                        utils.alert('请求错误，请重试');
                    }else if(a['type']=='not_login'){
                        window.location.href = '/users/login.html?referer=/tuan_teams/mei_shi_tuan.html';
                    }else{
                        $('#authorizeModal').modal('show');
                    }
                }
            }
        });
    });

    $('<ul class="clearfix"><li><a href="#X" class="county cur" data-county-id="all">全部</a></li></ul>').appendTo(area);
    $.each(tuanAreas,function(index,item){
        var areaId = item['id'];
        if($('div[data-county-id="'+areaId+'"]',areaIds).length>0){
            $('<ul class="clearfix"><li><a href="#X" class="county" data-county-id="'+item['id']+'">'+ item['name']+ '</a></li></ul>').appendTo(area);
        }
    });
    $('.county').on('click',function(){
        var countyId = $(this).data('county-id');
        if(countyId=='all'){
            $(".tuan-team").removeClass('hide');
        }else{
            $(".tuan-team").each(function(){
                $(this).toggleClass('hide', $(this).data("county-id") != countyId);
            });
        }
        $('.county').removeClass('cur');
        $(this).addClass('cur');
    });
    if(area_id&&area_id!='null'&&area_id.length>0){
        $('a[data-county-id="'+area_id+'"]').trigger('click');
    }
});

