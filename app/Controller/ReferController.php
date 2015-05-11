<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 4/26/15
 * Time: 12:27 PM
 */

class ReferController extends AppController {


    var $uses = array('Refer', 'Comment');

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

        $userRefers = $this->Refer->find('all', array(
            'conditions' => array('from' => $uid, 'deleted' => DELETED_NO),
        ));

        $this->set('total_refers', count($userRefers));

        $product_comments = $this->build_comments($uid);
        $this->set('product_comments', $product_comments);

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

        $recommC = $this->Components->load('ProductRecom');
        $recommends = $recommC->recommend(0);
        $this->set('recommends', $recommends);
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

        $this->pageTitle = '我推荐的用户';
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

}