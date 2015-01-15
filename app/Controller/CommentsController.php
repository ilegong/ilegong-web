<?php

class CommentsController extends AppController {

    var $name = 'Comments';

    var $components = array(
        'Email',
    );
    var $uses = array('Comment', 'User', 'Shichituan');
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
    	$pagesize = intval($_GET['pagesize'])?intval($_GET['pagesize']):50;
    	$this->autoRender = false;
    	$model_name = Inflector::classify($model_name);
    	$comments = $this->Comment->find('all',array(
    		 'conditions' => array('Comment.type' => $model_name,'data_id'=>$id,'status'=>1,'rating'=>array('1','5','3')),
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
                        $orders = $this->Order->find_all_my_order_byId($orderIds, $uid);
                        foreach($orders as $o) {
                            $status = $o['Order']['status'];
                            if ($status == ORDER_STATUS_CANCEL
                                || $status == ORDER_STATUS_WAITING_PAY
                                || $status == ORDER_STATUS_RETURN_MONEY
                                ) {
                                $can_comment = false;
                                $this->log("cannot_comment: status=".$status.", order-id=".json_encode($o));
                                break;
                            }
                        }
                    } else {
                        $can_comment = false;
                    }
                }

                if (!$can_comment)  {
                    return array('error' => 'cannot_comment');
                }

                $shichi_status = $this->Shichituan->findByUser_id($uid, array('Shichituan.status'));
                if ($shichi_status['Shichituan']['status'] == 1) {
                    $this->data['Comment']['is_shichi_tuan_comment'] = 1;
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
}
?>