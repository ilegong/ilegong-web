/**
 * jQuery 'onImagesLoaded' plugin v1.2.2 (Updated May 13, 2013)
 * Fires callback functions when images have loaded within a particular selector.
 *
 * Copyright (c) Cirkuit Networks, Inc. (http://www.cirkuit.net), 2008-2013.
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 *
 * For documentation and usage, visit "http://www.cirkuit.net/projects/jquery/onImagesLoad/"
 */
(function(c){c.fn.onImagesLoad=function(b){"function"==typeof b&&(b={all:b});var a=this;a.opts=c.extend({},c.fn.onImagesLoad.defaults,b);a.opts.selectorCallback&&!a.opts.all&&(a.opts.all=a.opts.selectorCallback);a.opts.itemCallback&&!a.opts.each&&(a.opts.each=a.opts.itemCallback);b=!c.support.appendChecked;var f=!b&&!c.support.input,j=!b&&!f&&!c.support.clearCloneStyle&&!c.support.cors,h=b||f||j;a.bindEvents=function(e,b,g){if(0===e.length)a.opts.callbackIfNoImagesExist&&g&&g(b);else{var d=[];e.jquery||
(e=c(e));e.each(function(a){var f=this.src;h||(this.src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==");c(this).bind("load",function(){0>jQuery.inArray(a,d)&&(d.push(a),d.length==e.length&&g&&g.call(b,b))});if(h){if(this.complete||void 0===this.complete)this.src=f}else this.src=f})}};var d=[];a.each(function(){if(a.opts.each){var b;b="IMG"==this.tagName?this:c("img",this);a.bindEvents(b,this,a.opts.each)}a.opts.all&&("IMG"==this.tagName?d.push(this):c("img",this).each(function(){d.push(this)}))});
a.opts.all&&a.bindEvents(d,this,a.opts.all);return a.each(function(){})};c.fn.onImagesLoad.defaults={all:null,each:null,callbackIfNoImagesExist:!1}})(jQuery);