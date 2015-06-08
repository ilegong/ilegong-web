    /**
 * Created by algdev on 15/3/28.
 */
$(function(){
    var tuanName = $('#tuan_name');
    var leaderName = $('#leader_name');
    var leaderWX = $('#leader_weixin');
    var address =  $('#address');
    var tuanAddress = $('#tuan_addr');
    var longtitud = $('#location_long');
    var latitud  = $('#location_lat');
    var getPoint = $('#getPoint');
    var getCountyId = $('.tuan-teams');
    var offlineStoreBox = $('.offline-store');
    var submitCount = 0
    $.each(tuanAreas,function(index,item){
        $('<option value="'+item['id']+'">'+item['name']+'</option>').appendTo(getCountyId);
    });
    $.getJSON('/manage/admin/offline_stores/api_offline_stores',function(data){
        for(var category in data){
            var categoryName = category == 0 ? '好邻居' : '自有自提点';
            $('<optgroup label="--------' + categoryName + '"></optgroup>').appendTo(offlineStoreBox );
            for(var offlineStoreId in data[category]){
                $('<option value="' + offlineStoreId + '">' + data[category][offlineStoreId].name + '</option>').appendTo(offlineStoreBox);
            }
        }
        iUtils.initSelectBox(offlineStoreBox);
    });

    $(".tuanTeam-form").submit(function(e){
        if(submitCount!=0){
            alert("正在操作，请不要重复提交，谢谢！");
            return false;
        }
        submitCount = submitCount+1;
        var invalidTuanName = tuanName.val()=='';
        tuanName.parents('.form-group').toggleClass('has-error',invalidTuanName);
        var invalidLeaderName = leaderName.val()=='';
        leaderName.parents('.form-group').toggleClass('has-error',invalidLeaderName);
        var invalidWX = leaderWX.val()=='';
        leaderWX.parents('.form-group').toggleClass('has-error',invalidWX);
//        var invalidAddress = address.val()=='';
//        address.parents('.form-group').toggleClass('has-error',invalidAddress);
//        var invalidTuanAddress = tuanAddress.val()=='';
//        tuanAddress.parents('.form-group').toggleClass('has-error',invalidTuanAddress);
//        var invalidLongtitud = isNaN(Number(longtitud.val()));
//        longtitud.parents('.form-group').toggleClass('has-error',invalidLongtitud);
//        var invalidLatitud = isNaN(Number(latitud.val()));
//        latitud.parents('.form-group').toggleClass('has-error',invalidLatitud);

        if(invalidTuanName || invalidLeaderName || invalidWX){
            return false;
        }
        return true;
    });

    getPoint.click(function(){
        var tuanAddress = $('#tuan_addr').val();
        var location_lng = $('#location_long');
        var location_lat = $('#location_lat');
        var BaiDuPointUrl = 'http://api.map.baidu.com/geocoder/v2/?address='+ tuanAddress +'&output=json&ak=A1LHIfSqj7LRrmcbIBHmK7fC';
        $.ajax({url:BaiDuPointUrl,type:'get',dataType:'JSONP',success:function(data){
            if(data.status==0){
                location_lng.val(data['result']['location']['lng']);
                location_lat.val(data['result']['location']['lat'])
            }
        }
    });
    });
});