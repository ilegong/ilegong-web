#!/bin/sh

uglifyjs ./webroot/static/weshares/js/angular.min.js  ./webroot/static/weshares/js/angular-ui-router.min.js ./webroot/static/weshares/js/underscore-min.js  ./webroot/static/weshares/js/app.js ./webroot/static/weshares/js/edit-controller.js ./webroot/static/weshares/js/view-controller.js -o ./webroot/static/weshares/js/weshare.min.js

cat ./webroot/static/weshares/css/main.css ./webroot/static/weshares/css/site-common.css ./webroot/css/font-awesome-4.4.0/css/font-awesome.min.css | cleancss -o ./webroot/static/weshares/css/weshare.min.css

cat ./webroot/static/weshares/css/common.css ./webroot/static/weshares/css/index-view.css ./webroot/static/weshares/css/site-common.css | cleancss -o ./webroot/static/weshares/css/index.min.css