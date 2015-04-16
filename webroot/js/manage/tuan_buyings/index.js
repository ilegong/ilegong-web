$(function(){
    var tuanTeams = $('.tuan-teams');
    var tuanProducts = $('.tuan-products');

    var tuanStatus=$('.tuan-status');
    var tuanType = $('.tuan-type');
    var leftSelectData = [];
    var tuan_name = $('#tuan_name');

    var $check_all_tb = $('#check_all_tb');

    var $batch_complete = $('#batch_complete');

    var $batch_cancel = $('#batch_cancel');


    $check_all_tb.click(function(e){
        var table= $(e.target).closest('table');
        $('td input:checkbox',table).prop('checked',this.checked);
    });



    function setTuanStatus(){
        var tuanBuyingsForm = $('.tuan-buyings-form');
        var statusType = tuanBuyingsForm.data('status-type');
        var tStatus = tuanBuyingsForm.data('tuan-status');
        var tType = tuanBuyingsForm.data('tuan-type');
        $("option",tuanType).each(function(){
            if($(this).val() == tType){
                $(this).attr('selected', 'selected');
            }
            else{
                $(this).removeAttr('selected');
            }
        });
        $("option", tuanStatus).each(function(){
            if($(this).val() == tStatus){
              $(this).attr('selected', 'selected');
            }
            else{
              $(this).removeAttr('selected');
            }
        });
        $(".status-type").each(function(){
          if($(this).val() == statusType){
            $(this).attr('checked', 'checked');
          }
          else{
            $(this).removeAttr('checked');
          }
        });
    }
    $.getJSON('/manage/admin/tuanTeams/api_tuan_teams',function(data){
        $.each(data,function(index,item){
            $('<option value="'+item['id']+'">'+item['tuan_name']+'</option>').appendTo(tuanTeams);
        });
        search_tuanteam();
        tuanTeams.val(tuanTeams.attr('data-team-id'));
    });

    $.getJSON('/manage/admin/tuanProducts/api_tuan_products',function(data){
        tuanProducts.data('tuan-products', data);
        $.each(data,function(index,item){
            var tuan_product = item['TuanProduct'];
            $('<option value="' + tuan_product['product_id'] + '">' + tuan_product['alias'] + '</option>').appendTo(tuanProducts);
        });
        tuanProducts.val(tuanProducts.attr('data-product-id'));
    });
    setTuanStatus();

    var tuanBuyingForm = $('.tuan-buying-form');
    var tuanBuyingDue= $('.tuan-buying-due');
    var tuanBuyingCanceled= $('.tuan-buying-canceled');
    var tuanBuyingFinished = $('.tuan-buying-finished');
    var tuanBuyingRefunded = $('.tuan-buying-refunded');
    var tuanBuyingSendmsg = $('.tuan-buying-sendmsg');
    var tuanbuyingcancelmsg = $('.tuan-buying-cancelmsg');

    var tuanbuyingcompletemsg = $('.tuan-buying-completemsg');
    var tuanbuyingtipmsg = $('.tuan-buying-tipmsg');
    var tuanbuyingdelaymsg = $('.tuan-buying-delayemsg');
    var tuanBuyingStartDeliver = $('.tuanbuying-start-deliver');
    var tuanBuyingNotifyDeliver = $('.tuanbuying-notify-deliver');

    tuanBuyingDue.click(function(){
        var tuanBuyingId = $(this).parents('tr').data('id');
            bootbox.confirm('确定结束团购吗？',function(e){
                if(e){
                    $.post( "/manage/admin/tuanBuyings/api_tuan_buying_due", {id: tuanBuyingId}, function( data ) {
                        console.log('已设置为：团购截止！');
                    }).fail(function(){
                        console.log('设置团购截止失败！');
                    }).always(function(){
                        window.location.reload();
                    });
                }else{
                    return;
                }
            });
    });
    tuanBuyingCanceled.click(function(){
        var tuanBuyingId = $(this).parents('tr').data('id');
            bootbox.confirm('确定取消团购吗？',function(e){
                if(e){
                    $.post( "/manage/admin/tuanBuyings/api_tuan_buying_canceled", {id: tuanBuyingId}, function( data ) {
                        console.log('已设置为：团购取消！');
                    }).fail(function(){
                        console.log('设置团购取消失败！');
                    }).always(function(){
                        window.location.reload();
                    });
                }else{
                    return;
                }
            });
    });
    tuanBuyingFinished.click(function(){
        var tuanBuyingId = $(this).parents('tr').data('id');
            bootbox.confirm('确定发货完成了吗？',function(e){
                if(e){
                    $.post( "/manage/admin/tuanBuyings/api_tuan_buying_finished", {id: tuanBuyingId}, function( data ) {
                        console.log('已设置为：发货完成！');
                    }).fail(function(){
                        console.log('设置发货完成失败！');
                    }).always(function(){
                        window.location.reload();
                    });
                }else{
                    return;
                }
            });
    });
    tuanBuyingRefunded.click(function(){
        var tuanBuyingId = $(this).parents('tr').data('id');
            bootbox.confirm('确定完成退款了吗？',function(e){
                if(e){
                    $.post( "/manage/admin/tuanBuyings/api_tuan_buying_refunded", {id: tuanBuyingId}, function( data ) {
                        console.log('已设置为：退款完成！');
                    }).fail(function(){
                        console.log('设置退款完成失败！');
                    }).always(function(){
                        window.location.reload();
                    });
                }else{
                    return;
                }
            })
    });

    tuanBuyingSendmsg.on('click',function(e){
        var tuanBuyingId = $(this).data('id');
            bootbox.confirm('静哥,确定要发送建团模板消息吗?',function(e){
                if(e){
                    $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_create_msg',{'tuan_buy_id':tuanBuyingId},function(data){
                        bootbox.alert(data['msg'],function(e){
                            window.location.reload();
                        });
                    });
                }else{
                    return;
                }
            })
    });
    tuanbuyingcancelmsg.on('click',function(e){
        var tuanBuyingId = $(this).data('id');
            bootbox.confirm('静哥,确定要发送取消团购模板消息吗?',function(e){
                if(e){
                   $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_fail_msg',{'tuan_buy_id':tuanBuyingId},function(data){
                        bootbox.alert(data['msg'],function(e){
                            window.location.reload();
                        });
                   });
                }else{
                  return;
                }
            });
    });

    tuanbuyingcompletemsg.on('click',function(e){
        var tuanStatus = $(this).attr('data-tuanBuying-id');
        var tuanBuyingId = $(this).data('id');
        if(tuanStatus == 1){
            bootbox.confirm('静哥,确定要发送团购完成模板消息吗?',function(e){
                if(e){
                    $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_complete_msg',{'tuan_buy_id':tuanBuyingId},function(data){
                        bootbox.alert(data.msg,function(e){
                            window.location.reload();
                        });
                    });
                }else{
                    return;
                }
            });
        }else{
            alert('只有团购截止后，才能发送团购完成模版消息');
        }
    });

    tuanbuyingtipmsg.on('click',function(e){
        var tuanBuyingId = $(this).data('id');
            bootbox.confirm('静哥,确定要发送团购提示模板消息吗?',function(e){
                if(e){
                    $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_tip_by_id_msg',{'tuan_buy_id':tuanBuyingId},function(data){
                        bootbox.alert(data.msg,function(e){
                            window.location.reload();
                        });
                    });
                }else{
                    return;
                }
            });
    });
    tuanBuyingStartDeliver.on('click',function(){
        var tuanBuyingId = $(this).data('id');
        var tuanProduct = $(this).data('product-name');
            bootbox.prompt({
                title:'请输入配送模版消息',
                value:'亲，您在***团购的'+tuanProduct+'已经在路上了啦，请注意收货',
                callback:function(msg){
                    if(msg!=null){
                      var tuan_msg = msg;
                      $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_start_deliver_msg',{'tuan_buying_id':tuanBuyingId,'deliver_msg':tuan_msg},function(data){
                         bootbox.alert(data.msg,function(e){
                              window.location.reload();
                         });
                      });
                  }else{
                      return;
                     }
                  }
            });
    });
    tuanBuyingNotifyDeliver.on('click',function(){
       var tuanBuyingId = $(this).data('id');
       var tuanProduct = $(this).data('product-name');
       var tuanTeamAddress = $(this).data('tuanteam-address');
            bootbox.prompt({
                title:'请输入到货通知模版消息',
                value:'亲，您在***团购的'+tuanProduct+'已经为您送到'+tuanTeamAddress+'，请注意收货',
                callback:function(msg){
                    if(msg!=null){
                        var tuan_msg = msg;
                        $.getJSON('/manage/admin/tuan_msg/send_tuan_buy_notify_deliver_msg',{'tuan_buying_id':tuanBuyingId,'deliver_msg':tuan_msg},function(data){
                            bootbox.alert(data.msg,function(e){
                                window.location.reload();
                            });
                        });
                    }else{
                        return;
                    }
                }
            });
    });

    var addPoints = function(leaderId, tuanBuyingId, points, reason){
      $.post('/manage/admin/users/do_add_score',{"user_id":leaderId, "score": points, "score_reason":reason, "tuan_buy_ids":tuanBuyingId},function(data){
        var result = JSON.parse(data);
        if(result['success']){
          alert(result['msg']);
        }else{
          alert(result['msg']);
        }
      });
    }
    $('.add-points').on('click',function(e){
      var tuanBuying = $(this).parents('tr');
      var leaderName = tuanBuying.data('leader-name');
      var leaderId = tuanBuying.data('leader-id');
      var tuanBuyingId = tuanBuying.data('id');
      var dialogMessage = '<div class="form-horizontal" method="post">' +
        '  <div class="form-group">' +
        '      <label for="box-tuan-buying-id" class="col-sm-2 control-label">团购ID</label>' +
        '      <div class="col-sm-10">' +
        '          <input id="box-tuan-buying-id" class="form-control" value="' + tuanBuyingId + '" readonly="readonly">' +
        '      </div>' +
        '  </div>' +
        '  <div class="form-group">' +
        '      <label for="box-user-id" class="col-sm-2 control-label">团长</label>' +
        '      <div class="col-sm-10">' +
        '          <input class="form-control" value="' + leaderName + '" disabled="disabled">' +
        '      </div>' +
        '  </div>' +
        '  <div class="form-group">' +
        '      <label for="box-leader-id" class="col-sm-2 control-label">团长ID</label>' +
        '      <div class="col-sm-10">' +
        '          <input type="number" class="form-control" id="box-leader-id" placeholder="' + leaderId + '" value="' + leaderId + '">' +
        '      </div>' +
        '  </div>' +
        '  <div class="form-group">' +
        '      <label for="box-points" class="col-sm-2 control-label">积分</label>' +
        '      <div class="col-sm-10">' +
        '          <input type="number" class="form-control" id="box-points" placeholder="要加的积分">' +
        '      </div>' +
        '  </div>' +
        '  <div class="form-group">' +
        '      <label for="box-reason" class="col-sm-2 control-label">添加原因</label>' +
        '      <div class="col-sm-10">' +
        '          <textarea id="box-reason" class="form-control" rows="3"></textarea>' +
        '      </div>' +
        '  </div>'+
        '</div>';
      bootbox.dialog({
          title: "团长积分",
          message: dialogMessage,
          buttons: {
            cancel: {
              label: "取消",
              className: "btn btn-default",
              callback: function () {}
            },
            success: {
              label: "添加",
              className: "btn btn-danger",
              callback: function () {
                var leaderId = $('#box-leader-id').val();
                var points = $('#box-points').val();
                var tuanBuyingId = $('#box-tuan-buying-id').val();
                var reason = $('#box-reason').val();
                addPoints(leaderId, tuanBuyingId, points, reason);
              }
            }
          }
        }
      );
    });

//    $('#tuan_down,#tuan_product_down').click(function(){
//        var id = $(this).attr('data-id');
//        var val=$(this).attr("value");
//        if(confirm('确定编辑吗？')){
//            if($(this).attr('id')== 'tuan_product_down'){
//                confirm('团购取消后请及时退款哦');
//            }
//            var data={"id":id,"val":val};
//            $.ajax({
//                type:'post',
//                success:function(data){
//                    window.location.reload();
//                    alert('状态修改成功');
//                },
//                error:function(e){alert(e);},
//                url:"{{$this->Html->url(array('controller'=>'tuan','action'=>'admin_tuan_buying_set'))}}",
//                data:data,
//                dataType:'json'
//            })
//        }
//        else{
//            return false;
//        }
//    });

    function search_tuanteam(){
        String.prototype.Trim = function() {
            return this.replace(/(^\s*)|(\s*$)/g, "");
           };
        $("select[name='team_id'] option").each(function(){
            leftSelectData.push({'val':$(this).val(),'name':$(this).text()});
        });
        if(navigator.userAgent.indexOf("MSIE")>0){
            tuan_name.on('onpropertychange',txChange);
        }else{
            tuan_name.on('input',txChange);
        }
    }
    function txChange(){
        var content= tuan_name.val().Trim();
        tuanTeams.empty();
        if(content == ''){
            $.each(leftSelectData,function(index,value){
                tuanTeams.append('<option value="'+value['val']+'">'+value['name']+'</option>');
            });
        }else{
            var reg = new RegExp(content,'i');
            $.each(leftSelectData,function(index,val){
                if(reg.test(val['name'])){
                    tuanTeams.append('<option selected="selected" value="'+val['val']+'">'+val['name']+'</option>');
                }
            })
        }
    }

});