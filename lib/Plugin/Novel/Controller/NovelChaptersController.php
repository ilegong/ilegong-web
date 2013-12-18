<?php

App::uses("CrawlUtility", "Utility");
App::uses("Charset", "Lib");

class TaobaoPromotionsController extends TaobaoAppController {

    public $name = 'TaobaoPromotions';


    /* 折扣 */

    public function admin_get() {
        $page = intval($this->params['named']['page']) ? intval($this->params['named']['page']) : 1;
        $pagesize = 20;

        do {
            $items = $this->Taobaoke->find('all', array(
                        'conditions' => array('published' => 1, 'deleted' => 0),
                        'limit' => $pagesize,
                        'fields' => array('num_iid'),
                        'page' => $page,
                        'order' => 'volume desc'
                    ));
            foreach ($items as $item) {
                $num_iid = $item['Taobaoke']['num_iid'];

                // 删除旧的后，再考虑其他，防止优惠信息过期，对用户造成不好影响
                $delete_flag = $this->TaobaoPromotion->deleteAll(array('num_iid' => $num_iid), true, true);
                $re = $this->admin_getpromotion($num_iid);
                if ($re) {
                    echo 'Get ' . $num_iid . " success.\r\n";
                } else {
                    echo 'Get ' . $num_iid . " false.\r\n";
                }
                sleep(1);
            }
            echo "get page $page \r\n";
            $page++;
        } while (count($items) == $pagesize);

        $this->__message(__('over'), '#', 999999);
    }

    public function admin_saecron_get(){
    	$page = intval($this->params['named']['page']) ? intval($this->params['named']['page']) : 1;
        $pagesize = 10;
		$items = $this->Taobaoke->find('all', array(
                    'conditions' => array('published' => 1, 'deleted' => 0),
                    'limit' => $pagesize,
                    'fields' => array('num_iid'),
                    'page' => $page,
                    'order' => 'volume desc'
        ));
        foreach ($items as $item) {
            $num_iid = $item['Taobaoke']['num_iid'];
            // 删除旧的后，再考虑其他，防止优惠信息过期，对用户造成不好影响
            $delete_flag = $this->TaobaoPromotion->deleteAll(array('num_iid' => $num_iid), true, true);
            $re = $this->admin_getpromotion($num_iid);
            if ($re) {
                echo 'Get ' . $num_iid . " success.\r\n";
            } else {
                echo 'Get ' . $num_iid . " false.\r\n";
            }
        }
        echo "get page $page \r\n";
    	if (count($items) == $pagesize && empty($_GET['skip_next'])){
        	$next_url = "http://".$_SERVER['HTTP_APPNAME'].".sinaapp.com/queue.php?cron_secret=".CLOUD_CRON_SECRET."&url=/admin/Taobao/taobao_promotions/saecron_get/page:".($page+1);
 			echo "next url:".$next_url;
        	$queue = new SaeTaskQueue('crons');
			$queue->addTask($next_url);
			$ret = $queue->push();
        }
        exit;
    }
    /**
     * 获取商品的优惠信息
     * @param $num__iid  商品编号 for test,12224681317, 5210490640,,,
     */
    public function admin_getpromotion($num_iid) {
        $promotion_url = "http://marketing.taobao.com/home/promotion/item_promotion_list.do?itemId=" . $num_iid;
        $promote = CrawlUtility::getRomoteUrlContent($promotion_url);
        preg_match("/{.+}/s", $promote->body, $matches);

        $jsoncode = $matches[0];
        if (empty($jsoncode)){
        	return $this->admin_getPromotionByPreg($num_iid);
        }
        $jsoncode = Charset::gbk_utf8($jsoncode);
        $promData = jsonToArray($jsoncode);
        //print_r($promData);
        if ($promData['isSuccess'] == 'T' && !empty($promData['promList'])) {
            $priceInfo = $promData['promList'][0];
            $policyList = $priceInfo['policyList'][0];
            if (empty($policyList)) {
            	return $this->admin_getPromotionByPreg($num_iid);
            }
            $this->TaobaoPromotion->create();
            $promotion_info = array();
            // promList
            $promotion_info['name'] = $priceInfo['iconTitle'];
            $promotion_info['promId'] = $priceInfo['promId'];
            $promotion_info['promType'] = $priceInfo['promType'];
            $promotion_info['promName'] = $priceInfo['promName'];
            $promotion_info['showPrice'] = $priceInfo['showPrice'];
            $promotion_info['showPoint'] = $priceInfo['showPoint'];
            $promotion_info['showIcon'] = $priceInfo['showIcon'];
            // policyList
            $promotion_info['policyId'] = $policyList['policyId'];
            $promotion_info['groupName'] = $policyList['groupName'];
            $promotion_info['iconFile'] = $policyList['iconFile'];
            $promotion_info['discountValue'] = $policyList['discountValue'];
            if ($policyList['discountType'] == 1) {
                // 若为直接降价，则转换成折扣的形式
                $policyList['discountType'] = 2;
                $promotion_info['discountValue'] = round($policyList['promPrice'] * 10 / ($promotion_info['discountValue'] + $policyList['promPrice']), 2);
            }
            $promotion_info['discountType'] = $policyList['discountType'];
            $promotion_info['promPrice'] = $policyList['promPrice'];
            $promotion_info['num_iid'] = $num_iid;
            $this->TaobaoPromotion->save($promotion_info);
            return true;
        }
        else{
        	return $this->admin_getPromotionByPreg($num_iid);
        }
        return false;
    }
    
    public function  admin_getPromotionByPreg($num_iid){
    	//http://marketing.taobao.com/home/promotion/item_promotion_list.do?itemId=12678875747
    	//http://detail.tmall.com/item.htm?id=12678875747
//    	$num_iid = 12678875747;
    	$this->autoRender =false;
    	$promotion_url = "http://detail.tmall.com/item.htm?id=" . $num_iid;
    	$promote = CrawlUtility::getRomoteUrlContent($promotion_url);        
        $promote = Charset::gbk_utf8($promote->body);
        /*
         * 
         * <ul id="Ul_promo" class="tb-clearfix">\s<li><span id="J_ImgLimitProm" class="tb-icon tb-limit-prom" title="限时促销"></span><strong class="tb-price" id="J_SpanLimitProm">129.00</strong>元</li>
                            <li>(剩 <em id="J_EmLimitPromCountdown">-</em> 结束)</li>\s</ul>
         */       
        preg_match('/<ul id="Ul_promo" class="tb-clearfix">\s+<li><span id="J_ImgLimitProm" class="tb-icon tb-limit-prom" title="(.+?)"><\/span><strong class="tb-price" id="J_SpanLimitProm">(.+?)<\/strong>.+?<\/li>\s+<li>.+?<\/li>\s+?<\/ul>/is', $promote, $matches);
        
        if(!empty($matches[1])){
	        $this->TaobaoPromotion->create();	        
	        $item = $this->Taobaoke->find('first', array(
	                    'conditions' => array('published' => 1, 'deleted' => 0,'num_iid' => $num_iid),
	                    'fields' => array('price','id','num_iid'),
	        ));	        
	        $promotion_info = array();
	        // promList
	        $promotion_info['name'] = $matches[1];
	        $promotion_info['promType'] = 1;
	        $promotion_info['promName'] = $matches[1];
	        $promotion_info['groupName'] = $matches[1];
	        
	        $promotion_info['discountType'] = 2;
	        $promotion_info['promPrice'] = $matches[2];
	        if($item['Taobaoke']['price']){
	        	$promotion_info['discountValue'] = round($promotion_info['promPrice'] * 10 / ($item['Taobaoke']['price']), 2);
	        }	        
	        $promotion_info['num_iid'] = $num_iid;
	        //print_r($promotion_info);
	        $this->TaobaoPromotion->save($promotion_info);  
	        return true;
        }
        else{
        	return false;
        }
    }

}

?>