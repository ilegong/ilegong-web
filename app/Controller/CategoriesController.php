<?php
class CategoriesController extends AppController {

    var $name = 'Categories';

    public function tag($tagSlug = '') {

        if ($tagSlug == '') {
            $this->view();
            return;
        }

        $current_cateid = -1;
        $page = 1;
        $pagesize = 60;
        $this->loadModel('ProductTag');
        $productTag = $this->ProductTag->find('first', array('conditions' => array(
            'slug' => $tagSlug,
            'published' => 1
        )));
        if (empty($productTag)) {
            $this->view();
            return;
        }
        $conditions = array('Product' .'.deleted'=>0, 'Product' .'.published'=>1);
        $conditions['Product' . '.recommend >'] = 0;

        $join_conditions = array(
            array(
                'table' => 'product_product_tags',
                'alias' => 'Tag',
                'conditions' => array(
                    'Tag.product_id = Product.id',
                    'Tag.tag_id' => $productTag['ProductTag']['id']
                ),
                'type' => 'RIGHT',
            )
        );
        $orderBy = 'Tag.recommend desc, Product.recommend desc';

        $this->loadModel('Product');
        $list = $this->Product->find('all', array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'order' => $orderBy,
                'fields' => Product::PRODUCT_PUBLIC_FIELDS,
                'limit' => $pagesize,
                'page' => $page)
        );
        if ($page == 1 && count($list) < $pagesize) {
            $total = count($list);
        } else {
            $total = $this->{$data_model}->find('count', array(
                'conditions' => $conditions,
                'joins' => $join_conditions
            ));
        }

        $productList = array();
        $brandIds = array();
        foreach ($list as $val) {
            $productList[] = $val['Product'];
            $brandIds[] = $val['Product']['brand_id'];
        }

        $this->setHasOfferBrandIds();

        $this->pageTitle = $productTag['ProductTag']['name'];
        $navigation = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $this->set('sub_title', $productTag['ProductTag']['name']);
        $this->set('tag', $productTag['ProductTag']);
        $this->set('brands', $mappedBrands);
        $this->set('total', $total);
        $this->set('current_cateid', $current_cateid);
        $this->set('category_control_name', 'products');
        $this->set('navigations', $navigation);
        $this->set('data_list', $productList);
        $this->set('withBrandInfo', true);

        $this->set('op_cate', OP_CATE_HOME);

        $this->set('_serialize', array('brands', 'data_list', 'sub_title'));
        $this->set('history',$_REQUEST['history']);
    }

    public function special_list($slug) {

        $current_cateid = -1;
        $this->loadModel('SpecialList');
        $specialList = $this->SpecialList->find('first', array('conditions' => array(
            'slug' => $slug,
            'published' => 1
        )));

        if (!empty($specialList)) {

            $limit = $specialList['SpecialList']['showed_count'];
            $limit = $limit ? $limit : 100;

            $join_conditions = array(
                array(
                    'table' => 'product_specials',
                    'alias' => 'Special',
                    'conditions' => array(
                        'Special.product_id = Product.id',
                    ),
                    'type' => 'INNER',
                )
            );
            $orderBy = 'Special.recommend desc, Product.recommend desc';
            $conditions = array('Product.deleted'=>0, 'Product.published'=>1, 'Special.published' => 1, 'Special.special_id' => $specialList['SpecialList']['id']);

            $this->loadModel('Product');
            $list = $this->Product->find('all', array(
                    'conditions' => $conditions,
                    'joins' => $join_conditions,
                    'order' => $orderBy,
                    'fields' => array('Product.id', 'Product.name','Product.brand_id','Product.price','Product.original_price', 'Product.created', 'Product.coverimg', 'Product.slug', 'Special.*'),
                    'limit' => $limit)
            );
            if (count($list) < $limit) {
                $total = count($list);
            } else {
                $total = $this->Product->find('count', array(
                    'conditions' => $conditions,
                    'joins' => $join_conditions
                ));
            }

            $range = array('start' => $specialList['SpecialList']['start'] , 'end' => $specialList['SpecialList']['end']);
            $brandIds = array();
            $cartModel = ClassRegistry::init('Cart');
            foreach ($list as &$val) {
                $brandIds[] = $val['Product']['brand_id'];
                $left = $val['Special']['limit_total'] - total_sold($val['Product']['id'], $range, $cartModel);
                $val['Special']['total_left'] = $left >= 0 ? $left : 0;
            }
        } else {
            $brandIds = array();
            $mappedBrands = array();
            $total = 0;
        }

        $specialName = $specialList['SpecialList']['name'];
        $this->pageTitle = $specialName;
        $navigation = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $this->set('sub_title', $specialName);
        $this->set('brands', $mappedBrands);
        $this->set('total', $total);
        $this->set('current_cateid', $current_cateid);
        $this->set('category_control_name', 'products');
        $this->set('navigations', $navigation);
        $this->set('data_list', $list);
        $this->set('special_list', $specialList);
        $this->set('withBrandInfo', true);

        $this->set('op_cate', OP_CATE_HOME);
    }

    public function listCategories() {
        $productTags = $this->findVisibleTags();
        $descs = array(
            3 => '苹果/柠檬/橙子/石榴/梨',
            5 => '枸杞/核桃/葡萄干/无花果/枣',
            6 => '白蜜/蜂蜜/花茶/牛奶/酒',
            8 => '马卡龙/牛轧酥饼/土凤梨酥/曲奇',
            4 => '散养鸡蛋/散养鸡/猪肉/排骨',
            9 => '大米/小米/青稞米/姜/木耳/黄粑',
            10 => '大闸蟹/虾米/海带',
            7 => '润喉糖/玉米脆片'
        );
        foreach($productTags as &$tag) {
            $tag['ProductTag']['description'] = $descs[$tag['ProductTag']['id']];
        }
        $this->set('productTags', $productTags);
        $this->set('op_cate', OP_CATE_CATEGORIES);
        $this->pageTitle = __('分类');
    }

    public function mobileHome() {
        $this->productsHome(true);

        $zutuangous = array(
            array('img' => "/img/banner/banner_zutuangou3.jpg", 'url' => "/groupons/view/chengzi.html?from=home1", 'id' => 0),
            array('img' => "/img/banner/banner_zutuangou2.jpg", 'url' => "/groupons/view/chengzi.html?from=home2", 'id' => 0),
//            array('img' => "/img/banner/banner_zutuangou.jpg", 'url' => "/groupons/view/gonggan.html?from=home", 'id' => 0),
        );

        $mobileTagIds = array(3,5,8,12,9,6,4,10);
        $mobileTags = $this->findTagsByIds($mobileTagIds);
        $mobileTags = Hash::combine($mobileTags,'{n}.ProductTag.id','{n}.ProductTag');
        $this->set('mobile_tag',$mobileTags);
        $specTagIds = array(13,14,15);
        $specTags = $this->findTagsByIds($specTagIds);
        $specTags = Hash::combine($specTags,'{n}.ProductTag.id','{n}.ProductTag');
        $this->set('spec_tags',$specTags);
        $bannerItems = array(
            array('img' => "/img/banner/banner_cao_mei_cai_zhai.jpg", 'url' => "/products/20150119/xing_shou_xiu_cao_mei_yuan_cai_zhai_2jin.html", 'id' => 697),
            $zutuangous[mt_rand(0, 1000) % count($zutuangous)],
            array('img' => "/img/banner/banner_shibin.jpg?v2", 'url' => "/products/20141204/fu_ping_te_ji_jian_shi_bing.html", 'id' => 331),
            array('img' => "/img/banner/banner_gonglianzi.jpg", 'url' => "/products/20141229/jiang_xi_gong_lian_zi.html", 'id' => 560),
            //array('img' => "/img/banner/banner_yongxing_btc.jpg?v1", 'url' => "/b/qian_yang_wan_cheng.html", 'id' => 365),
//            array('img' => "/img/banner/banner_songzi.jpg?v2", 'url' => "/products/20141117/dong_bei_hong_song_zi_1jin_zhuang.html", 'id' => 266),
//            array('img' => "/img/banner/banner_dami.jpg?v1",'url' =>  "/products/20141101/wu_chang_dao_hua_xiang_ti_qian_yu_shou_500jin_zhi_xian_bei_jing.html", 'id' => 231),
//            array('img' => "/img/banner/banner_huizao.jpg?v1", 'url' => "/products/20141117/xin_jiang_hui_zao.html", 'id' => 269),
//            array('img' => "/img/banner/banner_shiliu.jpg", 'url' => "/products/20141013/he_nan_xing_yang_he_yin_ruan_zi_shi_liu_8liang.html", 'id' => 202),
        );

        $this->loadModel('ProductTry');
        $tryings = $this->ProductTry->find_trying(2);
        if (!empty($tryings)) {
            $tryProducts = $this->Product->find_products_by_ids(Hash::extract($tryings, '{n}.ProductTry.product_id'), array(), false);
            if (!empty($tryProducts)) {
                foreach($tryings as &$trying) {
                    $prod = $tryProducts[$trying['ProductTry']['product_id']];
                    if (!empty($prod)) {
                        $trying['Product'] = $prod;
                    } else {
                        unset($trying);
                    }
                }
            }
        }

        $uid = $this->currentUser['id'];
        if (!empty($uid)) {
            $this->loadModel('Shichituan');
            $shichituan = $this->Shichituan->find_in_period($uid, get_shichituan_period());
            $is_shichi = (!empty($shichituan) || $shichituan);
            $this->set('shichiTuan', $shichituan);
        }
        $this->set('shichi_mem', $is_shichi);
        $this->set('tryings', $tryings);
        $this->set('bannerItems', $bannerItems);
        $this->set('max_show', $this->RequestHandler->isMobile()? 2 : 4);
        $this->set('_serialize', array('brands', 'tagsWithProducts', 'sub_title', 'bannerItems'));
    }

    public function productsHome($disableAutoRedirect = false) {

        if (!$disableAutoRedirect) {
            if ($this->RequestHandler->isMobile()) {
                $this->redirect('/categories/mobileHome.html');
                return;
            }
        }

        $current_cateid = CATEGORY_ID_TECHAN;
        $page = 1;
        $pagesize = 60;
        $productTags = $this->findVisibleTags();
        if (empty($productTags)) {
            $this->view();
            return;
        }

        $conditions = array('Product' .'.deleted'=>0, 'Product' .'.published'=>1);
        $conditions['Product' . '.recommend >'] = 0;

        $orderBy = /*'Tag.recommend desc,*/' Product.recommend desc';

        $brandIds = array();
        $this->loadModel('Product');
        foreach($productTags as &$tag) {

            //add class image
            $tag['ProductTag']['coverimg'] = '/img/class/classn'.$tag['ProductTag']['id'].'.png';

            $join_conditions = array(
                array(
                    'table' => 'product_product_tags',
                    'alias' => 'Tag',
                    'conditions' => array(
                        'Tag.product_id = Product.id',
                        'Tag.tag_id' => $tag['ProductTag']['id']
                    ),
                    'type' => 'RIGHT',
                )
            );
            $tag['Products'] = array();
            $products = $this->Product->find('all', array(
                    'conditions' => $conditions,
                    'joins' => $join_conditions,
                    'order' => $orderBy,
                    'fields' => explode(',', Product::PRODUCT_PUBLIC_FIELDS),
                    'limit' => ($tag['ProductTag']['size_in_home']>0?$tag['ProductTag']['size_in_home']:6),
                    'page' => $page)
            );
//            if ($page == 1 && count($list) < $pagesize) {
//                $total = count($list);
//            } else {
//                $total = $this->{$data_model}->find('count', array(
//                    'conditions' => $conditions,
//                    'joins' => $join_conditions
//                ));
//            }
            foreach($products as $p){
                $brandIds[] = $p['Product']['brand_id'];
                $tag['Products'][] = $p['Product'];
            }
        }

        $this->setHasOfferBrandIds();

        $this->pageTitle =  __('热卖');
        $navigation = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $this->set('sub_title', $productTags['ProductTag']['name']);
        $this->set('brands', $mappedBrands);
        $this->set('current_cateid', $current_cateid);
        $this->set('top_category_id', $current_cateid);
        $this->set('navigations', $navigation);
        $this->set('tagsWithProducts', $productTags);
        $this->set('withBrandInfo', true);
        $this->set('category_control_name', 'products');
        $this->set('op_cate', OP_CATE_HOME);
        $this->set('is_index',true);
    }

    public function specCategoryList($tagSlug){
        $productTag = $this->ProductTag->find('first', array('conditions' => array(
            'slug' => $tagSlug,
            'published' => 1
        )));
        $this->pageTitle=$productTag['ProductTag']['name'];
        $ids = array(16,17,18,19);
        $tags = $this->findTagsByIds($ids);
        $this->set('tags',$tags);
    }

    public function view($slug='/', $brand_id='') {
    	$this->__viewFileName = array();
    	
        $page=$_GET['page']?$_GET['page']:($this->request->params['named']['page']?$this->request->params['named']['page']:1);
        $conditions = array();
		if($GLOBALS['site_cate_id']>0 && !empty($GLOBALS['site_info'])){
			$conditions['left >']=$GLOBALS['site_info']['left'];
			$conditions['right <']=$GLOBALS['site_info']['right'];
		}
        if($slug=='/'){
        	$conditions['id'] = Configure::read('Site.index_page');
        }
        elseif(!empty($slug) && $slug != strval(intval($slug))) {
            $conditions['slug'] = $slug;
        } elseif (intval($slug)) {
            $conditions['id']=intval($slug);
        }

        if ($slug == 'techan' || $conditions['id'] == CATEGORY_ID_TECHAN) {
            $this->redirect($this->RequestHandler->isMobile() ? '/categories/mobileHome.html' : '/categories/productsHome.html');
            return;
        }

        $Category = $this->Category->find('first',array(
        		'conditions' => $conditions,
        	));
        
        if (empty($Category)) {
            throw new NotFoundException();
        }
        $current_cateid = $Category['Category']['id'];
        $left = $Category['Category']['left'];
        $right = $Category['Category']['right'];
        
        $this->Category->recursive = -1;
        $navigations = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
        /**
         * 若本栏目没有个性化模版，但上级栏目有个性化的模版时，继承使用上级的个性化模版，否则使用默认模版
         * */
        
        foreach($navigations as $k => $nav){
        	$tempslug = $nav['Category']['slug'];
        	$tempslug = str_replace('/', '_', $tempslug);
        	if($tempslug){
        		$this->__viewFileName[] = 'view_' . $tempslug;
        	}
        	// 去除站点类型的导航breadcrumb节点
        	if($navigations[$k]['Category']['model']=='website'){
        		unset($navigations[$k]);
        	}
        }
        /* view_template字段用于slug有重复时，指定特定的个性化模板 */
        if(!empty($Category['Category']['view_template'])){
        	$this->__viewFileName[] = $Category['Category']['view_template'];
        }
        
        $this->__viewFileName = array_reverse($this->__viewFileName);
        // 设置顶级栏目，与栏目名称。
        $top_cate = current($navigations);
        $top_category_id = $top_cate['Category']['id'];
        $top_category_name = $top_cate['Category']['name'];
        //栏目类型不为栏目和站点时，加载对应模块的数据列表
		if(!empty($Category['Category']['model']) && !in_array($Category['Category']['model'],array('Category','website'))){
			$data_model = Inflector::classify($Category['Category']['model']);
			$this->loadModel($data_model);
			$pagesize = intval(Configure::read($data_model.'.pagesize'));
			if(!$pagesize){
				$pagesize = 200;
			}
			$this->{$data_model}->recursive = -1;
			$conditions = array($data_model.'.deleted'=>0,$data_model.'.published'=>1);
			$orderby = '';
			if($data_model=='Product'){
				$conditions[$data_model.'.recommend >'] = 0;
				$orderby = $data_model.'.recommend desc,id desc';

                if ($brand_id) {
                    $conditions[$data_model . '.brand_id ='] = $brand_id;
                } else {
                    $conditions[$data_model . '.brand_id !='] = 18;
                }

			}
			else{
				$orderby = $data_model.'.created desc';
			}
			if(!empty($this->request->query) && !(count($this->request->query) == 1 && array_key_exists('techan_html?wx_openid', $this->request->query))){
				$conditions = getSearchOptions($this->request->query,$data_model);
			}

            if ($left + 1 == $right) { // 无子类时，直接查询本类的内容
                $conditions[$data_model . '.cate_id'] = $current_cateid;
                $datalist = $this->{$data_model}->find('all', array(
                    'conditions' => $conditions,
                    'order' => $orderby,
                    'limit' => $pagesize,
                    'page' => $page,));
                if ($page == 1 && count($datalist) < $pagesize) {
                    $total = count($datalist);
                } else {
                    $total = $this->{$data_model}->find('count', array(
                        'conditions' => $conditions
                    ));
                }
            } else { // 有子类时，查询本类及所有子类的内容
                $join_conditions = array(
                    array(
                        'table' => 'categories',
                        'alias' => 'Category',
                        'conditions' => array(
                            'Category.left >=' => $left,
                            'Category.right <=' => $right,
                            'Category.id = ' . $data_model . '.cate_id',
                        ),
                        'type' => 'inner',
                    )
                );

                $datalist = $this->{$data_model}->find('all', array(
                        'conditions' => $conditions,
                        'joins' => $join_conditions,
                        'order' => $data_model . '.updated desc',
                        'fields' => array($data_model . '.*', 'Category.*'),
                        'limit' => $pagesize,
                        'page' => $page,)
                );
                if ($page == 1 && count($datalist) < $pagesize) {
                    $total = count($datalist);
                } else {
                    $total = $this->{$data_model}->find('count', array(
                        'conditions' => $conditions,
                        'joins' => $join_conditions
                    ));
                }
            }

            $page_navi = getPageLinks($total, $pagesize, '/'.$Category['Category']['slug'].'.html', $page);
            $this->set('page_navi', $page_navi);
	
	        $Category['datalist'] = array();
//            if ($data_model=='Product' && !$brand_id) {
//                $Category['datalist'][] = array(
//                    'id' => 100000,
//                    'name' => '北京黑猪肉团购',
//                    'coverimg' => 'http://51daifan-images.stor.sinaapp.com/files/201402/thumb_m/a97f2ff8be6_0223.jpg',
//                    'slug' => 'heizhu_tuangou',
//                    'price' => '待定',
//                    'published' => '1',
//                    'brand_id' => 18,
//                    'cate_id' => 114,
//                    'comment_nums' => 16
//                );
//            }

            $brandIds = array();
	        foreach ($datalist as $val) {
	            $Category['datalist'][] = $val[$data_model];
                if ($data_model == 'Product') {
                    $brandIds[] = $val[$data_model]['brand_id'];
                }
	        }

            $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        }
        // 设置页面SEO标题、关键字、内容描述
        if (!empty($Category['Category']['seotitle'])) {
            $this->pageTitle = $Category['Category']['seotitle'];
        } else {
            $this->pageTitle = $Category['Category']['name'];
        }
        if ($Category['Category']['seodescription']) {
            $this->set('seodescription', $Category['Category']['seodescription']);
        }
        if ($Category['Category']['seokeywords']) {
            $this->set('seokeywords', $Category['Category']['seokeywords']);
        }

        if ($brand_id == 18) {
            $this->set('sub_title', '黑猪专栏');
        }

        $this->set('brands', $mappedBrands);

        $this->set('category_model_name', $data_model);
        $this->set('category_data_model', $data_model);
        // 不能使用region_control_name，会在模板中region调用生成的region_control_name覆盖掉
        $this->set('category_control_name', Inflector::tableize($data_model));
        
        $this->set('total', $total);
        $this->set('top_category_id', $top_category_id);
        $this->set('top_category_name', $top_category_name);
        $this->set('current_cateid', $current_cateid);
        $this->set('navigations', $navigations);
        $this->set('Category', $Category);
        $this->set('is_index', ($slug == 'techan' || $slug == '/'));
        if ($slug == 'share') {
            $this->set('op_cate', 'share');
        }
    }

    /**
     * @param $brandIds
     * @param $mappedBrands
     * @return array
     */
    protected function &findBrandsKeyedId($brandIds, &$mappedBrands) {
        $mappedBrands = array();
        if ($brandIds) {
            $this->loadModel('Brand');
            $brands = $this->Brand->find('all', array(
                'conditions' => array('id' => array_unique($brandIds)),
                'fields' => array('id', 'name', 'created', 'slug', 'coverimg')
            ));

            foreach ($brands as $brand) {
                $mappedBrands[$brand['Brand']['id']] = $brand;
            }
        }
        return $mappedBrands;
    }

    /**
     * @return mixed
     */
    protected function findVisibleTags() {
        $this->loadModel('ProductTag');
        $productTags = $this->ProductTag->find('all', array('conditions' => array(
            'show_in_home' => 1,
            'published' => 1
        ),
            'order' => 'priority desc'
        ));
        return $productTags;
    }

    protected function findNoVisibleTags(){
        $this->loadModel('ProductTag');
        $productTags = $this->ProductTag->find('all', array('conditions' => array(
            'show_in_home' => 0,
            'published' => 1
        ),
            'order' => 'priority desc'
        ));
        return $productTags;
    }

    protected function findTagsByIds($ids){
        $this->loadModel('ProductTag');
        $productTags = $this->ProductTag->find('all', array('conditions' => array(
            'id'=>$ids,
            'published' => 1
        ),
            'order' => 'priority desc'
        ));
        return $productTags;
    }
    public function spring(){
        $this->pageTitle="年货";
        $conditions = array('Product' .'.deleted'=>0, 'Product' .'.published'=>1);
        $conditions['Product' . '.recommend >'] = 0;

        $join_conditions = array(
            array(
                'table' => 'product_product_tags',
                'alias' => 'Tag',
                'conditions' => array(
                    'Tag.product_id = Product.id',
                    'Tag.tag_id' => 20
                ),
                'type' => 'RIGHT',
            )
        );
        $orderBy = 'Tag.recommend desc, Product.recommend desc';

        $this->loadModel('Product');
        $list = $this->Product->find('all', array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'order' => $orderBy,
                'fields' => Product::PRODUCT_PUBLIC_FIELDS,
               )
        );
        $brandIds = Hash::extract($list,'{n}.Product.brand_id');
        $productList = Hash::extract($list,'{n}.Product');
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $uid = $this->currentUser['id'];
        $this->loadModel('CouponItem');
        $pid_lists=Hash::extract($productList,'{n}.id');
        $rtn=$this->CouponItem->find_got_spring_festival_coupons($uid, $pid_lists);
        $this->set('brands', $mappedBrands);
        $this->set('data_list', $productList);
        $this->set('pid_coupon',$rtn);
        $spring_coupons = $this->CouponItem->find_got_spring_festival_coupons_infos($pid_lists);
        $this->set('spring_coupons',$spring_coupons);
        $this->set('not_show_nav',true);
        $uid = $this->Session->read('Auth.User.id');
        $this->set('uid',$uid);
        $temp_pid = $this->Session->read('coupon-id');
        $this->Session->delete('coupon-id');
        $this->set('pid',$temp_pid);
        $key = key_cache_sub($uid,'spring');
        $cache_pid = Cache::read($key);
        if(!empty($cache_pid)){
            $cM = ClassRegistry::init('CouponItem');
            $got = $cM->add_spring_festival_coupon($this->currentUser['id'], $cache_pid);
            if($got){
                $this->set('lingqu',true);
            }
            Cache::delete($key);
        }
    }
    public function mobile_get_spring_coupon(){
        $this->autoRender=false;
        $uid = $this->currentUser['id'];
        if($_GET['pid'] && $uid){
            $pid = intval($_GET['pid']);
            if($this->is_weixin()){
                $this->loadModel('WxOauth');
                if(!$this->WxOauth->is_subscribe_wx_service($uid)){
                    echo json_encode(array('success'=>false, 'reason' => 'need_sub'));
                }else{
                    try {
                        $cM = ClassRegistry::init('CouponItem');
                        $got = $cM->add_spring_festival_coupon($this->currentUser['id'], $pid);
                        $reason = 'got';
                    }catch (Exception $e) {
                        $this->log("exception:". $e);
                        $reason = 'unknown';
                    }
                    echo json_encode(array('success'=>$got , 'reason' => $reason));
                }
            }else{
                try {
                    $cM = ClassRegistry::init('CouponItem');
                    $got = $cM->add_spring_festival_coupon($this->currentUser['id'], $pid);
                    $reason = 'got';
                }catch (Exception $e) {
                    $this->log("exception:". $e);
                    $reason = 'unknown';
                }
                echo json_encode(array('success'=>$got, 'reason' => $reason));
            }
        }
    }

}  
