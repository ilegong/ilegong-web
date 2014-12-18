<?php
/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 12/15/14
 * Time: 5:24 PM
 */

class ShichiController extends AppController {

    var $uses = array('ProductTry', 'Product');

    public function beforeFilter() {
        parent::beforeFilter();
        if (empty($this->currentUser['id'])) {
            $ref = Router::url($_SERVER['REQUEST_URI']);
            if ($this->is_weixin()) {
                $this->redirect(redirect_to_wx_oauth($ref, WX_OAUTH_BASE, true));
            } else {
                $this->redirect('/users/login.html?referer='.$ref);
            }
        }
    }

    public function list_pai(){
        $this->loadModel('ProductTry');
        $tryings = $this->ProductTry->find_trying(20);
        if (!empty($tryings)) {
            $tryProducts = $this->Product->find_products_by_ids(Hash::extract($tryings, '{n}.ProductTry.product_id'), array(), false);
            if (!empty($tryProducts)) {
                foreach($tryings as &$trying) {
                    $prod = $tryProducts[$trying['ProductTry']['product_id']];
                    if (!empty($prod)) {
                        $trying['Product'] = $prod;
                    } else {
                        unset($trying);
                    }
                }
            }
        }

        $uid = $this->currentUser['id'];
        $this->loadModel('Shichituan');
        $shichituan = $this->Shichituan->find_in_period($uid, get_shichituan_period());
        $is_shichi =  (!empty($shichituan) || $shichituan);

        $this->set('shichi_mem', $is_shichi);
        $this->set('shichiTuan', $shichituan);
        $this->set('tryings', $tryings);
        $this->pageTitle = '试吃秒杀';
    }
}