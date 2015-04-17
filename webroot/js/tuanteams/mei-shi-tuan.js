/**
 * Created by algdev on 15/4/9.
 */
$(function(){
    var $tipInfoPanel = $('<div class="comment_tip_layer radius10" style="width:60%; left:50%; top:30%; margin-left:-30%; display: none;"></div>');
    $('body').append($tipInfoPanel);
    var area = $('.conordertuan');
    var areaIds = $('.tuan-teams');
    var $join_tuan = $('a.alltuan_addbtn');
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
                    me.removeClass('alltuan_addbtn').addClass('alltuan_curbtn').text('已经加入');
                    var $member_num  = $('span[name="member_num"]',me.parent());
                    $member_num.text(parseInt($member_num.text())+1);
                    $tipInfoPanel.text('加入团队成功').fadeIn(1000).fadeOut(2000);
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

    $.getJSON('/tuan_teams/api_getArea',function(data){
        $.each(data,function(index,item){
            var areaId = item['id'];
            var isArea = areaIds.find('[data-county-id="'+areaId+'"]');
            if(isArea.length){
            $('<ul class="clearfix"><li><a href="#X" class="county" data-county-id="'+item['id']+'">'+ item['name']+ '区</a></li></ul>').appendTo(area);
            }
        });
        $('.county').on('click',function(){
            var countyId = $(this).data('county-id');
            $(".tuan-team").each(function(){
                $(this).toggleClass('hide', $(this).data("county-id") != countyId);
            });
            $('.county').removeClass('cur');
            $(this).addClass('cur');
        });
    });

});

