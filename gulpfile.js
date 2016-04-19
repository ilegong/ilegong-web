var gulp = require('gulp');
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');
var cleancss = require('gulp-cleancss');

var all_js_task = [
  'weshare.min.js',
  'avatar.min.js',
  'opt.min.js',
  'share-order-list.min.js',
  'user-share-info.min.js',
  'faq.min.js',
  'user-info.min.js'
];

var all_css_task = [
  'weshare.min.css',
  'index.min.css',
  'opt.min.css',
  'product_pool.min.css',
  'share-info.min.css',
  'user-info.min.css'
];

var src_weshare_min_js = [
  'webroot/static/weshares/js/me-lazyload.js',
  'webroot/static/weshares/js/app.js',
  'webroot/static/weshares/js/offline-store.js',
  'webroot/static/weshares/js/edit-controller.js',
  'webroot/static/weshares/js/view-consignee-controller.js',
  'webroot/static/weshares/js/edit-consignee-controller.js',
  'webroot/static/weshares/js/view-controller.js',
  'webroot/static/weshares/js/pool-product-factory.js',
  'webroot/static/weshares/js/view-product-info.js',
];

var src_avatar_min_js = [
  'webroot/static/user/layer.m.js',
  'webroot/static/user/jquery.crop.js',
];

var src_opt_min_js = [
  'webroot/static/opt/js/lazyload.min.js',
  'webroot/static/opt/js/opt.js'
];

var src_share_order_list_min_js = [
  'webroot/static/weshares/js/share-order-list.js',
];

var src_user_share_info_min_js = [
  'webroot/static/opt/js/lazyload.min.js',
  'webroot/static/weshares/js/user-share-info.js'
];

var src_faq_min_js = [
  'webroot/static/share_faq/custom/word-limit.js',
  'webroot/static/share_faq/custom/faq.js'
];

var src_user_info_min_js = [
  'webroot/static/weshares/js/me-lazyload.js',
  'webroot/static/weshares/js/user-info-app.js',
  'webroot/static/weshares/js/user-list-data.js'
];

var src_weshare_min_css = [
  'webroot/static/weshares/css/main.css',
  'webroot/static/weshares/css/site-common.css',
  'webroot/static/weshares/css/share-balance-view.css',
  'webroot/static/weshares/css/share.css',
];

var src_index_min_css = [
  'webroot/static/weshares/css/common.css',
  'webroot/static/weshares/css/index-view.css',
  'webroot/static/weshares/css/site-common.css',
  'webroot/static/weshares/css/tab.css',
];

var src_opt_min_css = [
  'webroot/static/opt/css/postinfo.css',
  'webroot/static/opt/css/opt.css',
];

var src_product_pool_min_css = [
  'webroot/static/weshares/css/site-common.css',
  'webroot/static/product_pool/css/product_pool.css',
];

var src_share_info_min_css = [
  'webroot/static/weshares/css/share-info.css',
];

var src_user_info_min_css = [
  'webroot/static/weshares/css/user-info.css',
];

var all_task = all_js_task.concat(all_css_task);
var all_js_src = src_weshare_min_js
.concat(src_avatar_min_js)
.concat(src_opt_min_js)
.concat(src_share_order_list_min_js)
.concat(src_user_share_info_min_js)
.concat(src_faq_min_js)
.concat(src_user_info_min_js);

var all_css_src = src_weshare_min_css
.concat(src_index_min_css)
.concat(src_opt_min_css)
.concat(src_product_pool_min_css)
.concat(src_share_info_min_css)
.concat(src_user_info_min_css);


var all_src = all_js_src.concat(all_css_src);

gulp.task('default', ['js_task', 'css_task'], function() {
});

gulp.task('dev', ['js_task', 'css_task'], function (){
  // 查看是属于那个任务的依赖的改动, 然后编译相应的任务就可以了, 写在回调里面
  gulp.watch(all_src, ['js_task', 'css_task']);
});

gulp.task('js_task', all_js_task, function() {
});

gulp.task('css_task', all_css_task, function() {
});

gulp.task('weshare.min.js', function() {
  gulp.src(src_weshare_min_js)
  .pipe(concat('weshare.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/weshares/js/'));
});

gulp.task('avatar.min.js', function() {
  gulp.src(src_avatar_min_js)
  .pipe(concat('avatar.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/user/'));
});

gulp.task('opt.min.js', function() {
  gulp.src(src_opt_min_js)
  .pipe(concat('opt.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/opt/js/'));
});

gulp.task('share-order-list.min.js', function() {
  gulp.src(src_share_order_list_min_js)
  .pipe(concat('share-order-list.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/weshares/js/'));
});

gulp.task('user-share-info.min.js', function() {
  gulp.src(src_user_share_info_min_js)
  .pipe(concat('user-share-info.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/weshares/js/'));
});

gulp.task('faq.min.js', function() {
  gulp.src(src_faq_min_js)
  .pipe(concat('faq.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/share_faq/custom/'));
});

gulp.task('user-info.min.js', function() {
  gulp.src(src_user_info_min_js)
  .pipe(concat('user-info.min.js'))
  .pipe(uglify({mangle: false}))
  .pipe(gulp.dest('webroot/static/weshares/js/'));
});

gulp.task('weshare.min.css', function() {
  gulp.src(src_weshare_min_css)
  .pipe(concat('weshare.min.css'))
  .pipe(cleancss({keepBreaks: false}))
  .pipe(gulp.dest('webroot/static/weshares/css/'));
});

gulp.task('index.min.css', function() {
  gulp.src(src_index_min_css)
  .pipe(concat('index.min.css'))
  .pipe(cleancss({keepBreaks: false}))
  .pipe(gulp.dest('webroot/static/weshares/css/'));
});

gulp.task('opt.min.css', function() {
  gulp.src(src_opt_min_css)
  .pipe(concat('opt.min.css'))
  .pipe(cleancss({keepBreaks: false}))
  .pipe(gulp.dest('webroot/static/opt/css/'));
});

gulp.task('product_pool.min.css', function() {
  gulp.src(src_product_pool_min_css)
  .pipe(concat('product_pool.min.css'))
  .pipe(cleancss({keepBreaks: false}))
  .pipe(gulp.dest('webroot/static/product_pool/css/'));
});

gulp.task('share-info.min.css', function() {
  gulp.src(src_share_info_min_css)
  .pipe(concat('share-info.min.css'))
  .pipe(cleancss({keepBreaks: false}))
  .pipe(gulp.dest('webroot/static/weshares/css/'));
});

gulp.task('user-info.min.css', function() {
  gulp.src(src_user_info_min_css)
  .pipe(concat('user-info.min.css'))
  .pipe(cleancss({keepBreaks: false}))
  .pipe(gulp.dest('webroot/static/weshares/css/'));
});
