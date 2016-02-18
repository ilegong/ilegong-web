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
        $vm.offlineStores = {
            "110101": {
                "7": {
                    "id": "7",
                    "shop_no": "300",
                    "area_id": "110101",
                    "alias": "东直门南小街店",
                    "name": "东城区，东直门南小街20-1，好邻居便利店(300)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.43254",
                    "location_lat": "39.940958",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "14": {
                    "id": "14",
                    "shop_no": "251",
                    "area_id": "110101",
                    "alias": "金宝街金宝汇店",
                    "name": "东城区，金宝街道临时3号，好邻居便利店(251)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.429121",
                    "location_lat": "39.921926",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "15": {
                    "id": "15",
                    "shop_no": "146",
                    "area_id": "110101",
                    "alias": "兴化西里和平里医院店",
                    "name": "东城区，兴华西里2号楼南侧，好邻居便利店(146)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.419989",
                    "location_lat": "39.96611",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "257": {
                    "id": "257",
                    "shop_no": "146",
                    "area_id": "110101",
                    "alias": "好邻居兴化路店",
                    "name": "东城区兴华西里2号楼南侧",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.965882",
                    "location_lat": "116.420416",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "265": {
                    "id": "265",
                    "shop_no": "121",
                    "area_id": "110101",
                    "alias": "好邻居甘家口店",
                    "name": "西城区阜外大街44号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.92837",
                    "location_lat": "116.342501",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "279": {
                    "id": "279",
                    "shop_no": "399",
                    "area_id": "110101",
                    "alias": "和平里医院西分店",
                    "name": "东城区和平里北街18号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.965267",
                    "location_lat": "116.420259",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "289": {
                    "id": "289",
                    "shop_no": "219",
                    "area_id": "110101",
                    "alias": "北京国际大厦店",
                    "name": "好邻居中关村南大街店",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.959402",
                    "location_lat": "116.331593",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "310": {
                    "id": "310",
                    "shop_no": "824",
                    "area_id": "110101",
                    "alias": "安外大街蒋宅口店",
                    "name": "东城区安定门外大街82号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.420932",
                    "location_lat": "39.965267",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "311": {
                    "id": "311",
                    "shop_no": "225",
                    "area_id": "110101",
                    "alias": "好邻居北三环东路店",
                    "name": "海淀区中关村南大街1号友谊宾馆61711房间",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.326571",
                    "location_lat": "39.97204",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "312": {
                    "id": "312",
                    "shop_no": "831",
                    "area_id": "110101",
                    "alias": "安定门上龙西里店",
                    "name": "东城区安定门外上龙西里36号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.412892",
                    "location_lat": "39.959557",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "321": {
                    "id": "321",
                    "shop_no": "381",
                    "area_id": "110101",
                    "alias": "新太仓胡同簋街店",
                    "name": "东城区新太仓胡同1号1号楼一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.42759",
                    "location_lat": "39.946165",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "332": {
                    "id": "332",
                    "shop_no": "234",
                    "area_id": "110101",
                    "alias": "胜古北路西口店",
                    "name": "东城区安定路20号院5号楼102",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.415163",
                    "location_lat": "39.9788",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "350": {
                    "id": "350",
                    "shop_no": "229",
                    "area_id": "110101",
                    "alias": "好邻居都市馨园店",
                    "name": "崇文区兴隆都市馨园地上一层A101",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.42174",
                    "location_lat": "39.901802",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "373": {
                    "id": "373",
                    "shop_no": "258",
                    "area_id": "110101",
                    "alias": "好邻居广义街店",
                    "name": "宣武区广安门内大街311号院2号楼祥龙大厦首层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.363458",
                    "location_lat": "39.896673",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "378": {
                    "id": "378",
                    "shop_no": "265",
                    "area_id": "110101",
                    "alias": "好邻居美术馆后街店",
                    "name": "北京市东城区大佛寺东街1号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.415589",
                    "location_lat": "39.936165",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "387": {
                    "id": "387",
                    "shop_no": "287",
                    "area_id": "110101",
                    "alias": "好邻居安定门内店",
                    "name": "北京市东城区安定门内大街16号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.954631",
                    "location_lat": "116.414941",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "391": {
                    "id": "391",
                    "shop_no": "290",
                    "area_id": "110101",
                    "alias": "好邻居东四北店",
                    "name": "北京市东城区东四北大街146号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.423759",
                    "location_lat": "39.941014",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "398": {
                    "id": "398",
                    "shop_no": "312",
                    "area_id": "110101",
                    "alias": "好邻居美术馆东街店",
                    "name": "北京市东城区美术馆后街9号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.415629",
                    "location_lat": "39.936304",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "403": {
                    "id": "403",
                    "shop_no": "304",
                    "area_id": "110101",
                    "alias": "东城区张自忠路2号",
                    "name": "好邻居张自忠路店",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.422575",
                    "location_lat": "39.939621",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "406": {
                    "id": "406",
                    "shop_no": "305",
                    "area_id": "110101",
                    "alias": "好邻居前门西大街店西",
                    "name": "西城区前门西大街57号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.393358",
                    "location_lat": "39.90677",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "408": {
                    "id": "408",
                    "shop_no": "309",
                    "area_id": "110101",
                    "alias": "好邻居德胜东口店",
                    "name": "西城区德胜门内大街6号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.386649",
                    "location_lat": "39.954094",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "411": {
                    "id": "411",
                    "shop_no": "311",
                    "area_id": "110101",
                    "alias": "好邻居沙滩北街店",
                    "name": "东城区五四大街沙滩北街求是杂志社对面",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.409818",
                    "location_lat": "39.931568",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "414": {
                    "id": "414",
                    "shop_no": "316",
                    "area_id": "110101",
                    "alias": "好邻居紫竹院路店",
                    "name": "海淀区紫竹院路车道沟粮店",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.306968",
                    "location_lat": "39.951495",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "420": {
                    "id": "420",
                    "shop_no": "328",
                    "area_id": "110101",
                    "alias": "五四大街二分店",
                    "name": "北京市东城区沙滩北街甲2号五四大街甲31号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.411039",
                    "location_lat": "39.9307",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "422": {
                    "id": "422",
                    "shop_no": "331",
                    "area_id": "110101",
                    "alias": "东四大街三分店",
                    "name": "东城区东四北大街43号首层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.42303",
                    "location_lat": "39.946084",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "424": {
                    "id": "424",
                    "shop_no": "333",
                    "area_id": "110101",
                    "alias": "沙滩后街分店",
                    "name": "东城区沙滩后街53号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.931855",
                    "location_lat": "116.408147",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "430": {
                    "id": "430",
                    "shop_no": "347",
                    "area_id": "110101",
                    "alias": "好邻居东营房店",
                    "name": "东城区吉士口东路聚龙花园对面东城区东营房八条3号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.936417",
                    "location_lat": "116.446781",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "438": {
                    "id": "438",
                    "shop_no": "300",
                    "area_id": "110101",
                    "alias": "好邻居东直南小店",
                    "name": "北京市东直门南小街20-1",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.432541",
                    "location_lat": "39.940965",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                }
            },
            "110102": {
                "9": {
                    "id": "9",
                    "shop_no": "221",
                    "area_id": "110102",
                    "alias": "友谊医院店",
                    "name": "西城区，永安路32号，好邻居便利店(221)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.401018",
                    "location_lat": "39.892384",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "25": {
                    "id": "25",
                    "shop_no": "116",
                    "area_id": "110102",
                    "alias": "佟麟阁路新华社店",
                    "name": "西城区，佟麟阁路91号，好邻居便利店(116)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.374796",
                    "location_lat": "39.9067",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "26": {
                    "id": "26",
                    "shop_no": "110",
                    "area_id": "110102",
                    "alias": "月坛北街店",
                    "name": "西城区，月坛北街11号楼7号，好邻居便利店(110)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.352528",
                    "location_lat": "39.924319",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "42": {
                    "id": "42",
                    "shop_no": "416",
                    "area_id": "110102",
                    "alias": "木樨地好邻居",
                    "name": "西城区，木樨地25号，好邻居便利店(416)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.345032",
                    "location_lat": "39.913418",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "259": {
                    "id": "259",
                    "shop_no": "116",
                    "area_id": "110102",
                    "alias": "好邻居佟麟阁路店",
                    "name": "西城区佟麟阁路91号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.90687",
                    "location_lat": "116.374897",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "261": {
                    "id": "261",
                    "shop_no": "119",
                    "area_id": "110102",
                    "alias": "好邻居德内大街店",
                    "name": "西城区德内大街232号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.944099",
                    "location_lat": "116.386256",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "262": {
                    "id": "262",
                    "shop_no": "114",
                    "area_id": "110102",
                    "alias": "好邻居半壁街店",
                    "name": "西城区前半壁街35号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.944109",
                    "location_lat": "116.366064",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "263": {
                    "id": "263",
                    "shop_no": "389",
                    "area_id": "110102",
                    "alias": "地安门厂桥小学店",
                    "name": "西城区地安门西大街163号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.939216",
                    "location_lat": "116.38465",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "269": {
                    "id": "269",
                    "shop_no": "103",
                    "area_id": "110102",
                    "alias": "好邻居西四店",
                    "name": "西城区西四北大街158号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.933129",
                    "location_lat": "116.380035",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "271": {
                    "id": "271",
                    "shop_no": "118",
                    "area_id": "110102",
                    "alias": "好邻居旧鼓楼大街店",
                    "name": "西城区小石桥胡同3号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.952481",
                    "location_lat": "116.400045",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "273": {
                    "id": "273",
                    "shop_no": "109",
                    "area_id": "110102",
                    "alias": "好邻居二龙路店",
                    "name": "西城区二龙路41号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.916012",
                    "location_lat": "39.916012",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "275": {
                    "id": "275",
                    "shop_no": "112",
                    "area_id": "110102",
                    "alias": "好邻居宇宙红店",
                    "name": "西城区百万庄南街4号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.931903",
                    "location_lat": "116.34421",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "277": {
                    "id": "277",
                    "shop_no": "105",
                    "area_id": "110102",
                    "alias": "好邻居展览路店",
                    "name": "西城区展览路31号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.931276",
                    "location_lat": "116.350293",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "278": {
                    "id": "278",
                    "shop_no": "402",
                    "area_id": "110102",
                    "alias": "护国寺路口南店",
                    "name": "西城区新南大街154号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.063954",
                    "location_lat": "116.184985",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "280": {
                    "id": "280",
                    "shop_no": "102",
                    "area_id": "110102",
                    "alias": "好邻居车公庄店",
                    "name": "西城区车公庄西口29号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.937759",
                    "location_lat": "39.937759",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "282": {
                    "id": "282",
                    "shop_no": "110",
                    "area_id": "110102",
                    "alias": "好邻居月坛北街店",
                    "name": "西城区月坛北街11号楼7号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.924323",
                    "location_lat": "116.352528",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "284": {
                    "id": "284",
                    "shop_no": "101",
                    "area_id": "110102",
                    "alias": "好邻居中轴路店",
                    "name": "西城区裕中东里2号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.979206",
                    "location_lat": "116.398644",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "285": {
                    "id": "285",
                    "shop_no": "223",
                    "area_id": "110102",
                    "alias": "英蓝国际店",
                    "name": "北京市西城区金融大街7号英蓝国际金融中心B120（周一至周六营业）",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.364326",
                    "location_lat": "39.926289",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "296": {
                    "id": "296",
                    "shop_no": "501",
                    "area_id": "110102",
                    "alias": "好邻居中关村南大街店",
                    "name": "西城区西四南大街111号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.924936",
                    "location_lat": "116.380005",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "297": {
                    "id": "297",
                    "shop_no": "401",
                    "area_id": "110102",
                    "alias": "好邻居榆树馆店",
                    "name": "西城区榆树馆15号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.94284",
                    "location_lat": "116.351401",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "299": {
                    "id": "299",
                    "shop_no": "817",
                    "area_id": "110102",
                    "alias": "槐柏树街店",
                    "name": "西城区槐柏树街北里5号楼5-8号A",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.902009",
                    "location_lat": "116.361527",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "300": {
                    "id": "300",
                    "shop_no": "221",
                    "area_id": "110102",
                    "alias": "好邻居永安路东店",
                    "name": "北京市宣武区永安路32号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.401073",
                    "location_lat": "39.892503",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "301": {
                    "id": "301",
                    "shop_no": "176",
                    "area_id": "110102",
                    "alias": "好邻居阜外大街店",
                    "name": "西城区阜外大街37号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.929138",
                    "location_lat": "116.346122",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "302": {
                    "id": "302",
                    "shop_no": "209",
                    "area_id": "110102",
                    "alias": "冠英园店",
                    "name": "西城区西直门内大街132号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.946345",
                    "location_lat": "116.371861",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "303": {
                    "id": "303",
                    "shop_no": "170",
                    "area_id": "110102",
                    "alias": "好邻居百万庄店",
                    "name": "西城区百万庄大街31号院1号楼1门1层2号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.934364",
                    "location_lat": "116.350088",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "306": {
                    "id": "306",
                    "shop_no": "224",
                    "area_id": "110102",
                    "alias": "好邻居新德街店",
                    "name": "北京西城区新德街东口",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.384438",
                    "location_lat": "39.963915",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "318": {
                    "id": "318",
                    "shop_no": "382",
                    "area_id": "110102",
                    "alias": "护国寺公交站店",
                    "name": "西城区新街口南大街144号一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.381515",
                    "location_lat": "39.948668",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "322": {
                    "id": "322",
                    "shop_no": "222",
                    "area_id": "110102",
                    "alias": "好邻居丰盛店",
                    "name": "西城区西四南大街111号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.380005",
                    "location_lat": "39.924936",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "323": {
                    "id": "323",
                    "shop_no": "380",
                    "area_id": "110102",
                    "alias": "宣武门西大街分店",
                    "name": "西城区宣武门西大街103号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.370212",
                    "location_lat": "39.904687",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "339": {
                    "id": "339",
                    "shop_no": "416",
                    "area_id": "110102",
                    "alias": "好邻居复兴门中路店",
                    "name": "西城区木樨地25号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.913461",
                    "location_lat": "116.345005",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "340": {
                    "id": "340",
                    "shop_no": "417",
                    "area_id": "110102",
                    "alias": "好邻居官园店",
                    "name": "西城区官园南里三区1-1",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.937975",
                    "location_lat": "116.366814",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "343": {
                    "id": "343",
                    "shop_no": "418",
                    "area_id": "110102",
                    "alias": "好邻居辟才胡同店",
                    "name": "西城区辟才胡同56号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.920193",
                    "location_lat": "116.377419",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "346": {
                    "id": "346",
                    "shop_no": "420",
                    "area_id": "110102",
                    "alias": "好邻居地安门店",
                    "name": "西城区地外大街178号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.940582",
                    "location_lat": "116.402904",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "352": {
                    "id": "352",
                    "shop_no": "421",
                    "area_id": "110102",
                    "alias": "好邻居万方园店",
                    "name": "西城区葱店胡同2号院1号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.943645",
                    "location_lat": "116.36514",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "354": {
                    "id": "354",
                    "shop_no": "422",
                    "area_id": "110102",
                    "alias": "好邻居西便门店",
                    "name": "西城区复兴门南大街1号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.907359",
                    "location_lat": "116.362753",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "355": {
                    "id": "355",
                    "shop_no": "423",
                    "area_id": "110102",
                    "alias": "好邻居三里河店",
                    "name": "西城区月坛南街37号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.919879",
                    "location_lat": "116.353503",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "358": {
                    "id": "358",
                    "shop_no": "403",
                    "area_id": "110102",
                    "alias": "好邻居灵境胡同店",
                    "name": "西城区灵镜胡同1号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.920853",
                    "location_lat": "116.386711",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "359": {
                    "id": "359",
                    "shop_no": "404",
                    "area_id": "110102",
                    "alias": "好邻居月坛南街店",
                    "name": "西城区月坛南街甲1号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.91982",
                    "location_lat": "116.359306",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "360": {
                    "id": "360",
                    "shop_no": "405",
                    "area_id": "110102",
                    "alias": "好邻居西什库店",
                    "name": "西城区西什库大街24号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.93416",
                    "location_lat": "116.386686",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "361": {
                    "id": "361",
                    "shop_no": "406",
                    "area_id": "110102",
                    "alias": "好邻居鼓楼西店",
                    "name": "西城区鼓楼大街93号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.950309",
                    "location_lat": "116.395251",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "362": {
                    "id": "362",
                    "shop_no": "407",
                    "area_id": "110102",
                    "alias": "好邻居宏大店",
                    "name": "西城区地外大街19号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.945682",
                    "location_lat": "116.402319",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "363": {
                    "id": "363",
                    "shop_no": "409",
                    "area_id": "110102",
                    "alias": "好邻居北新华街店",
                    "name": "西城区北新华街88号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.910356",
                    "location_lat": "116.39084",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "364": {
                    "id": "364",
                    "shop_no": "410",
                    "area_id": "110102",
                    "alias": "好邻居白云里店",
                    "name": "西城区白云里丙1号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.905357",
                    "location_lat": "116.353689",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "365": {
                    "id": "365",
                    "shop_no": "411",
                    "area_id": "110102",
                    "alias": "好邻居月坛西街店",
                    "name": "西城区月坛西街21号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.922352",
                    "location_lat": "116.356126",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "366": {
                    "id": "366",
                    "shop_no": "412",
                    "area_id": "110102",
                    "alias": "好邻居德内大街北店",
                    "name": "西城区德内大街169号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.948195",
                    "location_lat": "116.385315",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "367": {
                    "id": "367",
                    "shop_no": "413",
                    "area_id": "110102",
                    "alias": "好邻居赵登禹路店",
                    "name": "西城区赵登禹路148号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.934596",
                    "location_lat": "116.374054",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "368": {
                    "id": "368",
                    "shop_no": "201",
                    "area_id": "110102",
                    "alias": "好邻居白石桥店",
                    "name": "海淀区中关村南大街48-9平房",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.948923",
                    "location_lat": "116.332037",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "369": {
                    "id": "369",
                    "shop_no": "208",
                    "area_id": "110102",
                    "alias": "好邻居永安路店",
                    "name": "宣武区永安路104号G间",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.89244",
                    "location_lat": "116.394263",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "370": {
                    "id": "370",
                    "shop_no": "211",
                    "area_id": "110102",
                    "alias": "好邻居西四东店",
                    "name": "西城区西安门大街152号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.928371",
                    "location_lat": "116.380402",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "374": {
                    "id": "374",
                    "shop_no": "260",
                    "area_id": "110102",
                    "alias": "好邻居棉花胡同北口店",
                    "name": "西城区新街口东街53号4幢号楼房一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.38109",
                    "location_lat": "39.94842",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "375": {
                    "id": "375",
                    "shop_no": "261",
                    "area_id": "110102",
                    "alias": "好邻居西四北大街店",
                    "name": "西城区西四北大街5号平房",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.38008",
                    "location_lat": "39.938001",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "377": {
                    "id": "377",
                    "shop_no": "266",
                    "area_id": "110102",
                    "alias": "好邻居德外店",
                    "name": "北京市西城区德外大街11号，德胜园区",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.385493",
                    "location_lat": "39.966536",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "379": {
                    "id": "379",
                    "shop_no": "270",
                    "area_id": "110102",
                    "alias": "好邻居月坛北桥店",
                    "name": "北京市西城区阜成门南大街9号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.362562",
                    "location_lat": "39.924593",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "385": {
                    "id": "385",
                    "shop_no": "280",
                    "area_id": "110102",
                    "alias": "好邻居什刹海店",
                    "name": "北京市西城区地安门西大街47号5号房",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.400289",
                    "location_lat": "39.94003",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "399": {
                    "id": "399",
                    "shop_no": "314",
                    "area_id": "110102",
                    "alias": "好邻居积水潭北店",
                    "name": "西城区新街口外大街28-13",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.378253",
                    "location_lat": "39.959436",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "402": {
                    "id": "402",
                    "shop_no": "302",
                    "area_id": "110102",
                    "alias": "好邻居鸭子桥北店",
                    "name": "宣武区南滨河31号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.355091",
                    "location_lat": "39.88646",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "404": {
                    "id": "404",
                    "shop_no": "306",
                    "area_id": "110102",
                    "alias": "好邻居西四东街店",
                    "name": "西城区西四东大街62号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.380553",
                    "location_lat": "39.929939",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "415": {
                    "id": "415",
                    "shop_no": "154",
                    "area_id": "110102",
                    "alias": "新南店",
                    "name": "西城区新街口南大街48号。新街口饭店往南200米路东。",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.379719",
                    "location_lat": "39.94411",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "417": {
                    "id": "417",
                    "shop_no": "166",
                    "area_id": "110102",
                    "alias": "新德店",
                    "name": "西城区新外大街小西天东里7号，好邻居总部楼下",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.378581",
                    "location_lat": "39.961568",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "419": {
                    "id": "419",
                    "shop_no": "138",
                    "area_id": "110102",
                    "alias": "北滨河店",
                    "name": "西城区北滨河路2号院",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.359098",
                    "location_lat": "39.906494",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "428": {
                    "id": "428",
                    "shop_no": "508",
                    "area_id": "110102",
                    "alias": "木樨地店J",
                    "name": "西城区复兴门外大街甲22号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.912755",
                    "location_lat": "116.346132",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "434": {
                    "id": "434",
                    "shop_no": "841",
                    "area_id": "110102",
                    "alias": "北三环中路3分店",
                    "name": "西城区北三环中路18号一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.973624",
                    "location_lat": "116.389087",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                }
            },
            "110105": {
                "3": {
                    "id": "3",
                    "shop_no": "275",
                    "area_id": "110105",
                    "alias": "丽都饭店东门店",
                    "name": "朝阳区，高家园小区311号，好邻居便利店(275)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.486509",
                    "location_lat": "39.984958",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "4": {
                    "id": "4",
                    "shop_no": "322",
                    "area_id": "110105",
                    "alias": "七圣路光熙门北里店",
                    "name": "朝阳区，光熙门北里甲31号，好邻居便利店(322)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.442097",
                    "location_lat": "39.973949",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "5": {
                    "id": "5",
                    "shop_no": "298",
                    "area_id": "110105",
                    "alias": "慧忠北路店",
                    "name": "朝阳区，慧忠北路慧忠里231楼鼓浪屿会所一层底商，好邻居便利店(298)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.407968",
                    "location_lat": "40.004823",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "6": {
                    "id": "6",
                    "shop_no": "291",
                    "area_id": "110105",
                    "alias": "酒仙桥路京都国际店",
                    "name": "朝阳区，酒仙桥路26号院1号楼A05号，好邻居便利店(291)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.500083",
                    "location_lat": "39.970223",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "10": {
                    "id": "10",
                    "shop_no": "343",
                    "area_id": "110105",
                    "alias": "管庄西里店",
                    "name": "朝阳区，朝阳路管庄西里65号，佳得宾馆楼下金凤成祥旁边好邻居便利店(343)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.595474",
                    "location_lat": "39.918661",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "11": {
                    "id": "11",
                    "shop_no": "148",
                    "area_id": "110105",
                    "alias": "北沙滩枫林绿洲店",
                    "name": "朝阳区，大屯路风林绿洲小区6号楼底商D单元S-F06-01D，好邻居便利店(148)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.390494",
                    "location_lat": "40.00807",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "12": {
                    "id": "12",
                    "shop_no": "249",
                    "area_id": "110105",
                    "alias": "广顺桥南博雅国际店",
                    "name": "朝阳区，利泽中一路1号望京科技大厦商铺，好邻居便利店(249)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.47653",
                    "location_lat": "40.019315",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "97": {
                    "id": "97",
                    "shop_no": "237",
                    "area_id": "110105",
                    "alias": "好邻居定福庄店",
                    "name": "朝阳区，定福庄北街7号楼西侧平房北京文教用品厂内，好邻居便利店(237)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.923365",
                    "location_lat": "116.560242",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "260": {
                    "id": "260",
                    "shop_no": "531",
                    "area_id": "110105",
                    "alias": "好邻居林达店",
                    "name": "朝阳区东北二环外林达大厦",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.962122",
                    "location_lat": "116.439102",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "287": {
                    "id": "287",
                    "shop_no": "148",
                    "area_id": "110105",
                    "alias": "好邻居北沙滩店",
                    "name": "朝阳区大屯路风林绿洲小区6号楼底商D单元S-F06-01D",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.008075",
                    "location_lat": "116.390495",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "290": {
                    "id": "290",
                    "shop_no": "150",
                    "area_id": "110105",
                    "alias": "好邻居雅宝路店",
                    "name": "朝阳区外交部南街8号，京华豪园南座213-C。",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.923624",
                    "location_lat": "116.443117",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "304": {
                    "id": "304",
                    "shop_no": "818",
                    "area_id": "110105",
                    "alias": "芍药居2号院店",
                    "name": "朝阳区太阳宫芍药居2号院甲2号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.438204",
                    "location_lat": "39.986223",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "307": {
                    "id": "307",
                    "shop_no": "823",
                    "area_id": "110105",
                    "alias": "望京西园星源国际A座店",
                    "name": "朝阳区望京西园222楼A-103号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.474849",
                    "location_lat": "40.008671",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "308": {
                    "id": "308",
                    "shop_no": "184",
                    "area_id": "110105",
                    "alias": "好邻居红领巾桥店",
                    "name": "朝阳区延静东里甲3号商务学院综合楼一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.928626",
                    "location_lat": "116.494072",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "309": {
                    "id": "309",
                    "shop_no": "185",
                    "area_id": "110105",
                    "alias": "好邻居花家地店",
                    "name": "朝阳区望京中环南路5号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.988666",
                    "location_lat": "116.48105",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "327": {
                    "id": "327",
                    "shop_no": "378",
                    "area_id": "110105",
                    "alias": "甜水园东街分店",
                    "name": "朝阳区甜水园东街10号17号楼1-3",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.48544",
                    "location_lat": "39.92896",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "334": {
                    "id": "334",
                    "shop_no": "327",
                    "area_id": "110105",
                    "alias": "好邻居望京西园店",
                    "name": "朝阳区望京西园222楼星源公寓E座底商E-3号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.008675",
                    "location_lat": "116.474847",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "335": {
                    "id": "335",
                    "shop_no": "375",
                    "area_id": "110105",
                    "alias": "酒仙桥360大厦店",
                    "name": "朝阳区酒仙桥路6号院2号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.497046",
                    "location_lat": "39.988765",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "336": {
                    "id": "336",
                    "shop_no": "326",
                    "area_id": "110105",
                    "alias": "好邻居永安里中街店",
                    "name": "朝阳区建国门外大街永安里中街25号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.911324",
                    "location_lat": "116.456232",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "337": {
                    "id": "337",
                    "shop_no": "218",
                    "area_id": "110105",
                    "alias": "好邻居广渠路店",
                    "name": "朝阳区广渠东路33号沿海赛洛城底商",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.511862",
                    "location_lat": "39.901627",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "341": {
                    "id": "341",
                    "shop_no": "373",
                    "area_id": "110105",
                    "alias": "建华南路商通大厦店",
                    "name": "北京市朝阳区建国门外大街建华南路11号商通大厦底商",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.450102",
                    "location_lat": "39.911605",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "342": {
                    "id": "342",
                    "shop_no": "321",
                    "area_id": "110105",
                    "alias": "好邻居工体南路店",
                    "name": "北京市朝阳区工体南路朝阳医院西侧",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.45747",
                    "location_lat": "39.932487",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "344": {
                    "id": "344",
                    "shop_no": "372",
                    "area_id": "110105",
                    "alias": "建华南路美华世纪店",
                    "name": "北京市朝阳区建华南路15号美华世纪大厦1层1-78号房屋",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.449915",
                    "location_lat": "39.910699",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "345": {
                    "id": "345",
                    "shop_no": "322",
                    "area_id": "110105",
                    "alias": "好邻居七圣路分店",
                    "name": "北京市朝阳区光熙门北里甲31号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.442098",
                    "location_lat": "39.97395",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "351": {
                    "id": "351",
                    "shop_no": "369",
                    "area_id": "110105",
                    "alias": "工体春秀路店",
                    "name": "北京市朝阳区工人体育场北路1号3号楼一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.456211",
                    "location_lat": "39.939741",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "356": {
                    "id": "356",
                    "shop_no": "239",
                    "area_id": "110105",
                    "alias": "好邻居百子湾三店",
                    "name": "朝阳区百子湾路16号百子园14号楼B门101号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.491909",
                    "location_lat": "39.906244",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "372": {
                    "id": "372",
                    "shop_no": "249",
                    "area_id": "110105",
                    "alias": "好邻居广顺桥南店",
                    "name": "朝阳区利泽中一路1号望京科技大厦商铺",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "1116.476429",
                    "location_lat": "40.01963",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "382": {
                    "id": "382",
                    "shop_no": "275",
                    "area_id": "110105",
                    "alias": "好邻居丽都店",
                    "name": "北京市朝阳区高家园小区311号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.486506",
                    "location_lat": "39.984957",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "383": {
                    "id": "383",
                    "shop_no": "277",
                    "area_id": "110105",
                    "alias": "好邻居东土城2店",
                    "name": "北京市朝阳区东土城路13号院1号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.438694",
                    "location_lat": "39.957714",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "388": {
                    "id": "388",
                    "shop_no": "286",
                    "area_id": "110105",
                    "alias": "好邻居财满街店",
                    "name": "朝阳区朝阳路69号楼1-1-1（5）号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.542551",
                    "location_lat": "39.923212",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "390": {
                    "id": "390",
                    "shop_no": "284",
                    "area_id": "110105",
                    "alias": "好邻居麦子西街店",
                    "name": "朝阳区枣营北里38号楼一层104",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.475901",
                    "location_lat": "39.950494",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "393": {
                    "id": "393",
                    "shop_no": "293",
                    "area_id": "110105",
                    "alias": "好邻居广泽果岭店",
                    "name": "北京市朝阳区广泽路6号院13号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.485421",
                    "location_lat": "40.013152",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "394": {
                    "id": "394",
                    "shop_no": "503",
                    "area_id": "110105",
                    "alias": "好邻居广顺北店",
                    "name": "北京市朝阳区利泽西园102号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.014023",
                    "location_lat": "116.475486",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "395": {
                    "id": "395",
                    "shop_no": "291",
                    "area_id": "110105",
                    "alias": "好邻居酒仙桥路店",
                    "name": "北京市朝阳区酒仙桥路26号院1号楼A05号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.50127",
                    "location_lat": "39.972673",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "397": {
                    "id": "397",
                    "shop_no": "298",
                    "area_id": "110105",
                    "alias": "好邻居慧忠北路店",
                    "name": "北京市朝阳区慧忠北路慧忠里231楼鼓浪屿会所一层底商",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.408649",
                    "location_lat": "40.004854",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "401": {
                    "id": "401",
                    "shop_no": "297",
                    "area_id": "110105",
                    "alias": "好邻居鼓外黄寺",
                    "name": "朝阳区安外黄寺大街3号院",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.403293",
                    "location_lat": "39.969729",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "405": {
                    "id": "405",
                    "shop_no": "303",
                    "area_id": "110105",
                    "alias": "好邻居外经贸店",
                    "name": "朝阳区太阳宫乡芍药居村甲3号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.437723",
                    "location_lat": "39.986458",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "410": {
                    "id": "410",
                    "shop_no": "309",
                    "area_id": "110105",
                    "alias": "好邻居安苑小关店",
                    "name": "朝阳区小关北街43号平房",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.415675",
                    "location_lat": "39.987759",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "416": {
                    "id": "416",
                    "shop_no": "502",
                    "area_id": "110105",
                    "alias": "光华桥店",
                    "name": "朝阳区光华路7号（周一至周五营业）",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.460404",
                    "location_lat": "39.919788",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "418": {
                    "id": "418",
                    "shop_no": "262",
                    "area_id": "110105",
                    "alias": "百子湾5店",
                    "name": "朝阳区百子湾路16号百子园4号楼一层C单元101室",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.490848",
                    "location_lat": "39.906404",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "423": {
                    "id": "423",
                    "shop_no": "332",
                    "area_id": "110105",
                    "alias": "左家庄店",
                    "name": "北京市朝阳区左家庄东里14号楼院",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.960224",
                    "location_lat": "116.45108",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "427": {
                    "id": "427",
                    "shop_no": "343",
                    "area_id": "110105",
                    "alias": "管庄分店",
                    "name": "朝阳区朝阳路管庄西里65号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.918461",
                    "location_lat": "116.596154",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "433": {
                    "id": "433",
                    "shop_no": "388",
                    "area_id": "110105",
                    "alias": "樱花园东街店",
                    "name": "朝阳区樱花园东街1号楼1层1-6",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.91431",
                    "location_lat": "116.37832",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "435": {
                    "id": "435",
                    "shop_no": "386",
                    "area_id": "110105",
                    "alias": "和平街分店",
                    "name": "朝阳区和平街11区甲16楼一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.975034",
                    "location_lat": "116.429374",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "436": {
                    "id": "436",
                    "shop_no": "387",
                    "area_id": "110105",
                    "alias": "建外大街分店",
                    "name": "朝阳区建外大街乙24号燕华苑N105",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.912587",
                    "location_lat": "116.44601",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "439": {
                    "id": "439",
                    "shop_no": "366",
                    "area_id": "110105",
                    "alias": "慧忠里洛克时代店",
                    "name": "朝阳区慧忠里洛克时代亚奥国际广场D座一层1019号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.407968",
                    "location_lat": "40.004823",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                }
            },
            "110106": {
                "314": {
                    "id": "314",
                    "shop_no": "839",
                    "area_id": "110106",
                    "alias": "草桥欣园店",
                    "name": "丰台区草桥欣园一区5号楼一层5号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.365107",
                    "location_lat": "39.852251",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "319": {
                    "id": "319",
                    "shop_no": "161",
                    "area_id": "110106",
                    "alias": "好邻居羊坊店路店",
                    "name": "丰台区西客站南路8号南广场往南过红绿灯直行300米路东",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.893183",
                    "location_lat": "116.327939",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "349": {
                    "id": "349",
                    "shop_no": "370",
                    "area_id": "110106",
                    "alias": "首经贸大学南门店",
                    "name": "丰台区首经贸中街8号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.327967",
                    "location_lat": "39.846621",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "429": {
                    "id": "429",
                    "shop_no": "346",
                    "area_id": "110106",
                    "alias": "好邻居刘家窑南里店",
                    "name": "丰台区刘家窑路丰台区刘家窑南里甲一号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.868292",
                    "location_lat": "116.423535",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                }
            },
            "110107": {
                "409": {
                    "id": "409",
                    "shop_no": "396",
                    "area_id": "110107",
                    "alias": "八角畅游大厦搜狐店",
                    "name": "北京市石景山区搜狐畅游大厦一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.231304",
                    "location_lat": "39.912525",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                }
            },
            "110108": {
                "1": {
                    "id": "1",
                    "shop_no": "843",
                    "area_id": "110108",
                    "alias": "上地鹏寰大厦百度店",
                    "name": "海淀区，上地东路一号院鹏寰大厦，好邻居便利店(843)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.308402",
                    "location_lat": "40.061414",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "8": {
                    "id": "8",
                    "shop_no": "263",
                    "area_id": "110108",
                    "alias": "畅春园美食街店",
                    "name": "海淀区，西苑草场5号，好邻居便利店(263)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.3113",
                    "location_lat": "39.995205",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "16": {
                    "id": "16",
                    "shop_no": "125",
                    "area_id": "110108",
                    "alias": "北三环放光社区店",
                    "name": "海淀区，北三环西路60号，好邻居便利店(125)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.326494",
                    "location_lat": "39.971958",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "17": {
                    "id": "17",
                    "shop_no": "183",
                    "area_id": "110108",
                    "alias": "厂洼武警总部店",
                    "name": "海淀区，厂洼小区24号楼北京电视台西门，好邻居便利店(183)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.313109",
                    "location_lat": "39.964403",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "18": {
                    "id": "18",
                    "shop_no": "351",
                    "area_id": "110108",
                    "alias": "板井路总部店",
                    "name": "海淀区，车道沟桥进入板井路单行路直行300米路北，好邻居便利店(351)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.295582",
                    "location_lat": "39.955243",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "19": {
                    "id": "19",
                    "shop_no": "350",
                    "area_id": "110108",
                    "alias": "大钟寺东路京仪大厦店",
                    "name": "海淀区，大钟寺东路京仪大厦底商海淀区大钟寺东路9号1幢1层101-1，好邻居便利店(350)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.344811",
                    "location_lat": "39.977391",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "20": {
                    "id": "20",
                    "shop_no": "169",
                    "area_id": "110108",
                    "alias": "科南中关村中学店",
                    "name": "海淀区，科学院南路55号 中关村中学正对面，好邻居便利店(169)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.331498",
                    "location_lat": "39.984315",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "21": {
                    "id": "21",
                    "shop_no": "561",
                    "area_id": "110108",
                    "alias": "万泉河好邻居",
                    "name": "海淀区，万泉河路68号紫金大厦1层，好邻居便利店(561)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.314524",
                    "location_lat": "39.972615",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "22": {
                    "id": "22",
                    "shop_no": "147",
                    "area_id": "110108",
                    "alias": "羊坊路京西宾馆店",
                    "name": "海淀区，羊坊店路3号，好邻居便利店(147)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.327509",
                    "location_lat": "39.911252",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "23": {
                    "id": "23",
                    "shop_no": "199",
                    "area_id": "110108",
                    "alias": "永定路航天工业部店",
                    "name": "海淀区，永定路63号(武警总医院北200米)，好邻居便利店(199)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.270987",
                    "location_lat": "39.918917",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "24": {
                    "id": "24",
                    "shop_no": "379",
                    "area_id": "110108",
                    "alias": "科南路搜狐大厦店",
                    "name": "海淀区，中关村新科祥园甲2号楼1层03室，好邻居便利店(379)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.33112",
                    "location_lat": "39.989367",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "43": {
                    "id": "43",
                    "shop_no": "296",
                    "area_id": "110108",
                    "alias": "西直门好邻居",
                    "name": "海淀区，西直门北大街47号院2号楼北侧一层，好邻居便利店(296)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.362382",
                    "location_lat": "39.953238",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "75": {
                    "id": "75",
                    "shop_no": "137",
                    "area_id": "110108",
                    "alias": "增光路紫玉大厦店",
                    "name": "海淀区，增光路乙48号，好邻居便利店(137)",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.31828",
                    "location_lat": "39.933911",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "258": {
                    "id": "258",
                    "shop_no": "147",
                    "area_id": "110108",
                    "alias": "好邻居羊坊路店",
                    "name": "海淀区羊坊店路3号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.91126",
                    "location_lat": "116.32751",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "264": {
                    "id": "264",
                    "shop_no": "393",
                    "area_id": "110108",
                    "alias": "广源大厦店",
                    "name": "海淀区广源闸路5-1号广源大厦",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.949622",
                    "location_lat": "116.317985",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "266": {
                    "id": "266",
                    "shop_no": "136",
                    "area_id": "110108",
                    "alias": "好邻居大慧寺店",
                    "name": "海淀区魏公村大慧寺路5号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.957206",
                    "location_lat": "116.332228",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "267": {
                    "id": "267",
                    "shop_no": "395",
                    "area_id": "110108",
                    "alias": "文慧园北路分店",
                    "name": "海淀区红联北村3号楼底商",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.961113",
                    "location_lat": "116.367708",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "268": {
                    "id": "268",
                    "shop_no": "135",
                    "area_id": "110108",
                    "alias": "好邻居阜成路店",
                    "name": "海淀区白堆子立新9号楼前",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.929375",
                    "location_lat": "116.333818",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "270": {
                    "id": "270",
                    "shop_no": "836",
                    "area_id": "110108",
                    "alias": "中关村医院店",
                    "name": "海淀区中关村甲943号楼8-3",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.986589",
                    "location_lat": "116.328315",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "272": {
                    "id": "272",
                    "shop_no": "137",
                    "area_id": "110108",
                    "alias": "好邻居增光路店",
                    "name": "海淀区增光路乙48号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.933911",
                    "location_lat": "116.31828",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "274": {
                    "id": "274",
                    "shop_no": "847",
                    "area_id": "110108",
                    "alias": "上地西路宏达花园广场店",
                    "name": "北京市海淀区上地西路华联东北旺农场开发建设项目综合楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.036138",
                    "location_lat": "116.316936",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "276": {
                    "id": "276",
                    "shop_no": "834",
                    "area_id": "110108",
                    "alias": "学院南路邮电大学店",
                    "name": "海淀区学院南路10号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.963968",
                    "location_lat": "116.363862",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "281": {
                    "id": "281",
                    "shop_no": "849",
                    "area_id": "110108",
                    "alias": "羊坊店西路什坊院店",
                    "name": "海淀区羊坊店西路什坊院一号院6号楼一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.903697",
                    "location_lat": "116.323079",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "283": {
                    "id": "283",
                    "shop_no": "846",
                    "area_id": "110108",
                    "alias": "朱房路清河派出所店",
                    "name": "海淀区清河朱房路临66号楼3栋10号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.033362",
                    "location_lat": "116.339848",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "286": {
                    "id": "286",
                    "shop_no": "810",
                    "area_id": "110108",
                    "alias": "复兴路军博店",
                    "name": "海淀区复兴路12号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.912311",
                    "location_lat": "116.330608",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "288": {
                    "id": "288",
                    "shop_no": "811",
                    "area_id": "110108",
                    "alias": "交大嘉园店",
                    "name": "海淀区交通大学路1号院交大嘉园4号底商",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.955388",
                    "location_lat": "116.352506",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "291": {
                    "id": "291",
                    "shop_no": "813",
                    "area_id": "110108",
                    "alias": "学清路大华加油站店",
                    "name": "海淀区学清路23号院东侧平房4号房间",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.016494",
                    "location_lat": "116.358192",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "292": {
                    "id": "292",
                    "shop_no": "199",
                    "area_id": "110108",
                    "alias": "好邻居永定路店",
                    "name": "海淀区永定路63号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.918919",
                    "location_lat": "116.270988",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "293": {
                    "id": "293",
                    "shop_no": "814",
                    "area_id": "110108",
                    "alias": "木荷路环保园华为店",
                    "name": "海淀区木荷路19号院2号楼一楼西厅东侧",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.063402",
                    "location_lat": "116.191489",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "294": {
                    "id": "294",
                    "shop_no": "191",
                    "area_id": "110108",
                    "alias": "好邻居车道沟店",
                    "name": "海淀区车道沟西南角嘉豪国际大厦B座大堂内（周一至周六营业）",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.953643",
                    "location_lat": "116.298784",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "298": {
                    "id": "298",
                    "shop_no": "173",
                    "area_id": "110108",
                    "alias": "好邻居文慧园店",
                    "name": "海淀区文慧园路10号，双汇超市对面",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.958261",
                    "location_lat": "116.369649",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "305": {
                    "id": "305",
                    "shop_no": "183",
                    "area_id": "110108",
                    "alias": "好邻居厂洼路店",
                    "name": "海淀区厂洼小区24号楼，北京电视台西门",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.964408",
                    "location_lat": "116.31311",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "313": {
                    "id": "313",
                    "shop_no": "155",
                    "area_id": "110108",
                    "alias": "好邻居索家坟店",
                    "name": "海淀区积水潭桥往西400米,远洋风景往北300米路东",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.956995",
                    "location_lat": "116.365342",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "315": {
                    "id": "315",
                    "shop_no": "838",
                    "area_id": "110108",
                    "alias": "中关村小学店",
                    "name": "海淀区中关村新科祥园2号楼",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.329841",
                    "location_lat": "39.989263",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "317": {
                    "id": "317",
                    "shop_no": "383",
                    "area_id": "110108",
                    "alias": "学府树家园店",
                    "name": "海淀区学府树家园3号楼3-1号3-12号1层3-6",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.341163",
                    "location_lat": "40.039874",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "320": {
                    "id": "320",
                    "shop_no": "163",
                    "area_id": "110108",
                    "alias": "好邻居北洼路东店",
                    "name": "海淀区北洼路42号院大门北侧，首都师大附中东门对面。",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.936299",
                    "location_lat": "116.308095",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "324": {
                    "id": "324",
                    "shop_no": "379",
                    "area_id": "110108",
                    "alias": "科南路搜狐大厦店",
                    "name": "海淀区中关村新科祥园甲2号楼1层03室",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.33112",
                    "location_lat": "39.989367",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "325": {
                    "id": "325",
                    "shop_no": "227",
                    "area_id": "110108",
                    "alias": "好邻居白堆子店",
                    "name": "海淀区阜成路23号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.322976",
                    "location_lat": "39.93062",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "326": {
                    "id": "326",
                    "shop_no": "164",
                    "area_id": "110108",
                    "alias": "好邻居交大东路店",
                    "name": "海淀区北下关广通苑小区四号楼一层，嘉世堂药店旁",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.951798",
                    "location_lat": "116.356048",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "328": {
                    "id": "328",
                    "shop_no": "169",
                    "area_id": "110108",
                    "alias": "好邻居科学院南路店",
                    "name": "海淀区科学院南路55号，中关村中学正对面",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.984316",
                    "location_lat": "116.331498",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "330": {
                    "id": "330",
                    "shop_no": "377",
                    "area_id": "110108",
                    "alias": "学院南路师范大学店",
                    "name": "海淀区（市容监督所东侧）学院南路1号楼1层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.37202",
                    "location_lat": "39.963739",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "331": {
                    "id": "331",
                    "shop_no": "174",
                    "area_id": "110108",
                    "alias": "好邻居诚品建筑店",
                    "name": "海淀区诚品建筑云慧里远流清园小区4号四季青桥东南角",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.952221",
                    "location_lat": "116.287734",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "333": {
                    "id": "333",
                    "shop_no": "502",
                    "area_id": "110108",
                    "alias": "好邻居海淀南路店",
                    "name": "海淀区通惠寺3号七一棉织厂东一楼东侧",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.485421",
                    "location_lat": "40.013152",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "338": {
                    "id": "338",
                    "shop_no": "374",
                    "area_id": "110108",
                    "alias": "复兴路翠微大厦店",
                    "name": "北京市海淀区复兴路甲18号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.310688",
                    "location_lat": "39.913175",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "347": {
                    "id": "347",
                    "shop_no": "371",
                    "area_id": "110108",
                    "alias": "西三环北路花园桥店",
                    "name": "北京市海淀区西三环北路91号7号楼国图大厦一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.315945",
                    "location_lat": "39.939575",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "348": {
                    "id": "348",
                    "shop_no": "320",
                    "area_id": "110108",
                    "alias": "好邻居学知轩店",
                    "name": "海淀区学清路16号学知轩一层西侧106号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.359619",
                    "location_lat": "40.018185",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "353": {
                    "id": "353",
                    "shop_no": "244",
                    "area_id": "110108",
                    "alias": "好邻居蓟门里店",
                    "name": "海淀区蓟门里小区北商业楼1幢号平房",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.357564",
                    "location_lat": "39.976246",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "357": {
                    "id": "357",
                    "shop_no": "351",
                    "area_id": "110108",
                    "alias": "好邻居板井店",
                    "name": "海淀区板井村60号南29号平房-9",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.955163",
                    "location_lat": "116.295331",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "371": {
                    "id": "371",
                    "shop_no": "214",
                    "area_id": "110108",
                    "alias": "好邻居魏公村店",
                    "name": "海淀区中关村南大街18号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.960411",
                    "location_lat": "116.330573",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": null,
                    "account_name": null
                },
                "376": {
                    "id": "376",
                    "shop_no": "278",
                    "area_id": "110108",
                    "alias": "好邻居人大店",
                    "name": "北京市海淀区中关村南大街1号院",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.327008",
                    "location_lat": "39.974144",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "380": {
                    "id": "380",
                    "shop_no": "272",
                    "area_id": "110108",
                    "alias": "好邻居马甸桥店",
                    "name": "北京市海淀区北太平庄邮信宿舍9门",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.383046",
                    "location_lat": "39.975784",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "381": {
                    "id": "381",
                    "shop_no": "263",
                    "area_id": "110108",
                    "alias": "好邻居畅春园店",
                    "name": "北京市海淀区西苑草场5号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.311304",
                    "location_lat": "39.995206",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "384": {
                    "id": "384",
                    "shop_no": "279",
                    "area_id": "110108",
                    "alias": "好邻居理工大学店",
                    "name": "北京市海淀区中关村南大街5号102",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.329293",
                    "location_lat": "39.96435",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "386": {
                    "id": "386",
                    "shop_no": "344",
                    "area_id": "110108",
                    "alias": "好邻居皂君庙店",
                    "name": "海淀区皂君庙大钟寺派出所正对面海淀区皂君庙14号院一号楼1层101室",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.343776",
                    "location_lat": "39.966212",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "389": {
                    "id": "389",
                    "shop_no": "285",
                    "area_id": "110108",
                    "alias": "好邻居苏州街工商店",
                    "name": "北京市海淀区苏州街49号一楼北侧111号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.312813",
                    "location_lat": "39.979466",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "392": {
                    "id": "392",
                    "shop_no": "292",
                    "area_id": "110108",
                    "alias": "好邻居花园路店",
                    "name": "北京市海淀区花园路C2号南1号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.372463",
                    "location_lat": "39.986685",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "396": {
                    "id": "396",
                    "shop_no": "296",
                    "area_id": "110108",
                    "alias": "好邻居西直门北店",
                    "name": "北京市海淀区西直门北大街47号院2号楼北侧一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.95324",
                    "location_lat": "116.362383",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "400": {
                    "id": "400",
                    "shop_no": "314",
                    "area_id": "110108",
                    "alias": "好邻居学清农大店",
                    "name": "北京市海淀区农大东校区B105",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.361065",
                    "location_lat": "40.011608",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "407": {
                    "id": "407",
                    "shop_no": "307",
                    "area_id": "110108",
                    "alias": "好邻居工商东校区店",
                    "name": "海淀区阜成路11号院高层学生公寓对面新建平房",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.326385",
                    "location_lat": "39.931696",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "412": {
                    "id": "412",
                    "shop_no": "313",
                    "area_id": "110108",
                    "alias": "好邻居北太平庄西店",
                    "name": "北太平庄路25号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.376216",
                    "location_lat": "39.978622",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "413": {
                    "id": "413",
                    "shop_no": "315",
                    "area_id": "110108",
                    "alias": "好邻居学院南路店",
                    "name": "海淀区大柳树路2号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.349387",
                    "location_lat": "39.963509",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "421": {
                    "id": "421",
                    "shop_no": "125",
                    "area_id": "110108",
                    "alias": "北三环店",
                    "name": "海淀区北三环西路60号",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "116.32179",
                    "location_lat": "39.970257",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "425": {
                    "id": "425",
                    "shop_no": "334",
                    "area_id": "110108",
                    "alias": "苏州街五分店",
                    "name": "海淀区苏州街工商局一层苏州街工商店对面偏南",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.978948",
                    "location_lat": "116.313501",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "426": {
                    "id": "426",
                    "shop_no": "341",
                    "area_id": "110108",
                    "alias": "四道口路二分店",
                    "name": "北京市海淀区四道口路净土寺32号东区41幢一层北部",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.955064",
                    "location_lat": "116.355083",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "431": {
                    "id": "431",
                    "shop_no": "348",
                    "area_id": "110108",
                    "alias": "好邻居清华东三",
                    "name": "海淀区农业大学南门东200米路北海淀区清华东路11号2号幢一层西侧",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "40.007298",
                    "location_lat": "116.366127",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "432": {
                    "id": "432",
                    "shop_no": "350",
                    "area_id": "110108",
                    "alias": "好邻居大钟寺东路",
                    "name": "海淀区大钟寺东路京仪大厦底商海淀区大钟寺东路9号1幢1层101-1",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.977196",
                    "location_lat": "116.344837",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                },
                "437": {
                    "id": "437",
                    "shop_no": "228",
                    "area_id": "110108",
                    "alias": "好邻居航北",
                    "name": "海淀区西三环中路88号（航天桥东北角辅路）华夏一层",
                    "type": "0",
                    "owner_name": "",
                    "owner_phone": "4000-508-528",
                    "location_long": "39.932134",
                    "location_lat": "116.31704",
                    "deleted": false,
                    "can_remark_address": false,
                    "child_area_id": null,
                    "account": "",
                    "account_name": ""
                }
            }
        };
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
        vm.faqTipText = '私信';
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
            "142": "人人快递",
            "143": "百世汇通"
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
        vm.showAutoCommentDialog = showAutoCommentDialog;
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
        vm.toggleTag = toggleTag;
        vm.supportGroupBuy = supportGroupBuy;
        vm.offlineAddressData = null;
        vm.loadOfflineAddressData = loadOfflineAddressData;
        vm.setShipFee = setShipFee;
        vm.newGroupShare = newGroupShare;
        vm.redirectFaq = redirectFaq;
        vm.checkUserHasStartGroupShare = checkUserHasStartGroupShare;
        vm.chatToUser = chatToUser;
        vm.calProxyRebateFee = calProxyRebateFee;
        vm.loadOrderCommentData = loadOrderCommentData;
        vm.isShareManager = isShareManager;
        vm.isShowExpressInfoBtn = isShowExpressInfoBtn;
        vm.isShowCallLogisticsBtn = isShowCallLogisticsBtn;
        vm.isShowCancelLogisticsBtn = isShowCancelLogisticsBtn;
        vm.handleCallLogistics = handleCallLogistics;
        vm.handleCancelLogistics = handleCancelLogistics;
        vm.showOrderExpressInfo = showOrderExpressInfo;
        vm.getLogisticsBtnText = getLogisticsBtnText;
        vm.showLogisticsStatusInfo = showLogisticsStatusInfo;
        vm.getLogisticsStatusText = getLogisticsStatusText;
        vm.childShareDetail = null;
        vm.currentUserOrderCount = 0;
        vm.totalBuyCount = 0;
        vm.rebateFee = 0;
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

        function loadOrderCommentData(share_id) {
            $http({method: 'GET', url: '/weshares/get_share_comment_data/' + share_id + '.json'}).
                success(function (data, status) {
                    vm.commentData = data['comment_data'];
                    vm.orderComments = vm.commentData['order_comments'];
                }).
                error(function (data, status) {
                    $log.log(data);
                });
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
            $http({
                method: 'GET',
                url: '/weshares/get_offline_address_detail/' + share_id + '.json',
                cache: $templateCache
            }).
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
            $http({
                method: 'GET',
                url: '/weshares/get_share_user_order_and_child_share/' + share_id + '.json',
                cache: $templateCache
            }).
                success(function (data, status) {
                    vm.ordersDetail = data['ordersDetail'];
                    vm.childShareDetail = data['childShareData']['child_share_data'];
                    vm.childShareDetailUsers = data['childShareData']['child_share_user_infos'];
                    vm.childShareDetailUsersLevel = data['childShareData']['child_share_level_data'];
                    //vm.shipTypes = data['ordersDetail']['ship_types'];
                    vm.rebateLogs = data['ordersDetail']['rebate_logs'];
                    vm.logisticsOrderData = data['logisticsOrderData'];
                    //vm.sortOrders();
                    vm.combineShareBuyData();
                    setWeiXinShareParams();
                    //check user is auto comment
                    if (vm.autoPopCommentData['comment_order_info']) {
                        vm.showAutoCommentDialog();
                    } else {
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
                    }
                    //process page order info
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
            //auto poup comment dialog
            var commentOrderId = angular.element(document.getElementById('weshareView')).attr('data-comment-order-id');
            var replayCommentId = angular.element(document.getElementById('weshareView')).attr('data-replay-comment-id');
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
            var shareDetailUrl = '/weshares/detail/' + weshareId + '.json?comment_order_id=' + commentOrderId + '&reply_comment_id=' + replayCommentId;
            $http({
                method: 'GET', url: shareDetailUrl, params: {
                    "comment_order_id": commentOrderId,
                    "reply_comment_id": replayCommentId
                }, cache: $templateCache
            }).
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
                    if (vm.weshare.addresses && vm.weshare.addresses.length == 1) {
                        vm.weshare.selectedAddressId = vm.weshare.addresses[0].id;
                    } else if (vm.weshare.addresses && vm.weshare.addresses.length > 1) {
                        vm.weshare.addresses.unshift({id: -1, address: '请选择收货地址'});
                        vm.weshare.selectedAddressId = -1;
                    }
                    vm.isManage = data['is_manage'];
                    vm.canManageShare = data['can_manage_share'];
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
                    vm.totalBuyCount = data['all_buy_count'];
                    vm.favourableConfig = data['favourable_config'];
                    vm.autoPopCommentData = data['prepare_comment_data'];
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
                    if (vm.isCreator() || vm.canManageShare) {
                        vm.faqTipText = '反馈消息';
                    }
                    //vm.checkShareInfoHeight();
                    //load all comments
                    vm.loadOrderDetail(weshareId);
                    vm.loadOrderCommentData(weshareId);
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

        function isShareManager() {
            return vm.isCreator() || vm.canManageShare;
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
            if (vm.ordersDetail && vm.ordersDetail.order_cart_map && vm.ordersDetail.order_cart_map[orderId]) {
                carts = vm.ordersDetail.order_cart_map[orderId];
            } else {
                carts = vm.shareOrder.order_cart_map[orderId];
            }
            return _.map(carts, function (cart) {
                return cart.name + 'X' + cart.num;
            }).join(', ');
        }

        function redirectFaq() {
            if (vm.isCreator() || vm.canManageShare) {
                window.location.href = '/share_faq/faq_list/' + vm.weshare.id + '/' + vm.weshare.creator.id;
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

        function calProxyRebateFee(totalPrice) {
            if (!vm.currentUser || vm.currentUser['is_proxy'] == 0) {
                return;
            }
            if (!vm.weshare.proxy_rebate_percent || vm.weshare.proxy_rebate_percent.percent <= 0) {
                return;
            }
            vm.rebateFee = (totalPrice * vm.weshare.proxy_rebate_percent.percent / 100).toFixed(2);
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
                if (vm.favourableConfig) {
                    //折扣
                    if (vm.favourableConfig['discount']) {
                        totalPrice = totalPrice * vm.favourableConfig['discount'];
                    }
                }
                calProxyRebateFee(totalPrice / 100);
                if (vm.userCouponReduce) {
                    totalPrice -= vm.userCouponReduce;
                }
                vm.shipFee = parseInt(getShipFee());
                vm.shipSetId = getShipSetId();
                totalPrice += vm.shipFee;
                vm.orderTotalPrice = totalPrice / 100;
                if (vm.rebateFee > 0) {
                    vm.orderTotalPrice -= vm.rebateFee;
                }
            } else {
                vm.orderTotalPrice = 0;
                vm.rebateFee = 0;
            }
        }

        function getOrderComment(order_id) {
            if (vm.commentData['order_comments']) {
                if (vm.commentData['order_comments'][order_id]) {
                    return vm.commentData['order_comments'][order_id];
                }
            }
            if (vm.shareOrder['orderComments']) {
                return vm.shareOrder['orderComments'][order_id];
            }
            return null;
        }

        function getOrderCommentLength() {
            if (vm.sharerAllComments) {
                if (vm.shareOrder['orderComments']) {
                    return (vm.shareOrder['orderComments'].length || 0) + vm.sharerAllComments.length;
                }
                return vm.sharerAllComments.length;
            }
            return 0;
        }

        function getReplyComments(comment_id) {
            if (vm.commentData['comment_replies']) {
                if (vm.commentData['comment_replies'][comment_id]) {
                    return vm.commentData['comment_replies'][comment_id];
                }
            }
            if (vm.shareOrder['orderCommentReplies']) {
                return vm.shareOrder['orderCommentReplies'][comment_id];
            }
        }

        function showReplies(comment_id) {
            if (!vm.commentData && !vm.shareOrder['orderCommentReplies']) {
                return false;
            }
            var allReplies = vm.commentData['comment_replies'];
            if (!allReplies) {
                allReplies = vm.shareOrder['orderCommentReplies'];
            }
            if (!allReplies) {
                return false;
            }
            var replies = allReplies[comment_id];
            if (!replies || replies.length == 0) {
                return false;
            }
            return true;
        }

        function getRecommendInfo(order) {
            var recommendId = 0;
            var recommend = '';
            if (vm.rebateLogs && vm.rebateLogs[order['cate_id']]) {
                recommendId = vm.rebateLogs[order['cate_id']];
                recommend = vm.ordersDetail['users'][recommendId]['nickname'];
            } else {
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
            if (vm.rebateLogs && vm.rebateLogs[order['cate_id']]) {
                recommendId = vm.rebateLogs[order['cate_id']];
            } else {
                recommendId = vm.shareOrder.rebate_logs[order['cate_id']];
            }
            if (vm.currentUser && vm.currentUser['id'] == recommendId) {
                return true;
            }
            return false;
        }

        function toRecommendUserInfo(order) {
            var recommendId = 0;
            if (vm.rebateLogs[order['cate_id']]) {
                recommendId = vm.rebateLogs[order['cate_id']];
            } else {
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
                var product_buy_num = parseInt(vm.ordersDetail['summery'].details[product.id]['num']);
                var store_num = product.store;
                return store_num - product_buy_num;
            }
            return product.store;
        }

        function checkProductNum(product) {
            var store_num = product.store;
            //sold out
            if (store_num == -1) {
                return false;
            }
            if (store_num == 0) {
                return true;
            }
            if (vm.ordersDetail && vm.ordersDetail['summery'].details[product.id]) {
                var product_buy_num = parseInt(vm.ordersDetail['summery'].details[product.id]['num']);
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
            if (vm.cloneShareProcessing) {
                return;
            }

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
            vm.loadOrderCommentData(vm.weshare.id);
        }

        function notifyUserToComment(order) {
            vm.submitTempCommentData.order_id = order.id;
            vm.submitTempCommentData.reply_comment_id = 0;
            vm.submitTempCommentData.share_id = order.member_id;
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
            if (vm.totalBuyCount > 10) {
                var index = 0;
                for (var userId in vm.shareOrder['users']) {
                    var user = vm.shareOrder['users'][userId];
                    index++;
                    if (index > 10) {
                        break;
                    }
                    if (index == 10) {
                        msgContent = msgContent + user['nickname'];
                    } else {
                        msgContent = msgContent + user['nickname'] + '，';
                    }
                }
                msgContent = msgContent + '...等' + vm.totalBuyCount + '人都已经报名' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title + '啦，就差你啦。';
            } else {
                msgContent = _.reduce(vm.shareOrder.users, function (memo, user) {
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
            if (vm.shareOrder) {
                if (vm.shareOrder.orders && vm.shareOrder.orders.length > 0) {
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
                        var msg = '发送成功';
                        if (data['msg']) {
                            msg = data['msg'];
                        }
                        alert(msg);
                    } else {
                        if (data['reason'] == 'user_bad') {
                            alert('发送失败，你已经被封号，请联系管理员..');
                        }
                        if (data['msg']) {
                            alert(data['msg']);
                        }
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
                    if (data['msg']) {
                        alert(data['msg']);
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

        //todo auto popped up comment dialog
        function showAutoCommentDialog() {
            var order = vm.autoPopCommentData['comment_order_info'];
            if (order) {
                vm.showCommentingDialog = true;
                vm.showLayer = true;
                var reply_comment_id = 0;
                var comment_tip_info = '';
                var comment = vm.autoPopCommentData['comment_info'];
                if (comment) {
                    reply_comment_id = comment.id || 0;
                    var reply_username = comment.username;
                    if (reply_username == vm.currentUser.nickname) {
                        comment_tip_info = '爱心评价';
                    } else {
                        comment_tip_info = '回复' + reply_username + '：';
                    }
                } else {
                    //check is creator
                    if (vm.currentUser.id == vm.weshare.creator.id) {
                        var order_username = order['creator_nickname'];
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
        }

        //open comment dialog
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
                    var order_username;
                    if (vm.ordersDetail.users[order.creator]) {
                        order_username = vm.ordersDetail.users[order.creator]['nickname'];
                    } else {
                        order_username = vm.shareOrder.users[order.creator]['nickname'];
                    }
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
            vm.submitTempCommentData.share_id = order.member_id;
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
                window.location.href = '/weshares/add?from=share_view';
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
                var ship_type_name = order['ship_type_name'];
                if (!ship_type_name) {
                    var ship_company = order['ship_type'];
                    ship_type_name = vm.shipTypes[ship_company]
                }
                return ship_type_name + ': ' + code;
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
                if (data['msg']) {
                    alert(data['msg']);
                }
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
                    var code = order['ship_code'];
                    if (code) {
                        return true;
                    }
                }
            }
            return false;
        }

        function isShowCancelLogisticsBtn(order) {
            if (vm.isShowCallLogisticsBtn(order)) {
                var orderId = order['id'];
                var logisticsOrder = vm.logisticsOrderData[orderId];
                if (logisticsOrder && logisticsOrder['status'] == 1) {
                    return true;
                }
            }
            return false;
        }

        function isShowCallLogisticsBtn(order) {
            //草莓可以呼叫闪送
            if (vm.weshare.id == 1305 || vm.weshare.id == 1585) {
                if (order['ship_mark'] == 'pys_ziti' && order['status'] == 2 && vm.isOwner(order)) {
                    return true;
                }
            }
            return false;
        }

        function isShowExpressInfoBtn(order) {
            if (order['ship_mark'] == 'kuai_di') {
                if (order.status > 1 && vm.isOwner(order)) {
                    return true;
                }
            }
            return false;
        }

        function handleCancelLogistics(order) {
            var orderId = order['id'];
            if (vm.logisticsOrderData[orderId]) {
                var logisticsOrder = vm.logisticsOrderData[orderId];
                if (logisticsOrder['status'] == 1 && logisticsOrder['business_order_id']) {
                    var logisticsOrderId = logisticsOrder['id'];
                    $http.get('/logistics/cancel_rr_logistics_order/' + logisticsOrderId).success(function (data) {
                        if (data['status'] == 1) {
                            vm.logisticsOrderData[orderId]['status'] = 4;
                            vm.logisticsOrderData[orderId]['business_order_id'] = data['orderNo'];
                        } else {
                            alert(data['msg']);
                        }
                    }).error(function () {
                        alert('呼叫失败，请联系客服。');
                    });
                } else {
                    alert("快递已接单，不能取消。");
                }
                return;
            }
        }

        function handleCallLogistics(order) {
            var orderId = order['id'];
            //订单已经叫过快递
            if (vm.logisticsOrderData[orderId]) {
                var logisticsOrder = vm.logisticsOrderData[orderId];
                if (logisticsOrder['status'] == 5 || (logisticsOrder['status'] == 1 && !logisticsOrder['business_order_id'])) {
                    var logisticsOrderId = logisticsOrder['id'];
                    $http.get('/logistics/re_confirm_rr_logistics_order/' + logisticsOrderId).success(function (data) {
                        if (data['status'] == 1) {
                            vm.logisticsOrderData[orderId]['status'] = 1;
                            vm.logisticsOrderData[orderId]['business_order_id'] = data['orderNo'];
                        } else {
                            alert(data['msg']);
                        }
                    }).error(function () {
                        alert('呼叫失败，请联系客服。');
                    });
                } else {
                    if (logisticsOrder['status'] == 1) {
                        alert('等待快递员接单');
                    }
                    if (logisticsOrder['status'] == 2) {
                        alert('快递已接单，请耐心等候');
                    }
                }
                return;
            }
            window.location.href = '/logistics/rr_logistics/' + orderId;
        }

        function getLogisticsStatusText(order) {
            var orderId = order['id'];
            if (vm.logisticsOrderData && vm.logisticsOrderData[orderId]) {
                var logisticsOrder = vm.logisticsOrderData[orderId];
                if (logisticsOrder['status'] == 5) {
                    return '快递已经取消(超时或者自己取消)';
                }
            }
            return '';
        }

        function showLogisticsStatusInfo(order) {
            //草莓可以呼叫闪送
            if (vm.isShowCallLogisticsBtn(order)) {
                var orderId = order['id'];
                if (vm.logisticsOrderData && vm.logisticsOrderData[orderId]) {
                    var logisticsOrder = vm.logisticsOrderData[orderId];
                    if (logisticsOrder['status'] == 5) {
                        return true;
                    }
                }
            }
            return false;
        }

        function getLogisticsBtnText(order) {
            var orderId = order['id'];
            //订单已经叫过快递
            if (vm.logisticsOrderData && vm.logisticsOrderData[orderId]) {
                var logisticsOrder = vm.logisticsOrderData[orderId];
                if (logisticsOrder['status'] == 1) {
                    if (logisticsOrder['business_order_id']) {
                        return '待接单';
                    }
                    return '重新叫快递';
                }
                if (logisticsOrder['status'] == 2) {
                    return '已接单';
                }
                if (logisticsOrder['status'] == 3) {
                    return '已取货';
                }
                if (logisticsOrder['status'] == 4) {
                    return '已签收';
                }
                if (logisticsOrder['status'] == 5) {
                    return '重新叫快递';
                }
            }
            return '叫快递';
        }

        function showOrderExpressInfo(order) {
            window.location.href = '/weshares/express_info/' + order.id;
            return;
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
                if (vm.totalBuyCount >= 5) {
                    desc += '已经有' + vm.totalBuyCount + '人报名，';
                }
                desc += vm.weshare.description.substr(0, 20);
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
                desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0, 20);
            } else if (vm.currentUser) {
                //default custom
                if (vm.weshare.type !== 4) {
                    if (vm.isProxy()) {
                        url = url + '?recommend=' + vm.currentUser['id'];
                    }
                    if (!vm.isProxy() && vm.recommendUserId != 0) {
                        url = url + '?recommend=' + vm.recommendUserId;
                    }
                }
                to_timeline_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
                to_friend_title = vm.currentUser.nickname + '推荐' + vm.weshare.creator.nickname + '分享的' + vm.weshare.title;
                imgUrl = vm.weshare.images[0] || vm.currentUser.image;
                desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0, 20);
            } else {
                to_timeline_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
                to_friend_title = vm.weshare.creator.nickname + '分享了' + vm.weshare.title;
                imgUrl = vm.weshare.images[0] || vm.weshare.creator.image;
                desc = vm.weshare.creator.nickname + '我认识，很靠谱。' + vm.weshare.description.substr(0, 20);
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