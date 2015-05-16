<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 4/26/15
 * Time: 12:27 PM
 */

class ReferController extends AppController {


    var $uses = array('Refer', 'Comment','Order','OrderComment','ReferAward','ExchangeReferAward');

    public function beforeFilter() {
        parent::beforeFilter();

        if (empty($this->currentUser['id']) || ($this->is_weixin() && name_empty_or_weixin($this->currentUser['nickname']))) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            $this->redirect('/users/login.html?force_login=1&auto_weixin='.$this->is_weixin().'&referer=' . urlencode($ref));
        }

        $this->set('hideNav', true);
    }

    public function index($uid = 0) {

        if (!$uid) {
            $uid = $this->currentUser['id'];
            $this->redirect('/refer/index/'.$this->currentUser['id'].'.html');
        } else if ($uid != $this->currentUser['id']) {
            $this->redirect('/refer/client/'.$uid.'/'.$this->currentUser['id'].'.html');
        }

        $cond = array('from' => $uid, 'deleted' => DELETED_NO);

        $userRefers = $this->Refer->find('count', array(
            'conditions' => $cond,
        ));

        $cond['bind_done'] = 1;
        $cond['first_order_done'] = 1;
        $userSuccessRefers = $this->Refer->find('count',array(
            'conditions' => $cond
        ));

        $this->set('total_refers', $userRefers);

        $this->set('success_refers',$userSuccessRefers);

//        $product_comments = $this->build_comments($uid);
//        $this->set('product_comments', $product_comments);

        //$this->init_award_info($uid);
        $this->set_user_recommend_condition($uid);
        $this->set_wx_share_data();
        $this->pageTitle = $this->currentUser['nickname']. '向您推荐了【朋友说】, 朋友间分享健康美食的平台';
    }

    public function accept() {
        $this->autoRender = false;
        //todo:检查是否可以接受邀请，并且必须用 POST
        $success = false;
        $uid = $_POST['uid'];
        if ($uid) {
            $cuid = $this->currentUser['id'];
            $referred = $this->find_be_referred_for_me($cuid);
            if (empty($referred)) {
                $this->loadModel('Order');
                $bought_cnt = $this->Order->count_received_order($cuid);
                if ($bought_cnt == 0) {
                    $data = array();
                    $data['Refer']['from'] = $uid;
                    $data['Refer']['to'] = $cuid;
                    $data['Refer']['bind_done'] = !empty($this->currentUser['mobilephone']);
                    $this->Refer->save($data);
                }

                $success = true;
            } else {
                $success = true;
            }
        }

        echo json_encode(array('success' => $success));
    }

    public function client($uid) {
        $this->loadModel('User');
        $user = $this->User->findById($uid);
        if (empty($user)) {
            $this->redirect('/');
        }
        $this->set('ref_user', $user['User']);
        $this->pageTitle = $user['User']['nickname'].'向您推荐了【朋友说】, 朋友间分享健康美食的平台';

        $phone_bind = !empty($this->currentUser['mobilephone']);
        $mOrder = ClassRegistry::init('Order');
        $curr_uid = $this->currentUser['id'];
        $received_cnt = $mOrder->count_received_order($curr_uid);
        $reg_done = $received_cnt > 0 && !$phone_bind;
        $this->set('reg_done', $reg_done);
        $this->set('received_cnt', $received_cnt);
        $this->set('phone_bind', $phone_bind);

        $this->log("refer client ". $curr_uid .' from '. $uid .', phone_bind='.$phone_bind.', received_cnt='.$received_cnt);

        if (!$phone_bind || $received_cnt <= 0) {
            $referred = $this->find_be_referred_for_me($curr_uid);
            if (!empty($referred)) {
                $this->set('referred', $referred);
                if ($referred['Refer']['from'] != $uid) {
                    $old_ref_user = $this->User->findById($referred['Refer']['from']);
                    $this->set('old_refer_name', $old_ref_user['User']['nickname']);
                }
            }
        }

        $product_comments = $this->build_comments($uid);
        $this->set('product_comments', $product_comments);
        $this->set_wx_share_data();
    }

    public function add_got_notify() {
        $refer_id = $_GET['refer_id'];
        $this->Refer->updateAll(array('got_notify' => 1), array('id' => $refer_id, 'bind_done' => 1, 'first_order_done' => 1));
    }

    public function my_refer() {
        $uid = $this->currentUser['id'];
        $this->loadModel('Refer');
        $refers = $this->Refer->find('all', array('conditions' => array('from' => $uid, 'deleted' => DELETED_NO)));
        $this->set('refers', $refers);
        $uid_list = Hash::extract($refers, '{n}.Refer.to');
        if (!empty($uid_list)) {
            $this->loadModel('User');
            $users = $this->User->find('all', array(
                'conditions' => array('id' => $uid_list),
            ));
            $m_users = Hash::combine($users, '{n}.User.id', '{n}.User');
            $this->set('m_users', $m_users);
        }
        $this->set_wx_share_data();
        $this->pageTitle = '我推荐的用户';
    }

    public function exchange_award($awardId) {
        $this->autoRender = false;
        $result = array();
        $award = $this->ReferAward->getAwardById($awardId);
        if (empty($award)) {
            $result['success'] = false;
            $result['reason'] = '奖品不存在';
            echo json_encode($result);
            return;
        }
        $uid = $this->currentUser['id'];
        $awardLimitCount = $award['limit_num'];
        if ($awardLimitCount > 0) {
            $allExchangeCounts = $this->ExchangeReferAward->find('count', array('conditions' => array('award_id' => $awardId, 'deleted' => DELETED_NO)));
            if ($allExchangeCounts >= $awardLimitCount) {
                $result['success'] = false;
                $result['reason'] = '奖品已经兑换完';
                echo json_encode($result);
                return;
            }
        }
        $needCount = $award['exchange_condition'];
        if (!$this->can_exchange($uid, $needCount)) {
            $result['success'] = false;
            $result['reason'] = '兑换机会不够,继续推荐人';
            echo json_encode($result);
            return;
        }
        $exchangeData = array('ExchangeReferAward' => array('uid' => $uid, 'use_count' => $needCount, 'exchange_time' => date('Y-m-d H:i:s'),'award_id' => $awardId));
        if ($this->ExchangeReferAward->save($exchangeData)) {
            $result['success'] = true;
            $result['reason'] = '兑换成功';
            echo json_encode($result);
            return;
        } else {
            $result['success'] = false;
            $result['reason'] = '兑换失败,请联系客服';
            echo json_encode($result);
            return;
        }
    }

    /**
     * @param $uid
     * @return array
     */
    private function build_comments($uid) {
        $product_comments = $this->Comment->find('all', array(
            'conditions' => array('user_id' => $uid, 'rating >' => 3, 'type' => 'Product'),
            'order' => 'created desc',
        ));

        if (!empty($product_comments)) {
            $product_comments = Hash::extract($product_comments, '{n}.Comment');
            $pids = Hash::extract($product_comments, '{n}.data_id');
        }

        if (!empty($pids)) {
            $this->loadModel('Product');
            $prods = $this->Product->find_products_by_ids($pids);
        }

        $product_comments_n = array();
        foreach ($product_comments as &$item) {

            $prod = $prods[$item['data_id']];

            $item['buy_time'] = friendlyDateFromStr($item['buy_time'], 'ymd');
            if ($item['pictures']) {
                $images = array();
                $pics = mbsplit("\\|", $item['pictures']);
                foreach ($pics as $pic) {
                    if ($pic && (strpos($pic, "http://") === 0 || strpos($pic, "/") === 0)) {
                        $images[] = $pic;
                    }
                }
                if (count($pics) > 0) {
                    $item['images'] = $images;
                }
            }

            $item['prod'] = $prod;
            if ($prod['published'] == PUBLISH_YES) {
                $product_comments_n[] = $item;
            }

            unset($item['pictures']);
        }
        return $product_comments_n;
    }

    /**
     * @param $uid
     * @return mixed
     */
    private function find_be_referred_for_me($uid) {
        $referred = $this->Refer->find('first', array('conditions' => array(
            'to' => $uid,
            'deleted' => DELETED_NO,
        )));
        return $referred;
    }

    private function set_user_recommend_condition($uid){
        //order count >=3
        //comments >=3
        $orderStatus = array(ORDER_STATUS_PAID,ORDER_STATUS_CONFIRMED,ORDER_STATUS_COMMENT,ORDER_STATUS_DONE,ORDER_STATUS_RECEIVED,ORDER_STATUS_SHIPPED);
        $order_count = $this->Order->find('count',array('conditions' => array('creator' => $uid,'status'=> $orderStatus)));
        $comment_count = $this->OrderComment->find('count',array('conditions'=>array('user_id' => $uid,'status'=>PUBLISH_YES)));
        if($order_count>=1&&$comment_count>=1){
            $this->set('can_recommend',true);
        }else{
            $this->set('order_count',$order_count);
            $this->set('comment_count',$comment_count);
        }
    }

    private function init_award_info($uid) {
        $allAwards = $this->ReferAward->getAllAward();
        $allUseCount = $this->ExchangeReferAward->query('SELECT SUM(use_count) FROM cake_exchange_refer_awards WHERE uid='.$uid.' and deleted='.DELETED_NO);
        $allUseCount = $allUseCount[0][0]['SUM(use_count)'];
        if($allUseCount==null){
            $allUseCount = 0;
        }
        $this->set('all_use_count',$allUseCount);
        $this->set('all_awards',$allAwards);
    }

    private function can_exchange($uid,$needCount){
        $allUseCount = $this->ExchangeReferAward->query('SELECT SUM(use_count) FROM cake_exchange_refer_awards WHERE uid='.$uid.' and deleted='.DELETED_NO);
        $allUseCount = $allUseCount[0][0]['SUM(use_count)'];
        if($allUseCount==null){
            $allUseCount = 0;
        }
        $cond = array('from' => $uid, 'deleted' => DELETED_NO,'bind_done' => 1,'first_order_done' => 1);
        $userReferSuccessCount = $this->Refer->find('count',array(
            'conditions' => $cond
        ));
        return ($userReferSuccessCount-$allUseCount) >= $needCount;
    }

    private function set_wx_share_data(){
        if(parent::is_weixin()){
            $title = $this->currentUser['nickname'].'向您推荐了【朋友说】，接受邀请立即获得10元积分优惠';
            $this->set('to_timeline_title',$title);
            $this->set('to_friend_title',$title);
            $this->set('share_desc', '原产地直供、新鲜现摘，找到最初的味道!');
            $this->set('share_imag_url','http://51daifan.sinaapp.com/img/refer/logo.jpg');
        }
    }

}