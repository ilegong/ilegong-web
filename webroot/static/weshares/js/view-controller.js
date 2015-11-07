(function (window, angular) {

  angular.module('weshares')
    .controller('WesharesViewCtrl', WesharesViewCtrl);


  function ChooseOfflineStore($vm, $log, $http, $templateCache, $timeout) {
    $vm.areas = {
      110101: {
        'name': "东城区"
      },
      110108: {
        'name': "海淀区"
      },
      110102: {
        'name': "西城区"
      },
      110105: {
        'name': "朝阳区"
      },
      110106: {
        'name': "丰台区"
      },
      110114: {
        'name': "昌平区"
      },
      110113: {
        'name': "顺义区"
      },
      110115: {
        'name': "大兴区"
      },
      110112: {
        'name': "通州区"
      }
    };
    $vm.currentAreaCode = '110101';
    //$http({
    //  method: 'GET',
    //  url: '/tuan_buyings/get_offline_address.json?type=-1',
    //  cache: $templateCache
    //}).success(function (data) {
    //  $vm.offlineStores = data['address'];
    //});
    $vm.offlineStores = {"110108":{"1":{"id":"1","shop_no":"843","area_id":"110108","alias":"\u4e0a\u5730\u9e4f\u5bf0\u5927\u53a6\u767e\u5ea6\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u4e0a\u5730\u4e1c\u8def\u4e00\u53f7\u9662\u9e4f\u5bf0\u5927\u53a6\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(843)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.308402","location_lat":"40.061414","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"8":{"id":"8","shop_no":"263","area_id":"110108","alias":"\u7545\u6625\u56ed\u7f8e\u98df\u8857\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u897f\u82d1\u8349\u573a5\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(263)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.3113","location_lat":"39.995205","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"16":{"id":"16","shop_no":"125","area_id":"110108","alias":"\u5317\u4e09\u73af\u653e\u5149\u793e\u533a\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u5317\u4e09\u73af\u897f\u8def60\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(125)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.326494","location_lat":"39.971958","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"17":{"id":"17","shop_no":"183","area_id":"110108","alias":"\u5382\u6d3c\u6b66\u8b66\u603b\u90e8\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u5382\u6d3c\u5c0f\u533a24\u53f7\u697c\u5317\u4eac\u7535\u89c6\u53f0\u897f\u95e8\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(183)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.313109","location_lat":"39.964403","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"18":{"id":"18","shop_no":"351","area_id":"110108","alias":"\u677f\u4e95\u8def\u603b\u90e8\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u8f66\u9053\u6c9f\u6865\u8fdb\u5165\u677f\u4e95\u8def\u5355\u884c\u8def\u76f4\u884c300\u7c73\u8def\u5317\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(351)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.295582","location_lat":"39.955243","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"19":{"id":"19","shop_no":"350","area_id":"110108","alias":"\u5927\u949f\u5bfa\u4e1c\u8def\u4eac\u4eea\u5927\u53a6\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u5927\u949f\u5bfa\u4e1c\u8def\u4eac\u4eea\u5927\u53a6\u5e95\u5546\u6d77\u6dc0\u533a\u5927\u949f\u5bfa\u4e1c\u8def9\u53f71\u5e621\u5c42101-1\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(350)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.344811","location_lat":"39.977391","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"20":{"id":"20","shop_no":"169","area_id":"110108","alias":"\u79d1\u5357\u4e2d\u5173\u6751\u4e2d\u5b66\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u79d1\u5b66\u9662\u5357\u8def55\u53f7 \u4e2d\u5173\u6751\u4e2d\u5b66\u6b63\u5bf9\u9762\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(169)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.331498","location_lat":"39.984315","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"21":{"id":"21","shop_no":"561","area_id":"110108","alias":"\u4e07\u6cc9\u6cb3\u597d\u90bb\u5c45","name":"\u6d77\u6dc0\u533a\uff0c\u4e07\u6cc9\u6cb3\u8def68\u53f7\u7d2b\u91d1\u5927\u53a61\u5c42\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(561)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.314524","location_lat":"39.972615","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"22":{"id":"22","shop_no":"147","area_id":"110108","alias":"\u7f8a\u574a\u8def\u4eac\u897f\u5bbe\u9986\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u7f8a\u574a\u5e97\u8def3\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(147)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.327509","location_lat":"39.911252","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"23":{"id":"23","shop_no":"199","area_id":"110108","alias":"\u6c38\u5b9a\u8def\u822a\u5929\u5de5\u4e1a\u90e8\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u6c38\u5b9a\u8def63\u53f7(\u6b66\u8b66\u603b\u533b\u9662\u5317200\u7c73)\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(199)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.270987","location_lat":"39.918917","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"24":{"id":"24","shop_no":"379","area_id":"110108","alias":"\u79d1\u5357\u8def\u641c\u72d0\u5927\u53a6\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u4e2d\u5173\u6751\u65b0\u79d1\u7965\u56ed\u75322\u53f7\u697c1\u5c4203\u5ba4\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(379)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.33112","location_lat":"39.989367","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"28":{"id":"28","shop_no":"0","area_id":"110108","alias":"\u4e0a\u5965\u4e16\u7eaaB\u5ea7","name":"\u6d77\u6dc0\u533a\uff0c\u897f\u4e09\u65d7\u6865\u4e1c\uff0c\u4e0a\u5965\u4e16\u7eaaB\u5ea71612","type":"1","owner_name":"","owner_phone":"","location_long":"116.336402","location_lat":"40.06276","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"43":{"id":"43","shop_no":"296","area_id":"110108","alias":"\u897f\u76f4\u95e8\u597d\u90bb\u5c45","name":"\u6d77\u6dc0\u533a\uff0c\u897f\u76f4\u95e8\u5317\u5927\u885747\u53f7\u96622\u53f7\u697c\u5317\u4fa7\u4e00\u5c42\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(296)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.362382","location_lat":"39.953238","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"75":{"id":"75","shop_no":"137","area_id":"110108","alias":"\u589e\u5149\u8def\u7d2b\u7389\u5927\u53a6\u5e97","name":"\u6d77\u6dc0\u533a\uff0c\u589e\u5149\u8def\u4e5948\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(137)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.31828","location_lat":"39.933911","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"258":{"id":"258","shop_no":"147","area_id":"110108","alias":"\u597d\u90bb\u5c45\u7f8a\u574a\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u7f8a\u574a\u5e97\u8def3\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.91126","location_lat":"116.32751","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"264":{"id":"264","shop_no":"393","area_id":"110108","alias":"\u5e7f\u6e90\u5927\u53a6\u5e97","name":"\u6d77\u6dc0\u533a\u5e7f\u6e90\u95f8\u8def5-1\u53f7\u5e7f\u6e90\u5927\u53a6","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.949622","location_lat":"116.317985","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"266":{"id":"266","shop_no":"136","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5927\u6167\u5bfa\u5e97","name":"\u6d77\u6dc0\u533a\u9b4f\u516c\u6751\u5927\u6167\u5bfa\u8def5\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.957206","location_lat":"116.332228","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"267":{"id":"267","shop_no":"395","area_id":"110108","alias":"\u6587\u6167\u56ed\u5317\u8def\u5206\u5e97","name":"\u6d77\u6dc0\u533a\u7ea2\u8054\u5317\u67513\u53f7\u697c\u5e95\u5546","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.961113","location_lat":"116.367708","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"268":{"id":"268","shop_no":"135","area_id":"110108","alias":"\u597d\u90bb\u5c45\u961c\u6210\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u767d\u5806\u5b50\u7acb\u65b09\u53f7\u697c\u524d","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.929375","location_lat":"116.333818","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"270":{"id":"270","shop_no":"836","area_id":"110108","alias":"\u4e2d\u5173\u6751\u533b\u9662\u5e97","name":"\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u7532943\u53f7\u697c8-3","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.986589","location_lat":"116.328315","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"272":{"id":"272","shop_no":"137","area_id":"110108","alias":"\u597d\u90bb\u5c45\u589e\u5149\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u589e\u5149\u8def\u4e5948\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.933911","location_lat":"116.31828","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"274":{"id":"274","shop_no":"847","area_id":"110108","alias":"\u4e0a\u5730\u897f\u8def\u5b8f\u8fbe\u82b1\u56ed\u5e7f\u573a\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u4e0a\u5730\u897f\u8def\u534e\u8054\u4e1c\u5317\u65fa\u519c\u573a\u5f00\u53d1\u5efa\u8bbe\u9879\u76ee\u7efc\u5408\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.036138","location_lat":"116.316936","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"276":{"id":"276","shop_no":"834","area_id":"110108","alias":"\u5b66\u9662\u5357\u8def\u90ae\u7535\u5927\u5b66\u5e97","name":"\u6d77\u6dc0\u533a\u5b66\u9662\u5357\u8def10\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.963968","location_lat":"116.363862","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"281":{"id":"281","shop_no":"849","area_id":"110108","alias":"\u7f8a\u574a\u5e97\u897f\u8def\u4ec0\u574a\u9662\u5e97","name":"\u6d77\u6dc0\u533a\u7f8a\u574a\u5e97\u897f\u8def\u4ec0\u574a\u9662\u4e00\u53f7\u96626\u53f7\u697c\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.903697","location_lat":"116.323079","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"283":{"id":"283","shop_no":"846","area_id":"110108","alias":"\u6731\u623f\u8def\u6e05\u6cb3\u6d3e\u51fa\u6240\u5e97","name":"\u6d77\u6dc0\u533a\u6e05\u6cb3\u6731\u623f\u8def\u4e3466\u53f7\u697c3\u680b10\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.033362","location_lat":"116.339848","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"286":{"id":"286","shop_no":"810","area_id":"110108","alias":"\u590d\u5174\u8def\u519b\u535a\u5e97","name":"\u6d77\u6dc0\u533a\u590d\u5174\u8def12\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.912311","location_lat":"116.330608","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"288":{"id":"288","shop_no":"811","area_id":"110108","alias":"\u4ea4\u5927\u5609\u56ed\u5e97","name":"\u6d77\u6dc0\u533a\u4ea4\u901a\u5927\u5b66\u8def1\u53f7\u9662\u4ea4\u5927\u5609\u56ed4\u53f7\u5e95\u5546","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.955388","location_lat":"116.352506","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"291":{"id":"291","shop_no":"813","area_id":"110108","alias":"\u5b66\u6e05\u8def\u5927\u534e\u52a0\u6cb9\u7ad9\u5e97","name":"\u6d77\u6dc0\u533a\u5b66\u6e05\u8def23\u53f7\u9662\u4e1c\u4fa7\u5e73\u623f4\u53f7\u623f\u95f4","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.016494","location_lat":"116.358192","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"292":{"id":"292","shop_no":"199","area_id":"110108","alias":"\u597d\u90bb\u5c45\u6c38\u5b9a\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u6c38\u5b9a\u8def63\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.918919","location_lat":"116.270988","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"293":{"id":"293","shop_no":"814","area_id":"110108","alias":"\u6728\u8377\u8def\u73af\u4fdd\u56ed\u534e\u4e3a\u5e97","name":"\u6d77\u6dc0\u533a\u6728\u8377\u8def19\u53f7\u96622\u53f7\u697c\u4e00\u697c\u897f\u5385\u4e1c\u4fa7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.063402","location_lat":"116.191489","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"294":{"id":"294","shop_no":"191","area_id":"110108","alias":"\u597d\u90bb\u5c45\u8f66\u9053\u6c9f\u5e97","name":"\u6d77\u6dc0\u533a\u8f66\u9053\u6c9f\u897f\u5357\u89d2\u5609\u8c6a\u56fd\u9645\u5927\u53a6B\u5ea7\u5927\u5802\u5185\uff08\u5468\u4e00\u81f3\u5468\u516d\u8425\u4e1a\uff09","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.953643","location_lat":"116.298784","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"295":{"id":"295","shop_no":"816","area_id":"110108","alias":"\u516c\u4e3b\u575f\u8363\u5e74\u5927\u53a6\u5e97","name":"\u6d77\u6dc0\u533a\u897f\u4e09\u73af\u4e2d\u8def14\u53f7\u8363\u5e74\u5927\u53a6\uff08\u9996\u5c426113\uff09","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.925751","location_lat":"116.317332","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"298":{"id":"298","shop_no":"173","area_id":"110108","alias":"\u597d\u90bb\u5c45\u6587\u6167\u56ed\u5e97","name":"\u6d77\u6dc0\u533a\u6587\u6167\u56ed\u8def10\u53f7\uff0c\u53cc\u6c47\u8d85\u5e02\u5bf9\u9762","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.958261","location_lat":"116.369649","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"305":{"id":"305","shop_no":"183","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5382\u6d3c\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u5382\u6d3c\u5c0f\u533a24\u53f7\u697c\uff0c\u5317\u4eac\u7535\u89c6\u53f0\u897f\u95e8","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.964408","location_lat":"116.31311","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"313":{"id":"313","shop_no":"155","area_id":"110108","alias":"\u597d\u90bb\u5c45\u7d22\u5bb6\u575f\u5e97","name":"\u6d77\u6dc0\u533a\u79ef\u6c34\u6f6d\u6865\u5f80\u897f400\u7c73,\u8fdc\u6d0b\u98ce\u666f\u5f80\u5317300\u7c73\u8def\u4e1c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.956995","location_lat":"116.365342","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"315":{"id":"315","shop_no":"838","area_id":"110108","alias":"\u4e2d\u5173\u6751\u5c0f\u5b66\u5e97","name":"\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u65b0\u79d1\u7965\u56ed2\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.329841","location_lat":"39.989263","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"317":{"id":"317","shop_no":"383","area_id":"110108","alias":"\u5b66\u5e9c\u6811\u5bb6\u56ed\u5e97","name":"\u6d77\u6dc0\u533a\u5b66\u5e9c\u6811\u5bb6\u56ed3\u53f7\u697c3-1\u53f73-12\u53f71\u5c423-6","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.341163","location_lat":"40.039874","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"320":{"id":"320","shop_no":"163","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5317\u6d3c\u8def\u4e1c\u5e97","name":"\u6d77\u6dc0\u533a\u5317\u6d3c\u8def42\u53f7\u9662\u5927\u95e8\u5317\u4fa7\uff0c\u9996\u90fd\u5e08\u5927\u9644\u4e2d\u4e1c\u95e8\u5bf9\u9762\u3002","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.936299","location_lat":"116.308095","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"324":{"id":"324","shop_no":"379","area_id":"110108","alias":"\u79d1\u5357\u8def\u641c\u72d0\u5927\u53a6\u5e97","name":"\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u65b0\u79d1\u7965\u56ed\u75322\u53f7\u697c1\u5c4203\u5ba4","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.33112","location_lat":"39.989367","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"325":{"id":"325","shop_no":"227","area_id":"110108","alias":"\u597d\u90bb\u5c45\u767d\u5806\u5b50\u5e97","name":"\u6d77\u6dc0\u533a\u961c\u6210\u8def23\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.322976","location_lat":"39.93062","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"326":{"id":"326","shop_no":"164","area_id":"110108","alias":"\u597d\u90bb\u5c45\u4ea4\u5927\u4e1c\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u5317\u4e0b\u5173\u5e7f\u901a\u82d1\u5c0f\u533a\u56db\u53f7\u697c\u4e00\u5c42\uff0c\u5609\u4e16\u5802\u836f\u5e97\u65c1","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.951798","location_lat":"116.356048","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"328":{"id":"328","shop_no":"169","area_id":"110108","alias":"\u597d\u90bb\u5c45\u79d1\u5b66\u9662\u5357\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u79d1\u5b66\u9662\u5357\u8def55\u53f7\uff0c\u4e2d\u5173\u6751\u4e2d\u5b66\u6b63\u5bf9\u9762","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.984316","location_lat":"116.331498","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"330":{"id":"330","shop_no":"377","area_id":"110108","alias":"\u5b66\u9662\u5357\u8def\u5e08\u8303\u5927\u5b66\u5e97","name":"\u6d77\u6dc0\u533a\uff08\u5e02\u5bb9\u76d1\u7763\u6240\u4e1c\u4fa7\uff09\u5b66\u9662\u5357\u8def1\u53f7\u697c1\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.37202","location_lat":"39.963739","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"331":{"id":"331","shop_no":"174","area_id":"110108","alias":"\u597d\u90bb\u5c45\u8bda\u54c1\u5efa\u7b51\u5e97","name":"\u6d77\u6dc0\u533a\u8bda\u54c1\u5efa\u7b51\u4e91\u6167\u91cc\u8fdc\u6d41\u6e05\u56ed\u5c0f\u533a4\u53f7\u56db\u5b63\u9752\u6865\u4e1c\u5357\u89d2","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.952221","location_lat":"116.287734","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"333":{"id":"333","shop_no":"502","area_id":"110108","alias":"\u597d\u90bb\u5c45\u6d77\u6dc0\u5357\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u901a\u60e0\u5bfa3\u53f7\u4e03\u4e00\u68c9\u7ec7\u5382\u4e1c\u4e00\u697c\u4e1c\u4fa7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.485421","location_lat":"40.013152","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"338":{"id":"338","shop_no":"374","area_id":"110108","alias":"\u590d\u5174\u8def\u7fe0\u5fae\u5927\u53a6\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u590d\u5174\u8def\u753218\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.310688","location_lat":"39.913175","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"347":{"id":"347","shop_no":"371","area_id":"110108","alias":"\u897f\u4e09\u73af\u5317\u8def\u82b1\u56ed\u6865\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u897f\u4e09\u73af\u5317\u8def91\u53f77\u53f7\u697c\u56fd\u56fe\u5927\u53a6\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.315945","location_lat":"39.939575","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"348":{"id":"348","shop_no":"320","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5b66\u77e5\u8f69\u5e97","name":"\u6d77\u6dc0\u533a\u5b66\u6e05\u8def16\u53f7\u5b66\u77e5\u8f69\u4e00\u5c42\u897f\u4fa7106\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.359619","location_lat":"40.018185","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"353":{"id":"353","shop_no":"244","area_id":"110108","alias":"\u597d\u90bb\u5c45\u84df\u95e8\u91cc\u5e97","name":"\u6d77\u6dc0\u533a\u84df\u95e8\u91cc\u5c0f\u533a\u5317\u5546\u4e1a\u697c1\u5e62\u53f7\u5e73\u623f","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.357564","location_lat":"39.976246","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"357":{"id":"357","shop_no":"351","area_id":"110108","alias":"\u597d\u90bb\u5c45\u677f\u4e95\u5e97","name":"\u6d77\u6dc0\u533a\u677f\u4e95\u675160\u53f7\u535729\u53f7\u5e73\u623f-9","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.955163","location_lat":"116.295331","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"371":{"id":"371","shop_no":"214","area_id":"110108","alias":"\u597d\u90bb\u5c45\u9b4f\u516c\u6751\u5e97","name":"\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u5357\u5927\u885718\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.960411","location_lat":"116.330573","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"376":{"id":"376","shop_no":"278","area_id":"110108","alias":"\u597d\u90bb\u5c45\u4eba\u5927\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u5357\u5927\u88571\u53f7\u9662","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.327008","location_lat":"39.974144","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"380":{"id":"380","shop_no":"272","area_id":"110108","alias":"\u597d\u90bb\u5c45\u9a6c\u7538\u6865\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u5317\u592a\u5e73\u5e84\u90ae\u4fe1\u5bbf\u820d9\u95e8","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.383046","location_lat":"39.975784","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"381":{"id":"381","shop_no":"263","area_id":"110108","alias":"\u597d\u90bb\u5c45\u7545\u6625\u56ed\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u897f\u82d1\u8349\u573a5\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.311304","location_lat":"39.995206","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"384":{"id":"384","shop_no":"279","area_id":"110108","alias":"\u597d\u90bb\u5c45\u7406\u5de5\u5927\u5b66\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u5357\u5927\u88575\u53f7102","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.329293","location_lat":"39.96435","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"386":{"id":"386","shop_no":"344","area_id":"110108","alias":"\u597d\u90bb\u5c45\u7682\u541b\u5e99\u5e97","name":"\u6d77\u6dc0\u533a\u7682\u541b\u5e99\u5927\u949f\u5bfa\u6d3e\u51fa\u6240\u6b63\u5bf9\u9762\u6d77\u6dc0\u533a\u7682\u541b\u5e9914\u53f7\u9662\u4e00\u53f7\u697c1\u5c42101\u5ba4","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.343776","location_lat":"39.966212","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"389":{"id":"389","shop_no":"285","area_id":"110108","alias":"\u597d\u90bb\u5c45\u82cf\u5dde\u8857\u5de5\u5546\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u82cf\u5dde\u885749\u53f7\u4e00\u697c\u5317\u4fa7111\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.312813","location_lat":"39.979466","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"392":{"id":"392","shop_no":"292","area_id":"110108","alias":"\u597d\u90bb\u5c45\u82b1\u56ed\u8def\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u82b1\u56ed\u8defC2\u53f7\u53571\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.372463","location_lat":"39.986685","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"396":{"id":"396","shop_no":"296","area_id":"110108","alias":"\u597d\u90bb\u5c45\u897f\u76f4\u95e8\u5317\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u897f\u76f4\u95e8\u5317\u5927\u885747\u53f7\u96622\u53f7\u697c\u5317\u4fa7\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.95324","location_lat":"116.362383","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"400":{"id":"400","shop_no":"314","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5b66\u6e05\u519c\u5927\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u519c\u5927\u4e1c\u6821\u533aB105","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.361065","location_lat":"40.011608","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"407":{"id":"407","shop_no":"307","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5de5\u5546\u4e1c\u6821\u533a\u5e97","name":"\u6d77\u6dc0\u533a\u961c\u6210\u8def11\u53f7\u9662\u9ad8\u5c42\u5b66\u751f\u516c\u5bd3\u5bf9\u9762\u65b0\u5efa\u5e73\u623f","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.326385","location_lat":"39.931696","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"412":{"id":"412","shop_no":"313","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5317\u592a\u5e73\u5e84\u897f\u5e97","name":"\u5317\u592a\u5e73\u5e84\u8def25\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.376216","location_lat":"39.978622","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"413":{"id":"413","shop_no":"315","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5b66\u9662\u5357\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u5927\u67f3\u6811\u8def2\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.349387","location_lat":"39.963509","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"421":{"id":"421","shop_no":"125","area_id":"110108","alias":"\u5317\u4e09\u73af\u5e97","name":"\u6d77\u6dc0\u533a\u5317\u4e09\u73af\u897f\u8def60\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.32179","location_lat":"39.970257","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"425":{"id":"425","shop_no":"334","area_id":"110108","alias":"\u82cf\u5dde\u8857\u4e94\u5206\u5e97","name":"\u6d77\u6dc0\u533a\u82cf\u5dde\u8857\u5de5\u5546\u5c40\u4e00\u5c42\u82cf\u5dde\u8857\u5de5\u5546\u5e97\u5bf9\u9762\u504f\u5357","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.978948","location_lat":"116.313501","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"426":{"id":"426","shop_no":"341","area_id":"110108","alias":"\u56db\u9053\u53e3\u8def\u4e8c\u5206\u5e97","name":"\u5317\u4eac\u5e02\u6d77\u6dc0\u533a\u56db\u9053\u53e3\u8def\u51c0\u571f\u5bfa32\u53f7\u4e1c\u533a41\u5e62\u4e00\u5c42\u5317\u90e8","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.955064","location_lat":"116.355083","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"431":{"id":"431","shop_no":"348","area_id":"110108","alias":"\u597d\u90bb\u5c45\u6e05\u534e\u4e1c\u4e09","name":"\u6d77\u6dc0\u533a\u519c\u4e1a\u5927\u5b66\u5357\u95e8\u4e1c200\u7c73\u8def\u5317\u6d77\u6dc0\u533a\u6e05\u534e\u4e1c\u8def11\u53f72\u53f7\u5e62\u4e00\u5c42\u897f\u4fa7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.007298","location_lat":"116.366127","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"432":{"id":"432","shop_no":"350","area_id":"110108","alias":"\u597d\u90bb\u5c45\u5927\u949f\u5bfa\u4e1c\u8def","name":"\u6d77\u6dc0\u533a\u5927\u949f\u5bfa\u4e1c\u8def\u4eac\u4eea\u5927\u53a6\u5e95\u5546\u6d77\u6dc0\u533a\u5927\u949f\u5bfa\u4e1c\u8def9\u53f71\u5e621\u5c42101-1","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.977196","location_lat":"116.344837","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"437":{"id":"437","shop_no":"228","area_id":"110108","alias":"\u597d\u90bb\u5c45\u822a\u5317","name":"\u6d77\u6dc0\u533a\u897f\u4e09\u73af\u4e2d\u8def88\u53f7\uff08\u822a\u5929\u6865\u4e1c\u5317\u89d2\u8f85\u8def\uff09\u534e\u590f\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.932134","location_lat":"116.31704","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""}},"110105":{"3":{"id":"3","shop_no":"275","area_id":"110105","alias":"\u4e3d\u90fd\u996d\u5e97\u4e1c\u95e8\u5e97","name":"\u671d\u9633\u533a\uff0c\u9ad8\u5bb6\u56ed\u5c0f\u533a311\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(275)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.486509","location_lat":"39.984958","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"4":{"id":"4","shop_no":"322","area_id":"110105","alias":"\u4e03\u5723\u8def\u5149\u7199\u95e8\u5317\u91cc\u5e97","name":"\u671d\u9633\u533a\uff0c\u5149\u7199\u95e8\u5317\u91cc\u753231\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(322)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.442097","location_lat":"39.973949","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"5":{"id":"5","shop_no":"298","area_id":"110105","alias":"\u6167\u5fe0\u5317\u8def\u5e97","name":"\u671d\u9633\u533a\uff0c\u6167\u5fe0\u5317\u8def\u6167\u5fe0\u91cc231\u697c\u9f13\u6d6a\u5c7f\u4f1a\u6240\u4e00\u5c42\u5e95\u5546\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(298)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.407968","location_lat":"40.004823","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"6":{"id":"6","shop_no":"291","area_id":"110105","alias":"\u9152\u4ed9\u6865\u8def\u4eac\u90fd\u56fd\u9645\u5e97","name":"\u671d\u9633\u533a\uff0c\u9152\u4ed9\u6865\u8def26\u53f7\u96621\u53f7\u697cA05\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(291)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.500083","location_lat":"39.970223","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"10":{"id":"10","shop_no":"343","area_id":"110105","alias":"\u7ba1\u5e84\u897f\u91cc\u5e97","name":"\u671d\u9633\u533a\uff0c\u671d\u9633\u8def\u7ba1\u5e84\u897f\u91cc65\u53f7\uff0c\u4f73\u5f97\u5bbe\u9986\u697c\u4e0b\u91d1\u51e4\u6210\u7965\u65c1\u8fb9\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(343)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.595474","location_lat":"39.918661","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"11":{"id":"11","shop_no":"148","area_id":"110105","alias":"\u5317\u6c99\u6ee9\u67ab\u6797\u7eff\u6d32\u5e97","name":"\u671d\u9633\u533a\uff0c\u5927\u5c6f\u8def\u98ce\u6797\u7eff\u6d32\u5c0f\u533a6\u53f7\u697c\u5e95\u5546D\u5355\u5143S-F06-01D\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(148)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.390494","location_lat":"40.00807","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"12":{"id":"12","shop_no":"249","area_id":"110105","alias":"\u5e7f\u987a\u6865\u5357\u535a\u96c5\u56fd\u9645\u5e97","name":"\u671d\u9633\u533a\uff0c\u5229\u6cfd\u4e2d\u4e00\u8def1\u53f7\u671b\u4eac\u79d1\u6280\u5927\u53a6\u5546\u94fa\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(249)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.47653","location_lat":"40.019315","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"97":{"id":"97","shop_no":"237","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5b9a\u798f\u5e84\u5e97","name":"\u671d\u9633\u533a\uff0c\u5b9a\u798f\u5e84\u5317\u88577\u53f7\u697c\u897f\u4fa7\u5e73\u623f\u5317\u4eac\u6587\u6559\u7528\u54c1\u5382\u5185\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(237)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.923365","location_lat":"116.560242","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"260":{"id":"260","shop_no":"531","area_id":"110105","alias":"\u597d\u90bb\u5c45\u6797\u8fbe\u5e97","name":"\u671d\u9633\u533a\u4e1c\u5317\u4e8c\u73af\u5916\u6797\u8fbe\u5927\u53a6","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.962122","location_lat":"116.439102","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"287":{"id":"287","shop_no":"148","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5317\u6c99\u6ee9\u5e97","name":"\u671d\u9633\u533a\u5927\u5c6f\u8def\u98ce\u6797\u7eff\u6d32\u5c0f\u533a6\u53f7\u697c\u5e95\u5546D\u5355\u5143S-F06-01D","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.008075","location_lat":"116.390495","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"290":{"id":"290","shop_no":"150","area_id":"110105","alias":"\u597d\u90bb\u5c45\u96c5\u5b9d\u8def\u5e97","name":"\u671d\u9633\u533a\u5916\u4ea4\u90e8\u5357\u88578\u53f7\uff0c\u4eac\u534e\u8c6a\u56ed\u5357\u5ea7213-C\u3002","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.923624","location_lat":"116.443117","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"304":{"id":"304","shop_no":"818","area_id":"110105","alias":"\u828d\u836f\u5c452\u53f7\u9662\u5e97","name":"\u671d\u9633\u533a\u592a\u9633\u5bab\u828d\u836f\u5c452\u53f7\u9662\u75322\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.438204","location_lat":"39.986223","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"307":{"id":"307","shop_no":"823","area_id":"110105","alias":"\u671b\u4eac\u897f\u56ed\u661f\u6e90\u56fd\u9645A\u5ea7\u5e97","name":"\u671d\u9633\u533a\u671b\u4eac\u897f\u56ed222\u697cA-103\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.474849","location_lat":"40.008671","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"308":{"id":"308","shop_no":"184","area_id":"110105","alias":"\u597d\u90bb\u5c45\u7ea2\u9886\u5dfe\u6865\u5e97","name":"\u671d\u9633\u533a\u5ef6\u9759\u4e1c\u91cc\u75323\u53f7\u5546\u52a1\u5b66\u9662\u7efc\u5408\u697c\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.928626","location_lat":"116.494072","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"309":{"id":"309","shop_no":"185","area_id":"110105","alias":"\u597d\u90bb\u5c45\u82b1\u5bb6\u5730\u5e97","name":"\u671d\u9633\u533a\u671b\u4eac\u4e2d\u73af\u5357\u8def5\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.988666","location_lat":"116.48105","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"316":{"id":"316","shop_no":"243","area_id":"110105","alias":"\u56e2\u7ed3\u6e56\u5e97","name":"\u671d\u9633\u533a\u671d\u9633\u5317\u8def219\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.473282","location_lat":"39.92927","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"327":{"id":"327","shop_no":"378","area_id":"110105","alias":"\u751c\u6c34\u56ed\u4e1c\u8857\u5206\u5e97","name":"\u671d\u9633\u533a\u751c\u6c34\u56ed\u4e1c\u885710\u53f717\u53f7\u697c1-3","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.48544","location_lat":"39.92896","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"334":{"id":"334","shop_no":"327","area_id":"110105","alias":"\u597d\u90bb\u5c45\u671b\u4eac\u897f\u56ed\u5e97","name":"\u671d\u9633\u533a\u671b\u4eac\u897f\u56ed222\u697c\u661f\u6e90\u516c\u5bd3E\u5ea7\u5e95\u5546E-3\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.008675","location_lat":"116.474847","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"335":{"id":"335","shop_no":"375","area_id":"110105","alias":"\u9152\u4ed9\u6865360\u5927\u53a6\u5e97","name":"\u671d\u9633\u533a\u9152\u4ed9\u6865\u8def6\u53f7\u96622\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.497046","location_lat":"39.988765","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"336":{"id":"336","shop_no":"326","area_id":"110105","alias":"\u597d\u90bb\u5c45\u6c38\u5b89\u91cc\u4e2d\u8857\u5e97","name":"\u671d\u9633\u533a\u5efa\u56fd\u95e8\u5916\u5927\u8857\u6c38\u5b89\u91cc\u4e2d\u885725\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.911324","location_lat":"116.456232","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"337":{"id":"337","shop_no":"218","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5e7f\u6e20\u8def\u5e97","name":"\u671d\u9633\u533a\u5e7f\u6e20\u4e1c\u8def33\u53f7\u6cbf\u6d77\u8d5b\u6d1b\u57ce\u5e95\u5546","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.511862","location_lat":"39.901627","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"341":{"id":"341","shop_no":"373","area_id":"110105","alias":"\u5efa\u534e\u5357\u8def\u5546\u901a\u5927\u53a6\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5efa\u56fd\u95e8\u5916\u5927\u8857\u5efa\u534e\u5357\u8def11\u53f7\u5546\u901a\u5927\u53a6\u5e95\u5546","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.450102","location_lat":"39.911605","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"342":{"id":"342","shop_no":"321","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5de5\u4f53\u5357\u8def\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5de5\u4f53\u5357\u8def\u671d\u9633\u533b\u9662\u897f\u4fa7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.45747","location_lat":"39.932487","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"344":{"id":"344","shop_no":"372","area_id":"110105","alias":"\u5efa\u534e\u5357\u8def\u7f8e\u534e\u4e16\u7eaa\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5efa\u534e\u5357\u8def15\u53f7\u7f8e\u534e\u4e16\u7eaa\u5927\u53a61\u5c421-78\u53f7\u623f\u5c4b","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.449915","location_lat":"39.910699","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"345":{"id":"345","shop_no":"322","area_id":"110105","alias":"\u597d\u90bb\u5c45\u4e03\u5723\u8def\u5206\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5149\u7199\u95e8\u5317\u91cc\u753231\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.442098","location_lat":"39.97395","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"351":{"id":"351","shop_no":"369","area_id":"110105","alias":"\u5de5\u4f53\u6625\u79c0\u8def\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5de5\u4eba\u4f53\u80b2\u573a\u5317\u8def1\u53f73\u53f7\u697c\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.456211","location_lat":"39.939741","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"356":{"id":"356","shop_no":"239","area_id":"110105","alias":"\u597d\u90bb\u5c45\u767e\u5b50\u6e7e\u4e09\u5e97","name":"\u671d\u9633\u533a\u767e\u5b50\u6e7e\u8def16\u53f7\u767e\u5b50\u56ed14\u53f7\u697cB\u95e8101\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.491909","location_lat":"39.906244","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"372":{"id":"372","shop_no":"249","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5e7f\u987a\u6865\u5357\u5e97","name":"\u671d\u9633\u533a\u5229\u6cfd\u4e2d\u4e00\u8def1\u53f7\u671b\u4eac\u79d1\u6280\u5927\u53a6\u5546\u94fa","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"1116.476429","location_lat":"40.01963","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"382":{"id":"382","shop_no":"275","area_id":"110105","alias":"\u597d\u90bb\u5c45\u4e3d\u90fd\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u9ad8\u5bb6\u56ed\u5c0f\u533a311\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.486506","location_lat":"39.984957","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"383":{"id":"383","shop_no":"277","area_id":"110105","alias":"\u597d\u90bb\u5c45\u4e1c\u571f\u57ce2\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u4e1c\u571f\u57ce\u8def13\u53f7\u96621\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.438694","location_lat":"39.957714","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"388":{"id":"388","shop_no":"286","area_id":"110105","alias":"\u597d\u90bb\u5c45\u8d22\u6ee1\u8857\u5e97","name":"\u671d\u9633\u533a\u671d\u9633\u8def69\u53f7\u697c1-1-1\uff085\uff09\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.542551","location_lat":"39.923212","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"390":{"id":"390","shop_no":"284","area_id":"110105","alias":"\u597d\u90bb\u5c45\u9ea6\u5b50\u897f\u8857\u5e97","name":"\u671d\u9633\u533a\u67a3\u8425\u5317\u91cc38\u53f7\u697c\u4e00\u5c42104","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.475901","location_lat":"39.950494","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"393":{"id":"393","shop_no":"293","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5e7f\u6cfd\u679c\u5cad\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5e7f\u6cfd\u8def6\u53f7\u966213\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.485421","location_lat":"40.013152","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"394":{"id":"394","shop_no":"503","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5e7f\u987a\u5317\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5229\u6cfd\u897f\u56ed102\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.014023","location_lat":"116.475486","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"395":{"id":"395","shop_no":"291","area_id":"110105","alias":"\u597d\u90bb\u5c45\u9152\u4ed9\u6865\u8def\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u9152\u4ed9\u6865\u8def26\u53f7\u96621\u53f7\u697cA05\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.50127","location_lat":"39.972673","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"397":{"id":"397","shop_no":"298","area_id":"110105","alias":"\u597d\u90bb\u5c45\u6167\u5fe0\u5317\u8def\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u6167\u5fe0\u5317\u8def\u6167\u5fe0\u91cc231\u697c\u9f13\u6d6a\u5c7f\u4f1a\u6240\u4e00\u5c42\u5e95\u5546","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.408649","location_lat":"40.004854","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"401":{"id":"401","shop_no":"297","area_id":"110105","alias":"\u597d\u90bb\u5c45\u9f13\u5916\u9ec4\u5bfa","name":"\u671d\u9633\u533a\u5b89\u5916\u9ec4\u5bfa\u5927\u88573\u53f7\u9662","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.403293","location_lat":"39.969729","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"405":{"id":"405","shop_no":"303","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5916\u7ecf\u8d38\u5e97","name":"\u671d\u9633\u533a\u592a\u9633\u5bab\u4e61\u828d\u836f\u5c45\u6751\u75323\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.437723","location_lat":"39.986458","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"410":{"id":"410","shop_no":"309","area_id":"110105","alias":"\u597d\u90bb\u5c45\u5b89\u82d1\u5c0f\u5173\u5e97","name":"\u671d\u9633\u533a\u5c0f\u5173\u5317\u885743\u53f7\u5e73\u623f","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.415675","location_lat":"39.987759","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"416":{"id":"416","shop_no":"502","area_id":"110105","alias":"\u5149\u534e\u6865\u5e97","name":"\u671d\u9633\u533a\u5149\u534e\u8def7\u53f7\uff08\u5468\u4e00\u81f3\u5468\u4e94\u8425\u4e1a\uff09","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.460404","location_lat":"39.919788","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"418":{"id":"418","shop_no":"262","area_id":"110105","alias":"\u767e\u5b50\u6e7e5\u5e97","name":"\u671d\u9633\u533a\u767e\u5b50\u6e7e\u8def16\u53f7\u767e\u5b50\u56ed4\u53f7\u697c\u4e00\u5c42C\u5355\u5143101\u5ba4","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.490848","location_lat":"39.906404","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"423":{"id":"423","shop_no":"332","area_id":"110105","alias":"\u5de6\u5bb6\u5e84\u5e97","name":"\u5317\u4eac\u5e02\u671d\u9633\u533a\u5de6\u5bb6\u5e84\u4e1c\u91cc14\u53f7\u697c\u9662","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.960224","location_lat":"116.45108","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"427":{"id":"427","shop_no":"343","area_id":"110105","alias":"\u7ba1\u5e84\u5206\u5e97","name":"\u671d\u9633\u533a\u671d\u9633\u8def\u7ba1\u5e84\u897f\u91cc65\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.918461","location_lat":"116.596154","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"433":{"id":"433","shop_no":"388","area_id":"110105","alias":"\u6a31\u82b1\u56ed\u4e1c\u8857\u5e97","name":"\u671d\u9633\u533a\u6a31\u82b1\u56ed\u4e1c\u88571\u53f7\u697c1\u5c421-6","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.91431","location_lat":"116.37832","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"435":{"id":"435","shop_no":"386","area_id":"110105","alias":"\u548c\u5e73\u8857\u5206\u5e97","name":"\u671d\u9633\u533a\u548c\u5e73\u885711\u533a\u753216\u697c\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.975034","location_lat":"116.429374","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"436":{"id":"436","shop_no":"387","area_id":"110105","alias":"\u5efa\u5916\u5927\u8857\u5206\u5e97","name":"\u671d\u9633\u533a\u5efa\u5916\u5927\u8857\u4e5924\u53f7\u71d5\u534e\u82d1N105","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.912587","location_lat":"116.44601","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"439":{"id":"439","shop_no":"366","area_id":"110105","alias":"\u6167\u5fe0\u91cc\u6d1b\u514b\u65f6\u4ee3\u5e97","name":"\u671d\u9633\u533a\u6167\u5fe0\u91cc\u6d1b\u514b\u65f6\u4ee3\u4e9a\u5965\u56fd\u9645\u5e7f\u573aD\u5ea7\u4e00\u5c421019\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.407968","location_lat":"40.004823","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""}},"110101":{"7":{"id":"7","shop_no":"300","area_id":"110101","alias":"\u4e1c\u76f4\u95e8\u5357\u5c0f\u8857\u5e97","name":"\u4e1c\u57ce\u533a\uff0c\u4e1c\u76f4\u95e8\u5357\u5c0f\u885720-1\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(300)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.43254","location_lat":"39.940958","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"13":{"id":"13","shop_no":"229","area_id":"110101","alias":"\u90fd\u5e02\u99a8\u56ed\u5e97","name":"\u4e1c\u57ce\u533a\uff0c\u5174\u9686\u90fd\u5e02\u99a8\u56ed\u5e95\u5546\u4e00\u5c42A101\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(229)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.42161","location_lat":"39.901753","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"14":{"id":"14","shop_no":"251","area_id":"110101","alias":"\u91d1\u5b9d\u8857\u91d1\u5b9d\u6c47\u5e97","name":"\u4e1c\u57ce\u533a\uff0c\u91d1\u5b9d\u8857\u9053\u4e34\u65f63\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(251)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.429121","location_lat":"39.921926","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"15":{"id":"15","shop_no":"146","area_id":"110101","alias":"\u5174\u5316\u897f\u91cc\u548c\u5e73\u91cc\u533b\u9662\u5e97","name":"\u4e1c\u57ce\u533a\uff0c\u5174\u534e\u897f\u91cc2\u53f7\u697c\u5357\u4fa7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(146)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.419989","location_lat":"39.96611","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"257":{"id":"257","shop_no":"146","area_id":"110101","alias":"\u597d\u90bb\u5c45\u5174\u5316\u8def\u5e97","name":"\u4e1c\u57ce\u533a\u5174\u534e\u897f\u91cc2\u53f7\u697c\u5357\u4fa7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.965882","location_lat":"116.420416","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"265":{"id":"265","shop_no":"121","area_id":"110101","alias":"\u597d\u90bb\u5c45\u7518\u5bb6\u53e3\u5e97","name":"\u897f\u57ce\u533a\u961c\u5916\u5927\u885744\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.92837","location_lat":"116.342501","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"279":{"id":"279","shop_no":"399","area_id":"110101","alias":"\u548c\u5e73\u91cc\u533b\u9662\u897f\u5206\u5e97","name":"\u4e1c\u57ce\u533a\u548c\u5e73\u91cc\u5317\u885718\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.965267","location_lat":"116.420259","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"289":{"id":"289","shop_no":"219","area_id":"110101","alias":"\u5317\u4eac\u56fd\u9645\u5927\u53a6\u5e97","name":"\u597d\u90bb\u5c45\u4e2d\u5173\u6751\u5357\u5927\u8857\u5e97","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.959402","location_lat":"116.331593","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"310":{"id":"310","shop_no":"824","area_id":"110101","alias":"\u5b89\u5916\u5927\u8857\u848b\u5b85\u53e3\u5e97","name":"\u4e1c\u57ce\u533a\u5b89\u5b9a\u95e8\u5916\u5927\u885782\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.420932","location_lat":"39.965267","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"311":{"id":"311","shop_no":"225","area_id":"110101","alias":"\u597d\u90bb\u5c45\u5317\u4e09\u73af\u4e1c\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u5357\u5927\u88571\u53f7\u53cb\u8c0a\u5bbe\u998661711\u623f\u95f4","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.326571","location_lat":"39.97204","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"312":{"id":"312","shop_no":"831","area_id":"110101","alias":"\u5b89\u5b9a\u95e8\u4e0a\u9f99\u897f\u91cc\u5e97","name":"\u4e1c\u57ce\u533a\u5b89\u5b9a\u95e8\u5916\u4e0a\u9f99\u897f\u91cc36\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.412892","location_lat":"39.959557","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"321":{"id":"321","shop_no":"381","area_id":"110101","alias":"\u65b0\u592a\u4ed3\u80e1\u540c\u7c0b\u8857\u5e97","name":"\u4e1c\u57ce\u533a\u65b0\u592a\u4ed3\u80e1\u540c1\u53f71\u53f7\u697c\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.42759","location_lat":"39.946165","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"329":{"id":"329","shop_no":"233","area_id":"110101","alias":"\u597d\u90bb\u5c45\u671d\u9633\u95e8\u5185\u5e97","name":"\u4e1c\u57ce\u533a\u671d\u9633\u95e8\u5185\u5927\u8857192\u53f7\u4e1c\u5355\u660e\u73e0\u9996\u5c42\u897f\u4fa7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.428979","location_lat":"39.929906","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"332":{"id":"332","shop_no":"234","area_id":"110101","alias":"\u80dc\u53e4\u5317\u8def\u897f\u53e3\u5e97","name":"\u4e1c\u57ce\u533a\u5b89\u5b9a\u8def20\u53f7\u96625\u53f7\u697c102","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.415163","location_lat":"39.9788","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"350":{"id":"350","shop_no":"229","area_id":"110101","alias":"\u597d\u90bb\u5c45\u90fd\u5e02\u99a8\u56ed\u5e97","name":"\u5d07\u6587\u533a\u5174\u9686\u90fd\u5e02\u99a8\u56ed\u5730\u4e0a\u4e00\u5c42A101","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.42174","location_lat":"39.901802","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"373":{"id":"373","shop_no":"258","area_id":"110101","alias":"\u597d\u90bb\u5c45\u5e7f\u4e49\u8857\u5e97","name":"\u5ba3\u6b66\u533a\u5e7f\u5b89\u95e8\u5185\u5927\u8857311\u53f7\u96622\u53f7\u697c\u7965\u9f99\u5927\u53a6\u9996\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.363458","location_lat":"39.896673","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"378":{"id":"378","shop_no":"265","area_id":"110101","alias":"\u597d\u90bb\u5c45\u7f8e\u672f\u9986\u540e\u8857\u5e97","name":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u5927\u4f5b\u5bfa\u4e1c\u88571\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.415589","location_lat":"39.936165","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"387":{"id":"387","shop_no":"287","area_id":"110101","alias":"\u597d\u90bb\u5c45\u5b89\u5b9a\u95e8\u5185\u5e97","name":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u5b89\u5b9a\u95e8\u5185\u5927\u885716\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.954631","location_lat":"116.414941","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"391":{"id":"391","shop_no":"290","area_id":"110101","alias":"\u597d\u90bb\u5c45\u4e1c\u56db\u5317\u5e97","name":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u4e1c\u56db\u5317\u5927\u8857146\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.423759","location_lat":"39.941014","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"398":{"id":"398","shop_no":"312","area_id":"110101","alias":"\u597d\u90bb\u5c45\u7f8e\u672f\u9986\u4e1c\u8857\u5e97","name":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u7f8e\u672f\u9986\u540e\u88579\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.415629","location_lat":"39.936304","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"403":{"id":"403","shop_no":"304","area_id":"110101","alias":"\u4e1c\u57ce\u533a\u5f20\u81ea\u5fe0\u8def2\u53f7","name":"\u597d\u90bb\u5c45\u5f20\u81ea\u5fe0\u8def\u5e97","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.422575","location_lat":"39.939621","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"406":{"id":"406","shop_no":"305","area_id":"110101","alias":"\u597d\u90bb\u5c45\u524d\u95e8\u897f\u5927\u8857\u5e97\u897f","name":"\u897f\u57ce\u533a\u524d\u95e8\u897f\u5927\u885757\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.393358","location_lat":"39.90677","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"408":{"id":"408","shop_no":"309","area_id":"110101","alias":"\u597d\u90bb\u5c45\u5fb7\u80dc\u4e1c\u53e3\u5e97","name":"\u897f\u57ce\u533a\u5fb7\u80dc\u95e8\u5185\u5927\u88576\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.386649","location_lat":"39.954094","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"411":{"id":"411","shop_no":"311","area_id":"110101","alias":"\u597d\u90bb\u5c45\u6c99\u6ee9\u5317\u8857\u5e97","name":"\u4e1c\u57ce\u533a\u4e94\u56db\u5927\u8857\u6c99\u6ee9\u5317\u8857\u6c42\u662f\u6742\u5fd7\u793e\u5bf9\u9762","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.409818","location_lat":"39.931568","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"414":{"id":"414","shop_no":"316","area_id":"110101","alias":"\u597d\u90bb\u5c45\u7d2b\u7af9\u9662\u8def\u5e97","name":"\u6d77\u6dc0\u533a\u7d2b\u7af9\u9662\u8def\u8f66\u9053\u6c9f\u7cae\u5e97","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.306968","location_lat":"39.951495","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"420":{"id":"420","shop_no":"328","area_id":"110101","alias":"\u4e94\u56db\u5927\u8857\u4e8c\u5206\u5e97","name":"\u5317\u4eac\u5e02\u4e1c\u57ce\u533a\u6c99\u6ee9\u5317\u8857\u75322\u53f7\u4e94\u56db\u5927\u8857\u753231\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.411039","location_lat":"39.9307","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"422":{"id":"422","shop_no":"331","area_id":"110101","alias":"\u4e1c\u56db\u5927\u8857\u4e09\u5206\u5e97","name":"\u4e1c\u57ce\u533a\u4e1c\u56db\u5317\u5927\u885743\u53f7\u9996\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.42303","location_lat":"39.946084","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"424":{"id":"424","shop_no":"333","area_id":"110101","alias":"\u6c99\u6ee9\u540e\u8857\u5206\u5e97","name":"\u4e1c\u57ce\u533a\u6c99\u6ee9\u540e\u885753\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.931855","location_lat":"116.408147","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"430":{"id":"430","shop_no":"347","area_id":"110101","alias":"\u597d\u90bb\u5c45\u4e1c\u8425\u623f\u5e97","name":"\u4e1c\u57ce\u533a\u5409\u58eb\u53e3\u4e1c\u8def\u805a\u9f99\u82b1\u56ed\u5bf9\u9762\u4e1c\u57ce\u533a\u4e1c\u8425\u623f\u516b\u67613\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.936417","location_lat":"116.446781","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"438":{"id":"438","shop_no":"300","area_id":"110101","alias":"\u597d\u90bb\u5c45\u4e1c\u76f4\u5357\u5c0f\u5e97","name":"\u5317\u4eac\u5e02\u4e1c\u76f4\u95e8\u5357\u5c0f\u885720-1","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.432541","location_lat":"39.940965","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""}},"110102":{"9":{"id":"9","shop_no":"221","area_id":"110102","alias":"\u53cb\u8c0a\u533b\u9662\u5e97","name":"\u897f\u57ce\u533a\uff0c\u6c38\u5b89\u8def32\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(221)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.401018","location_lat":"39.892384","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"25":{"id":"25","shop_no":"116","area_id":"110102","alias":"\u4f5f\u9e9f\u9601\u8def\u65b0\u534e\u793e\u5e97","name":"\u897f\u57ce\u533a\uff0c\u4f5f\u9e9f\u9601\u8def91\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(116)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.374796","location_lat":"39.9067","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"26":{"id":"26","shop_no":"110","area_id":"110102","alias":"\u6708\u575b\u5317\u8857\u5e97","name":"\u897f\u57ce\u533a\uff0c\u6708\u575b\u5317\u885711\u53f7\u697c7\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(110)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.352528","location_lat":"39.924319","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"42":{"id":"42","shop_no":"416","area_id":"110102","alias":"\u6728\u6a28\u5730\u597d\u90bb\u5c45","name":"\u897f\u57ce\u533a\uff0c\u6728\u6a28\u573025\u53f7\uff0c\u597d\u90bb\u5c45\u4fbf\u5229\u5e97(416)","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.345032","location_lat":"39.913418","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"259":{"id":"259","shop_no":"116","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4f5f\u9e9f\u9601\u8def\u5e97","name":"\u897f\u57ce\u533a\u4f5f\u9e9f\u9601\u8def91\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.90687","location_lat":"116.374897","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"261":{"id":"261","shop_no":"119","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5fb7\u5185\u5927\u8857\u5e97","name":"\u897f\u57ce\u533a\u5fb7\u5185\u5927\u8857232\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.944099","location_lat":"116.386256","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"262":{"id":"262","shop_no":"114","area_id":"110102","alias":"\u597d\u90bb\u5c45\u534a\u58c1\u8857\u5e97","name":"\u897f\u57ce\u533a\u524d\u534a\u58c1\u885735\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.944109","location_lat":"116.366064","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"263":{"id":"263","shop_no":"389","area_id":"110102","alias":"\u5730\u5b89\u95e8\u5382\u6865\u5c0f\u5b66\u5e97","name":"\u897f\u57ce\u533a\u5730\u5b89\u95e8\u897f\u5927\u8857163\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.939216","location_lat":"116.38465","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"269":{"id":"269","shop_no":"103","area_id":"110102","alias":"\u597d\u90bb\u5c45\u897f\u56db\u5e97","name":"\u897f\u57ce\u533a\u897f\u56db\u5317\u5927\u8857158\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.933129","location_lat":"116.380035","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"271":{"id":"271","shop_no":"118","area_id":"110102","alias":"\u597d\u90bb\u5c45\u65e7\u9f13\u697c\u5927\u8857\u5e97","name":"\u897f\u57ce\u533a\u5c0f\u77f3\u6865\u80e1\u540c3\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.952481","location_lat":"116.400045","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"273":{"id":"273","shop_no":"109","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4e8c\u9f99\u8def\u5e97","name":"\u897f\u57ce\u533a\u4e8c\u9f99\u8def41\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.916012","location_lat":"39.916012","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"275":{"id":"275","shop_no":"112","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5b87\u5b99\u7ea2\u5e97","name":"\u897f\u57ce\u533a\u767e\u4e07\u5e84\u5357\u88574\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.931903","location_lat":"116.34421","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"277":{"id":"277","shop_no":"105","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5c55\u89c8\u8def\u5e97","name":"\u897f\u57ce\u533a\u5c55\u89c8\u8def31\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.931276","location_lat":"116.350293","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"278":{"id":"278","shop_no":"402","area_id":"110102","alias":"\u62a4\u56fd\u5bfa\u8def\u53e3\u5357\u5e97","name":"\u897f\u57ce\u533a\u65b0\u5357\u5927\u8857154\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"40.063954","location_lat":"116.184985","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"280":{"id":"280","shop_no":"102","area_id":"110102","alias":"\u597d\u90bb\u5c45\u8f66\u516c\u5e84\u5e97","name":"\u897f\u57ce\u533a\u8f66\u516c\u5e84\u897f\u53e329\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.937759","location_lat":"39.937759","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"282":{"id":"282","shop_no":"110","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6708\u575b\u5317\u8857\u5e97","name":"\u897f\u57ce\u533a\u6708\u575b\u5317\u885711\u53f7\u697c7\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.924323","location_lat":"116.352528","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"284":{"id":"284","shop_no":"101","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4e2d\u8f74\u8def\u5e97","name":"\u897f\u57ce\u533a\u88d5\u4e2d\u4e1c\u91cc2\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.979206","location_lat":"116.398644","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"285":{"id":"285","shop_no":"223","area_id":"110102","alias":"\u82f1\u84dd\u56fd\u9645\u5e97","name":"\u5317\u4eac\u5e02\u897f\u57ce\u533a\u91d1\u878d\u5927\u88577\u53f7\u82f1\u84dd\u56fd\u9645\u91d1\u878d\u4e2d\u5fc3B120\uff08\u5468\u4e00\u81f3\u5468\u516d\u8425\u4e1a\uff09","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.364326","location_lat":"39.926289","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"296":{"id":"296","shop_no":"501","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4e2d\u5173\u6751\u5357\u5927\u8857\u5e97","name":"\u897f\u57ce\u533a\u897f\u56db\u5357\u5927\u8857111\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.924936","location_lat":"116.380005","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"297":{"id":"297","shop_no":"401","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6986\u6811\u9986\u5e97","name":"\u897f\u57ce\u533a\u6986\u6811\u998615\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.94284","location_lat":"116.351401","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"299":{"id":"299","shop_no":"817","area_id":"110102","alias":"\u69d0\u67cf\u6811\u8857\u5e97","name":"\u897f\u57ce\u533a\u69d0\u67cf\u6811\u8857\u5317\u91cc5\u53f7\u697c5-8\u53f7A","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.902009","location_lat":"116.361527","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"300":{"id":"300","shop_no":"221","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6c38\u5b89\u8def\u4e1c\u5e97","name":"\u5317\u4eac\u5e02\u5ba3\u6b66\u533a\u6c38\u5b89\u8def32\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.401073","location_lat":"39.892503","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"301":{"id":"301","shop_no":"176","area_id":"110102","alias":"\u597d\u90bb\u5c45\u961c\u5916\u5927\u8857\u5e97","name":"\u897f\u57ce\u533a\u961c\u5916\u5927\u885737\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.929138","location_lat":"116.346122","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"302":{"id":"302","shop_no":"209","area_id":"110102","alias":"\u51a0\u82f1\u56ed\u5e97","name":"\u897f\u57ce\u533a\u897f\u76f4\u95e8\u5185\u5927\u8857132\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.946345","location_lat":"116.371861","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"303":{"id":"303","shop_no":"170","area_id":"110102","alias":"\u597d\u90bb\u5c45\u767e\u4e07\u5e84\u5e97","name":"\u897f\u57ce\u533a\u767e\u4e07\u5e84\u5927\u885731\u53f7\u96621\u53f7\u697c1\u95e81\u5c422\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.934364","location_lat":"116.350088","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"306":{"id":"306","shop_no":"224","area_id":"110102","alias":"\u597d\u90bb\u5c45\u65b0\u5fb7\u8857\u5e97","name":"\u5317\u4eac\u897f\u57ce\u533a\u65b0\u5fb7\u8857\u4e1c\u53e3","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.384438","location_lat":"39.963915","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"318":{"id":"318","shop_no":"382","area_id":"110102","alias":"\u62a4\u56fd\u5bfa\u516c\u4ea4\u7ad9\u5e97","name":"\u897f\u57ce\u533a\u65b0\u8857\u53e3\u5357\u5927\u8857144\u53f7\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.381515","location_lat":"39.948668","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"322":{"id":"322","shop_no":"222","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4e30\u76db\u5e97","name":"\u897f\u57ce\u533a\u897f\u56db\u5357\u5927\u8857111\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.380005","location_lat":"39.924936","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"323":{"id":"323","shop_no":"380","area_id":"110102","alias":"\u5ba3\u6b66\u95e8\u897f\u5927\u8857\u5206\u5e97","name":"\u897f\u57ce\u533a\u5ba3\u6b66\u95e8\u897f\u5927\u8857103\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.370212","location_lat":"39.904687","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"339":{"id":"339","shop_no":"416","area_id":"110102","alias":"\u597d\u90bb\u5c45\u590d\u5174\u95e8\u4e2d\u8def\u5e97","name":"\u897f\u57ce\u533a\u6728\u6a28\u573025\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.913461","location_lat":"116.345005","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"340":{"id":"340","shop_no":"417","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5b98\u56ed\u5e97","name":"\u897f\u57ce\u533a\u5b98\u56ed\u5357\u91cc\u4e09\u533a1-1","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.937975","location_lat":"116.366814","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"343":{"id":"343","shop_no":"418","area_id":"110102","alias":"\u597d\u90bb\u5c45\u8f9f\u624d\u80e1\u540c\u5e97","name":"\u897f\u57ce\u533a\u8f9f\u624d\u80e1\u540c56\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.920193","location_lat":"116.377419","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"346":{"id":"346","shop_no":"420","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5730\u5b89\u95e8\u5e97","name":"\u897f\u57ce\u533a\u5730\u5916\u5927\u8857178\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.940582","location_lat":"116.402904","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"352":{"id":"352","shop_no":"421","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4e07\u65b9\u56ed\u5e97","name":"\u897f\u57ce\u533a\u8471\u5e97\u80e1\u540c2\u53f7\u96621\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.943645","location_lat":"116.36514","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"354":{"id":"354","shop_no":"422","area_id":"110102","alias":"\u597d\u90bb\u5c45\u897f\u4fbf\u95e8\u5e97","name":"\u897f\u57ce\u533a\u590d\u5174\u95e8\u5357\u5927\u88571\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.907359","location_lat":"116.362753","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"355":{"id":"355","shop_no":"423","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4e09\u91cc\u6cb3\u5e97","name":"\u897f\u57ce\u533a\u6708\u575b\u5357\u885737\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.919879","location_lat":"116.353503","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"358":{"id":"358","shop_no":"403","area_id":"110102","alias":"\u597d\u90bb\u5c45\u7075\u5883\u80e1\u540c\u5e97","name":"\u897f\u57ce\u533a\u7075\u955c\u80e1\u540c1\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.920853","location_lat":"116.386711","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"359":{"id":"359","shop_no":"404","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6708\u575b\u5357\u8857\u5e97","name":"\u897f\u57ce\u533a\u6708\u575b\u5357\u8857\u75321\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.91982","location_lat":"116.359306","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"360":{"id":"360","shop_no":"405","area_id":"110102","alias":"\u597d\u90bb\u5c45\u897f\u4ec0\u5e93\u5e97","name":"\u897f\u57ce\u533a\u897f\u4ec0\u5e93\u5927\u885724\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.93416","location_lat":"116.386686","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"361":{"id":"361","shop_no":"406","area_id":"110102","alias":"\u597d\u90bb\u5c45\u9f13\u697c\u897f\u5e97","name":"\u897f\u57ce\u533a\u9f13\u697c\u5927\u885793\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.950309","location_lat":"116.395251","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"362":{"id":"362","shop_no":"407","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5b8f\u5927\u5e97","name":"\u897f\u57ce\u533a\u5730\u5916\u5927\u885719\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.945682","location_lat":"116.402319","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"363":{"id":"363","shop_no":"409","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5317\u65b0\u534e\u8857\u5e97","name":"\u897f\u57ce\u533a\u5317\u65b0\u534e\u885788\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.910356","location_lat":"116.39084","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"364":{"id":"364","shop_no":"410","area_id":"110102","alias":"\u597d\u90bb\u5c45\u767d\u4e91\u91cc\u5e97","name":"\u897f\u57ce\u533a\u767d\u4e91\u91cc\u4e191\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.905357","location_lat":"116.353689","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"365":{"id":"365","shop_no":"411","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6708\u575b\u897f\u8857\u5e97","name":"\u897f\u57ce\u533a\u6708\u575b\u897f\u885721\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.922352","location_lat":"116.356126","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"366":{"id":"366","shop_no":"412","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5fb7\u5185\u5927\u8857\u5317\u5e97","name":"\u897f\u57ce\u533a\u5fb7\u5185\u5927\u8857169\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.948195","location_lat":"116.385315","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"367":{"id":"367","shop_no":"413","area_id":"110102","alias":"\u597d\u90bb\u5c45\u8d75\u767b\u79b9\u8def\u5e97","name":"\u897f\u57ce\u533a\u8d75\u767b\u79b9\u8def148\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.934596","location_lat":"116.374054","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"368":{"id":"368","shop_no":"201","area_id":"110102","alias":"\u597d\u90bb\u5c45\u767d\u77f3\u6865\u5e97","name":"\u6d77\u6dc0\u533a\u4e2d\u5173\u6751\u5357\u5927\u885748-9\u5e73\u623f","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.948923","location_lat":"116.332037","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"369":{"id":"369","shop_no":"208","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6c38\u5b89\u8def\u5e97","name":"\u5ba3\u6b66\u533a\u6c38\u5b89\u8def104\u53f7G\u95f4","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.89244","location_lat":"116.394263","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"370":{"id":"370","shop_no":"211","area_id":"110102","alias":"\u597d\u90bb\u5c45\u897f\u56db\u4e1c\u5e97","name":"\u897f\u57ce\u533a\u897f\u5b89\u95e8\u5927\u8857152\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.928371","location_lat":"116.380402","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"374":{"id":"374","shop_no":"260","area_id":"110102","alias":"\u597d\u90bb\u5c45\u68c9\u82b1\u80e1\u540c\u5317\u53e3\u5e97","name":"\u897f\u57ce\u533a\u65b0\u8857\u53e3\u4e1c\u885753\u53f74\u5e62\u53f7\u697c\u623f\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.38109","location_lat":"39.94842","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"375":{"id":"375","shop_no":"261","area_id":"110102","alias":"\u597d\u90bb\u5c45\u897f\u56db\u5317\u5927\u8857\u5e97","name":"\u897f\u57ce\u533a\u897f\u56db\u5317\u5927\u88575\u53f7\u5e73\u623f","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.38008","location_lat":"39.938001","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"377":{"id":"377","shop_no":"266","area_id":"110102","alias":"\u597d\u90bb\u5c45\u5fb7\u5916\u5e97","name":"\u5317\u4eac\u5e02\u897f\u57ce\u533a\u5fb7\u5916\u5927\u885711\u53f7\uff0c\u5fb7\u80dc\u56ed\u533a","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.385493","location_lat":"39.966536","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"379":{"id":"379","shop_no":"270","area_id":"110102","alias":"\u597d\u90bb\u5c45\u6708\u575b\u5317\u6865\u5e97","name":"\u5317\u4eac\u5e02\u897f\u57ce\u533a\u961c\u6210\u95e8\u5357\u5927\u88579\u53f7\u697c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.362562","location_lat":"39.924593","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"385":{"id":"385","shop_no":"280","area_id":"110102","alias":"\u597d\u90bb\u5c45\u4ec0\u5239\u6d77\u5e97","name":"\u5317\u4eac\u5e02\u897f\u57ce\u533a\u5730\u5b89\u95e8\u897f\u5927\u885747\u53f75\u53f7\u623f","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.400289","location_lat":"39.94003","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"399":{"id":"399","shop_no":"314","area_id":"110102","alias":"\u597d\u90bb\u5c45\u79ef\u6c34\u6f6d\u5317\u5e97","name":"\u897f\u57ce\u533a\u65b0\u8857\u53e3\u5916\u5927\u885728-13","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.378253","location_lat":"39.959436","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"402":{"id":"402","shop_no":"302","area_id":"110102","alias":"\u597d\u90bb\u5c45\u9e2d\u5b50\u6865\u5317\u5e97","name":"\u5ba3\u6b66\u533a\u5357\u6ee8\u6cb331\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.355091","location_lat":"39.88646","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"404":{"id":"404","shop_no":"306","area_id":"110102","alias":"\u597d\u90bb\u5c45\u897f\u56db\u4e1c\u8857\u5e97","name":"\u897f\u57ce\u533a\u897f\u56db\u4e1c\u5927\u885762\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.380553","location_lat":"39.929939","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"415":{"id":"415","shop_no":"154","area_id":"110102","alias":"\u65b0\u5357\u5e97","name":"\u897f\u57ce\u533a\u65b0\u8857\u53e3\u5357\u5927\u885748\u53f7\u3002\u65b0\u8857\u53e3\u996d\u5e97\u5f80\u5357200\u7c73\u8def\u4e1c\u3002","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.379719","location_lat":"39.94411","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"417":{"id":"417","shop_no":"166","area_id":"110102","alias":"\u65b0\u5fb7\u5e97","name":"\u897f\u57ce\u533a\u65b0\u5916\u5927\u8857\u5c0f\u897f\u5929\u4e1c\u91cc7\u53f7\uff0c\u597d\u90bb\u5c45\u603b\u90e8\u697c\u4e0b","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.378581","location_lat":"39.961568","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"419":{"id":"419","shop_no":"138","area_id":"110102","alias":"\u5317\u6ee8\u6cb3\u5e97","name":"\u897f\u57ce\u533a\u5317\u6ee8\u6cb3\u8def2\u53f7\u9662","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.359098","location_lat":"39.906494","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"428":{"id":"428","shop_no":"508","area_id":"110102","alias":"\u6728\u6a28\u5730\u5e97J","name":"\u897f\u57ce\u533a\u590d\u5174\u95e8\u5916\u5927\u8857\u753222\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.912755","location_lat":"116.346132","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""},"434":{"id":"434","shop_no":"841","area_id":"110102","alias":"\u5317\u4e09\u73af\u4e2d\u8def3\u5206\u5e97","name":"\u897f\u57ce\u533a\u5317\u4e09\u73af\u4e2d\u8def18\u53f7\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.973624","location_lat":"116.389087","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""}},"110106":{"314":{"id":"314","shop_no":"839","area_id":"110106","alias":"\u8349\u6865\u6b23\u56ed\u5e97","name":"\u4e30\u53f0\u533a\u8349\u6865\u6b23\u56ed\u4e00\u533a5\u53f7\u697c\u4e00\u5c425\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.365107","location_lat":"39.852251","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"319":{"id":"319","shop_no":"161","area_id":"110106","alias":"\u597d\u90bb\u5c45\u7f8a\u574a\u5e97\u8def\u5e97","name":"\u4e30\u53f0\u533a\u897f\u5ba2\u7ad9\u5357\u8def8\u53f7\u5357\u5e7f\u573a\u5f80\u5357\u8fc7\u7ea2\u7eff\u706f\u76f4\u884c300\u7c73\u8def\u4e1c","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.893183","location_lat":"116.327939","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"349":{"id":"349","shop_no":"370","area_id":"110106","alias":"\u9996\u7ecf\u8d38\u5927\u5b66\u5357\u95e8\u5e97","name":"\u4e30\u53f0\u533a\u9996\u7ecf\u8d38\u4e2d\u88578\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.327967","location_lat":"39.846621","deleted":false,"can_remark_address":false,"child_area_id":null,"account":null,"account_name":null},"429":{"id":"429","shop_no":"346","area_id":"110106","alias":"\u597d\u90bb\u5c45\u5218\u5bb6\u7a91\u5357\u91cc\u5e97","name":"\u4e30\u53f0\u533a\u5218\u5bb6\u7a91\u8def\u4e30\u53f0\u533a\u5218\u5bb6\u7a91\u5357\u91cc\u7532\u4e00\u53f7","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"39.868292","location_lat":"116.423535","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""}},"110107":{"409":{"id":"409","shop_no":"396","area_id":"110107","alias":"\u516b\u89d2\u7545\u6e38\u5927\u53a6\u641c\u72d0\u5e97","name":"\u5317\u4eac\u5e02\u77f3\u666f\u5c71\u533a\u641c\u72d0\u7545\u6e38\u5927\u53a6\u4e00\u5c42","type":"0","owner_name":"","owner_phone":"4000-508-528","location_long":"116.231304","location_lat":"39.912525","deleted":false,"can_remark_address":false,"child_area_id":null,"account":"","account_name":""}}};
    $vm.changeOfflineStoreArea = changeOfflineStoreArea;
    $vm.showOfflineStoreDetail = showOfflineStoreDetail;
    $vm.chooseOfflineStore = chooseOfflineStore;
    $vm.showChooseOfflineStore = showChooseOfflineStore;
    //$vm.mapPanTo = mapPanTo;
    function showChooseOfflineStore() {
      $vm.showOfflineStoreDetailView = false;
      $vm.chooseOfflineStoreView = true;
      $vm.showShareDetailView = false;
      $vm.showBalanceView = false;
    }

    function chooseOfflineStore(offlineStore) {
      $vm.showOfflineStoreDetailView = false;
      $vm.chooseOfflineStoreView = false;
      $vm.showShareDetailView = false;
      $vm.showBalanceView = true;
      $vm.checkedOfflineStore = offlineStore;
    }

    //function mapPanTo(offlineStore) {
    //  var point = new BMap.Point(offlineStore.location_long, offlineStore.location_lat);
    //  $vm.offlineStoreMap.panTo(point);
    //}

    //function showMap(offlineStore) {
    //  var point = new BMap.Point(offlineStore.location_long, offlineStore.location_lat);
    //  if ($vm.offlineStoreMap == null) {
    //    $vm.offlineStoreMap = new BMap.Map("offline-store-map");
    //    $vm.offlineStoreMap.centerAndZoom(point, 15);
    //  } else {
    //    $timeout(function () {
    //      $vm.mapPanTo(offlineStore);
    //    }, 200);
    //  }
    //  $vm.offlineStoreMap.clearOverlays();
    //  var marker = new BMap.Marker(point);        // 创建标注
    //  $vm.offlineStoreMap.addOverlay(marker);
    //}

    function showOfflineStoreDetail(offlineStore) {
      if (!offlineStore['mapImg']) {
        var pointStr = offlineStore['location_long'] + ',' + offlineStore['location_lat'];
        var reversePointStr = offlineStore['location_lat'] + ',' + offlineStore['location_long'];
        var pointName = offlineStore['alias'];
        offlineStore['mapImg'] = 'http://api.map.baidu.com/staticimage?width=400&height=200&center=' + pointStr + '&markers=' + pointName + '|' + pointStr + '&zoom=18&markerStyles=l,A,0xff0000';
        offlineStore['mapDetailUrl'] = 'http://api.map.baidu.com/marker?location=' + reversePointStr + '&title=' + pointName + '&content=' + pointName + '&output=html';
        //http://api.map.baidu.com/marker?location=39.916979519873,116.41004950566&title=我的位置&content=百度奎科大厦&output=html
      }
      $vm.currentOfflineStore = offlineStore;
      $vm.showOfflineStoreDetailView = true;
      $vm.chooseOfflineStoreView = false;
      $vm.showShareDetailView = false;
      $vm.showBalanceView = false;
      //showMap($vm.currentOfflineStore);
    }

    function changeOfflineStoreArea(code) {
      $vm.currentAreaCode = code;
    }
  }

  function WesharesViewCtrl($scope, $rootScope, $log, $http, $templateCache, $timeout, $filter, $window, Utils, staticFilePath, ShareOrder) {
    var vm = this;
    vm.staticFilePath = staticFilePath;
    vm.showShareDetailView = true;
    vm.subShareTipTxt = '+关注';
    vm.faqTipText = '联系团长';
    vm.showUnReadMark = false;
    vm.readMoreBtnText = '全文';
    vm.hideMoreShareInfo = false;
    vm.shouldShowReadMoreBtn = false;
    vm.startNewGroupShare = false;
    vm.chooseOfflineAddress = null;
    vm.isGroupShareType = false;
    ChooseOfflineStore(vm, $log, $http, $templateCache, $timeout);
    vm.statusMap = {
      0: '进行中',
      1: '已截止'
    };
    vm.shipTypes = {
      "101": "申通",
      "102": "圆通",
      "103": "韵达",
      "104": "顺丰",
      "105": "EMS",
      "106": "邮政包裹",
      "107": "天天",
      "108": "汇通",
      "109": "中通",
      "110": "全一",
      "111": "宅急送",
      "112": "全峰",
      "113": "快捷",
      "115": "城际快递",
      "132": "优速",
      "133": "增益快递",
      "134": "万家康",
      "135": "京东快递",
      "136": "德邦快递",
      "137": "自提",
      "138": "百富达",
      "139": "黑狗",
      "140": "E快送",
      "141": "国通快递",
      "142": "人人快递"
    };
    vm.commentData = {};
    vm.submitTempCommentData = {};
    vm.viewImage = viewImage;
    vm.increaseProductNum = increaseProductNum;
    vm.decreaseProductNum = decreaseProductNum;
    vm.getOrderDisplayName = getOrderDisplayName;
    vm.isCreator = isCreator;
    vm.isProxy = isProxy;
    vm.isOwner = isOwner;
    vm.isOrderReceived = isOrderReceived;
    vm.getConsigneeInfo = getConsigneeInfo;
    vm.validateAddress = validateAddress;
    vm.validateProducts = validateProducts;
    vm.buyProducts = buyProducts;
    vm.startGroupShare = startGroupShare;
    vm.validateMobile = validateMobile;
    vm.validateUserName = validateUserName;
    vm.validateUserAddress = validateUserAddress;
    vm.validateOrderData = validateOrderData;
    vm.submitOrder = submitOrder;
    vm.confirmReceived = confirmReceived;
    vm.toUserShareInfo = toUserShareInfo;
    vm.toShareDetailView = toShareDetailView;
    vm.checkProductNum = checkProductNum;
    vm.getProductLeftNum = getProductLeftNum;
    vm.toShareOrderList = toShareOrderList;
    vm.createMyShare = createMyShare;
    vm.toUpdate = toUpdate;
    vm.stopShare = stopShare;
    vm.showShareDetail = showShareDetail;
    vm.calOrderTotalPrice = calOrderTotalPrice;
    vm.getStatusName = getStatusName;
    vm.getShipCode = getShipCode;
    vm.isShowShipCode = isShowShipCode;
    vm.showCommentDialog = showCommentDialog;
    vm.submitComment = submitComment;
    vm.getOrderComment = getOrderComment;
    vm.getReplyComments = getReplyComments;
    vm.showReplies = showReplies;
    vm.reloadCommentData = reloadCommentData;
    vm.showCommentListDialog = showCommentListDialog;
    vm.getOrderCommentLength = getOrderCommentLength;
    vm.initWeshareData = initWeshareData;
    vm.sortOrders = sortOrders;
    vm.combineShareBuyData = combineShareBuyData;
    vm.closeCommentDialog = closeCommentDialog;
    vm.notifyUserToComment = notifyUserToComment;
    vm.loadSharerAllComments = loadSharerAllComments;
    vm.loadOrderDetail = loadOrderDetail;
    vm.getFormatDate = getFormatDate;
    vm.notifyFans = notifyFans;
    vm.notifyType = notifyType;
    vm.sendNewShareMsg = sendNewShareMsg;
    vm.sendNotifyShareMsg = sendNotifyShareMsg;
    vm.validNotifyMsgContent = validNotifyMsgContent;
    vm.subSharer = subSharer;
    vm.pageLoaded = pageLoaded;
    vm.getRecommendInfo = getRecommendInfo;
    vm.isCurrentUserRecommend = isCurrentUserRecommend;
    vm.toRecommendUserInfo = toRecommendUserInfo;
    vm.cloneShare = cloneShare;
    vm.resetNotifyContent = resetNotifyContent;
    vm.defaultNotifyHasBuyMsgContent = defaultNotifyHasBuyMsgContent;
    vm.closeRecommendDialog = closeRecommendDialog;
    vm.submitRecommend = submitRecommend;
    vm.validRecommendContent = validRecommendContent;
    vm.checkHasUnRead = checkHasUnRead;
    vm.getProcessPrepaidStatus = getProcessPrepaidStatus;
    vm.handleReadMoreBtn = handleReadMoreBtn;
    vm.checkShareInfoHeight = checkShareInfoHeight;
    vm.toggleTag = toggleTag;
    vm.supportGroupBuy = supportGroupBuy;
    vm.offlineAddressData = null;
    vm.loadOfflineAddressData = loadOfflineAddressData;
    vm.setShipFee = setShipFee;
    vm.newGroupShare = newGroupShare;
    vm.redirectFaq = redirectFaq;
    vm.checkUserHasStartGroupShare = checkUserHasStartGroupShare;
    vm.chatToUser = chatToUser;
    vm.childShareDetail = null;
    vm.currentUserOrderCount = 0;
    vm.shareOrderCount = 0;
    function pageLoaded() {
      $rootScope.loadingPage = false;
    }

    activate();
    function activate() {
      vm.initWeshareData();
    }

    function getDate(strDate) {
      var date = eval('new Date(' + strDate.replace(/\d+(?=-[^-]+$)/,
        function (a) {
          return parseInt(a, 10) - 1;
        }).match(/\d+/g) + ')');
      return date;
    }

    function getFormatDate(dateStr) {
      var date = getDate(dateStr);
      var formatedDate = $filter('date')(date, 'MM-dd HH:mm');
      return formatedDate;
    }

    function loadSharerAllComments(sharer_id) {
      $http({method: 'GET', url: '/weshares/load_share_comments/' + sharer_id + '.json', cache: $templateCache}).
        success(function (data, status) {
          vm.sharerAllComments = data['share_all_comments'];
          vm.sharerAllCommntesUser = data['share_comment_all_users'];
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    /**
     * 获取线下自提点和简单的购买数据购买数据
     * @param share_id
     */
    function loadOfflineAddressData(share_id) {
      $http({method: 'GET', url: '/weshares/get_offline_address_detail/' + share_id + '.json', cache: $templateCache}).
        success(function (data, status) {
          vm.offlineAddressData = data;
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function checkUserHasStartGroupShare(userId) {
      for (key in vm.childShareDetail) {
        var itemShare = vm.childShareDetail[key];
        if (itemShare['creator'] == userId) {
          return true;
        }
      }
      return false;
    }

    function chatToUser(userId) {
      if (userId == vm.currentUser.id) {
        window.location.href = '/share_faq/faq_list/' + vm.weshare.id + '/' + userId;
        return;
      }
      window.location.href = '/share_faq/faq/' + vm.weshare.id + '/' + userId;
    }

    function loadOrderDetail(share_id) {
      var fromType = angular.element(document.getElementById('weshareView')).attr('data-from-type');
      //first share
      var initSharedOfferId = angular.element(document.getElementById('weshareView')).attr('data-shared-offer');
      var followSharedType = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-type');
      var followSharedNum = angular.element(document.getElementById('sharedOfferResult')).attr('data-shared-coupon-num');
      vm.sharedOfferId = initSharedOfferId;
      if (initSharedOfferId) {
        vm.isSharePacket = true;
      }
      $http({method: 'GET', url: '/weshares/get_share_user_order_and_child_share/' + share_id + '.json', cache: $templateCache}).
        success(function (data, status) {
          vm.ordersDetail = data['ordersDetail'];
          vm.childShareDetail = data['childShareData']['child_share_data'];
          vm.childShareDetailUsers = data['childShareData']['child_share_user_infos'];
          //vm.shipTypes = data['ordersDetail']['ship_types'];
          vm.rebateLogs = data['ordersDetail']['rebate_logs'];
          //vm.sortOrders();
          vm.combineShareBuyData();
          setWeiXinShareParams();
          //from paid done
          if (fromType == 1) {
            if (_.isEmpty(initSharedOfferId)) {
              vm.showNotifyShareDialog = true;
              vm.showLayer = true;
            } else {
              //check is new user buy it
              var userInfo = vm.ordersDetail.users[vm.currentUser.id];
              if (userInfo) {
                vm.showNotifyShareOfferDialog = true;
                vm.sharedOfferMsg = '恭喜发财，大吉大利！';
                vm.showLayer = true;
              }
            }
          }
          //follow share
          if (followSharedType) {
            if (followSharedType == 'got') {
              vm.showNotifyGetPacketDialog = true;
              vm.getPacketNum = followSharedNum + '元';
              vm.showLayer = true;
              $timeout(function () {
                vm.showLayer = false;
                vm.showNotifyGetPacketDialog = false;
              }, 10000);
            }
          }
          vm.shareOrder = new ShareOrder();
          vm.shareOrder.shareId = share_id;
        }).
        error(function (data, status) {
          $log.log(data);
        });
    }

    function initWeshareData() {
      var rebateLogId = angular.element(document.getElementById('weshareView')).attr('data-rebate-log-id');
      var recommendUserId = angular.element(document.getElementById('weshareView')).attr('data-recommend-id');
      vm.rebateLogId = rebateLogId || 0;
      vm.recommendUserId = recommendUserId || 0;
      var weshareId = angular.element(document.getElementById('weshareView')).attr('data-weshare-id');
      vm.weshare = {};
      vm.orderTotalPrice = 0;
      $scope.$watch('vm.selectShipType', function (val) {
        if (val != -1) {
          vm.chooseShipType = false;
        }
      });
      $scope.$watchCollection('vm.checkedOfflineStore', function (val) {
        if (val) {
          vm.chooseOfflineStoreError = false;
        }
      });
      $http({method: 'GET', url: '/weshares/detail/' + weshareId + '.json', cache: $templateCache}).
        success(function (data, status) {
          vm.weshare = data['weshare'];
          if (vm.weshare.type == 1) {
            vm.isGroupShareType = true;
          }
          vm.toggleState = {0: {open: true, statusText: '收起'}};
          _.each(vm.weshare.tags, function (value, key) {
            vm.toggleState[key] = {
              open: true,
              statusText: '收起'
            };
          });
          vm.commentData = data['comment_data'];
          vm.orderComments = vm.commentData['order_comments'];
          if (vm.weshare.addresses && vm.weshare.addresses.length == 1) {
            vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
          } else if (vm.weshare.addresses && vm.weshare.addresses.length > 1) {
            vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
            vm.weshare.selectedAddressId = -1;
          }
          vm.isManage = data['is_manage'];
          vm.canEditShare = data['can_edit_share'];
          vm.recommendData = data['recommendData'];
          vm.currentUser = data['current_user'] || {};
          vm.weixinInfo = data['weixininfo'];
          vm.consignee = data['consignee'];
          vm.myCoupons = data['my_coupons'];
          vm.weshareSettings = data['weshare_ship_settings'];
          vm.supportPysZiti = data['support_pys_ziti'];
          vm.selectShipType = getSelectTypeDefaultVal();
          vm.userSubStatus = data['sub_status'];
          vm.shareOrderCount = data['share_order_count'];
          vm.submitRecommendData = {};
          vm.submitRecommendData.recommend_content = vm.weshare.creator.nickname + '我认识，很靠谱！';
          vm.submitRecommendData.recommend_user = vm.currentUser.id;
          vm.submitRecommendData.recommend_share = vm.weshare.id;
          if (vm.consignee && vm.consignee.offlineStore) {
            vm.checkedOfflineStore = vm.consignee.offlineStore;
          }
          if (vm.myCoupons) {
            vm.useCouponId = vm.myCoupons.CouponItem.id;
            vm.userCouponReduce = vm.myCoupons.Coupon.reduced_price;
          }
          vm.userShareSummery = data['user_share_summery'];
          if (vm.consignee) {
            vm.buyerName = vm.consignee.name;
            vm.buyerMobilePhone = vm.consignee.mobilephone;
            vm.buyerAddress = vm.consignee.address;
            vm.buyerPatchAddress = vm.consignee.remark_address;
          }
          if (vm.isCreator()) {
            vm.faqTipText = '反馈消息';
          }
          vm.checkShareInfoHeight();
          //load all comments
          vm.loadOrderDetail(weshareId);
        }).
        error(function (data, status) {
          $log.log(data);
        });
      vm.checkHasUnRead();
    }

    /**
     * 订单数据和拼团数据组合
     */
    function combineShareBuyData() {
      var insertIndex = vm.currentUserOrderCount;
      for (childShareItem in vm.childShareDetail) {
        vm.ordersDetail.orders.splice(insertIndex, 0, vm.childShareDetail[childShareItem]);
        insertIndex++;
      }
    }

    function sortOrders() {
      if (!vm.isCreator()) {
        vm.ordersDetail.orders = _.sortBy(vm.ordersDetail.orders, function (order) {
          if (order.status == 9 && order.creator == vm.currentUser.id) {
            vm.currentUserOrderCount = vm.currentUserOrderCount + 1;
            return -2147483646;
          } else if (order.creator == vm.currentUser.id) {
            vm.currentUserOrderCount = vm.currentUserOrderCount + 1;
            return -2147483647;
          } else {
            return order.id;
          }
        });
      }
    }

    function getSelectTypeDefaultVal() {
      if (vm.weshareSettings.pin_tuan && vm.weshareSettings.pin_tuan.status == 1) {
        vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
        return 3;
      }
      if (vm.weshareSettings.kuai_di && vm.weshareSettings.kuai_di.status == 1) {
        vm.shipFee = vm.weshareSettings.kuai_di.ship_fee;
        return 0;
      }
      if (vm.weshareSettings.self_ziti && vm.weshareSettings.self_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.self_ziti.ship_fee;
        return 1;
      }
      if (vm.weshareSettings.pys_ziti && vm.weshareSettings.pys_ziti.status == 1) {
        vm.shipFee = vm.weshareSettings.pys_ziti.ship_fee;
        return 2;
      }
      return -1;
    }

    function isProxy() {
      return !_.isEmpty(vm.currentUser) && vm.currentUser.is_proxy == 1
    }

    function isCreator() {
      return !_.isEmpty(vm.currentUser) && vm.currentUser.id == vm.weshare.creator.id;
    }

    function isOwner(order) {
      //note may be a child share item
      return !_.isEmpty(vm.currentUser) && !_.isEmpty(order) && vm.currentUser.id == order.creator;
    }

    function isOrderReceived(order) {
      return !_.isEmpty(order) && order.status == 3;
    }

    function getOrderDisplayName(orderId) {
      var carts = [];
      if(vm.ordersDetail&&vm.ordersDetail.order_cart_map&&vm.ordersDetail.order_cart_map[orderId]){
        carts = vm.ordersDetail.order_cart_map[orderId];
      }else{
        carts = vm.shareOrder.order_cart_map[orderId];
      }
      return _.map(carts, function (cart) {
        return cart.name + 'X' + cart.num;
      }).join(', ');
    }

    function redirectFaq() {
      if (vm.isCreator()) {
        window.location.href = '/share_faq/faq_list/' + vm.weshare.id + '/' + vm.currentUser.id;
      } else {
        window.location.href = '/share_faq/faq/' + vm.weshare.id + '/' + vm.weshare.creator.id;
      }
    }

    function showShareDetail() {
      vm.showOfflineStoreDetailView = false;
      vm.chooseOfflineStoreView = false;
      vm.showBalanceView = false;
      vm.showStartGroupShareView = false;
      vm.showShareDetailView = true;
      vm.startNewGroupShare = false;
    }

    function toShareDetailView($share_id) {
      window.location.href = '/weshares/view/' + $share_id;
    }

    function toUserShareInfo($uid) {
      window.location.href = '/weshares/user_share_info/' + $uid;
    }

    function viewImage(url) {
      wx.previewImage({
        current: url,
        urls: vm.weshare.images
      });
    }

    function getConsigneeInfo(order) {
      if (_.isEmpty(order)) {
        return '';
      }
      return _.reject([order.consignee_name, order.consignee_mobilephone], function (e) {
        return _.isEmpty(e)
      }).join(',');
    }

    function calOrderTotalPrice() {
      var submit_products = [];
      _.each(vm.weshare.products, function (products) {
        _.each(products, function (product) {
          if (product.num && (product.num > 0)) {
            submit_products.push(product);
          }
        });
      });
      var totalPrice = 0;
      _.each(submit_products, function (product) {
        totalPrice += product.price * product.num;
      });
      if (totalPrice != 0) {
        if (vm.userCouponReduce) {
          totalPrice -= vm.userCouponReduce;
        }
        vm.shipFee = parseInt(getShipFee());
        vm.shipSetId = getShipSetId();
        totalPrice += vm.shipFee;
        vm.orderTotalPrice = totalPrice / 100;
      } else {
        vm.orderTotalPrice = 0;
      }
    }

    function getOrderComment(order_id) {
      if (vm.commentData['order_comments']) {
        return vm.commentData['order_comments'][order_id];
      }
      return null;
    }

    function getOrderCommentLength() {
      if (vm.sharerAllComments) {
        return vm.sharerAllComments.length;
      }
      return 0;
    }

    function getReplyComments(comment_id) {
      return vm.commentData['comment_replies'][comment_id];
    }

    function showReplies(comment_id) {
      var replies = vm.commentData['comment_replies'][comment_id];
      if (!replies || replies.length == 0) {
        return false;
      }
      return true;
    }

    function getRecommendInfo(order) {
      var recommendId = 0;
      var recommend = '';
      if(vm.rebateLogs[order['cate_id']]){
        recommendId = vm.rebateLogs[order['cate_id']];
        recommend = vm.ordersDetail['users'][recommendId]['nickname'];
      }else{
        recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
        recommend = vm.shareOrder['users'][recommendId]['nickname'];
      }
      return recommend + '推荐';
    }

    function isCurrentUserRecommend(order) {
      if (vm.isCreator()) {
        return true;
      }
      if (vm.currentUser && vm.currentUser['is_proxy'] == 0) {
        return false;
      }
      var recommendId = 0;
      if(vm.rebateLogs&&vm.rebateLogs[order['cate_id']]){
         recommendId = vm.rebateLogs[order['cate_id']];
      }else{
        recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
      }
      if (vm.currentUser && vm.currentUser['id'] == recommendId) {
        return true;
      }
      return false;
    }

    function toRecommendUserInfo(order) {
      var recommendId = 0;
      if(vm.rebateLogs[order['cate_id']]){
        recommendId = vm.rebateLogs[order['cate_id']];
      }else{
        recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
      }
      window.location.href = '/weshares/user_share_info/' + recommendId;
    }

    function getShipSetId() {
      if (vm.selectShipType == 0) {
        return vm.weshareSettings.kuai_di.id;
      }
      if (vm.selectShipType == 1) {
        return vm.weshareSettings.self_ziti.id;
      }
      if (vm.selectShipType == 2) {
        return vm.weshareSettings.pys_ziti.id;
      }
      if (vm.selectShipType == 3) {
        return vm.weshareSettings.pin_tuan.id;
      }
    }

    function setShipFee() {
      if (vm.selectShipType == 0) {
        vm.shipFee = vm.weshareSettings.kuai_di.ship_fee;
      }
      if (vm.selectShipType == 1) {
        vm.shipFee = vm.weshareSettings.self_ziti.ship_fee;
      }
      if (vm.selectShipType == 2) {
        vm.shipFee = vm.weshareSettings.pys_ziti.ship_fee;
      }
      if (vm.selectShipType == 3) {
        vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
      }
    }

    function getShipFee() {
      if (vm.selectShipType == 0) {
        return vm.weshareSettings.kuai_di.ship_fee;
      }
      if (vm.selectShipType == 1) {
        return vm.weshareSettings.self_ziti.ship_fee;
      }
      if (vm.selectShipType == 2) {
        return vm.weshareSettings.pys_ziti.ship_fee;
      }
      if (vm.selectShipType == 3) {
        return vm.weshareSettings.pin_tuan.ship_fee;
      }
    }

    function increaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      //check product is reach limit
      if (product.limit > 0 && product.num == product.limit) {
        alert('亲，最多可购' + product.limit + '份.');
      } else {
        product.num = product.num + 1;
      }
      calOrderTotalPrice();
      vm.validateProducts();
    }

    function decreaseProductNum(product) {
      if (!Utils.isNumber(product.num)) {
        product.num = 0;
      }
      if (product.num >= 1) {
        product.num = product.num - 1;
      }
      calOrderTotalPrice();
      vm.validateProducts();
    }

    function validateMobile() {
      vm.buyerMobilePhoneHasError = !Utils.isMobileValid(vm.buyerMobilePhone);
      return vm.buyerMobilePhoneHasError;
    }

    function validateUserName() {
      vm.usernameHasError = _.isEmpty(vm.buyerName);
      return vm.usernameHasError;
    }

    function validateAddress() {
      vm.addressHasError = vm.weshare.addresses.length > 0 && vm.weshare.selectedAddressId == -1;
      return vm.addressHasError;
    }

    function validateProducts() {
      vm.productsHasError = _.all(vm.weshare.products, function (products) {
        return _.all(products, function (product) {
          return !product.num || product.num <= 0;
        });
      });
      return vm.productsHasError;
    }


    function validateUserAddress() {
      vm.userAddressHasError = _.isEmpty(vm.buyerAddress);
      return vm.userAddressHasError;
    }

    function getProductLeftNum(product) {
      if (vm.ordersDetail && vm.ordersDetail['summery'].details[product.id]) {
        var product_buy_num = vm.ordersDetail['summery'].details[product.id]['num'];
        var store_num = product.store;
        return store_num - product_buy_num;
      }
      return product.store;
    }

    function checkProductNum(product) {
      var store_num = product.store;
      if (store_num == 0) {
        return true;
      }
      if (vm.ordersDetail && vm.ordersDetail['summery'].details[product.id]) {
        var product_buy_num = vm.ordersDetail['summery'].details[product.id]['num'];
        return product_buy_num < store_num;
      }
      return true;
    }

    function buyProducts() {
      vm.showShareDetailView = false;
      vm.showBalanceView = true;
      vm.chooseShipType = false;
    }

    function startGroupShare() {
      if (vm.checkUserHasStartGroupShare(vm.currentUser.id)) {
        alert('您已经发起一次拼团了');
        return false;
      }
      vm.selectShipType = 3;
      vm.shipFee = vm.weshareSettings.pin_tuan.ship_fee;
      vm.showGroupShareTipDialog = false;
      vm.showLayer = false;
      vm.showShareDetailView = false;
      vm.showStartGroupShareView = true;
      vm.chooseShipType = false;
      vm.startNewGroupShare = true;
    }

    function submitOrder(paymentType) {
      if (!vm.validateOrderData()) {
        return false;
      }
      var submit_products = [];
      _.each(vm.weshare.products, function (products) {
        _.each(products, function (product) {
          if (product.num && (product.num > 0)) {
            submit_products.push(product);
          }
        });
      });
      submit_products = _.map(submit_products, function (product) {
        return {id: product.id, num: product.num};
      });
      vm.shipFee = vm.shipFee || 0;
      var ship_info = {
        ship_type: vm.selectShipType,
        ship_fee: vm.shipFee,
        ship_set_id: vm.shipSetId
      };
      //self ziti
      if (vm.selectShipType == 1) {
        ship_info['address_id'] = vm.weshare.selectedAddressId;
      }
      //offline store
      if (vm.selectShipType == 2) {
        ship_info['address_id'] = vm.checkedOfflineStore.id;
      }
      //邻里拼团
      if (vm.selectShipType == 3 && !vm.startNewGroupShare) {
        if (!vm.chooseOfflineAddress) {
          vm.offlineAddressHasError = true;
          return;
        }
        vm.buyerAddress = vm.childShareDetail[vm.chooseOfflineAddress]['address'];
        ship_info['weshare_id'] = vm.chooseOfflineAddress;
      }
      var orderData = {
        weshare_id: vm.weshare.id,
        rebate_log_id: vm.rebateLogId,
        products: submit_products,
        ship_info: ship_info,
        remark: vm.buyerRemark,
        is_group_share: vm.isGroupShareType,
        start_new_group_share: vm.startNewGroupShare,
        buyer: {
          name: vm.buyerName,
          mobilephone: vm.buyerMobilePhone,
          address: vm.buyerAddress,
          patchAddress: vm.buyerPatchAddress
        }
      };
      if (vm.useCouponId) {
        orderData['coupon_id'] = vm.useCouponId;
      }
      if (vm.submitProcessing) {
        return;
      }
      vm.submitProcessing = true;
      $http.post('/weshares/makeOrder', orderData).success(function (data) {
        if (data.success) {
          //pay
          window.location.href = '/weshares/pay/' + data.orderId + '/' + paymentType;
        } else {
          vm.submitProcessing = false;
          vm.showBuyingDialog = false;
          vm.showLayer = false;
          if (data['reason']) {
            alert(data['reason']);
          } else {
            alert('提交失败.请联系客服..');
          }
        }
      }).error(function () {
        vm.submitProcessing = false;
      });
    }

    //发起拼团
    function newGroupShare() {
      if (vm.checkUserHasStartGroupShare(vm.currentUser.id)) {
        alert('您已经发起一次拼团了');
        return false;
      }
      //valid address
      if (vm.validateUserAddress()) {
        return false;
      }
      var cloneShareData = {
        weshare_id: vm.weshare.id,
        address: vm.buyerAddress,
        business_remark: vm.buyerRemark
      };
      $http.post('/weshares/start_new_group_share', cloneShareData).success(function (data) {
        if (data.success) {
          window.location.href = '/weshares/view/' + data.shareId;
        } else {
          alert('提交失败.请联系客服..');
        }
      }).error(function () {
      });
    }

    function cloneShare() {
      vm.cloneShareProcessing = true;
      $http.post('/weshares/cloneShare/' + vm.weshare.id).success(function (data) {
        if (data.success) {
          //redirect view
          window.location.href = '/weshares/view/' + data['shareId'];
        } else {
          vm.cloneShareProcessing = false;
          if (data['reason']) {
            alert(data['reason']);
          } else {
            alert('提交失败');
          }
        }
      }).error(function () {
        vm.cloneShareProcessing = false;
      });
    }

    function reloadCommentData() {
      $http({method: 'GET', url: '/weshares/loadComment/' + vm.weshare.id + '.json'}).
        success(function (data) {
          vm.commentData = data;
        }).
        error(function (data) {
          $log.log(data);
        });
    }

    function notifyUserToComment(order) {
      vm.submitTempCommentData.order_id = order.id;
      vm.submitTempCommentData.reply_comment_id = 0;
      vm.submitTempCommentData.share_id = vm.weshare.id;
      vm.submitComment();
      order.notify = true;
    }

    function submitComment() {
      $http.post('/weshares/comment', vm.submitTempCommentData).success(function (data) {
        if (data.success) {
          if (data.type == 'notify') {
            $window.alert('已经通知TA');
            return true;
          }
          var order_id = data['order_id'];
          vm.reloadCommentData();
          if (vm.commentOrder.id == order_id && vm.commentOrder.status == 3) {
            vm.commentOrder.status = 9;
          }
        } else {
          alert('提交失败');
        }
      }).error(function () {
        alert('提交失败');
      });
      vm.closeCommentDialog();
    }

    function resetNotifyContent() {
      if (vm.sendNotifyType == 0) {
        vm.notify.content = vm.defaultNotifyHasBuyMsgContent();
      } else {
        vm.notify.content = '';
      }
    }

    function defaultNotifyHasBuyMsgContent() {
      var msgContent = '';
      if (Object.keys(vm['ordersDetail']['users']).length > 10) {
        var usersCount = Object.keys(vm['ordersDetail']['users']).length;
        var index = 0;
        for (var userId in vm['ordersDetail']['users']) {
          var user = vm['ordersDetail']['users'][userId];
          index++;
          if (index > 10) {
            break
          }
          if (index == 10) {
            msgContent = msgContent + user['nickname'];
          } else {
            msgContent = msgContent + user['nickname'] + '，';
          }
        }
        msgContent = msgContent + '...等' + usersCount + '人都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
      } else {
        msgContent = _.reduce(vm.ordersDetail.users, function (memo, user) {
          return memo + user['nickname'] + '，';
        }, '');
        msgContent = msgContent + '都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
      }
      return msgContent;
    }

    function notifyFans() {
      vm.showNotifyView = true;
      vm.showLayer = true;
      vm.sendNotifyType = 0;
      vm.notify = {};
      vm.notify.type = vm.sendNotifyType;
      vm.notify.content = vm.defaultNotifyHasBuyMsgContent();
    }

    function notifyType() {
      if (vm.ordersDetail) {
        if (vm.ordersDetail.orders && vm.ordersDetail.orders.length > 0) {
          return 1;
        }
      }
      return 0;
    }

    function sendNewShareMsg() {
      if (confirm('是否要发送消息，发送次数过多会对用户形成骚扰?')) {
        $http({
          method: 'GET',
          url: '/weshares/send_new_share_msg/' + vm.weshare.id
        }).success(function (data) {
          // With the data succesfully returned, call our callback
          if (data['success']) {
            alert('发送成功');
          }
        }).error(function () {
          alert("发送失败,请联系朋友说客服。。");
        });
      }
    }

    function subSharer() {
      if (vm.hasProcessSubSharer) {
        return;
      }
      $http({
        method: 'GET',
        url: '/weshares/subscribe_sharer/' + vm.weshare.creator.id + '/' + vm.currentUser.id + '/1/' + vm.weshare.id + '.json'
      }).success(function (data) {
        // With the data succesfully returned, call our callback
        if (data['success']) {
          vm.hasProcessSubSharer = true;
          vm.subShareTipTxt = '已关注';
        } else {
          alert('请先关注朋友说微信公众号！');
          window.location.href = "http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400154588&idx=1&sn=5568f4566698bacbc5a1f5ffeab4ccc3";
        }
      }).error(function () {
        vm.hasProcessSubSharer = false;
      });
    }

    function validNotifyMsgContent() {
      if (_.isEmpty(vm.notify.content)) {
        vm.notifyMsgHasError = true;
        return;
      }
      vm.notifyMsgHasError = false;
    }

    function sendNotifyShareMsg() {
      if (_.isEmpty(vm.notify.content)) {
        vm.notifyMsgHasError = true;
        return;
      }
      vm.notifyMsgHasError = false;
      if (confirm('是否要发送消息，发送次数过多会对用户形成骚扰?')) {
        $http({
          method: 'POST',
          url: '/weshares/send_buy_percent_msg/' + vm.weshare.id,
          data: {content: vm.notify.content, type: vm.sendNotifyType}
        }).success(function (data) {
          // With the data succesfully returned, call our callback
          if (data['success']) {
            vm.showNotifyView = false;
            vm.showLayer = false;
          }
        }).error(function () {
          alert("发送失败,请联系朋友说客服。。");
        });
      }
    }

    function showCommentListDialog() {
      vm.loadSharerAllComments(vm.weshare.creator.id);
      vm.showCommentListDialogView = true;
      vm.showLayer = true;
    }

    function closeCommentDialog() {
      vm.showCommentingDialog = false;
      vm.showLayer = false;
      vm.submitTempCommentData = {};
    }

    function showCommentDialog(order, comment) {
      vm.showCommentingDialog = true;
      vm.showLayer = true;
      var reply_comment_id = 0;
      var comment_tip_info = '';
      if (comment) {
        reply_comment_id = comment.id || 0;
        var reply_username = comment.username;
        if (comment.plain_username) {
          reply_username = comment.plain_username;
        }
        if (reply_username == vm.currentUser.nickname) {
          comment_tip_info = '爱心评价';
        } else {
          comment_tip_info = '回复' + reply_username + '：';
        }
      } else {
        //check is creator
        if (vm.currentUser.id == vm.weshare.creator.id) {
          var order_username = vm.ordersDetail.users[order.creator]['nickname'];
          comment_tip_info = '回复' + order_username + '说：';
        } else {
          comment_tip_info = '回复' + vm.weshare.creator.nickname + '说：';
        }
      }
      vm.submitTempCommentData = {};
      vm.commentTipInfo = comment_tip_info;
      vm.commentOrder = order;
      vm.submitTempCommentData.order_id = order.id;
      vm.submitTempCommentData.reply_comment_id = reply_comment_id;
      vm.submitTempCommentData.share_id = vm.weshare.id;
    }

    function getProcessPrepaidStatus(status) {
      if (status == 25) {
        return '金额待定';
      }
      if (status == 26) {
        return '待补款';
      }
      if (status == 27) {
        return '已补款';
      }
      if (status == 28) {
        return '待退差价';
      }
      if (status == 29) {
        return '差价已退';
      }
    }

    function getStatusName(status, orderType) {
      if (status == 1) {
        return '待发货';
      }
      if (status == 2) {
        if (orderType == 'kuai_di') {
          return '待签收';
        }
        return '待取货';
      }
      if (status == 3) {
        return '待评价';
      }
      return '已完成';
    }

    function validateOrderData() {
      var productsHasError = vm.validateProducts();
      if (productsHasError) {
        alert('请输入商品数量');
        return;
      }
      if (vm.selectShipType == -1) {
        alert('请选择快递方式');
        vm.chooseShipType = true;
        return false;
      }
      if (vm.selectShipType == 2 && !vm.checkedOfflineStore) {
        alert('请选择自提点');
        vm.chooseOfflineStoreError = true;
        return false;
      }
      vm.validateUserName();
      vm.validateMobile();
      if (vm.buyerMobilePhoneHasError || vm.usernameHasError) {
        return false;
      }
      //kuai di
      if (vm.selectShipType == 0) {
        if (vm.validateUserAddress()) {
          return false;
        }
      }
      //self ziti
      if (vm.selectShipType == 1) {
        var addressHasError = vm.validateAddress();
        if (addressHasError) {
          return false;
        }
      }
      return true;
    }

    function confirmReceived(order) {
      if (_.isEmpty(order) || vm.isOrderReceived(order)) {
        return;
      }
      $http.post('/weshares/confirmReceived/' + order.id).success(function (data) {
        if (data.success) {
          order.status = 3;
        }
        else {
        }
      }).error(function (e) {
        $log.log(e);
      });
    }

    function stopShare() {
      var confirmResult = window.confirm('是否截止分享?');
      if (!confirmResult) {
        return false;
      }
      $http.post('/weshares/stopShare/' + vm.weshare.id).success(function (data) {
        if (data.success) {
          vm.weshare.status = 1;
        }
      }).error(function (e) {
        $log.log(e);
      });
    }

    function createMyShare() {
      var wx_article = 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=209712305&idx=1&sn=ddb8431d97100d7e6305c2bc46d9ae75#rd';
      if (vm.currentUser['mobilephone'] && vm.currentUser['payment']) {
        window.location.href = '/weshares/add';
      } else {
        window.location.href = wx_article;
      }
    }

    function toShareOrderList() {
      window.location.href = '/weshares/share_order_list/' + vm.weshare.id;
    }

    function toUpdate() {
      window.location.href = '/weshares/update/' + vm.weshare.id;
    }

    function getShipCode(order) {
      var shipMark = order['ship_mark'];
      var code = order['ship_code'];
      if (shipMark == 'kuai_di') {
        var ship_company = order['ship_type'];
        return vm.shipTypes[ship_company] + ': ' + code;
      }
      if (shipMark == 'pys_ziti') {
        return '提货码: ' + code;
      }
      return '';
    }

    function closeRecommendDialog() {
      vm.showShareDialog = true;
      vm.showRecommendDialog = false;
    }

    function submitRecommend() {
      if (vm.validRecommendContent()) {
        return false;
      }
      $http.post('/weshares/recommend', vm.submitRecommendData).success(function (data) {

      }).error(function (e) {
        $log.log(e);
      });
      vm.closeRecommendDialog();
    }

    function validRecommendContent() {
      vm.recommendContentHasError = false;
      if (_.isEmpty(vm.submitRecommendData.recommend_content)) {
        vm.recommendContentHasError = true;
      }
      return vm.recommendContentHasError;
    }

    function checkHasUnRead() {
      $http.get('/share_opt/check_opt_has_new.json').success(function (data) {
        if (data['has_new']) {
          vm.showUnReadMark = true;
        }
      });
    }

    function isShowShipCode(order) {
      if (order['ship_mark'] == 'kuai_di' || order['ship_mark'] == 'pys_ziti') {
        if (order.status != 1 && vm.isOwner(order)) {
          return true;
        }
      }
      return false;
    }

    //vm.handleReadMoreBtn = handleReadMoreBtn;
    //vm.checkShareInfoHeight = checkShareInfoHeight;

    function handleReadMoreBtn() {
      vm.hideMoreShareInfo = !vm.hideMoreShareInfo;
      if (vm.hideMoreShareInfo) {
        vm.readMoreBtnText = '全文';
      } else {
        vm.readMoreBtnText = '收起';
      }
    }

    function supportGroupBuy() {
      if (vm.weshareSettings && vm.weshareSettings.pin_tuan && vm.weshareSettings.pin_tuan.status == 1) {
        return true;
      }
      return false;
    }

    function toggleTag(tag) {
      var currentToggleState = vm.toggleState[tag];
      currentToggleState['open'] = !currentToggleState['open'];
      currentToggleState['statusText'] = currentToggleState['open'] ? '收起' : '展开';
    }

    function checkShareInfoHeight() {
      vm.shareDescInfoElement = angular.element(document.getElementById('share-description'));
      vm.shareDescInfoElement.ready(function () {
        if (vm.shareDescInfoElement[0]) {
          var height = vm.shareDescInfoElement[0].offsetHeight;
          if (height > 65) {
            vm.shouldShowReadMoreBtn = true;
            vm.hideMoreShareInfo = true;
          } else {
            vm.shouldShowReadMoreBtn = false;
            vm.hideMoreShareInfo = false;
          }
        }
      });
    }

    function setWeiXinShareParams() {
      var url = 'http://www.tongshijia.com/weshares/view/' + vm.weshare.id;
      //creator
      var to_timeline_title = '';
      var to_friend_title = '';
      var imgUrl = '';
      var desc = '';
      var share_string = 'we_share';
      //member
      var userInfo = vm.ordersDetail.users[vm.currentUser.id];
      if (vm.currentUser.id == vm.weshare.creator.id) {
        to_timeline_title = vm.weshare.creator.nickname + '分享:' + vm.weshare.title;
        to_friend_title = vm.weshare.creator.nickname + '分享:' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
        if (vm.shareOrderCount >= 5) {
          desc += '已经有' + vm.shareOrderCount + '人报名，';
        }
        desc += vm.weshare.description.substr(0,20);
      } else if (userInfo) {
        if (vm.isProxy()) {
          url = url + '?recommend=' + vm.currentUser['id'];
        }
        if (!vm.isProxy() && vm.recommendUserId != 0) {
          url = url + '?recommend=' + vm.recommendUserId;
        }
        to_timeline_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || userInfo.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0,20);
      } else if (vm.currentUser) {
        //default custom
        if (vm.isProxy()) {
          url = url + '?recommend=' + vm.currentUser['id'];
        }
        if (!vm.isProxy() && vm.recommendUserId != 0) {
          url = url + '?recommend=' + vm.recommendUserId;
        }
        to_timeline_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_friend_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.currentUser.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0,20);
      } else {
        to_timeline_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
        to_friend_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
        imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
        desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0,20);
      }
      if (vm.weixinInfo) {
        share_string = vm.weixinInfo.share_string;
      }
      //share packet
      if (vm.isSharePacket && userInfo) {
        url = 'http://www.tongshijia.com/weshares/view/' + vm.weshare.id;
        imgUrl = 'http://www.tongshijia.com/static/weshares/images/share_icon.jpg';
        var title = userInfo.nickname + '报名了' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
        to_timeline_title = title;
        to_friend_title = title;
        url = url + '?shared_offer_id=' + vm.sharedOfferId;
        if (vm.isProxy()) {
          url = url + '&recommend=' + vm.currentUser['id'];
        }
        if (!vm.isProxy() && vm.recommendUserId != 0) {
          url = url + '&recommend=' + vm.recommendUserId;
        }
        desc = vm.weshare.creator.nickname + '我认识，很靠谱！送你一个爱心礼包，一起来参加。';
      }
      var to_friend_link = url;
      var to_timeline_link = url;
      if (wx) {
        wx.ready(function () {
          wx.onMenuShareAppMessage({
            title: to_friend_title,
            desc: desc,
            link: to_friend_link,
            imgUrl: imgUrl,
            success: function () {
              // 用户确认分享后执行的回调函数
              if (share_string != '0') {
                setTimeout(function () {
                  $http.post('/wx_shares/log_share', {trstr: share_string, share_type: "appMsg"}).
                    success(function (data, status, headers, config) {
                      // this callback will be called asynchronously
                      // when the response is available
                    }).
                    error(function (data, status, headers, config) {
                      // called asynchronously if an error occurs
                      // or server returns response with an error status.
                    });
                }, 500);
              }
            }
          });
          wx.onMenuShareTimeline({
            title: to_timeline_title,
            link: to_timeline_link,
            imgUrl: imgUrl,
            success: function () {
              if (share_string != '0') {
                setTimeout(function () {
                  $http.post('/wx_shares/log_share', {trstr: share_string, share_type: "timeline"}).
                    success(function (data, status, headers, config) {
                      // this callback will be called asynchronously
                      // when the response is available
                    }).
                    error(function (data, status, headers, config) {
                      // called asynchronously if an error occurs
                      // or server returns response with an error status.
                    });
                }, 500);
              }
            }
          });
        });
      }
      return;
    }
  }
})(window, window.angular);