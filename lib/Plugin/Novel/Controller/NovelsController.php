<?php

/**
 * 
 * 
 * 
 * 
 * php queue.php  /admin/Taobao/taobaokes/updateProductInfo/page:350
 * cron更新产品的信息，包括taobao.taobaoke.items.detail.get和taobao.taobaoke.items.convert
 * 更新频率可稍低，内容描述的变动周期小
 * 
 * 
 * 单独的cron更新volume的周期要大一些，每天一次。taobao.taobaoke.items.convert
 * 
 * @author Arlon
 *
 */
class TaobaokesController extends TaobaoAppController {

    public $name = 'Taobaokes';

    /**
     * 前台index显示内容，取出对应分类下的产品，并(non-PHPdoc)
     * @see app/Controller/AppController#index()
     */
    public function index() {
        $current_cateid = $_GET['cid'];
        $Cate = $this->TaobaoCate->findById($current_cateid);
        $GLOBALS['RegionReplaceVar']['left'] = $Cate['TaobaoCate']['left'];
        $GLOBALS['RegionReplaceVar']['right'] = $Cate['TaobaoCate']['right'];

        if ($current_cateid) {

            $this->pageTitle = $Cate['TaobaoCate']['name'];
            $navigations = $this->TaobaoCate->getPath($current_cateid);
            $topCate = $navigations[0];

            $this->set('top_category_id', $topCate['TaobaoCate']['id']);
            $this->set('top_category_name', $topCate['TaobaoCate']['name']);
            $this->set('navigations', $navigations);
        }

        if (isset($this->params['named']['Taobaoke.name'])) {
            $conditions = array();
            $join = array(
                'conditions' => array(
                    'Taobaoke.cate_id=TaobaoCate.cid',
                    'TaobaoCate.deleted' => 0, 'TaobaoCate.published' => 1,
                    'Taobaoke.published' => 1, 'Taobaoke.deleted' => 0,
                    'Taobaoke.name like' => '%' . $this->params['named']['Taobaoke.name'] . '%',
                ),
                'table' => Inflector::tableize('TaobaoCate'),
                'alias' => 'TaobaoCate',
                'type' => 'inner',
            );

            $subCates = $this->Taobaoke->find('all', array(
                        'conditions' => $conditions,
                        'joins' => array($join),
                        'group' => 'TaobaoCate.cid',
                        'fields' => array('TaobaoCate.cid', 'TaobaoCate.name', 'TaobaoCate.parent_id', 'count(Taobaoke.id) as totalnum'),
                        'order' => 'totalnum desc'
                    ));
            $this->set('top_category_name', __('Search') . '"' . $this->params['named']['Taobaoke.name'] . '"');
        } else {
            $conditions = array('published' => 1, 'deleted' => 0,);
            if ($topCate['TaobaoCate']['left']) {
                $conditions['left >'] = $topCate['TaobaoCate']['left'];
                $conditions['right <'] = $topCate['TaobaoCate']['right'];
            }
            $subCates = $this->TaobaoCate->find('all', array('conditions' => $conditions, 'order' => 'TaobaoCate.left asc'));
        }
        $this->set('subCates', $subCates);
        $this->set('current_cateid', $current_cateid);

        //$requesturl = array('controller'=>'taobaokes','plugin'=>'Taobao', 'action'=>'search');
        //echo $search_data = $this->requestAction($requesturl, array('return'=>1,'autoRender'=>1,'requestAction'=>1,'referer'=>rawurlencode($this->request->url)));//, array('old_request'=>$this->request,'return'=>1,'autoRender'=>1)
        /*         * **
         * requestAction方法会使  $this->Region->request 修改。需要重新将值设定
         */
        //$this->Region->request = $this->request;

        $this->set('page_request', $this->request);
    }
    
    public function getAd(){
//    	$this->Taobaoke->recursive = -1;
//    	$data = $this->Taobaoke->find('first', array(
//                        'conditions' => array(
//    						'deleted'=>0,
//    						'published'=>1,
//    					),
//                        'fields' => array( 'max(Taobaoke.id) as maxnum','min(Taobaoke.id) as minnum'),
//                    ));
//		$minmax = $data[0];
		$this->Taobaoke->recursive = 1;
		$id = rand($minmax['minnum'],$minmax['maxnum']);
        $taobaoke = $this->Taobaoke->find('first', array(
                        'conditions' => array(
//    						'id >='=> $id,
//'id'=> '27996',
    						'deleted'=> 0,
    						'published'=> 1,
        					'volume >'=> 1000,
    					),
                        'fields' => array( '*'),
    					'order' => 'rand()'
                    ));
         $this->layout = 'ad';
         $this->set('item', $taobaoke['Taobaoke']);
         $this->set('item_all', $taobaoke);
                    //print_r($taobaoke);
    } 

    public function index_commend() {
        $this->set('page_request', $this->request);
    }

    public function search() {
        $this->autoRender = true;
        $url = rawurldecode($this->request->params['referer']);

        $old_request = new CakeRequest($url);
        $params = Router::parse($old_request->url);
        $old_request->addParams($params);

        $this->set('page_request', $old_request);
    }

    /**
     * 跳转到taobao页面
     * @param $id
     */
    public function goitem($id) {
        $this->autoRender = false;
        $item = $this->Taobaoke->findById($id);
        header('location:' . $item['Taobaoke']['click_url']);
//		print_r($item);
        exit;
    }

    public function admin_saveTopCats($parent_id=0) {
        $existscates = $this->__saveTopCats($parent_id);
        //不自动删除，仅选出不存在的就好哦。加入人工判断的页面，防止接口网络错误，误删数据
        echo 'save over';
        exit;
    }

    public function admin_toplist($parent_cid = null) {

        $itemcats = $this->TaobaoCate->find('all', array(
                    'conditions' => array('published' => 1, 'deleted' => 0, 'parent_id' => $parent_cid),
                    'order' => 'TaobaoCate.left asc'
                ));
        $this->set('itemcats', $itemcats);

        $current_cate = $this->TaobaoCate->find('first', array(
                    'conditions' => array('published' => 1, 'deleted' => 0, 'id' => $parent_cid),
                ));
        $this->set('parent_cid', $current_cate['TaobaoCate']['parent_id']);


//		$c = new TopClient();
//		 
//		$req_array = array(
//				"method"          => "taobao.itemcats.get",
//				"parent_cid"        => $parent_cid,
//		);
//		$resp = $c->execute($req_array);
//		if(isset($resp['item_cats']['item_cat'])){
//			$this->set('itemcats', $resp['item_cats']['item_cat']);
//		}
//		$this->set('parent_cid', $parent_cid);
    }

    /**
     * 列表显示淘宝客产品，可以不传cid而传关键字。
     *
     * @var $cid 类别编号
     */
    public function admin_products($cid = '') {

        $page = $this->params['named']['page'] ? $this->params['named']['page'] : 1;

        $c = new TopClient();

        $req_array = array(
            "method" => "taobao.taobaoke.items.get",
            'fields' => 'iid,num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,taobaoke_cat_click_url', //返回字段
            'nick' => TOP_NICK,
            'page_size' => 40, //每页返回结果数.最大每页40
            'page_no' => $page,
        );
        if (intval($cid)) {
            $req_array['cid'] = $cid;
        }
        $req_array['sort'] = 'commissionNum_desc';

        /**
         * 搜索的参数由GET方式传入
         */
        foreach ($_GET as $key => $val) {
            if (!empty($val)) {
                $req_array[$key] = $val;
            }
        }
        if ($req_array['keyword']) {
            unset($req_array['cid']);
        }
        if ($req_array['start_commissionRate'] && !$req_array['end_commissionRate']) { // 有开始佣金比例时，设置结束范围,两者需结合才能使用
            $req_array['end_commissionRate'] = '5000';
        }
        if ($req_array['start_commissionNum'] && !$req_array['end_commissionNum']) { // 有开始累计数量时，设置结束范围,两者需结合才能使用
            $req_array['end_commissionNum'] = '100000';
        }

        if ($req_array['start_price'] && !$req_array['end_price']) { // 有开始价格时，设置结束价格范围,两者需结合才能使用
            $req_array['end_price'] = '1000000';
        }

        $resp = $c->execute($req_array);
        if ($resp['total_results']) {
            $tkids = array();
            foreach ($resp['taobaoke_items']['taobaoke_item'] as $item) {
                $tkids[] = $item['num_iid'];
            }
            /**
             * 查询已经在淘宝客中的产品
             * @var $exists_items
             */
            $exists_items = $this->Taobaoke->find('all', array(
                        'conditions' => array('num_iid' => $tkids,),
                        'fields' => array('num_iid'),
                    ));
            $exists_num_iids = array();
            foreach ($exists_items as $taobaoke) {
                $exists_num_iids[] = $taobaoke['Taobaoke']['num_iid'];
            }
            $this->set('exists_num_iids', $exists_num_iids);
            $this->set('products', $resp['taobaoke_items']['taobaoke_item']);
            $page_navi = getPageLinks($resp['total_results'], 40, $this->request, $page);
            $this->set('page_navi', $page_navi);
        } else {
            echo '没有找到，检查搜索条件是否正确';
        }
        $this->set('cid', $cid);
    }

    /* 聚划算 */

    public function admin_juhuasuan() {
        $c = new TopClient();
        $req_array = array(
            "method" => "taobao.ju.items.get",
            'fields' => 'item_id,long_name,pay_postage,original_price,activity_price,item_status,discount,sold_count,is_lock,current_stock', //返回字段
            'ids' => '3435224732,3391942080',
        );
        $resp = $c->execute($req_array);
        print_r($resp);
    }

    public function admin_saveProducts($cid, $withSubcate = true) {
        set_time_limit(0);
        $this->__autoSaveProducts($cid, $withSubcate);

        $this->__message(__('Save Over!', true), '', 99999);
        return true;
    }

    /**
     * 更新淘宝商品信息，或者根据num_iid新增商品入库
     * @param string $num_iids 产品的编号，多个时，使用逗号隔开
     */
    public function admin_updateTaobaoke($num_iids='') {
        if ($_GET['num_iid']) {
            $num_iids = $_GET['num_iid'];
        }

        $this->autoRender = false;
        if ($this->__convertTaokeItem($num_iids)) { //支持新增和修改
            $this->__getTaokeDetail($num_iids); //只支持修改
            $successinfo = array('success' => __('get success', true));
        } else {
            $successinfo = array('error' => __('get error. network error or not in taobaoke.', true));
        }

        echo json_encode($successinfo);
    }

    /**
     * items.convert更新淘客商品信息
     * taobao.taobaoke.items.convert
     */
    public function admin_itemConvert() {
        $page = intval($this->params['named']['page']) ? intval($this->params['named']['page']) : 1;
        $pagesize = 20; // 每次取20条，top items.convert接口num_iids参数最大支持40个,但实际返回结果只20条
        do {
            $items = $this->Taobaoke->find('all', array(
                        'conditions' => array('published' => 1, 'deleted' => 0),
                        'limit' => $pagesize,
                        'fields' => array('num_iid'),
                        'order' => 'volume desc',
                        'page' => $page
                    ));
            $num_iids = array();
            $c = new TopClient();
            foreach ($items as $item) {
                if ($item['Taobaoke']['num_iid']) {
                    $num_iids[] = $item['Taobaoke']['num_iid'];
                }
            }
            if (!empty($num_iids)) {
                $num_iids = implode(',', $num_iids);
                $this->__convertTaokeItem($num_iids);
            }
            echo "==$page===<BR/>";
            $page++;
        } while (count($items) == $pagesize);

        $this->__message(__('over.Page:' . $page), '#', 999999);
        exit;
    }

    /**
     * taobao.taobaoke.items.detail.get
     *
     */
    public function admin_updateProductInfo() {

        $page = intval($this->params['named']['page']) ? intval($this->params['named']['page']) : 1;
        $pagesize = 10; // 每次取10条，taobao.taobaoke.items.detail.get接口num_iids参数最大支持10个

        do {
            $items = $this->Taobaoke->find('all', array(
                        'conditions' => array('published' => 1, 'deleted' => 0),
                        'limit' => $pagesize,
                        'fields' => array('id', 'num_iid'),
                        'page' => $page,
                        'order' => 'volume desc',
                    ));
            $num_iids = array();
            foreach ($items as $item) {
                if ($item['Taobaoke']['num_iid']) {
                    $num_iids[] = $item['Taobaoke']['num_iid'];
                }
            }
            if ($this->__convertTaokeItem($num_iids)) { //支持新增和修改
                $this->__getTaokeDetail($num_iids); //只支持修改
            }
            ++$page;
            if (!empty($GLOBALS['argv'])) {
                echo "\r\n\r\ncurrent page:" . $page . "\r\n";
                foreach ($items as $item) {
                    echo $item['Taobaoke']['id'] . "-" . $item['Taobaoke']['num_iid'] . "\r\n";
                }
                sleep(2); //停2s,保证每分钟接口调用次数小于100次
            }
            /**
             * 若打开以下注释，则按页取，每次一页，跳转进入下一页
             */
//			if(count($items)<10){
//				$this->__message(__('over'), '#', 999999);
//			}
//			else{
//				$this->__message(__('next  page. current:'.$page." num: ".count($num_iids)), array('page'=> $page), 2);
//			}
        } while (count($items) == 10);

        $this->__message(__('over'), '#', 999999);
        exit;
    }

/**
     * taobao.taobaoke.items.detail.get
     *
     */
    public function admin_saecron_updateProductInfo() {

        $page = intval($this->params['named']['page']) ? intval($this->params['named']['page']) : 1;
        $pagesize = 10; // 每次取10条，taobao.taobaoke.items.detail.get接口num_iids参数最大支持10个

        
        $items = $this->Taobaoke->find('all', array(
                    'conditions' => array('published' => 1, 'deleted' => 0),
                    'limit' => $pagesize,
                    'fields' => array('id', 'num_iid'),
                    'page' => $page,
                    'order' => 'num_iid desc',
        ));
        $num_iids = array();
        foreach ($items as $item) {
            if ($item['Taobaoke']['num_iid']) {
                $num_iids[] = $item['Taobaoke']['num_iid'];
            }
        }
        if ($this->__convertTaokeItem($num_iids)) { //支持新增和修改
            $this->__getTaokeDetail($num_iids); //只支持修改
        }
    	echo "\r\n\r\ncurrent page:" . $page . "\r\n";
        foreach ($items as $item) {
            echo $item['Taobaoke']['id'] . "-" . $item['Taobaoke']['num_iid'] . "\r\n";
        }
        if (count($items) == $pagesize && empty($_GET['skip_next'])){
        	$next_url = "http://".$_SERVER['HTTP_APPNAME'].".sinaapp.com/queue.php?cron_secret=".CLOUD_CRON_SECRET."&url=/admin/Taobao/taobaokes/saecron_updateProductInfo/page:".($page+1);
 			echo "next url:".$next_url;
        	$queue = new SaeTaskQueue('crons');
			$queue->addTask($next_url);
			$ret = $queue->push();
        }
        exit;
    }
    
    /**
     * 保存某一分类的商品
     * @param $cid
     * @param $withSubcate，为true时，自己进入下级类别并保存下级类别下的产品
     */
    private function __autoSaveProducts($cid, $withSubcate = false) {

        $c = new TopClient();
        $req_array = array(
            "method" => "taobao.taobaoke.items.get",
            'fields' => 'iid,num_iid,title,nick,pic_url,price,click_url,commission,commission_rate,commission_num,commission_volume,shop_click_url,seller_credit_score,item_location,taobaoke_cat_click_url', //返回字段
            'nick' => TOP_NICK,
            "cid" => $cid,
            'page_size' => 10, // 默认每个类别自动保存成交量最大的10个
            'sort' => 'commissionNum_desc',
        );
        $resp = $c->execute($req_array);
        echo "==$cid===<BR/>";
        if (!empty($resp['taobaoke_items']['taobaoke_item'])) {
            foreach ($resp['taobaoke_items']['taobaoke_item'] as $top_item) {
                $this->data['Taobaoke'] = array();

                $this->data['Taobaoke']['cate_id'] = $cid;
                $this->data['Taobaoke']['name'] = $top_item['title'];
                $this->data['Taobaoke']['nick'] = $top_item['nick'];
                $this->data['Taobaoke']['commission'] = $top_item['commission'];
                $this->data['Taobaoke']['commission_num'] = $top_item['commission_num'];
                $this->data['Taobaoke']['ommission_rate'] = $top_item['ommission_rate'];
                $this->data['Taobaoke']['commission_volume'] = $top_item['commission_volume'];
                $this->data['Taobaoke']['item_location'] = $top_item['item_location'];
                $this->data['Taobaoke']['num_iid'] = $top_item['num_iid'];
                $this->data['Taobaoke']['pic_url'] = $top_item['pic_url'];
                $this->data['Taobaoke']['price'] = $top_item['price'];
                $this->data['Taobaoke']['click_url'] = $top_item['click_url'];
                $this->data['Taobaoke']['creator'] = 1;
                $this->data['Taobaoke']['published'] = 1;

                $this->Taobaoke->create();
                $this->Taobaoke->save($this->data);
            }
        }

        if ($withSubcate) {
            $cates = $this->__getSubCats($cid);
            if (!empty($cates)) {
                foreach ($cates as $cate) {
                    sleep(5);
                    $this->__autoSaveProducts($cate['cid'], $withSubcate);
                }
            }
        }

        return true;
    }

    /**
     * 根据商品的num_iid,新增（存在时修改）淘宝客商品。 淘宝接口：taobao.taobaoke.items.convert
     * @param $num_iids 商品编号，值为数组或者逗号连接的字符串
     * @return boolean if num_iid product exists,return true,else return false.
     */
    private function __convertTaokeItem($num_iids = null) {

        if (!empty($num_iids)) {
        	$num_iids_str = $num_iids;
            if (is_array($num_iids)) {
                $num_iids_str = implode(',', $num_iids);
            }
            else{
            	$num_iids = explode(',', $num_iids);
            }
            $c = new TopClient();
            $req_array = array(
                "method" => "taobao.taobaoke.items.convert",
                'fields' => 'num_iid,title,nick,pic_url,price,click_url,volume,commission,commission_rate,commission_num,commission_volume,item_location', //返回字段
                'nick' => TOP_NICK,
                "num_iids" => $num_iids_str, // 淘宝客商品数字id串.最大输入10个.格式如:"value1,value2,value3" 用" , "号分隔商品id.
            );

            // 此接口支持返回volume
            $resp = $c->execute($req_array);            
            if ($resp['total_results']) {
                $db = $this->Taobaoke->getDataSource();
                if ($resp['total_results'] == 1) {
                    $resp['taobaoke_items']['taobaoke_item'] = array($resp['taobaoke_items']['taobaoke_item']);
                }
                $exists_item = array();
                
                foreach ($resp['taobaoke_items']['taobaoke_item'] as $taobaokeItem) {
                	$exists_item[] = $taobaokeItem['num_iid'];
                    $this->Taobaoke->create();
                    $this->data['Taobaoke'] = array();
                    $this->data['Taobaoke']['name'] = $taobaokeItem['title'];
                    $this->data['Taobaoke']['nick'] = $taobaokeItem['nick'];
                    $this->data['Taobaoke']['volume'] = $taobaokeItem['volume'];
                    $this->data['Taobaoke']['pic_url'] = $taobaokeItem['pic_url'];
                    $this->data['Taobaoke']['price'] = $taobaokeItem['price'];
                    $this->data['Taobaoke']['click_url'] = $taobaokeItem['click_url'];
                    $this->data['Taobaoke']['commission'] = $taobaokeItem['commission'];
                    $this->data['Taobaoke']['commission_rate'] = $taobaokeItem['commission_rate'];
                    $this->data['Taobaoke']['commission_num'] = $taobaokeItem['commission_num'];
                    $this->data['Taobaoke']['commission_volume'] = $taobaokeItem['commission_volume'];
                    $this->data['Taobaoke']['item_location'] = $taobaokeItem['item_location'];
                    $this->data['Taobaoke']['num_iid'] = $taobaokeItem['num_iid'];
                    $this->data['Taobaoke']['updated'] = date('Y-m-d H:i:s');

                    $this->data['Taobaoke']['creator'] = 1;
                    $this->data['Taobaoke']['published'] = 1;

                    $exists_items = $this->Taobaoke->find('all', array(
                                'conditions' => array('num_iid' => $taobaokeItem['num_iid'],),
                                'fields' => array('num_iid'),
                            ));

                    if (!empty($exists_items)) {
                        // 清空内容中的链接，链接不是推广链接，没有提成的都干掉
                        foreach ($this->data['Taobaoke'] as &$value) {
                            $value = $db->value($value);
                        }
                        $this->Taobaoke->updateAll($this->data['Taobaoke'], array('num_iid' => $taobaokeItem['num_iid']));
                    } else {
                        $this->Taobaoke->save($this->data);
                    }
                }                
                $not_exists_item = array_diff($num_iids, $exists_item);
                if(!empty($not_exists_item)){
                	$this->Taobaoke->deleteAll(array('num_iid' => $not_exists_item));
                	echo 'delete items:';
                	print_r($not_exists_item);
                }
               
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
    
    public function admin_getTaokeDetail($num_iids = null) {
    	
    	if ($this->__convertTaokeItem($num_iids)) { //支持新增和修改
            $this->__getTaokeDetail($num_iids); //只支持修改
        }
    }

    /**
     * 根据$num_iid获取商品的详细信息
     * @param $num_iids
     */
    private function __getTaokeDetail($num_iids = null) {
        if (!empty($num_iids)) {
            if (is_array($num_iids)) {
                $num_iids = implode(',', $num_iids);
            }
            $c = new TopClient();
            $req_array = array(
                "method" => "taobao.taobaoke.items.detail.get",
                'fields' => 'detail_url,cid,num_iid,title,nick,pic_url,item_img.id,item_img.url,property_alias,props,input_str,input_pids,price,click_url,volume,desc,post_fee,express_fee,ems_fee,freight_payer,num', //返回字段
                'nick' => TOP_NICK,
                "num_iids" => $num_iids, // 淘宝客商品数字id串.最大输入10个.格式如:"value1,value2,value3" 用" , "号分隔商品id.
            );
            // 此接口无法返回volume
            $resp = $c->execute($req_array);
			//print_r($resp);	
            if ($resp['total_results']) {

                $db = $this->Taobaoke->getDataSource();
                if ($resp['total_results'] == 1) {
                    $resp['taobaoke_item_details']['taobaoke_item_detail'] = array($resp['taobaoke_item_details']['taobaoke_item_detail']);
                }
                foreach ($resp['taobaoke_item_details']['taobaoke_item_detail'] as $itemDetail) {

                    $this->data['Taobaoke'] = array();
                    $this->data['Taobaoke']['cate_id'] = $itemDetail['item']['cid'];
                    $this->data['Taobaoke']['name'] = $itemDetail['item']['title'];
                    $this->data['Taobaoke']['nick'] = $itemDetail['item']['nick'];
                    $this->data['Taobaoke']['pic_url'] = $itemDetail['item']['pic_url'];
                    $this->data['Taobaoke']['item_imgs'] = serialize($itemDetail['item']['item_imgs']['item_img']);
                    $this->data['Taobaoke']['price'] = $itemDetail['item']['price'];
                    $this->data['Taobaoke']['click_url'] = $itemDetail['click_url'];
                    $this->data['Taobaoke']['post_fee'] = $itemDetail['item']['post_fee'];
                    $this->data['Taobaoke']['express_fee'] = $itemDetail['item']['express_fee'];
                    $this->data['Taobaoke']['ems_fee'] = $itemDetail['item']['ems_fee'];
                    $this->data['Taobaoke']['freight_payer'] = $itemDetail['item']['freight_payer'];
                    $this->data['Taobaoke']['content'] = preg_replace('/<a.+?>(.+?)<\s*\/a>/is', '\\1', $itemDetail['item']['desc']);
                    $this->data['Taobaoke']['updated'] = date('Y-m-d H:i:s');
                    // 清空内容中的链接，链接不是推广链接，没有提成的,都干掉
                    foreach ($this->data['Taobaoke'] as &$value) {
                        $value = $db->value($value);
                    }
                    $this->Taobaoke->updateAll($this->data['Taobaoke'], array('num_iid' => $itemDetail['item']['num_iid']));
                    //$this->Taobaoke->save($this->data);
                }
            }
        }
    }

    private function __getSubCats($parent_cid) {
        $c = new TopClient();
//		$this->
        $req_array = array(
            "method" => "taobao.itemcats.get",
            "parent_cid" => $parent_cid,
        );

        $resp = $c->execute($req_array);
        if ($resp['item_cats'] && $resp['item_cats']['item_cat']) {
            return $resp['item_cats']['item_cat'];
        } else {
            return array();
        }
    }

    /**
     * 保存一个分类下的所有子类数据，返回保存的数据的id
     * @param unknown_type $parent_cid
     * @return multitype:
     */
    private function __saveTopCats($parent_cid = 0) {

        echo "$parent_cid<BR/>\r\n";

        $c = new TopClient();

        $req_array = array(
            "method" => "taobao.itemcats.get",
            "parent_cid" => $parent_cid,
        );
        $resp = $c->execute($req_array);

        $saved_items = array();
        if (!empty($resp['item_cats']['item_cat'])) {
            foreach ($resp['item_cats']['item_cat'] as $itemcat) {

                $this->data['TaobaoCate'] = array();
                $this->TaobaoCate->create();
                $havegot = $this->TaobaoCate->findByCid($itemcat['cid']);
                if (!empty($havegot['TaobaoCate'])) {
                    // 对已插入的数据，需要设置id，parent_id,left,right来修改数据
                    $this->data['TaobaoCate'] = $havegot['TaobaoCate'];
                }

                $this->data['TaobaoCate']['cid'] = $itemcat['cid'];
                $this->data['TaobaoCate']['name'] = $itemcat['name'];
                if ($parent_cid) {
                    $this->data['TaobaoCate']['parent_id'] = $parent_cid;
                } else {
                    $this->data['TaobaoCate']['parent_id'] = null;
                }
                if ($this->TaobaoCate->save($this->data)) {
                    $saved_items[] = $itemcat['cid'];
                    $id = $this->TaobaoCate->getLastInsertID();
                    // ID与TOP保持一致。 先插入后，再修改id。
                    // (直接插入id的数据时，会视为是修改，tree结构的left，right值不对)
                    if (empty($havegot['TaobaoCate'])) {
                        $this->TaobaoCate->update(array('TaobaoCate.id' => $itemcat['cid']), array('id' => $this->TaobaoCate->id));
                    }
                }
            }
            //exit;
            // 保存完所有分类后，再一次获取子类并保存
            foreach ($resp['item_cats']['item_cat'] as $itemcat) {
                if ($itemcat['is_parent']) {
                    sleep(5);
                    $subcats = $this->__saveTopCats($itemcat['cid']);
                    $saved_items = array_merge($saved_items, $subcats);
                }
            }
        }
        return $saved_items;
    }

}

?>