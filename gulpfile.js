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
    name: 'index.min.js',
    sources: [
      'webroot/static/weshares/js/me-lazyload.js',
      'webroot/static/weshares/js/app.js',
      'webroot/src/scripts/index.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'weshare.min.js',
    sources: [
      'webroot/static/weshares/js/me-lazyload.js',
      'webroot/static/weshares/js/app.js',
      'webroot/static/weshares/js/offline-store.js',
      'webroot/static/weshares/js/edit-controller.js',
      'webroot/static/weshares/js/view-consignee-controller.js',
      'webroot/static/weshares/js/edit-consignee-controller.js',
      'webroot/static/weshares/js/view-controller.js',
      'webroot/static/weshares/js/pool-product-factory.js',
      'webroot/static/weshares/js/view-product-info.js',
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
    name: 'opt.min.js',
    sources: [
      'webroot/static/opt/js/lazyload.min.js',
      'webroot/static/opt/js/opt.js'
    ],
    dist: 'webroot/static/opt/js/'
  }, {
    name: 'share-order-list.min.js',
    sources: [
      'webroot/static/weshares/js/share-order-list.js',
    ],
    dist: 'webroot/static/weshares/js/'
  }, {
    name: 'user-share-info.min.js',
    sources: [
      'webroot/static/opt/js/lazyload.min.js',
      'webroot/static/weshares/js/user-share-info.js'
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
      'webroot/static/weshares/js/me-lazyload.js',
      'webroot/static/weshares/js/user-info-app.js',
      'webroot/static/weshares/js/user-list-data.js'
    ],
    dist: 'webroot/static/weshares/js/'
  }];

var all_css = [
  {
    name: 'site-common.css',
    sources: [
      'webroot/src/scss/site-common.scss',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'weshare.min.css',
    sources: [
      'webroot/static/weshares/css/main.css',
      'webroot/static/weshares/css/share-balance-view.css',
      'webroot/static/weshares/css/share.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'index.min.css',
    sources: [
      'webroot/src/scss/index.scss'
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'opt.min.css',
    sources: [
      'webroot/static/opt/css/postinfo.css',
      'webroot/static/opt/css/opt.css',
    ],
    dist: 'webroot/static/opt/css/'
  }, {
    name: 'product_pool.min.css',
    sources: [
      'webroot/static/product_pool/css/product_pool.css',
    ],
    dist: 'webroot/static/product_pool/css/'
  }, {
    name: 'share-info.min.css',
    sources: [
      'webroot/static/weshares/css/share-info.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }, {
    name: 'user-info.min.css',
    sources: [
      'webroot/static/weshares/css/user-info.css',
    ],
    dist: 'webroot/static/weshares/css/'
  }];

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

var tasks = _.union(_.map(all_css, function(file){return file.name;}),_.map(all_js, function(file){return file.name;}) );
gulp.task('default', tasks, function () {
  console.log("Default done.");
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
  gulp.src(_.map(all_css, function(file){
    return file.dist + file.name;
  })).pipe(vinylPaths(del));
  gulp.src(_.map(all_js, function(file){
    return file.dist + file.name;
  })).pipe(vinylPaths(del));
});