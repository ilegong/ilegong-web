<?php
class CategoriesController extends AppController {

    var $name = 'Categories';

    public function view($slug='/') {
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
        $path_cachekey = 'category_path_'.$current_cateid;
        $navigations = Cache::read($path_cachekey);
        if ($navigations === false) {
        	$navigations = $this->Category->getPath($current_cateid);
        	Cache::write($path_cachekey, $navigations);
        }
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
        if($conditions['id'] == Configure::read('Site.index_page')){
        	$this->__viewFileName[] = 'view_index';
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
				$pagesize = 15;
			}
			$this->{$data_model}->recursive = -1;
			$conditions = array();
			if(!empty($this->request->query)){
				$conditions = getSearchOptions($this->request->query,$data_model);
			}
			if($left+1==$right){	// 无子类时，直接查询本类的内容
				$conditions[$data_model.'.cate_id'] = $current_cateid;
				$datalist = $this->{$data_model}->find('all', array(
						'conditions' => $conditions,
						'order' => $data_model.'.id desc',
						'limit' => $pagesize,
						'page' => $page,));
				if ($page == 1 && count($datalist) < $pagesize) {
					$total = count($datalist);
				} else {
					$total = $this->{$data_model}->find('count', array(
							'conditions' => $conditions
					));
				}
			}
			else{	// 有子类时，查询本类及所有子类的内容
	        	$join_conditions = array(
	        			array(
	        					'table' => 'categories',
	        					'alias' => 'Category',
	        					'conditions' => array(
	        							'Category.left >=' => $left,
	        							'Category.right <=' => $right,
	        							'Category.id = '.$data_model.'.cate_id',
	        					),
	        					'type' => 'inner',
	        			)
	        	);
	        	
		        $datalist = $this->{$data_model}->find('all', array(
			                    'conditions' => $conditions,
			                    'joins' => $join_conditions,
			                    'order' => $data_model.'.id desc',
			                    'fields' => array($data_model.'.*', 'Category.*'),
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
	        foreach ($datalist as $val) {
	            $Category['datalist'][] = $val[$data_model];
	        }
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
    }
}
