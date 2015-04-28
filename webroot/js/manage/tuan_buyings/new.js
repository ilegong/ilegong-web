$(function(){
    $('.form_datetime').datetimepicker({
        language:  'zh-CN',
        weekStart: 1,
        todayBtn:  1,
        autoclose: 1,
        todayHighlight: 1,
        startView: 2,
        forceParse: 0,
        showMeridian: 1
    });
    var tuanTeams = $('.tuan-teams');
    var leftSelectData = [];
    var tuan_name = $('#tuan_name');
    var team_ids = $('#team_ids');
    $.getJSON('/manage/admin/tuanTeams/api_tuan_teams',function(data){
        $.each(data,function(teamId, item){
          var tuanTeam = item['TuanTeam'];
          var ele = $('<option value="' + teamId + '">' + tuanTeam['tuan_name']+'</option>');
          ele.appendTo(tuanTeams);
        });
        search_tuanteam();
        tuanTeams.val(tuanTeams.attr('data-team-id'));
    });
    var tuanProducts = $('.tuan-products');
    $.getJSON('/manage/admin/tuanProducts/api_tuan_products',function(data){
        $.each(data,function(index,item){
            var tuan_product = item['TuanProduct'];
            $('<option value="' + tuan_product['product_id'] + '">' + tuan_product['alias'] + '</option>').appendTo(tuanProducts);
        });
    });
    var tuanTeamList = $('.tuan-teams-list');
    $.getJSON('/manage/admin/tuanTeams/api_tuan_teams',function(data){
        $.each(tuanAreas,function(Index,Item){
            var tuan_area =  $('<p class="tuan-area"  value="'+ Item['id']+'"><strong>'+ Item['name']+'</strong></p>');
            tuan_area.appendTo(tuanTeamList).css('color','red').hide();
            $.each(data,function(teamId, item){
                var tuanTeam = item['TuanTeam'];
                if(tuanTeam['county_id'] == Item['id']){
                    $('<input type="checkbox" class="tuan-team" data-id="' + tuanTeam['county_id'] + '" value="' + teamId + '"name="team_id">'+ tuanTeam['tuan_name'] + '</input>').appendTo(tuanTeamList);
                    tuan_area.show();
                }
            });
        });
    });
    var tuanEndTime = $('.tuan-end-time');
    var tuanTargetNum = $('.tuan-target-num');
    var consignmentType = $('.consignment-type');
    var consignmentTime = $('.consignment-time');
    $(".tuan-buying-form").submit(function(e){
        var tuanTeamId = new Array();
        $(".tuan-team[type='checkbox']:checked").each(function(){
            tuanTeamId.push($(this).val());
        });
        team_ids.val(tuanTeamId.join(','));
        var invalidTuanProduct = tuanProducts.val() == -1;
        tuanProducts.parents('.form-group').toggleClass('has-error', invalidTuanProduct);
        var invalidTuanEndTime = tuanEndTime.val() == '';
        tuanEndTime.parents('.form-group').toggleClass('has-error', invalidTuanEndTime);
        var targetNum = Number(tuanTargetNum.val());
        var invalidTargetNum = isNaN(targetNum) || targetNum < 1;
        tuanTargetNum.parents('.form-group').toggleClass('has-error', invalidTargetNum);
        var invalidConsignmentTime = (consignmentType.val() == 0 || consignmentType.val() == 1) && consignmentTime.val() == '';
        consignmentTime.parents('.form-group').toggleClass('has-error', invalidConsignmentTime);

        if(invalidTuanProduct || invalidTuanEndTime || invalidTargetNum || invalidConsignmentTime){
            return false;
        }
        return true;
    });

    function search_tuanteam(){

        String.prototype.Trim = function() {
            return this.replace(/(^\s*)|(\s*$)/g, "");
        };

        $("select[name='data[TuanBuying][tuan_id]'] option").each(function(){
            leftSelectData.push({'val':$(this).val(),'name':$(this).text()});
        });
        if(navigator.userAgent.indexOf("MSIE")>0){
            tuan_name.on('onpropertychange',txChange);
        }else{
            tuan_name.on('change',txChange);
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
})