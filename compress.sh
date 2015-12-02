#!/bin/sh

uglifyjs ./webroot/static/weshares/js/angular.min.js  ./webroot/static/weshares/js/ng-infinite-scroll.min.js ./webroot/static/weshares/js/me-lazyload.js ./webroot/static/weshares/js/underscore-min.js  ./webroot/static/weshares/js/app.js ./webroot/static/weshares/js/edit-controller.js ./webroot/static/weshares/js/view-controller.js ./webroot/static/weshares/js/view-product-info.js -o ./webroot/static/weshares/js/weshare.min.js

cat ./webroot/static/weshares/css/main.css ./webroot/static/weshares/css/site-common.css ./webroot/css/font-awesome-4.4.0/css/font-awesome.css | cleancss -o ./webroot/static/weshares/css/weshare.min.css

cat ./webroot/static/weshares/css/common.css ./webroot/static/weshares/css/index-view.css ./webroot/static/weshares/css/site-common.css ./webroot/static/weshares/css/tab.css  | cleancss -o ./webroot/static/weshares/css/index.min.css

uglifyjs ./webroot/static/opt/js/lazyload.min.js ./webroot/static/opt/js/opt.js  -o ./webroot/static/opt/js/opt.min.js

cat ./webroot/static/opt/css/postinfo.css ./webroot/static/opt/css/opt.css | cleancss -o  ./webroot/static/opt/css/opt.min.css

uglifyjs ./webroot/static/weshares/js/share-order-list.js -m  -o ./webroot/static/weshares/js/share-order-list.min.js

uglifyjs ./webroot/static/user/layer.m.js ./webroot/static/user/touch-0.2.14.min.js ./webroot/static/user/jquery.crop.js   -o ./webroot/static/user/avatar.min.js

uglifyjs ./webroot/static/weshares/js/user-share-info.js -m  -o ./webroot/static/weshares/js/user-share-info.min.js

uglifyjs ./webroot/static/share_faq/custom/word-limit.js ./webroot/static/share_faq/custom/faq.js -m -o ./webroot/static/share_faq/custom/faq.min.js

