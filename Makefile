# make 貌似不太适合做这个, 以后试试npm吧.

DIR_WESHARES = webroot/static/weshares
DIR_WESHARES_JS = ${DIR_WESHARES}/js
DIR_WESHARES_CSS = ${DIR_WESHARES}/css
DIR_STATIC_OPT = webroot/static/opt
DIR_STATIC_OPT_JS = ${DIR_STATIC_OPT}/js
DIR_STATIC_OPT_CSS = ${DIR_STATIC_OPT}/css

ALL_JS = \
    ${DIR_WESHARES_JS}/weshare.min.js \
    ${DIR_STATIC_OPT_JS}/opt.min.js \
    ${DIR_WESHARES_JS}/share-order-list.min.js \
    ./webroot/static/user/avatar.min.js \
    ${DIR_WESHARES_JS}/user-share-info.min.js \
    ./webroot/static/share_faq/custom/faq.min.js \
    ${DIR_WESHARES_JS}/user-info.min.js

ALL_CSS = \
    ./webroot/static/product_pool/css/product_pool.min.css \
    ${DIR_WESHARES_CSS}/share-info.min.css \
    ${DIR_WESHARES_CSS}/user-info.min.css \
    ${DIR_WESHARES_CSS}/weshare.min.css \
    ${DIR_WESHARES_CSS}/index.min.css \
    ${DIR_STATIC_OPT_CSS}/opt.min.css

all: ${ALL_JS} ${ALL_CSS}

${DIR_WESHARES_JS}/weshare.min.js: ${DIR_WESHARES_JS}/me-lazyload.js \
	${DIR_WESHARES_JS}/app.js \
	${DIR_WESHARES_JS}/offline-store.js \
	${DIR_WESHARES_JS}/edit-controller.js \
	${DIR_WESHARES_JS}/view-consignee-controller.js \
	${DIR_WESHARES_JS}/edit-consignee-controller.js \
	${DIR_WESHARES_JS}/view-controller.js \
	${DIR_WESHARES_JS}/pool-product-factory.js \
	${DIR_WESHARES_JS}/view-product-info.js
	uglifyjs $^ -o $@

${DIR_STATIC_OPT_JS}/opt.min.js: ${DIR_STATIC_OPT_JS}/lazyload.min.js ${DIR_STATIC_OPT_JS}/opt.js
	uglifyjs $^ -o $@

${DIR_WESHARES_JS}/share-order-list.min.js: ${DIR_WESHARES_JS}/share-order-list.js
	uglifyjs $^ -m -o $@

./webroot/static/user/avatar.min.js: ./webroot/static/user/layer.m.js \
	./webroot/static/user/jquery.crop.js
	uglifyjs $^ -o $@

# this use https://github.com/jieyou/lazyload which is not the official
# jquery_lazyload release, so I can't import it from BootCDN
${DIR_WESHARES_JS}/user-share-info.min.js: ${DIR_STATIC_OPT_JS}/lazyload.min.js \
	${DIR_WESHARES_JS}/user-share-info.js
	uglifyjs $^ -o $@

./webroot/static/share_faq/custom/faq.min.js: ./webroot/static/share_faq/custom/word-limit.js \
	./webroot/static/share_faq/custom/faq.js
	uglifyjs $^ -m -o $@

${DIR_WESHARES_JS}/user-info.min.js: ${DIR_WESHARES_JS}/me-lazyload.js \
	${DIR_WESHARES_JS}/user-info-app.js \
	${DIR_WESHARES_JS}/user-list-data.js
	uglifyjs $^ -o $@

./webroot/static/product_pool/css/product_pool.min.css: ${DIR_WESHARES_CSS}/site-common.css \
	./webroot/static/product_pool/css/product_pool.css
	cat $^ | cleancss -o $@

${DIR_WESHARES_CSS}/share-info.min.css: ${DIR_WESHARES_CSS}/share-info.css
	cat $^ | cleancss -o $@

${DIR_WESHARES_CSS}/user-info.min.css: ${DIR_WESHARES_CSS}/user-info.css
	cat $^ | cleancss -o $@

${DIR_WESHARES_CSS}/weshare.min.css: ${DIR_WESHARES_CSS}/main.css \
	${DIR_WESHARES_CSS}/site-common.css \
	${DIR_WESHARES_CSS}/share-balance-view.css \
	${DIR_WESHARES_CSS}/share.css
	cat $^ | cleancss -o $@

${DIR_WESHARES_CSS}/index.min.css: ${DIR_WESHARES_CSS}/common.css \
	${DIR_WESHARES_CSS}/index-view.css \
	${DIR_WESHARES_CSS}/site-common.css \
	${DIR_WESHARES_CSS}/tab.css
	cat $^ | cleancss -o $@

${DIR_STATIC_OPT_CSS}/opt.min.css: ${DIR_STATIC_OPT_CSS}/postinfo.css \
	${DIR_STATIC_OPT_CSS}/opt.css
	cat $^ | cleancss -o $@

.PHONY: clean

clean:
	-rm ${ALL_JS} ${ALL_CSS}
