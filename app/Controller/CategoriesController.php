<?php
class CategoriesController extends AppController {

    var $name = 'Categories';

    public function beforeFilter(){
        parent::beforeFilter();
        $this->redirect('/');
    }
    
    public function api_seckills(){
        $this->autoRender=false;

        $seckills = $this->_get_seckill_products();
        foreach($seckills as &$seckill){
            $seckill['ProductTry']['status'] = ProductTry::cal_op($seckill['ProductTry']['limit_num'], $seckill['ProductTry']['sold_num'], $seckill['ProductTry']['start_time'], $seckill['ProductTry']['status']);
            $seckill['ProductTry']['remaining_time'] = strtotime($seckill['ProductTry']['start_time']) - time();
        }

        return json_encode($seckills);
    }

    public function api_tag_products($tagId){
        $this->autoRender=false;
        $result = Cache::read('api-tag-products-'.$tagId);
        if(!empty($result)){
            echo $result;
            return;
        }

        $products = $this->load_products_by_tagid($tagId);
        $products = Hash::combine($products, '{n}.Product.id', '{n}');

        $brand_ids = array_unique(Hash::extract($products,'{n}.Product.brand_id'));
        $brands = $this->findBrandsKeyedId($brand_ids, $mappedBrands);

        $tuan_products = getTuanProducts();
        $tuan_products = Hash::combine($tuan_products, '{n}.TuanProduct.product_id', '{n}');

        $temp_products = array();
        foreach($products as &$product){
            $product_id = $product['Product']['id'];
            if(array_key_exists($product_id, $tuan_products) && $tuan_products[$product_id]['TuanProduct']['general_show'] == 0){
                $this->loadModel('TuanBuying');
                $tuan_buying = $this->TuanBuying->find('first', array(
                    'conditions' => array('pid' => $product_id, 'tuan_id'=>PYS_M_TUAN, 'published' => 1)
                ));
                if(!empty($tuan_buying)){
                    $product['TuanBuying'] = $tuan_buying['TuanBuying'];
                    $temp_products[] = $product;
                }
                $this->log('product '.$product_id.' is a tuan product');
            }
            else{
                $this->log('product '.$product_id.' is a product');
                $temp_products[] = $product;
            }
        }

        $result = json_encode(array('products'=>$temp_products, 'brands'=>$brands));
        Cache::write('api-tag-products-'.$tagId, $result);
        echo $result;
    }

    public function getTagProducts($tagId){
        $this->autoRender=false;
        $result = Cache::read('tag-products'.$tagId);
        $result_json = json_decode($result,true);
        if(!empty($result_json)){
            echo $result;
            return;
        }
        $list = $this->load_products_by_tagid($tagId);
        $this->loadModel('GroupBuy');
        $brandIds = Hash::extract($list,'{n}.Product.brand_id');
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $productList = array();
        $tuan_products = getTuanProducts();
        $tuan_products = Hash::combine($tuan_products, '{n}.TuanProduct.product_id', '{n}');
        foreach ($list as &$val) {
            $product_id = $val['Product']['id'];
            $brand = $mappedBrands[$val['Product']['brand_id']];
            $groupBuyInfo = $this->GroupBuy->getGroupBuyProductInfo($product_id);
            $val['Product']['brand_link'] = $this->brand_link($brand);
            $val['Product']['brand_name'] = $brand['Brand']['name'];
            $val['Product']['brand_img'] = $brand['Brand']['coverimg'];
            $val['Product']['good_url'] = product_link2($val);
            //$val['Product']['is_618'] = pid_in_special($product_id,7);
            $val['Product']['group_buy'] = $groupBuyInfo;
            if(array_key_exists($product_id, $tuan_products) && $tuan_products[$product_id]['TuanProduct']['general_show'] == 0){
                $this->loadModel('TuanBuying');
                $tuan_buying = $this->TuanBuying->find('first', array(
                    'conditions' => array('pid' => $product_id, 'tuan_id'=>PYS_M_TUAN, 'published' => PUBLISH_YES, 'consignment_type'=>0, 'status'=>0),
                    'order' => array('id DESC')
                ));
                if(!empty($tuan_buying)){
                    $val['Product']['TuanBuying'] = $tuan_buying['TuanBuying'];
                }
            }
            $productList[] = $val['Product'];
        }
        $result = array('data_list'=>$productList);
        $result = json_encode($result);
        Cache::write('tag-products'.$tagId,$result);
        echo $result;
    }

    public function tag($tagSlug = '') {
        if ($tagSlug == '') {
            $this->view();
            return;
        }
        $this->set('flagTag',$tagSlug);
        $current_cateid = -1;
        $this->loadModel('ProductTag');
        $productTag = $this->ProductTag->find('first', array('conditions' => array(
            'slug' => $tagSlug,
            'published' => 1
        )));
        if (empty($productTag)) {
            $this->view();
            return;
        }
        //团购商品列表 不显示在分类页
        if(!$this->RequestHandler->isMobile()){
            $tuan_products = getTuanProducts();
            $exclude_pids = Hash::extract($tuan_products,'{n}.TuanProduct.product_id');
        }
        $list = $this->load_products_by_tagid($productTag['ProductTag']['id'],null,1,60,$exclude_pids);
        $productList = array();
        $brandIds = array();
        foreach ($list as $val) {
            $productList[] = $val['Product'];
            $brandIds[] = $val['Product']['brand_id'];
        }

        $this->setHasOfferBrandIds();
        //mobile show category ids
        $mobileTagIds = array(3,5,8,12,9,6,4,10);
        $mobileTags = $this->findTagsByIds($mobileTagIds);
        $mobileTags = Hash::combine($mobileTags,'{n}.ProductTag.id','{n}.ProductTag');
        $this->set('mobile_tag',$mobileTags);
        //spec category ids
        $specTagIds = array(13,22,15);
        $specTags = $this->findTagsByIds($specTagIds);
        $specTags = Hash::combine($specTags,'{n}.ProductTag.id','{n}.ProductTag');
        $this->set('spec_tags',$specTags);
        $this->pageTitle = $productTag['ProductTag']['name'];
        $navigation = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $this->set('sub_title', $productTag['ProductTag']['name']);
        $this->set('tag', $productTag['ProductTag']);
        $this->set('brands', $mappedBrands);
        $this->set('current_cateid', $current_cateid);
        $this->set('category_control_name', 'products');
        $this->set('navigations', $navigation);
        $this->set('data_list', $productList);
        $this->set('withBrandInfo', true);
        $this->set('page_title',$this->pageTitle);
        $this->set('op_cate', OP_CATE_HOME);
        $this->set('_serialize', array('brands', 'data_list', 'sub_title'));
        $this->set('history',$_REQUEST['history']);

        if($this->is_weixin()){
            $currUid = empty($this->currentUser) ? 0 : $this->currentUser['id'];
            $tag_id = $productTag['ProductTag']['id'];
            $this->prepare_wx_sharing($currUid, $tag_id);
        }
    }

    public function happy_618(){
        $this->special_list('618_mid_year_happy');
        $this->set('hideFooter',true);
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
                    'fields' => array('Product.id', 'Product.name','Product.brand_id','Product.price','Product.original_price', 'Product.created', 'Product.coverimg', 'Product.slug', 'Special.*','Product.listimg'),
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

    public function mobileIndex($type=0){
        if($type==0){
            $this->redirect('/weshares/index.html');
            return;
        }
        $this->pageTitle='首页';
        //add sec kill
        $this->loadModel('ProductTry');
        $this->loadModel('Product');
        $tryings = $this->_get_seckill_products();
        if($_REQUEST['tagId']){
            $this->set('tagId',$_REQUEST['tagId']);
        }else{
            $this->set('tagId',RECOMMEND_TAG_ID);
        }
        if(parent::is_weixin()){
            $this->set('is_weixin',true);
            $this->wexin_share_datas($tryings);
        }
        $mOrder = ClassRegistry::init('Order');
        $received_cnt = $mOrder->count_received_order($this->currentUser['id']);
        $this->set('received_cnt', $received_cnt);
        $this->set('tryings',$tryings);
        $this->set('hideFooter',true);
        $this->set('op_cate', OP_CATE_HOME);
    }

    public function mobileHome() {
        $this->productsHome(true);

//        $zutuangous = array(
//            array('img' => "/img/banner/banner_zutuangou3.jpg", 'url' => "/groupons/view/chengzi.html?from=home1", 'id' => 0),
//            array('img' => "/img/banner/banner_zutuangou2.jpg", 'url' => "/groupons/view/chengzi.html?from=home2", 'id' => 0),
//            array('img' => "/img/banner/banner_zutuangou.jpg", 'url' => "/groupons/view/gonggan.html?from=home", 'id' => 0),
//        );

        $mobileTagIds = array(3,5,8,12,9,6,4,10,11);
        $mobileTags = $this->findTagsByIds($mobileTagIds);
        $mobileTags = Hash::combine($mobileTags,'{n}.ProductTag.id','{n}.ProductTag');
        $this->set('mobile_tag',$mobileTags);
        $specTagIds = array(13,22,15);
        $specTags = $this->findTagsByIds($specTagIds);
        $specTags = Hash::combine($specTags,'{n}.ProductTag.id','{n}.ProductTag');
        $this->set('spec_tags',$specTags);

        $bannerItems = array(
            array('mobile_image' => "/img/banner/spring-weixin.jpg", 'detail_url' => "/categories/spring"),
        );

        $this->loadModel('ProductTry');
        $tryings = $this->ProductTry->find_trying();
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

        $this->loadModel('SpecialList');
        $daily_special = $this->SpecialList->find_daily_special();
        if (!empty($daily_special)) {
            $this->set('daily_special', $daily_special);
        }

        $configBanners = $this->getBanner(array(0,2));
        if(!empty($configBanners)){
            $bannerItems = Hash::combine($configBanners,'{n}.Banner.id','{n}.Banner');
        }
        $this->set('bannerItems', $bannerItems);
        $this->set('tryings', $tryings);
        $this->set('max_show', $this->RequestHandler->isMobile()? 2 : 4);
        $this->set('_serialize', array('brands', 'tagsWithProducts', 'sub_title', 'bannerItems'));
    }

    public function productsHome($disableAutoRedirect = false) {
        if(empty($_REQUEST['old'])){
            $this->redirect('/weshares/index.html');
        }
        return;

//        $current_cateid = CATEGORY_ID_TECHAN;
//        $page = 1;
//        $pagesize = 60;
//        $productTags = $this->findVisibleTags();
//        if (empty($productTags)) {
//            $this->view();
//            return;
//        }
//
//        $excludeProductIds = array(148,705,383,869,567,315,354,161,715,153);
//
//        $conditions = array('Product' .'.deleted'=>0, 'Product' .'.published'=>1);
//        $conditions['Product' . '.recommend >='] = 0;
//        $conditions['NOT']=array('Product'.'.id'=>$excludeProductIds);
//
//        $orderBy = /*'Tag.recommend desc,*/' Product.recommend desc';
//        $brandIds = array();
//        $this->loadModel('Product');
//        $orderFiledValue = array("FIELD(Product.id,".join(',',$excludeProductIds).")");
//        //recommend products
//        $excludeProducts = $this->Product->find('all',array(
//            'conditions'=>array(
//                'id'=>$excludeProductIds
//            ),
//            'order'=>$orderFiledValue
//        ));
//        $this->set('recommendProducts',$excludeProducts);
//        foreach($productTags as &$tag) {
//
//            //add class image
//            $tag['ProductTag']['coverimg'] = '/img/class/classn'.$tag['ProductTag']['id'].'.png';
//
//            $join_conditions = array(
//                array(
//                    'table' => 'product_product_tags',
//                    'alias' => 'Tag',
//                    'conditions' => array(
//                        'Tag.product_id = Product.id',
//                        'Tag.tag_id' => $tag['ProductTag']['id']
//                    ),
//                    'type' => 'RIGHT',
//                )
//            );
//            $tag['Products'] = array();
//            $products = $this->Product->find('all', array(
//                    'conditions' => $conditions,
//                    'joins' => $join_conditions,
//                    'order' => $orderBy,
//                    'fields' => explode(',', Product::PRODUCT_PUBLIC_FIELDS),
//                    'limit' => ($tag['ProductTag']['size_in_home']>=0?$tag['ProductTag']['size_in_home']:6),
//                    'page' => $page)
//            );
//            foreach($products as $p){
//                $brandIds[] = $p['Product']['brand_id'];
//                $tag['Products'][] = $p['Product'];
//            }
//        }
//
//        $this->setHasOfferBrandIds();
//        $configBanners = $this->getBanner(array(0,1));
//        if(!empty($configBanners)){
//            $bannerItems = Hash::combine($configBanners,'{n}.Banner.id','{n}.Banner');
//            $this->set('bannerItems',$bannerItems);
//        }
//        $this->pageTitle =  __('热卖');
//        $navigation = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
//        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
//        $this->set('sub_title', $productTags['ProductTag']['name']);
//        $this->set('brands', $mappedBrands);
//        $this->set('current_cateid', $current_cateid);
//        $this->set('top_category_id', $current_cateid);
//        $this->set('navigations', $navigation);
//        $this->set('tagsWithProducts', $productTags);
//        $this->set('withBrandInfo', true);
//        $this->set('category_control_name', 'products');
//        $this->set('op_cate', OP_CATE_HOME);
//        $this->set('is_index',true);
    }

    public function specCategoryList($tagSlug){
        global $_display_tags_in_home;
        $this->loadModel('ProductTag');
        $productTag = $this->ProductTag->find('first', array('conditions' => array(
            'slug' => $tagSlug,
            'published' => PUBLISH_YES,
        )));
        $this->pageTitle=$productTag['ProductTag']['name'];
        $tags = $this->findTagsByIds($_display_tags_in_home);
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
            //change mobile index view
            $redirectUrl = '/categories/productsHome.html';
            if($this->RequestHandler->isMobile()){
//                $tagId = RECOMMEND_TAG_ID;
//                if($_REQUEST['tagId']){
//                    $tagId = $_REQUEST['tagId'];
//                }
//                $redirectUrl = '/weshares/index.html?tagId='.$tagId;
                $redirectUrl = '/weshares/index.html';
            }
            $this->redirect($redirectUrl);
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
        $conditions['Product' . '.recommend >='] = 0;

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
        $uid = $this->currentUser['id'];
        if($this->is_weixin()){
            $this->loadModel('User');
            $nickname =$this->User->findNicknamesOfUid($uid);
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
            $this->loadModel('WxOauth');
            $signPackage = $this->WxOauth->getSignPackage();
            $this->set('signPackage', $signPackage);
            $this->set('nickname', $nickname);
            $this->set('is_weixin', true);
        }

        $brandIds = Hash::extract($list,'{n}.Product.brand_id');
        $productList = Hash::extract($list,'{n}.Product');
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        
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
    }
    public function mobile_get_spring_coupon(){
        $this->autoRender=false;
        $uid = $this->currentUser['id'];
        if($_GET['pid'] && $uid){
            $pid = intval($_GET['pid']);
            $OauthbindM = ClassRegistry::init('Oauthbind');
            $oauth = $OauthbindM->findWxServiceBindByUid($uid);
            if(!empty($oauth)){
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

    function getBanner($types){
        $Banner = ClassRegistry::init('Banner');
        //0 -> show pc and mobile
        //1 -> only show pc
        //2 -> only show mobile
        $banners = $Banner->find('all',array(
            'conditions'=>array(
                'type'=>$types
            ),
            'order'=>'recommend desc',
            'limit'=>4
        ));
        return $banners;
    }

    protected function prepare_wx_sharing($currUid, $tag_id) {
        $currUid = empty($currUid) ? 0 : $currUid;
        $share_string = $currUid . '-' . time() . '-rebate-tag_id_' . $tag_id;
        $share_code = authcode($share_string, 'ENCODE', 'SHARE_TID');
        $oauthM = ClassRegistry::init('WxOauth');
        $signPackage = $oauthM->getSignPackage();
        $this->set('signPackage', $signPackage);
        $this->set('share_string', urlencode($share_code));
        $this->set('jWeixinOn', true);
    }

    private function brand_link($brand, $params = array()) {
        $url = (!empty($brand)) ? "/brands/" . date('Ymd', strtotime($brand['Brand']['created'])) . "/" . $brand['Brand']['slug'] . ".html" : '/';
        return $url;
    }

    private function load_products_by_tagid($tagId,$productFileds=null,$page=1,$pagesize=60,$excludePids = array()){
        $this->loadModel('Product');
        if($productFileds==null){
            $productFileds = Product::PRODUCT_PUBLIC_FIELDS;
        }
        $conditions = array('Product' .'.deleted'=>DELETED_NO, 'Product' .'.published'=>PUBLISH_YES);
        $conditions['Product' . '.recommend >'] = 0;
        if(!empty($excludePids)){
            $conditions['not'] = array(
                'Product.id' => $excludePids
            );
        }
        $join_conditions = array(
            array(
                'table' => 'product_product_tags',
                'alias' => 'Tag',
                'conditions' => array(
                    'Tag.product_id = Product.id',
                    'Tag.tag_id' => $tagId
                ),
                'type' => 'RIGHT',
            )
        );
        $orderBy = 'Tag.recommend desc, Product.recommend desc';
        $list = $this->Product->find('all', array(
                'conditions' => $conditions,
                'joins' => $join_conditions,
                'order' => $orderBy,
                'fields' => $productFileds,
                'limit' => $pagesize,
                'page' => $page)
        );
        return $list;
    }

    private function wexin_share_datas($tryings=null){
        $uid = $this->currentUser['id'];
        if(!empty($tryings)){
            $trying = $tryings[0];
            $title = ($trying['ProductTry']['price']/100).'元秒杀'.$trying['ProductTry']['spec'].$trying['ProductTry']['product_name'].'赶紧快来枪';
            $to_friend_title = $title;
            $to_timeline_title = $title;
            $share_imag_url = $trying['Product']['listimg'];
            $desc = $trying['Product']['promote_name'];
            $weixinJs = prepare_wx_share_log($uid, 'indextry', $trying['ProductTry']['id']);
        }else{
            $recommend_products = $this->load_products_by_tagid(RECOMMEND_TAG_ID,'Product.id, Product.name, Product.coverimg',1,6);
            $first_p = array_shift($recommend_products);
            $to_friend_title = $first_p['Product']['name'];
            $to_timeline_title = $first_p['Product']['name'];
            $share_imag_url = $first_p['Product']['coverimg'];
            $p_names = Hash::extract($recommend_products,'{n}.Product.name');
            $desc = implode(',',$p_names).'……等你来抢~';
            $weixinJs = prepare_wx_share_log($uid, 'indexproduct', $first_p['Product']['id']);
        }
        $this->set($weixinJs);
        $this->set('to_timeline_title',$to_timeline_title);
        $this->set('to_friend_title',$to_friend_title);
        $this->set('share_imag_url',$share_imag_url);
        $this->set('share_desc',$desc);
    }

    private function _get_seckill_products(){
        $this->loadModel('Product');
        $this->loadModel('ProductTry');
        $tryings = $this->ProductTry->find_global_trying();
        $this->log('tryings: '.json_encode($tryings));
        if (!empty($tryings)) {
            $trying_result = array();
            $try_pids = Hash::extract($tryings, '{n}.ProductTry.product_id');
            $this->log('try pids: '.json_encode($try_pids));
            $tryProducts = $this->Product->find_products_by_ids($try_pids, array(), false);
            $this->log('try products: '.json_encode($tryProducts));

            if (!empty($tryProducts)) {
                foreach($tryings as &$trying) {
                    $pid = $trying['ProductTry']['product_id'];
                    $prod = $tryProducts[$pid];
                    if (!empty($prod)) {
                        $trying['Product'] = $prod;
                        $trying_result[] = $trying;
                    } else {
                        unset($trying);
                    }
                }
            }
            $tryings = $trying_result;
        }
        return $tryings;
    }
}
