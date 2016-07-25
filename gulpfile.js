var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var cleancss = require('gulp-cleancss');
var sass = require('gulp-sass');
var _ = require('underscore');
var del = require('del');
var vinylPaths = require('vinyl-paths');

var all_js = [
  {
    name: 'weshare.min.js',
    sources: [
      'webroot/src/scripts/module-filters.js',
      'webroot/src/scripts/module-directives.js',
      'webroot/src/scripts/module-services.js',
      'webroot/src/scripts/app.js',
      'webroot/src/scripts/subscription-controller.js'
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'index.min.js',
    sources: [
      'webroot/src/scripts/index-products-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'weshare-view.min.js',
    sources: [
      'webroot/src/scripts/consignee-view-controller.js',
      'webroot/src/scripts/consignee-edit-controller.js',
      'webroot/src/scripts/weshare-view-controller.js',
      'webroot/src/scripts/pool-product-factory.js',
      'webroot/src/scripts/share-product-view-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'weshare-edit.min.js',
    sources: [
      'webroot/src/scripts/weshare-edit-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'share-opt-index.min.js',
    sources: [
      'webroot/src/scripts/share-opt-index-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'avatar.min.js',
    sources: [
      'webroot/static/user/layer.m.js',
      'webroot/static/user/jquery.crop.js',
    ],
    dist: 'webroot/static/user/'
  }, {
    name: 'share-order-list.min.js',
    sources: [
      'webroot/static/weshares/js/share-order-list.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'faq.min.js',
    sources: [
      'webroot/static/share_faq/custom/word-limit.js',
      'webroot/static/share_faq/custom/faq.js'
    ],
    dist: 'webroot/static/share_faq/custom/'
  }, {
    name: 'user-info.min.js',
    sources: [
      'webroot/src/scripts/user-list-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  },{
    name: 'sub-list.min.js',
    sources: [
      'webroot/src/scripts/sub-list-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'get-user-info.min.js',
    sources: [
      'webroot/src/scripts/get-user-info-controller.js'
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'tutorial.min.js',
    sources: [
      'webroot/src/scripts/tutorial-binding-card-controller.js',
      'webroot/src/scripts/tutorial-binding-mobile-controller.js',
    ],
    dist: 'webroot/static/weshares/js/'
  },{
        name: 'pay-result.min.js',
        sources: [
            'webroot/src/scripts/pay-result-controller.js'
        ],
        dist: 'webroot/static/weshares/js/'
    },{
        name: 'user-coupons-list-controller.min.js',
        sources: [
            'webroot/src/scripts/user-coupons-list-controller.js'
        ],
        dist: 'webroot/static/weshares/js/'
    },{
        name: 'read-share-count.min.js',
        sources: [
            'webroot/src/scripts/read-share-count-controller.js'
        ],
        dist: 'webroot/static/weshares/js/'
    },{
        name: 'user-rebate-list.min.js',
        sources: [
            'webroot/src/scripts/user-rebate-list-controller.js'
        ],
        dist: 'webroot/static/weshares/js/'
    }, {
    name: 'vote.min.js',
    sources: [
      'webroot/src/scripts/vote-signup-controller.js',
      'webroot/src/scripts/vote-event-controller.js'
    ],
    dist: 'webroot/static/weshares/js/'
  },
];

var all_css = [
  {
    name: 'site-common.min.css',
    sources: [
      'webroot/src/scss/site-common.scss',
      'webroot/src/scss/iconfont.css'
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'weshare-view.min.css',
    sources: [
      'webroot/src/scss/main.css',
      'webroot/src/scss/share-balance-view.css',
      'webroot/src/scss/share.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'weshare-edit.min.css',
    sources: [
      'webroot/src/scss/weshare-edit.scss',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'share-info.min.css',
    sources: [
      'webroot/static/weshares/css/share-info.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'fans-list.min.css',
    sources: [
      'webroot/src/scss/fans-list.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'fansrule.min.css',
    sources: [
      'webroot/src/scss/fansrule.css',
    ],
    dist: 'webroot/static/weshares/css/'
  },{
    name: 'get-user-info.min.css',
    sources: [
      'webroot/src/scss/get-user-info.scss',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'tutorial.min.css',
    sources: [
      'webroot/src/scss/tutorial.scss',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'read-share-count.min.css',
    sources: [
      'webroot/src/scss/read-share-count.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }
];

var js_tasks = [];
var css_tasks = [];

for (var i = 0; i < all_js.length; i++) {
  var name = all_js[i].name;
  var sources = all_js[i].sources;
  var dist = all_js[i].dist;
  var task_name = function (name, sources, dist) {
    gulp.task(name, function () {
      gulp.src(sources)
        .pipe(concat(name))
        .pipe(uglify({mangle: false}))
        .pipe(gulp.dest(dist));
    });
    return name;
  }(name, sources, dist);
  console.log('add task ' + task_name);
  js_tasks.push(task_name);
}

for (var i = 0; i < all_css.length; i++) {
  var name = all_css[i].name;
  var sources = all_css[i].sources;
  var dist = all_css[i].dist;
  var task_name = function (name, sources, dist) {
    gulp.task(name, function () {
      gulp.src(sources)
        .pipe(sass())
        .pipe(concat(name))
        .pipe(cleancss({keepBreaks: false}))
        .pipe(gulp.dest(dist));
    });
    return name;
  }(name, sources, dist);
  css_tasks.push(task_name);
}

var tasks = _.union(_.map(all_css, function (file) {
  return file.name;
}), _.map(all_js, function (file) {
  return file.name;
}));
gulp.task('default', tasks, function () {
  console.log("Default donef.");
});

gulp.task('dev', tasks, function () {
  for (var i = 0; i < all_js.length; i++) {
    gulp.watch(all_js[i].sources, [all_js[i].name]);
  }

  for (var i = 0; i < all_css.length; i++) {
    gulp.watch(all_css[i].sources, [all_css[i].name]);
  }
});

//clean 任务单独执行，一般用不到
gulp.task('clean', function () {
  gulp.src(_.map(all_css, function (file) {
    return file.dist + file.name;
  })).pipe(vinylPaths(del));
  gulp.src(_.map(all_js, function (file) {
    return file.dist + file.name;
  })).pipe(vinylPaths(del));
});