(function (window, angular) {
    angular.module('module.services', [])
        .service('Utils', Utils)
        .service('CoreReactorChannel', CoreReactorChannel)
        .service('Locations', Locations)
        .service('PoolProductInfo', PoolProductInfo)
        .service('OfflineStore', OfflineStore)
        .service('ShareOrder', ShareOrder);

    function Utils($location, $log) {
        var staticHost = '';
        var Storage = {
            save: function (key, jsonData, expirationHour) {
                var expirationMS = expirationHour * 60 * 60 * 1000;
                var record = {value: JSON.stringify(jsonData), timestamp: new Date().getTime() + expirationMS}
                localStorage.setItem(key, JSON.stringify(record));
                return jsonData;
            },
            load: function (key) {
                //if (!Modernizr.localstorage){return false;}
                var record = JSON.parse(localStorage.getItem(key));
                if (!record) {
                    return false;
                }
                return (new Date().getTime() < record.timestamp && JSON.parse(record.value));
            },
            clear: function () {
                localStorage.clear();
            }
        }

        return {
            isBlank: isBlank,
            isMobileValid: isMobileValid,
            isNumber: isNumber,
            toPercent: toPercent,
            removeEmoji: removeEmoji,
            staticFilePath: staticFilePath,
            shipTypes: shipTypes,
            isWeixin : isWeixin,
            Storage: Storage
        };
        function isMobileValid(mobile) {
            return /^1\d{10}$/.test(mobile);
        }

        function isBlank(str) {
            return (!str || /^\s*$/.test(str));
        }

        function isNumber(n) {
            return !isNaN(n);
        }

        function toPercent(value) {
            return Math.min(Math.round(value * 10000) / 100, 100);
        }

        function removeEmoji(str) {
            return str.replace(/([\uE000-\uF8FF]|\uD83C[\uDF00-\uDFFF]|\uD83D[\uDC00-\uDDFF])/g, '');
        }

        function shipTypes() {
            return {
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
            }
        }

        function staticFilePath() {
            if (staticHost != '') {
                return staticHost;
            }

            var host = $location.host();
            if (host == 'www.tongshijia.com') {
                staticHost = 'http://static.tongshijia.com';
            } else if (host == 'preprod.tongshijia.com') {
                staticHost = 'http://static-preprod.tongshijia.com';
            } else if (host == 'sh.tongshijia.com') {
                staticHost = 'http://static-sh.tongshijia.com';
            } else if (host == 'test.tongshijia.com') {
                staticHost = 'http://static-test.tongshijia.com';
            } else {
                staticHost = 'http://dev.tongshijia.com';
            }
            return staticHost;
        }

        function isWeixin() {
            var ua = navigator.userAgent.toLowerCase();
            if (ua.match(/MicroMessenger/i) == "micromessenger") {
                return true;
            } else {
                return false;
            }
        }
    }

    function CoreReactorChannel($rootScope) {
        var elevatedEvent = function (event, data) {
            $rootScope.$broadcast(event, data);
        };

        // subscribe to elevatedCoreTemperature event.
        // note that you should require $scope first
        // so that when the subscriber is destroyed you
        // don't create a closure over it, and te scope can clean up.
        var onElevatedEvent = function ($scope, event, handler) {
            $scope.$on(event, function (e, data) {
                // note that the handler is passed the problem domain parameters
                handler(data);
            });
        };
        // other CoreReactorChannel events would go here.
        return {
            elevatedEvent: elevatedEvent,
            onElevatedEvent: onElevatedEvent
        };
    }

    function ShareOrder($http, $templateCache) {

        var ShareOrder = function () {
            this.orders = [];
            this.order_cart_map = {};
            this.rebate_logs = {};
            this.users = {};
            this.levelData = {};
            this.shareId = 0;
            this.busy = false;
            this.noMore = false;
            this.page = 1;
            this.pageInfo = {};
            //this.referShareId = 0;
            //this.loadedShareIds = [];
            this.orderComments = {};
            this.orderCommentReplies = {};
            this.combineComment = 1;
        };

        /**
         * Overwrites obj1's values with obj2's and adds obj2's if non existent in obj1
         * @param obj1
         * @param obj2
         * @returns obj3 a new object based on obj1 and obj2
         */
        function merge_options(obj1, obj2) {
            var obj3 = {};
            for (var attrname in obj1) {
                obj3[attrname] = obj1[attrname];
            }
            for (var attrname in obj2) {
                obj3[attrname] = obj2[attrname];
            }
            return obj3;
        }

        ShareOrder.prototype.nextPage = function () {
            if (this.busy || this.noMore) return;
            if (this.page > this.pageInfo['page_count']) {
                this.noMore = true;
            }
            this.busy = true;
            var url = "/weshares/get_share_order_by_page/" + this.shareId + "/" + this.page + ".json?combineComment=" + this.combineComment;
            $http({method: 'GET', url: url, cache: $templateCache}).
                success(function (data, status) {
                    this.busy = false;
                    this.orders = this.orders.concat(data['orders']);
                    this.order_cart_map = merge_options(this.order_cart_map, data['order_cart_map']);
                    this.rebate_logs = merge_options(this.rebate_logs, data['rebate_logs']);
                    this.users = merge_options(this.users, data['users']);
                    this.levelData = merge_options(this.levelData, data['level_data']);
                    if (data['page_info']) {
                        this.pageInfo = data['page_info'];
                        //this.referShareId = data['page_info']['refer_share_id'];
                    }
                    if (data['comment_data']) {
                        this.orderComments = merge_options(this.orderComments, data['comment_data']['order_comments']);
                        this.orderCommentReplies = merge_options(this.orderCommentReplies, data['comment_data']['comment_replies']);
                    }
                    this.page = this.page + 1;
                }.bind(this)).
                error(function (data, status) {

                });
        };
        return ShareOrder;
    }

    function Locations($http, $templateCache) {
        var me = this;
        me.provinceData = [{"id": "110100", "name": "\u5317\u4eac", "parent_id": "2"}, {
            "id": "120100",
            "name": "\u5929\u6d25",
            "parent_id": "2"
        }, {"id": "130000", "name": "\u6cb3\u5317", "parent_id": "2"}, {
            "id": "140000",
            "name": "\u5c71\u897f",
            "parent_id": "2"
        }, {"id": "150000", "name": "\u5185\u8499\u53e4", "parent_id": "2"}, {
            "id": "210000",
            "name": "\u8fbd\u5b81",
            "parent_id": "5"
        }, {"id": "220000", "name": "\u5409\u6797", "parent_id": "5"}, {
            "id": "230000",
            "name": "\u9ed1\u9f99\u6c5f",
            "parent_id": "5"
        }, {"id": "310100", "name": "\u4e0a\u6d77", "parent_id": "1"}, {
            "id": "320000",
            "name": "\u6c5f\u82cf",
            "parent_id": "1"
        }, {"id": "330000", "name": "\u6d59\u6c5f", "parent_id": "1"}, {
            "id": "340000",
            "name": "\u5b89\u5fbd",
            "parent_id": "1"
        }, {"id": "350000", "name": "\u798f\u5efa", "parent_id": "4"}, {
            "id": "360000",
            "name": "\u6c5f\u897f",
            "parent_id": "1"
        }, {"id": "370000", "name": "\u5c71\u4e1c", "parent_id": "2"}, {
            "id": "410000",
            "name": "\u6cb3\u5357",
            "parent_id": "3"
        }, {"id": "420000", "name": "\u6e56\u5317", "parent_id": "3"}, {
            "id": "430000",
            "name": "\u6e56\u5357",
            "parent_id": "3"
        }, {"id": "440000", "name": "\u5e7f\u4e1c", "parent_id": "4"}, {
            "id": "450000",
            "name": "\u5e7f\u897f",
            "parent_id": "4"
        }, {"id": "460000", "name": "\u6d77\u5357", "parent_id": "4"}, {
            "id": "500100",
            "name": "\u91cd\u5e86",
            "parent_id": "7"
        }, {"id": "510000", "name": "\u56db\u5ddd", "parent_id": "7"}, {
            "id": "520000",
            "name": "\u8d35\u5dde",
            "parent_id": "7"
        }, {"id": "530000", "name": "\u4e91\u5357", "parent_id": "7"}, {
            "id": "540000",
            "name": "\u897f\u85cf",
            "parent_id": "7"
        }, {"id": "610000", "name": "\u9655\u897f", "parent_id": "6"}, {
            "id": "620000",
            "name": "\u7518\u8083",
            "parent_id": "6"
        }, {"id": "630000", "name": "\u9752\u6d77", "parent_id": "6"}, {
            "id": "640000",
            "name": "\u5b81\u590f",
            "parent_id": "6"
        }, {"id": "650000", "name": "\u65b0\u7586", "parent_id": "6"}, {
            "id": "710000",
            "name": "\u53f0\u6e7e",
            "parent_id": "8"
        }, {"id": "810000", "name": "\u9999\u6e2f", "parent_id": "8"}, {
            "id": "820000",
            "name": "\u6fb3\u95e8",
            "parent_id": "8"
        }];
        function loadCityData(provinceId) {
            //vm.calOrderTotalPrice();
            $http({method: 'GET', url: '/locations/get_city.json?provinceId=' + provinceId, cache: $templateCache}).
                success(function (data) {
                    me.cityData = data;
                }).
                error(function (data, status) {
                });
        }

        function loadCountyData(cityId) {
            $http({method: 'GET', url: '/locations/get_county.json?cityId=' + cityId, cache: $templateCache}).
                success(function (data) {
                    me.countyData = data;
                }).
                error(function (data, status) {
                });
        }

        return {
            'provinceData': this.provinceData,
            'cityData': this.cityData,
            'countyData': this.countyData,
            'loadCityData': loadCityData,
            'loadCountyData': loadCountyData
        };
    }

    function PoolProductInfo($http, $log, $templateCache) {
        return {
            prepareProductInfo: function (weshareId, callBackFunc) {
                $http({
                    method: 'GET',
                    url: '/share_product_pool/get_share_product_detail/' + weshareId + '.json',
                    cache: $templateCache
                }).success(function (data) {
                    callBackFunc(data);
                }).error(function () {
                    $log.log('get share product info error');
                    $rootScope.loadingPage = false;
                });
            }
        };
    }

    function OfflineStore() {
        return {
            'ChooseOfflineStore': ChooseOfflineStore
        };

        function ChooseOfflineStore($vm, $http, $templateCache) {
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
            $vm.offlineStores = {};
            $vm.changeOfflineStoreArea = changeOfflineStoreArea;
            $vm.showOfflineStoreDetail = showOfflineStoreDetail;
            $vm.chooseOfflineStore = chooseOfflineStore;
            $vm.showChooseOfflineStore = showChooseOfflineStore;
            //$vm.mapPanTo = mapPanTo;
            function showChooseOfflineStore() {
                changeOfflineStoreArea($vm.currentAreaCode);
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
                $http({
                    method: 'GET',
                    url: '/commonApi/get_offline_store/' + code + '.json',
                    cache: $templateCache
                }).success(function (data) {
                    $vm.offlineStores[code] = data;
                }).error(function () {
                });
            }
        }
    }
})(window, window.angular);