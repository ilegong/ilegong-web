/**
 * Created by ldy on 15/3/25.
 */
var zitiAddress = function(){
    var beijingArea= {
        110101:"东城区",
        110108:"海淀区",
        110102:"西城区",
        110105:"朝阳区",
        110106:"丰台区",
        110114:"昌平区",
        110115:"大兴区"
    };
    //崇文并入东城区， 宣武并入西城区
    var ship_address = {};
    var area = [];
    $.getJSON('/tuan_buyings/get_offline_address',function(data){
        ship_address = data;
        for(var index in data){
            $("[area-id="+index+"]").show();
        }
    });
    var getShipAddress = function(areaId){
        return ship_address[areaId];
    };
    return {
        getBeijingAreas: beijingArea,
        getShipAddress: getShipAddress
    }
}();