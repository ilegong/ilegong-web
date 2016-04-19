var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var cleancss = require('gulp-cleancss');

var all_js = [{
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

var all_css = [{
  name: 'weshare.min.css',
  sources: [
    'webroot/static/weshares/css/main.css',
    'webroot/static/weshares/css/site-common.css',
    'webroot/static/weshares/css/share-balance-view.css',
    'webroot/static/weshares/css/share.css',
  ],
  dist: 'webroot/static/weshares/css/'
}, {
  name: 'index.min.css',
  sources: [
    'webroot/static/weshares/css/common.css',
    'webroot/static/weshares/css/index-view.css',
    'webroot/static/weshares/css/site-common.css',
    'webroot/static/weshares/css/tab.css',
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
    'webroot/static/weshares/css/site-common.css',
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

gulp.task('default', ['js_task', 'css_task'], function() {
});

gulp.task('dev', ['js_task', 'css_task'], function() {
  var watchers = [];
  for (var i = 0; i < all_js.length; i++) {
    watchers.push({
      watcher: gulp.watch(all_js[i].sources),
      type: 'js',
      'ball': all_js[i]
    });
  }

  for (var i = 0; i < all_css.length; i++) {
    watchers.push({
      watcher: gulp.watch(all_css[i].sources),
      type: 'css',
      'ball': all_css[i]
    });
  }

  watchers.map(function (item) {
    item.watcher.on('change', function(event){
      console.log("File changed: " + event.path);
      var pipe = gulp.src(item.ball.sources).pipe(concat(item.ball.name));
      if (item.type == 'js') {
        pipe = pipe.pipe(uglify({mangle: false}));
      } else {
        pipe = pipe.pipe(cleancss({keepBreaks: false}));
      }
      pipe = pipe.pipe(gulp.dest(item.ball.dist));
    });
  });
});

gulp.task('js_task', function() {
  for (var i = 0; i < all_js.length; i++) {
    gulp.src(all_js[i].sources)
    .pipe(concat(all_js[i].name))
    .pipe(uglify({mangle: false}))
    .pipe(gulp.dest(all_js[i].dist));
  }
});

gulp.task('css_task', function() {
  for (var i = 0; i < all_css.length; i++) {
    gulp.src(all_css[i].sources)
    .pipe(concat(all_css[i].name))
    .pipe(cleancss({keepBreaks: false}))
    .pipe(gulp.dest(all_css[i].dist));
  }
});
