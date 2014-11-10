<?php

/**
 * Created by IntelliJ IDEA.
 * User: liuzhr
 * Date: 10/12/14
 * Time: 3:57 PM
 */
class HuiyanController extends AppController
{

    var $uses = array('User', 'Order', 'Cart');
    public function beforeFilter()
    {
        parent::beforeFilter();
        if (empty($this->currentUser['id'])) {
            $this->redirect(redirect_to_wx_oauth(Router::url($_SERVER['REQUEST_URI']), WX_OAUTH_BASE, true));
        }
        $this->pageTitle = __('帮助我们的腾讯同事慧艳，一起帮她带爸爸平安回来');
        $this->set('hideNav', true);
        $this->set('noFlash', true);
        $this->set('isMobile', $this->RequestHandler->isMobile());
    }

    public function index()
    {
        $helpHis = $this->Order->find('all', array('conditions' => array('brand_id' => 71, 'creator' => $this->currentUser['id'])));
        $total = $this->Order->find('first', array(
            'conditions' => array('brand_id' => 71, 'status' => ORDER_STATUS_PAID),
            'fields' => array('total_all_price')
        ));
    }

    public function log_num() {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $rtn = array();
        $rtn['success'] = false;
        if (!empty($_REQUEST['num'])) {

            $num = intval(intval($_REQUEST['num']));

            $data = array();
            $data['total_price'] = $num;
            $data['total_all_price'] = $num;
            $data['ship_fee'] = 0;
            $data['brand_id'] = 632;
            $data['creator'] = $uid;
            $data['remark'] = '帮慧艳';
            $this->Order->create();
            if ($this->Order->save($data)) {
                $order_id = $this->Order->getLastInsertID();
                $cartItem = array(
                    'product_id' => 220,
                    'name' => '帮助慧艳',
                    'coverimg' => '',
                    'num' => 1,
                    'price' => $num,
                );
                $cartItem['creator'] = $uid;
                $cartItem['order_id'] = $order_id;
                $this->Cart->create();
                $this->Cart->save($cartItem);

                $rtn['orderId'] = $order_id;
                $rtn['success'] = true;
            }

        }
        echo json_encode($rtn);
    }

}