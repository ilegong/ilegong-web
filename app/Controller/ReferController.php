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
        }

        $product_comments = $this->build_comments($uid);
        $this->set('product_comments', $product_comments);
    }

    public function client($uid) {
        $this->loadModel('User');
        $user = $this->User->findById($uid);
        if (empty($user)) {
            $this->redirect('/');
        }
        $this->set('ref_user', $user['User']);

        $product_comments = $this->build_comments($uid);
        $this->set('product_comments', $product_comments);

        $recommC = $this->Components->load('ProductRecom');
        $recommends = $recommC->recommend(0);
        $this->set('recommends', $recommends);
    }

    public function my_refer() {
        $uid = $this->currentUser['id'];
        $this->loadModel('UserRefer');
        $refers = $this->UserRefer->find('all', array('conditions' => array('to' => $uid, 'deleted' => DELETED_NO)));
        $this->set('refers', $refers);
        $uid_list = Hash::extract($refers, '{n}.User.id');
        if (!empty($uid_list)) {
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

}