<?php

class CommentsController extends AppController {

    var $name = 'Comments';

    var $components = array(
        'Email',
    );
    var $uses = array('Comment', 'User');
    function add() {
    	$this->autoRender = false;
    	$this->layout = 'ajax';
        if (!isset($this->data['Comment']['data_id'])) {
            $this->Session->setFlash(__('Invalid Params', true));
            $this->redirect('/');
        }
        $returnInfo = $this->_save_comment();
        echo json_encode($returnInfo);
    }

    function add_shichi(){
        $this->autoRender = false;
        $uid = $this->currentUser['id'];

        if (!empty($this->data)) {
            $this->loadModel('OrderShichi');
            $re = $this->OrderShichi->find('first', array('conditions' => array('creator' => $uid, 'data_id' => $this->data['Comment']['data_id']))); //查找是否有试吃订单
           $this->log('re:'.json_encode($re));
            if (!empty($re) && $re['OrderShichi']['is_comment'] == 0) {
                $this->data['Comment']['is_shichi_vote'] = 1;
                $info = $this->_save_comment();
                if ($info['success']) {
                    $shichiId = $re['OrderShichi']['id'];
                    $updated = $this->OrderShichi->updateAll(array('is_comment' => 1), array('id' => $shichiId));
                    $this->log("OrderShichi udpate is_comment($updated):" . $shichiId . ", uid=" . $uid);
                } else {
                    $this->log("error to save comment/shichi_vote for $uid:". json_encode($info));
                }
            }
        } else {
            $info = array('success' => false, 'error' => 'NO_DATA');
        }

        $this->log('add_shichi_return:'. json_encode($info));

        echo json_encode($info);
    }

    function shichi_vote($id){

        $this->autoRender=false;
        $this->layout='ajax';
        $this->loadModel('OrderShichi');
        $this->log('id'.json_encode($id));
        $num=$this->OrderShichi->find('count',array('conditions' => array('data_id' =>$id)));
        $this->log('num'.json_encode($num));
        $comments=$this->Comment->find('all',array('conditions'=>array('Comment.data_id'=>$id,'Comment.is_shichi_vote'=>1),'fields'=>array('Comment.shichi_rating')));
        $this->log('comments'.json_encode($comments));
//      $Vote_num=array();
        $Vote_num[0]='0';$Vote_num[1]='0';
        foreach($comments as $re){
            if($re['Comment']['shichi_rating']==1){
                $Vote_num[0]++;
            }else{
                $Vote_num[1]++;
            }

        }
        $count1=$Vote_num[0];$count2=$Vote_num[1];
        $count=$Vote_num[0]+$Vote_num[1]; $this->log('count'.json_encode($count));
        if ($count !=0){
        $Vote_num[0] = round($Vote_num[0]/$count*100);
        $Vote_num[1] = round($Vote_num[1]/$count*100);
        }else {
            $Vote_num[0] = 0;
            $Vote_num[1] = 0;
        }
        $data=array();
        $data[]=$Vote_num;$data[]=$count;$data[]=$count1;$data[]=$count2;$data[]=$num;
        $this->log('data'.json_encode($data));
//        echo json_encode($Vote_num);
        echo json_encode($data);
    }

    function get_shichi_list($model_name,$id) {
        $page = intval($_GET['page'])?intval($_GET['page']):1;
        $pagesize = intval($_GET['pagesize'])?intval($_GET['pagesize']):50;
        $this->autoRender = false;
        $model_name = Inflector::classify($model_name);
        $comments = $this->Comment->find('all',array(
            'conditions' => array('Comment.type' => $model_name,'data_id'=>$id,'status'=>1,'rating'=>0),
            'order' => array('Comment.created DESC'), //定义顺序的字符串或者数组
            'limit' => $pagesize, //整型
            'page' => $page, //整型
        ));
//        $this->loadModel('Shichituan');
        $result = array();
        foreach ($comments as $comt){
            $item = $comt['Comment'];
            if(empty($item['username']) && !empty($item['user_id']) ){
                $data = $this->User->find('first', array('conditions' => array('id' => $item['user_id'])));
                if(isset($data['User']['id']) ){
                    $item['username'] = $data['User']['nickname'];
                }
            }
            if( empty($item['username']) ){
                $item['username'] = '微信用户';
            }
            unset($item['ip']);
            unset($item['lft']);
            unset($item['rght']);

            if ($item['pictures']) {
                $images = array();
                $pics = mbsplit("\\|", $item['pictures']);
                foreach($pics as $pic) {
                    if($pic && strpos($pic, "http://") === 0) {
                        $images[] = $pic;
                    }
                }
                if (count($pics) > 0) {
                    $item['images'] = $images;
                }
            }

            unset($item['pictures']);
            array_push($result,array('Comment' => $item));
        }

        echo json_encode($result);


    }

    function getlist($model_name,$id){
    	$page = intval($_GET['page'])?intval($_GET['page']):1;
    	$pagesize = intval($_GET['pagesize'])?intval($_GET['pagesize']):100;
    	$this->autoRender = false;
    	$model_name = Inflector::classify($model_name);
        //'rating'=>array('1','5','3')
        $p_ids = get_group_product_ids($id);
    	$comments = $this->Comment->find('all',array(
    		 'conditions' => array('Comment.type' => $model_name,'data_id'=>$p_ids,'status'=>1,'is_shichi_vote'=>0,),
    		 'order' => array('Comment.publish_time DESC'), //定义顺序的字符串或者数组
		    'limit' => $pagesize, //整型
		    'page' => $page, //整型
    	));
//        $this->loadModel('Shichituan');
        $result = array();
        foreach ($comments as $comt){
            $item = $comt['Comment'];
            if(empty($item['username']) && !empty($item['user_id']) ){
                $data = $this->User->find('first', array('conditions' => array('id' => $item['user_id'])));
                if(isset($data['User']['id']) ){
                    $item['username'] = $data['User']['nickname'];
                }  
            }
            if( empty($item['username']) ){
                $item['username'] = '微信用户';
            }
            unset($item['ip']);
            unset($item['lft']);
            unset($item['rght']);
            $item['buy_time'] = friendlyDateFromStr($item['buy_time'],'ymd');
            if ($item['pictures']) {
                $images = array();
                $pics = mbsplit("\\|", $item['pictures']);
                foreach($pics as $pic) {
                    if($pic && (strpos($pic, "http://") === 0||strpos($pic, "/") === 0)) {
                        $images[] = $pic;
                    }
                }
                if (count($pics) > 0) {
                    $item['images'] = $images;
                }
            }

            unset($item['pictures']);
            $commentUserId = $item['user_id'];
            $photo = $this->User->find('all',array(
                'fields'=>array('image'),
                'conditions'=>array(
                    'id' => $commentUserId
                ),
                'recursive'=>-1
            ));
            $item['userPhoto']= $photo[0]['User']['image'];
            array_push($result,array('Comment' => $item));
        }

    	echo json_encode($result);

    } 

    function __spam_protection($continue, $type, $node) {
        if (!empty($this->data) &&
            $type['Type']['comment_spam_protection'] &&
            $continue === true) {
            $this->Akismet->setCommentAuthor($this->data['Comment']['username']);
            $this->Akismet->setCommentContent($this->data['Comment']['body']);
            //$this->Akismet->setPermalink(Router::url($node['Node']['url'], true));
            if ($this->Akismet->isCommentSpam()) {
                $continue = false;
                $this->Session->setFlash(__('Sorry, the comment appears to be spam.', true));
            }
        }
        return $continue;
    }

    function __captcha($continue, $type, $node) {
        if (!empty($this->data) &&
            $type['Type']['comment_captcha'] &&
            $continue === true &&
            !$this->Recaptcha->valid($this->params['form'])) {
            $continue = false;
            $this->Session->setFlash(__('Invalid captcha entry', true));
        }        
        return $continue;
    }
	/**
	 * 用户删除自己发布的评论
	 * @param integer $id 数据编号
	 */
    function delete($id) {
        $success = 0;
        $userId = $this->Session->read('Auth.User.id');
        if ($userId) {            
            $comment = $this->Comment->find('first', array(
                'conditions' => array(
                    'Comment.id' => $id,
                    'Comment.user_id' => $userId,
                ),
            ));

            if (isset($comment['Comment']['id']) &&
                $this->Comment->delete($id)) {
                $success = 1;
            }
        }
        $this->set('success',$success);
    }

    /**
     * @return array the result, which will contains a 'success' index with a not null string value
     */
    public function _save_comment()
    {
        if ($this->Session->check('Auth.User.id') && !empty($this->data)) {
            if ($this->nick_should_edited($this->Session->read('Auth.User.nickname'))) {
                $returnInfo = array('error' => 'edit_nick_name');
                return $returnInfo;
            } else {

                $this->data['Comment']['user_id'] = $this->Session->read('Auth.User.id');
                $this->data['Comment']['username'] = $this->Session->read('Auth.User.nickname');
                $this->data['Comment']['body'] = htmlspecialchars($this->data['Comment']['body']);
                $this->data['Comment']['ip'] = $this->request->clientIp(false);
                $this->data['Comment']['status'] = 1;
                $this->data['Comment']['created'] = date('Y-m-d H:i:s');

                $can_comment = true;
                $uid = $this->currentUser['id'];
                $type_model = $this->data['Comment']['type'];
                if ($type_model == 'Product') {
                    $product_id = $this->data['Comment']['data_id'];
                    $this->loadModel('Cart');
                    $found = $this->Cart->find('all', array(
                        'conditions' => array('product_id' => $product_id, 'creator' => $uid, 'status' => CART_ITEM_STATUS_BALANCED),
                        'fields' => array('order_id')
                    ));

                    $this->log("found to comment orders-id:". json_encode($found));

                    if (!empty($found)) {
                        $this->loadModel('Order');
                        $orderIds = array();
                        foreach($found as $e) {
                            $orderIds[] = $e['Cart']['order_id'];
                        }

                        $has_valid = 0;
                        $orders = $this->Order->find_all_my_order_byId($orderIds, $uid);
                        foreach($orders as $o) {
                            $status = $o['Order']['status'];
                            if ($status != ORDER_STATUS_CANCEL
//                                && $status != ORDER_STATUS_WAITING_PAY
                                && $status != ORDER_STATUS_RETURN_MONEY
                                && $status != ORDER_STATUS_RETURNING_MONEY
                                ) {
                                $has_valid++;
                            } else {
                                $this->log("cannot_comment: status=".$status.", order-id=".json_encode($o));
                            }
                        }
                        if ($has_valid == 0) {
                            $can_comment = false;
                        }
                    } else {
                        $can_comment = false;
                    }
                }

                if (!$can_comment)  {
                    return array('error' => 'cannot_comment');
                }

                //comment_type: 评论，补充完善，扩展阅读等等
                if ($this->Comment->save($this->data)) {
                    //$comment_id = $this->Comment->getLastInsertID();
                    $this->loadModel($type_model);
                    $this->{$type_model}->updateAll(
                        array('comment_nums' => 'comment_nums+1'),
                        array('id' => $product_id)
                    );

                    if ($this->data['status']) {
                        $returnInfo = array('success' => '您的评论已成功提交');
                        //$this->Session->setFlash(__('Your comment has been added successfully.', true));
                    } else {
                        $returnInfo = array('success' => '您的评论已成功提交');
                        //$this->Session->setFlash(__('Your comment will appear after moderation.', true));
                    }
                    $returnInfo['Comment'] = $this->data['Comment'];
                    return $returnInfo;
                } else {
                    $returnInfo = $this->{$this->modelClass}->validationErrors;
                    return $returnInfo;
                }
            }
        } else {
            $returnInfo = array('error' => 'please_login');
            return $returnInfo;
        }
    }

    function add_order_comment(){
        $this->autoRender=false;
        $status = $_REQUEST['status'];
        $currentDate = date('Y-m-d H:i:s');
        $orderId = $_REQUEST['orderId'];
        $serverStar = $_REQUEST['service_star'];
        $logisticsStar=$_REQUEST['logistics_star'];
        if ($this->Session->check('Auth.User.id')){
            $userId = $this->Session->read('Auth.User.id');
            $orderComment = ClassRegistry::init('OrderComment');
            $tempComment = $orderComment->find('all',array(
                'conditions'=>array('order_id'=>$orderId,'user_id'=>$userId)
            ));
            if($tempComment){
                //$tempComment=array_merge(array('server_star'=>$serverStar, 'logistics_star'=>$logisticsStar, 'updated'=>$currentDate, 'status'=>$status),$tempComment[0]['BrandComment']);
                $orderComment->id=$tempComment[0]['OrderComment']['id'];
                $tempComment = $orderComment->save(array('service_star'=>$serverStar, 'logistics_star'=>$logisticsStar, 'updated'=>$currentDate, 'status'=>$status));
            }else{
                $tempComment=array(
                    'order_id'=>$orderId,
                    'user_id'=>$userId,
                    'service_star'=>$serverStar,
                    'logistics_star'=>$logisticsStar,
                    'created'=>$currentDate,
                    'status'=>$status
                );
                $orderComment->save($tempComment);
            }
            if($tempComment){
                $result = array('success'=>true);
            }else{
                $result = array('success'=>false);
            }
            echo json_encode($result);
        }

    }
    //to add comment view
    function add_comment($orderId){
        $this->pageTitle="添加评论";
        //TODO check user nick name
        if ($this->Session->check('Auth.User.id')) {
            $force = $_REQUEST['force'];
            //has bind mobile
            $uid = $this->currentUser['id'];
            $order= $this->get_order($orderId,$uid);
            if ( true || $force || $order['Order']['is_comment'] == ORDER_COMMENTED) {
                $products = $this->get_order_products($orderId,$uid);
                $this->set("products",$products);
                $this->set("order",$order);
                $this->set('hideNav',true);
                $draftOrderRating = $this->get_order_rating($uid,$orderId);
                if($draftOrderRating){
                    $this->set('order_rating',$draftOrderRating);
                }
                $this->set('jWeixinOn', true);
            }else{
                $this->redirect('/users/to_bind_mobile?order_id='.$orderId);
            }
        }else{
            $this->redirect('/users/login');
        }
    }
    //add comment to database
    function persistent_comment(){
        $this->autoRender=false;
        $uid = $this->Session->read('Auth.User.id');
        $orderId = $this->data['Comment']['order_id'];
        $dataId = $this->data['Comment']['data_id'];
        $this->data['Comment']['ip'] = $this->request->clientIp(false);
        //check order

        $draftComment = $this->Comment->find('first',array(
            'conditions'=>array(
                'data_id'=>$dataId,
                'user_id'=>$uid,
                'order_id'=>$orderId
            )
        ));
        if(!empty($draftComment)){
            $this->data['Comment']['updated'] = date('Y-m-d H:i:s');
            $this->data['Comment']['body'] = htmlspecialchars($this->data['Comment']['body']);
            if($this->data['Comment']['status']=='1'){
                $this->data['Comment']['publish_time']=date('Y-m-d H:i:s');
            }
            $this->Comment->id=$draftComment['Comment']['id'];
            $comment = $this->Comment->save($this->request->data);
        }else{
            $this->data['Comment']['user_id'] = $uid;
            $this->data['Comment']['username'] = $this->Session->read('Auth.User.nickname');
            $this->data['Comment']['body'] = htmlspecialchars($this->data['Comment']['body']);
            $this->data['Comment']['created'] = date('Y-m-d H:i:s');
            $comment = $this->Comment->save($this->data);
        }
        if($comment){
            $result = array('success'=>true,'msg'=>'添加评论成功');
        }else{
            $result = array('success'=>false,'msg'=>'添加评论失败');
        }
        echo json_encode($result);
    }
    //change order comment status
    function submit_order_comment(){
        $this->autoRender=false;
        $orderId = $_REQUEST['orderId'];
        $uid = $this->Session->read('Auth.User.id');
        $Order = ClassRegistry::init('Order');
        $currentOrder = $Order->find('first', array(
            'conditions' => array(
                'id' => $orderId,
                'is_comment' => 0,
                'status' => ORDER_STATUS_RECEIVED,
                'published' => PUBLISH_YES,
            )
        ));
        if($currentOrder){
            //get order and update comment status
            $Order->id = $currentOrder['Order']['id'];
            $data = $Order->save(array('is_comment'=>1));
            if($data){
                $result = array('success'=>true,'msg'=>'评论成功');
                //update comment status 1
                //update product comment count
                $this->loadModel('Product');
                $this->Comment->updateAll(array('status'=>PUBLISH_YES),array('order_id' => $orderId));
                $comments = $this->Comment->find('all',array(
                    'conditions' => array(
                        'order_id' => $orderId
                    )
                ));
                foreach($comments as $comment){
                    $data_id = $comment['Comment']['data_id'];
                    $count = $this->Comment->find('count',array(
                        'conditions' => array(
                            'data_id' => $data_id,
                            'status' => PUBLISH_YES
                        )
                    ));
                    $this->Product->updateAll(
                        array('comment_nums' => $count),
                        array('id' => $data_id)
                    );
                }
                //add score log and set user score
                $this->_set_user_comment_score($orderId,$uid);
            }else{
                $result = array('success'=>true,'msg'=>'评论失败');
            }
        }else{
            //error
            $result=array('success'=>false,'msg'=>'该订单不能评价');
        }
        echo json_encode($result);
    }
    //get draft order rating
    function get_order_rating($uid,$orderId){
        $orderComment = ClassRegistry::init('OrderComment');
        $draftOrderRating = $orderComment->find('first',array(
            'conditions'=>array(
                'order_id'=>$orderId,
                'user_id'=>$uid
            )
        ));
        return $draftOrderRating;
    }

    //get product with draft comment
    function get_order_products($orderId,$uid){
        $Cart = ClassRegistry::init('Cart');
        $Product = ClassRegistry::init('Product');
        $order_products = $Cart->find('all', array(
            'conditions'=>array(
                'order_id' => $orderId,
                'creator'=> $uid
            ),
            'fields'=>array(
                'name',
                'order_id',
                'coverimg',
                'product_id'
            ),
        ));
        //add draft
        $productIds = Hash::extract($order_products,'{n}.Cart.product_id');
        //load product slug and created to gen link
        $products = $Product->find('all',array(
            'conditions'=>array('id'=>$productIds),
            'fields'=>array('slug','created','id')
        ));
        //load draft comment
        $draftComments = $this->Comment->find('all',array(
            'conditions'=>array(
                'user_id'=>$uid,
                'data_id'=>$productIds,
                'order_id'=>$orderId
            )
        ));
        //merge data product and comment
        $order_products = Hash::combine($order_products,'{n}.Cart.product_id','{n}.Cart');
        $draftComments = Hash::combine($draftComments,'{n}.Comment.data_id','{n}.Comment');
        $productWithComments = Hash::merge($order_products,$draftComments);
        foreach($products as $index=>$p){
            $pid = $p['Product']['id'];
            $productWithComments[$pid]['product_slug']=$p['Product']['slug'];
            $productWithComments[$pid]['product_created']=$p['Product']['created'];
        }
        return $productWithComments;
    }

    function get_order($orderId,$uid){
        $Order = ClassRegistry::init('Order');
        return $Order->find_my_order_byId($orderId, $uid);
    }

    function _set_user_comment_score($orderId, $uid) {

        $Order = ClassRegistry::init('Order');
        $commentM = ClassRegistry::init('Comment');

        $product_comments = $commentM->find('all', array(
            'conditions' => array(
                'order_id' => $orderId,
                'user_id' => $uid,
                'status' => COMMENT_SHOW_STATUS,
            )
        ));
        $product_ids = Hash::combine($product_comments, '{n}.Comment.id', '{n}.Comment.data_id');

        $current_order = $Order->findById($orderId);
        $total_price = $current_order['Order']['total_all_price'];

        list($score, $award_extra_ids) = $commentM->estimate_score_value($total_price, $product_ids);

        $Score = ClassRegistry::init('Score');
        $OrderComment = ClassRegistry::init('OrderComment');
        $order_comment = $OrderComment->find('first', array(
            'conditions' => array(
                'user_id' => $uid,
                'order_id' => $orderId,
            ),
        ));
        $commentId = $order_comment['OrderComment']['id'];

        if($Score->add_score_by_comment($uid, $score, $orderId, $commentId, $award_extra_ids)) {
            $userM = ClassRegistry::init('User');
            $userM->add_score($uid, $score);
            $this->log("add score: $uid, $score, $orderId, $commentId");
        } else {
            $this->log("failed to add core for comment order ".$orderId." by user with ". $uid);
        }

    }

}
?>