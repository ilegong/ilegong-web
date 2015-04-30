<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 4/26/15
 * Time: 12:27 PM
 */

class ReferController extends AppController {


    var $uses = array('UserRefer', 'Comment');

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
        }

        $userRefers = $this->UserRefer->find('all', array(
            'conditions' => array('from' => $this->currentUser['id'], 'deleted' => DELETED_NO),
        ));

        $this->set('total_refers', count($userRefers));

        $product_comments = $this->build_comments($uid);
        $this->set('product_comments', $product_comments);

        $this->pageTitle = $this->currentUser['nickname']. '向您推荐了【朋友说】';
    }

    public function accept() {
        $this->autoRender = false;
        //todo:检查是否可以接受邀请，并且必须用 POST
        $success = false;
        $uid = $_POST['uid'];
        if ($uid) {

            $referred = $this->find_be_referred_for_me($this->currentUser['id']);
            if (empty($referred)) {
                $data = array();
                $data['UserRefer']['from'] = $uid;
                $data['UserRefer']['to'] = $this->currentUser['id'];
                $this->UserRefer->save($data);
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

        $phone_binded = !empty($this->currentUser['mobilephone']);
        $mOrder = ClassRegistry::init('Order');
        $received_cnt = $mOrder->count_received_order($this->currentUser['id']);
        $reg_done = $phone_binded && $received_cnt > 0;
        $this->set('reg_done', $reg_done);
        $this->set('received_cnt', $received_cnt);
        $this->set('phone_bind', $phone_binded);

        if (!$phone_binded || $received_cnt <= 0) {
            $referred = $this->find_be_referred_for_me($uid);
            if (!empty($referred)) {
                $this->set('referred', $referred);
                $old_ref_user = $this->User->findById($referred['UserRefer']['from']);
                $this->set('$old_refer_name', $old_ref_user['User']['nickname']);
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
        $this->UserRefer->updateAll(array('got_notify' => 1), array('id' => $refer_id, 'bind_done' => 1, 'first_order_done' => 1));
    }

    public function my_refer() {
        $uid = $this->currentUser['id'];
        $this->loadModel('UserRefer');
        $refers = $this->UserRefer->find('all', array('conditions' => array('from' => $uid, 'deleted' => DELETED_NO)));
        $this->set('refers', $refers);
        $uid_list = Hash::extract($refers, '{n}.UserRefer.to');
        if (!empty($uid_list)) {
            $this->loadModel('User');
            $users = $this->User->find('all', array(
                'conditions' => array('id' => $uid_list),
            ));
            $m_users = Hash::combine($users, '{n}.User.id', '{n}.User');
            $this->set('m_users', $m_users);
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

        foreach ($product_comments as &$item) {
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

            $item['prod'] = $prods[$item['data_id']];

            unset($item['pictures']);
        }
        return $product_comments;
    }

    /**
     * @param $uid
     * @return mixed
     */
    private function find_be_referred_for_me($uid) {
        $referred = $this->UserRefer->find('first', array('conditions' => array(
            'to' => $uid,
            'deleted' => DELETED_NO,
        )));
        return $referred;
    }

}