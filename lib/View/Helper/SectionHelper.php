<?php
class SectionHelper extends AppHelper {

    /**
     * Other helpers used by this helper
     *
     * @var array
     * @access public
     */
    public $helpers = array('Html', 'Form','Hook');

    private $maxdepth = 2;
    /**
     * Current Node
     *
     * @var array
     * @access public
     */
    var $node = null;

    /**
     * 系统菜单
     * @param $modelClass
     * @param $parent_id
     */
    function getSystemsMenu($modelClass='Menu', $parent_id= null) {
        // 判断有模板缓存，不处理;注意不同页面，高亮显示的不一样。guid来对应html缓存内容
        // 判断有变量缓存，直接返回变量缓存
        // 若两个缓存都没有，则从数据库查出内容，返回
        $guid = $modelClass . '_systemmenu_' . $parent_id;
        
            $channels = Cache::read($guid . '_menu');
            if ($channels === false) {
                $model_obj = loadModelObject($modelClass);
                $channels = $model_obj->find('threaded',
                                array('conditions' => array('parent_id' => $parent_id, 'visible' => 1),
                                    'fields' => array('id', 'name', 'slug', 'link', 'parent_id'),
                                    'order' => 'left asc',
                        ));

                Cache::write($guid . '_menu', $channels);
            }
            $channels_html = '';
            foreach ($channels as $item) {
                $channels_html.=' ' . $this->Html->link($item[$modelClass]['name'], '/admin/?menu=' . $item[$modelClass]['id']);
            }
            //$channels_html = $this->nestedLinks($channels, $options);           
            return $channels_html;
    }

    /**
     * 栏目导航菜单
     * @param $modelClass
     * @param $options
     * 	$options['linkAttributes2']  定义2级的link属性。 末尾的数字表示菜单的层级
     *  $options['liAttributes2']  定义2级的li属性，会继承 liAttributes
     *  $options['subliAttributes2'] 带2级菜单中包含有下级的li的样式，会继承subliAttributes
     *  
     *  $options['conditions'] 传入筛选条件，可按model筛选出本模块的栏目。后台数据列表模板中选择栏目调用
     *  
     * @param $parent_id  父类别id
     * @param $with_child 为true是，选取所有的子类。为false时，仅选择当前父类的直属子类
     */
    function getNavMenu($modelClass, $options=array(), $parent_id= null, $with_child = true) {
        if (empty($options['conditions'])) {
            $options['conditions'] = array();
        }
        if($options['maxdepth']){
            $this->maxdepth = $options['maxdepth'];
        }
        $guid = $modelClass . '_' . guid_string($options['conditions']) . '-' . $parent_id . '-' . $with_child;
        
            $channels = Cache::read('menu_'.$guid);
            if ($channels === false) {
            	if(!ModelExists($modelClass)){
            		return null;
            	}
                $model_obj = loadModelObject($modelClass);
                $schema_fields = array_keys($model_obj->schema());
                $selectfields = array('id', 'name', 'slug', 'link', 'parent_id');
                if(in_array('model',$schema_fields)){
                	$selectfields[] = 'model'; // 模块字段含model时，将model也选出来，生成link的时候可能会用到。如link=>/controller:{model}/slug:{slug}
                }
                if(in_array('submenu',$schema_fields)){
                	$selectfields[] = 'submenu'; // 模块字段含model时，将model也选出来，生成link的时候可能会用到。如link=>/controller:{model}/slug:{slug}
                }
                //$model_obj->contain(); // 需要 var $actsAs = array('Containable');
				$model_obj->recursive = -1;
                if ($with_child) {
                    if ($parent_id) {
                        $parent_info = $model_obj->findById($parent_id);
                        $left = $parent_info[$modelClass]['left'];
                        $right = $parent_info[$modelClass]['right'];
                        $conditions = array($modelClass . '.left >' => $left, 'visible' => 1,$modelClass . '.right <' => $right);
                        $conditions = array_merge($conditions, $options['conditions']);
                        $channels = $model_obj->find('threaded', array('conditions' => $conditions, 'order' => 'left asc'));
                    } else {
                        $conditions = array('visible' => 1);
                        $conditions = array_merge($conditions, $options['conditions']);
                        $channels = $model_obj->find('threaded', array(
                                    'conditions' => $conditions,
                                    'fields' => $selectfields,
                                    'order' => 'left asc',
                                ));
                    }
                } else {
                    $conditions = array('visible' => 1, 'parent_id' => $parent_id);
                    $conditions = array_merge($conditions, $options['conditions']);
                    $channels = $model_obj->find('threaded', array(
                                'conditions' => $conditions,
                                'fields' => $selectfields,
                                'order' => 'left asc',
                            ));
                }                
                Cache::write('menu_'.$guid, $channels);
            }
            //print_r($channels);
            $this->Hook->call('NavMemu',array(&$channels,$modelClass,&$options, $parent_id, $with_child));
            
            $_options = array();
            $_options = array(
                'ulAttributes' => array('class' => 'nav navbar-nav'),
            	'liAttributes'=> array('activeclass'=>'active'),
            	'subliAttributes'=> array(),
            	'subliAttributes2'=> array('class'=>'dropdown-submenu'),
            	'subliAttributes3'=> array('class'=>'dropdown-submenu'),
            	'linkAttributes'=> array(), //'activeclass'=>'active'
                //'liSelectedClass' => 'active', // ui-state-active
            	//'linkSelectedClass' => 'active', 
                'selectedid' => 0,
                'dropdown' => true,
            	'outtag' => 'ul',// 当 outtag,midtag为空或false时，外围无ul,li.
            	'midtag' => 'li',
                'modelclass' => $modelClass,
            	'separator'=>'<li class="divider-vertical"></li>',
            	'inner_separator'=>'<li class="divider"></li>',
                'dropdownClass' => 'dropdown-menu',
            );
            $options = array_merge($_options,$options);
            return $this->nestedLinks($channels, $options);
    }

    /**
     * 模块类别列表参考
     * @param $modelClass
     * @param $options
     */
    function getLeftMenu($modelClass, $options = array()) {
        //$modelClass = 'Category';
        $parent_id = $options['parent_id'];
        // 判断有模板缓存，不处理
        // 判断有变量缓存，直接返回变量缓存
        // 若两个缓存都没有，则从数据库查出内容，返回
        if (empty($options['conditions'])) {
            $options['conditions'] = array();
        }
        if($options['maxdepth']){
        	$this->maxdepth = $options['maxdepth'];
        }
        $guid = $modelClass . '_' . $parent_id.'_'.guid_string($options['conditions']);        
            $channels = Cache::read('menu_left_'.$guid);
            if ($channels === false) {
            	if(!ModelExists($modelClass)){
            		return null;
            	}
                $model_obj = loadModelObject($modelClass);
                //$model_obj->contain(); // 需要 var $actsAs = array('Containable');
                $model_obj->recursive = -1;
                if ($parent_id) {
                    $parent_info = $model_obj->findById($parent_id);
                    $left = $parent_info[$modelClass]['left'];
                    $right = $parent_info[$modelClass]['right'];
                    $conditions = array($modelClass . '.left >' => $left,$modelClass . '.right <' => $right);
                    $conditions = array_merge($conditions, $options['conditions']);

                    $channels = $model_obj->find('threaded', array('conditions' => $conditions, 'order' => 'left asc'));
                } else {
                    $conditions = array('visible' => 1);
                    $conditions = array_merge($conditions, $options['conditions']);
                    $channels = $model_obj->find('threaded', array('conditions' => $conditions, 'order' => 'left asc'));
                }
                Cache::write('menu_left_'.$guid, $channels);
            }
            $_options = array();
            $_options = array(
            	'ulAttributes' => array('class' => 'nav nav-list'),
            	'liAttributes'=> array('activeclass'=>'active'),
            	'subliAttributes'=> array('class'=>'dropdown-submenu'),
            	'linkAttributes'=> array(), //'activeclass'=>'active'
                //'liSelectedClass' => 'active_li', // ui-state-active
            	//'linkSelectedClass' => 'active',            		
                'selectedid' => 0,
                'dropdown' => true,
            	'outtag' => 'ul',// 当 outtag,midtag为空或false时，外围无ul,li.
            	'midtag' => 'li',
            	'modelclass' => $modelClass,
            	'separator'=>'<li class="divider"></li>',
                'dropdownClass' => 'dropdown-menu',
            );
            $options = array_merge($_options, $options);

            return  $this->nestedLinks($channels, $options);            
    }

    /**
     * Nested Links
     *
     * @param array $links model datas find in threaded type. 
     * @param array $options (optional)
     * @param integer $depth depth level
     * @return string
     */
    function nestedLinks($links, $options = array(), $depth = 1) {
        $_options = array();
        $options = array_merge($_options, $options);
        $modelName = $options['modelclass'];
        $output = ''.$options['first_li'];
        // 修改链接的格式，默认是不带有span的
        $this->Html->tags['link'] = '<a href="%s.html"%s><span>%s</span></a>';
        $link_length = count($links);
        foreach ($links AS $key => $link) {

            if ((strpos($_SERVER['HTTP_HOST'], 'www.pengyoushuo.com.cn') !== false) && ($link[$modelName]['id'] != CATEGORY_ID_TECHAN && $link[$modelName]['id'] != 121)) {
                continue;
            }

        	if(!empty($options['liAttributes'.$depth])){
        		$liAttr = array_merge($liAttr,$options['liAttributes'.$depth]);
        	}
        	else{
            	$liAttr = $options['liAttributes'];
        	}            
            if(!empty($options['linkAttributes'.$depth])){
            	$linkAttr = array_merge($linkAttr,$options['linkAttributes'.$depth]);
            }
            else{
            	$linkAttr = $options['linkAttributes'];
            }
            $linkAttr['id'] = 'link-' . $link[$modelName]['id'];
            $linkAttr['ref'] = $link[$modelName]['slug']?$link[$modelName]['slug']:$link[$modelName]['id'];
            if ($key == 0 && empty($options['first_li'])) {
                $linkAttr['class'].=' ui-menu-first';
            } elseif ($key == $link_length - 1) {
                $linkAttr['class'].=' ui-menu-last';
            }
            if (!empty($options['selectedid']) && $link[$modelName]['id'] == $options['selectedid']) {
            	if($linkAttr['class']){
            		$linkAttr['class'].= ' '.$linkAttr['activeclass'];
            	}
            	else{
            		$linkAttr['class'] = $linkAttr['activeclass'];
            	}
            }
            unset($linkAttr['activeclass']);
            
            
            foreach ($linkAttr AS $attrKey => $attrValue) {
                if ($attrValue == null) {
                    unset($linkAttr[$attrKey]);
                }
            }

            if(!empty($options['url'])){
                $link[$modelName]['link'] = $this->_parselinkString($options['url'], $link[$modelName]);
            }
            elseif (strpos($link[$modelName]['link'], 'controller:')!==false) {
            	// if link is in the format: controller:contacts/action:view
                $link[$modelName]['link'] = $this->linkStringToArray($link[$modelName]['link'], $link[$modelName]);
            }
            elseif (empty($link[$modelName]['link'])) { // 无指定链接时，自动生成链接地址
                if ($link[$modelName]['slug'] == '/') {
                    $link[$modelName]['link'] = '/index.html';
                }
                elseif ($link[$modelName]['slug']) {
                    $link[$modelName]['link'] = '/' . $link[$modelName]['slug'].'.html';
                } else {
                    $link[$modelName]['link'] = '/' . $link[$modelName]['id'].'.html';
                }
                //若没有指定链接， 则以表名/id 作为链接
                if ($modelName != 'Category') {
                    $link[$modelName]['link'] = '/' . Inflector::tableize($modelName) . $link[$modelName]['link'];
                }
                //$link[$modelName]['link'] = Router::url($link[$modelName]['link']);
            }
            elseif(strpos($link[$modelName]['link'],'http://')!==false){
            	$linkAttr['target'] = '_blank'; //外部链接地址，在新窗口打开
            }
            $linkAttr = array_merge($options['linkAttributes'],$linkAttr);
            
            if($depth + 1 <= $this->maxdepth && (!empty($link[$modelName]['submenu']) || (isset($link['children']) && count($link['children']) > 0))){
            	$linkAttr['class'].='  dropdown-toggle';
            	$linkAttr['data-toggle']='dropdown';
            	$liAttr['class'] .=' dropdown';//dropdown-submenu
            	$liAttr = array_merge($liAttr,$options['subliAttributes']);
            	if(!empty($options['subliAttributes'.$depth])){
            		$liAttr = array_merge($liAttr,$options['subliAttributes'.$depth]);
            	}
            	$linkOutput = $this->Html->link($link[$modelName]['name'], $link[$modelName]['link'], $linkAttr); //
            	if(!empty($link[$modelName]['submenu'])){
            		$linkOutput .=$link[$modelName]['submenu'];
            	}
                elseif($depth + 1 <= $this->maxdepth){
                	$linkOutput .= $this->nestedLinks($link['children'], $options, $depth + 1);
                }
            }
            else{
            	$linkOutput = $this->Html->link($link[$modelName]['name'], $link[$modelName]['link'], $linkAttr); //
            }
            
            
            if($options['midtag']){
            	if (!empty($options['selectedid']) && $link[$modelName]['id'] == $options['selectedid']) {
            		if($liAttr['class']){
            			$liAttr['class'] .= ' '.$liAttr['activeclass']; // 将选中样式放在a中，为一个独立元素，对其他无影响。放在li中时，会对下级ul中样式产生影响。
            		}
            		else{
            			$liAttr['class'] = $liAttr['activeclass']; // 将选中样式放在a中，为一个独立元素，对其他无影响。放在li中时，会对下级ul中样式产生影响。
            		}
            	}
            	unset($liAttr['activeclass']);
            	$linkOutput = "\r\n" . $this->Html->tag($options['midtag'], $linkOutput,$liAttr);
            }
            if ($key != $link_length - 1){
	            if($depth>1){
	            	$output .= $linkOutput.$options['inner_separator'];
	            }
	            else{
	            	$output .= $linkOutput.$options['separator'];
	            }
            }
            else{
            	$output .= $linkOutput;
            }
        }
        if ($options['outtag'] && $output != null) {
            $tagAttr = array();
            $tagAttr = $options['ulAttributes'];
            if ($options['dropdown'] && $depth > 1) {
                $tagAttr['class'] = $options['dropdownClass'];
            }
            if ($depth == 1) {
                $output = $options['preli'] . $output . $options['sufli'];
            }
            $output = "\r\n" . $this->Html->tag($options['outtag'], $output, $tagAttr) . "\r\n";
        }
        return $output;
    }
    
    private function _parselinkString($url_string,$array){
    	if(is_array($url_string)){
    		$url_string = getSearchLinks($this->request,$url_string,array(),true) ;
    		//$url_string = Router::url($url_string);
    	}
    	$url_string = str_replace(array('%25','%7B','%7D'),array('%','{','}'),$url_string);
        if (preg_match_all('/{(\w+)}/', $url_string, $matches)) {
               foreach($matches[0] as $key => $val){
                   $url_string = str_replace($val,$array[$matches[1][$key]],$url_string);
               }
        }
        return $url_string;
    }
    
    /**
     * Converts strings like controller:abc/action:xyz/ to arrays
     *
     * @param string $link link
     * @return array
     */
    public function linkStringToArray($link, $data = array()) {
        $link = explode('/', $link);
        $linkArr = array();
        foreach ($link AS $linkElement) {
            if ($linkElement != null) {
                $linkElementE = explode(':', $linkElement);
                if (isset($linkElementE['1'])) {
                    if (preg_match('/^{(\w+)}$/', $linkElementE['1'], $matches)) {
                        $linkElementE['1'] = $data[$matches[1]];
                    }
                    if ($linkElementE['0'] == 'controller') {
                        $linkElementE['1'] = Inflector::tableize($linkElementE['1']);
                    }
                    $linkArr[$linkElementE['0']] = $linkElementE['1'];
                } else {
                    if (preg_match('/^{(\w+)}$/', $linkElement, $matches)) {
                        if ($matches[1] == 'slug' && empty($data[$matches[1]])) {
                            $data[$matches[1]] = $data['id'];
                        }
                        $linkElement = $data[$matches[1]];
                    }
                    $linkArr[] = $linkElement;
                }
            }
        }
        return $linkArr;
    }

    /**
     * Filter content
     *
     * Replaces bbcode-like element tags
     *
     * @param string $content content
     * @return string
     */
    function filter($content) {
        $content = $this->filterElements($content);
        return $content;
    }

    /**
     * Filter content for elements
     *
     * Original post by Stefan Zollinger: http://bakery.cakephp.org/articles/view/element-helper
     * [element:element_name] or [e:element_name]
     *
     * @param string $content
     * @return string
     */
    function filterElements($content) {
        preg_match_all('/\[(element|e):([A-Za-z0-9_\-]*)(.*?)\]/i', $content, $tagMatches);
        for ($i = 0; $i < count($tagMatches[1]); $i++) {
            $regex = '/(\S+)=[\'"]?((?:.(?![\'"]?\s+(?:\S+)=|[>\'"]))+.)[\'"]?/i';
            preg_match_all($regex, $tagMatches[3][$i], $attributes);
            $element = $tagMatches[2][$i];
            $options = array();
            for ($j = 0; $j < count($attributes[0]); $j++) {
                $options[$attributes[1][$j]] = $attributes[2][$j];
            }
            $content = str_replace($tagMatches[0][$i], $this->View->element($element, $options), $content);
        }
        return $content;
    }
	
	/**
	 * 获取内容列表
	 * @param int $id region id
	 * @param int $page page list
	 * @throws MissingModelException
	 * @return array. such as array('regioninfo' => array(), 'datalist' => array(), 'page_navi' => '');
	 */
	public function getRegionListById($id, $page=1) {
		if (isset($GLOBALS['regioninfo'][$id])) {
			$regioninfo = $GLOBALS['regioninfo'][$id];
		}
		else{
			$regioninfo = Cache::read('regioninfo_' . $id);
			if ($regioninfo === false) {
				$region = loadModelObject('Region');
				$GLOBALS['regioninfo'][$id] = $regioninfo = $region->read(null, $id);
				if (empty($regioninfo)) {
					//throw new NotFoundException("Region $id not found");
					return '';
				}
				$regioninfo = current($regioninfo);
				Cache::write('regioninfo_' . $id, $regioninfo);
			}
		}
		if (empty($regioninfo)) {
			return array('regioninfo' => array(), 'datalist' => array(), 'page_navi' => '');
		}
		/**
		 * 一个页面仅支持一个region接收参数传入（ajax动态交互不算在内）
		 */
		$page = 1;
		list($plugin, $modelClass) = pluginSplit($regioninfo['model'], true);
		if(!ModelExists($modelClass,$plugin)){
			return array('regioninfo' => $regioninfo, 'datalist' => array(), 'page_navi' => '');
		}
		$cache_key = 'region_list_'.$id.'_'.$page.'_'.guid_string($this->request->query);
		$rdlist = Cache::read($cache_key);
		if ($rdlist === false) {
			$this->request = Router::getRequest();
			if (!empty($regioninfo['conditions'])) {
				if (is_array($GLOBALS['RegionReplaceVar'])) {
					foreach ($GLOBALS['RegionReplaceVar'] as $key => $val) {
						$regioninfo['conditions'] = str_replace('$' . $key, $val, $regioninfo['conditions']);
						//{{named Taobaoke.name}}
						$regioninfo['conditions'] = preg_replace('/{{named\s+(.+?)\s*}}/ies', "\$this->request->params['named']['\\1']", $regioninfo['conditions']);
					}
				}
				$searchoptions = parseXmlToSqlOption($regioninfo['conditions']);
			}
		
			$searchoptions['conditions'][$modelClass . '.deleted'] = 0;
			$searchoptions['conditions'][$modelClass . '.published'] = 1;
		
			if ($regioninfo['auto_receive_param']) {
				if (!empty($searchoptions['joins'])) {
					foreach ($searchoptions['joins'] as $jk => $jv) {
						$joinmodel = $jv['alias'];
						$searchoptions['fields'][] = $jv['alias'] . '.*'; // 追加连接模块的字段
						if(!empty($this->request->query[$joinmodel])){
							$joincondition = getSearchOptions($this->request->query[$joinmodel],$joinmodel);
							$searchoptions['conditions'] +=$joincondition;
						}
					}
				}
				
				if($this->request->query['page']){
					$page = $this->request->query['page'];
				}
				// 排序参数加上_{id}的后缀
				if (!empty($this->request->query['order'])) {
					$searchoptions['order'] = $this->request->query['order'];
				}
				$conditions = getSearchOptions($this->request->query,$modelClass);
				$searchoptions['conditions'] = array_merge($searchoptions['conditions'],$conditions);
				
				if (!empty($this->request->query['conditions'])) {
					$searchoptions['conditions'] += $this->request->query['conditions'];
				}
			}
	// 		print_r($this->request->query);
	// 		print_r($searchoptions);
		
			$searchoptions['page'] = $page;
			
			$model = loadModelObject($modelClass,$plugin);
			$datas = $model->find('all', $searchoptions);
	// 		exit;
			$page_navi = '';
			if ($regioninfo['showpages']) {			 
				// 当前页，第一页。
				$total = $model->find('count',
						array(
								'conditions' => $searchoptions['conditions'],
								'joins' => $searchoptions['joins'],
						)
				);
		
				//                 $page_navi = getPageLinks($total, $regioninfo['rows'], '/'.$Category['Category']['slug'], $page);
		
				if ($regioninfo['pagelink_type'] == 'pageurl') {
					$page_navi = getPageLinks($total, $regioninfo['rows'], $this->request, $page);
				} else { // regionurl
					App::uses('Page', 'Lib');
					$pagelinks = new Page($total, $regioninfo['rows'], 'regions/' . $regioninfo['id'] . '/', $page);
					$page_navi = $pagelinks->renderNav(10);
				}
			}
		
			$rdlist = array('regioninfo' => $regioninfo, 'datalist' => $datas, 'page_navi' => $page_navi);
			Cache::write($cache_key, $rdlist);
		}
		return $rdlist;
	
	}
	
	
	/**
	 * 根据模版中设置的串转换后得到的数组来获取数据列表
	 * @param array $params
	 * @return array
	 */
	function getRegionListByArray($params)
	{
		if(empty($params['model'])){
			return array();
		}		
		
		$modelname = Inflector::classify($params['model']);
		
		$params['page'] = $this->request->query['page'] ? $this->request->query['page'] : 1;
		$params['limit'] = $params['limit'] ? $params['limit'] : 10;

		$cache_key = 'region_list_'.guid_string($params);
		$rdlist = Cache::read($cache_key);
		if ($rdlist === false) {
			$model = loadModelObject($modelname);
			if($params['recursive']){
				$model->recursive = $params['recursive'];
			}
			else{
				$model->recursive = -1;
			}
			
			$fields = array_keys($model->schema());
			
			if(in_array('deleted',$fields)){
				$params['conditions'][$modelname.'.deleted']=0;
			}
			if(in_array('published',$fields)){
				$params['conditions'][$modelname.'.published']=1;
			}
			if(!empty($params['data_id']) && in_array('id',$fields)){
				$params['conditions'][$modelname.'.id']=$params['data_id'];
			}
			
			$page_navi = '';
			$datas = $model->find('all', $params);
			if($params['showpages'] && empty($params['data_id'])){
				// 当前页，第一页。
				$total = $model->find('count',
						array(
								'conditions' => $params['conditions'],
								'joins' => $params['joins'],
						)
				);
				$page_navi = getPageLinks($total,$params['limit'], $this->request, $page);
			}
	// 		print_r($params);
			$rdlist = array('regioninfo' => array('Region'=>$params), 'datalist' => $datas, 'page_navi' => $page_navi);
			Cache::write($cache_key,$rdlist);
		}
		return $rdlist;
	}
	
}

?>