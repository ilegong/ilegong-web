<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/19/15
 * Time: 12:51
 */

class ApiTuanController extends AppController{
    public $components = array('OAuth.OAuth', 'TuanBuying');


    public function beforeFilter() {
        parent::beforeFilter();
        $allow_action = array('ping');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action)  == false) {
            $this->currentUser = $this->OAuth->user();
        }
    }

    /**
     * test net connect
     */
    public function ping(){
        echo json_encode(array('success'=>true));
        return;
    }

    /**
     * api tuan add cart
     * post data
     *          {
     *              "product_id" : 购买产品ID,
     *              "num" : 购买数量,
     *              "spec_id" :  选择的规格,
     *              "tuan_buy_id" : 团购的ID,
     *              "consignment_date_id" : 排期ID,
     *              "send_date" : 排期,
     *              "ship_id" : 商品发货方式ID,
     *              "ship_mark" : 发货方式代码
     *          }
     */
    public function add_cart(){
        $postStr = file_get_contents('php://input');
        $postdata = json_decode(trim($postStr), true);
        $product_id = $postdata['product_id'];
        $num = $postdata['num'];
        $spec_id = $postdata['spec_id'];
        $type = ORDER_TYPE_TUAN;
        $uId = $this->currentUser['id'];
        $tuan_buy_id = $postdata['tuan_buy_id'];
        $consignment_date_id = $postdata['consignment_date_id'];
        $send_date = $postdata['send_date'];
        $ship_id = $postdata['ship_id'];
        $ship_mark = $postdata['ship_mark'];
        $cart_tuan_param = array(
            'tuan_buy_id' => $tuan_buy_id,
            'product_id' => $product_id
        );
        $result = $this->TuanBuying->add_cart($product_id,$num,$spec_id,$type,$uId,$cart_tuan_param,$consignment_date_id,$send_date,$ship_id,$ship_mark);
        echo json_encode($result);
        return;
    }

    /**
     * api tuan balance
     *
     * post data
     *          {"tuan_buy_id": 团购ID,"cart_id": 购物车ID, "way_id": 发货方式ID}
     */
    public function balance(){
        $postStr = file_get_contents('php://input');
        $postdata = json_decode(trim($postStr), true);
        $uid = $this->currentUser['id'];
        $tuan_buy_id = $postdata['tuan_buy_id'];
        $cart_id = $postdata['cart_id'];
        $way_id = $postdata['way_id'];
        $result = $this->TuanBuying->balance_tuan($uid,$tuan_buy_id, $cart_id, $way_id);
        echo json_encode($result);
        return;
    }

    /**
     * api tuan balacne sec kill
     *
     * post data
     *         {"tuan_id":团的ID,"cart_id":购物车,"try_id":试吃ID}
     */
    public function balance_sec_kill(){
        $postStr = file_get_contents('php://input');
        $postdata = json_decode(trim($postStr), true);
        $uid = $this->currentUser['id'];
        $tuan_id = $postdata['tuan_id'];
        $cart_id = $postdata['cart_id'];
        $try_id = $postdata['try_id'];
        $result = $this->TuanBuying->balance_sec_kill($uid,$tuan_id,$cart_id,$try_id);
        echo json_encode($result);
        return;
    }

    /**
     * api tuan make order
     *
     * post data
     *          {
     *          "cart_id": "购物车ID",
     *          "tuan_id":“团的ID”,
     *          "member_id":"如果是团购就是团购ID,如果是秒杀就是try_id",
     *          "mobile":"发货手机号码",
     *          "name":"收货人名字",
     *          "way_id":"物流方式ID",
     *          "tuan_sec":"如果是秒杀为true 否则是false或者空",
     *          "global_sec":"tuan_sec为true的时候这个字段有意义,true为全局秒杀,false为小团秒杀"，
     *          “shop_id” : "自提点ID,默认是0",
 *              "p_address" : "非自提点用户自己填写的地址"
     *          }
     */
    public function make_order(){
        $postStr = file_get_contents('php://input');
        $postdata = json_decode(trim($postStr), true);
        $uid = $this->currentUser['id'];
        $cart_id = $postdata['cart_id'];
        $tuan_id = $postdata['tuan_id'];
        $member_id = $postdata['member_id'];
        $mobile = $postdata['mobile'];
        $name = $postdata['name'];
        $way_id = $postdata['way_id'];
        $tuan_sec = $postdata['tuan_sec'];
        $global_sec = $postdata['global_sec'];
        $shop_id = $postdata['shop_id'];
        $p_address = $postdata['p_address'];
        $result = $this->TuanBuying->make_order($cart_id, $tuan_id, $member_id, $mobile, $name, $uid, $way_id, $tuan_sec, $global_sec, $shop_id, $p_address);
        echo json_encode($result);
        return;
    }

}