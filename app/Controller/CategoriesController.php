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
                'fields' => array('Product.*'),
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

        $navigation = $this->readOrLoadAndCacheNavigations($current_cateid, $this->Category);
        $mappedBrands = $this->findBrandsKeyedId($brandIds, $mappedBrands);
        $this->set('sub_title', $productTag['ProductTag']['name']);
        $this->set('brands', $mappedBrands);
        $this->set('total', $total);
        $this->set('current_cateid', $current_cateid);
        $this->set('category_control_name', 'products');
        $this->set('navigations', $navigation);
        $this->set('data_list', $productList);
        $this->set('withBrandInfo', true);
    }

    public function productsHome() {

        $current_cateid = CATEGORY_ID_TECHAN;
        $page = 1;
        $pagesize = 60;
        $this->loadModel('ProductTag');
        $productTags = $this->ProductTag->find('all', array('conditions' => array(
            'show_in_home' => 1,
            'published' => 1
            ),
            'order' => 'priority desc'
        ));
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
                    'fields' => array('Product.*'),
                    'limit' => 6,
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

        if ($this->RequestHandler->isMobile()) {
            if ($slug == 'techan' || $conditions['id'] == CATEGORY_ID_TECHAN) {
                $this->redirect('/categories/productsHome.html');
                return;
            }
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
				$pagesize = 30;
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

            if ($this->is_pengyoushuo_com_cn()){

                $conditions = array('id' => array(168));
                $datalist = $this->{$data_model}->find('all', array(
                    'conditions' => $conditions
                ));
                $total = count($datalist);
            } else {
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
        $this->set('is_index', !($this->is_pengyoushuo_com_cn()) && ($slug == 'techan' || $slug == '/'));
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
}
