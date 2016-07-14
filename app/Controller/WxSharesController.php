<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 15/1/26
 * Time: 下午7:03
 */

class WxSharesController extends AppController{
    var $name = 'WxShares';
    public function log_share(){
        $this->autoRender = false;
        if ($this->is_weixin()) {
            if(!$_POST['share_type']){
                $post_params = json_decode(file_get_contents('php://input'),true);
                $share_string = urldecode($post_params['trstr']);
                $share_type = $post_params['share_type'];
            }else{
                $share_string = urldecode($_POST['trstr']);
                $share_type = $_POST['share_type'];
            }

            if ($share_type != 'timeline' && $share_type != 'appMsg') {
                $this->log("WxShare: type wrong");
                exit();
            }
            $type = $share_type == 'timeline' ? 1 : 0;
            if ($share_string == '0') {
                $this->log("WxShare: not login");
                exit();
            }
            //save share index data log
            if($share_string=='index'){
                $data = array('sharer' => 0, 'created' => 0, 'data_type' => 'index', 'data_id' => 0, 'share_type' => $type);
                $this->WxShare->save($data);
                exit();
            }
            if($share_string=='happy_618'){
                $data = array('sharer' => 0, 'created' => 0, 'data_type' => 'happy_618', 'data_id' => 0, 'share_type' => $type);
                $this->WxShare->save($data);
                exit();
            }
            if($share_string=='baby_vote'){
                $data = array('sharer' => 0, 'created' => 0, 'data_type' => 'baby_vote', 'data_id' => 0, 'share_type' => $type);
                $this->WxShare->save($data);
                exit();
            }
            if($share_string=='we_share'){
                $data = array('sharer' => 0, 'created' => 0, 'data_type' => 'we_share', 'data_id' => 0, 'share_type' => $type);
                $this->WxShare->save($data);
                exit();
            }

            $decode_string = authcode($share_string, 'DECODE', 'SHARE_TID');
            $str = explode('-', $decode_string);
            $data_str = explode('_', $str[3]);
            $uid = intval($str[0]);
            $created = intval($str[1]);

            if ($data_str[0] == 'pid') {
                $data_type = 'product';
            } elseif ($data_str[0] == 'tid') {
                $data_type = 'tuan';
            } elseif ($data_str[0] == 'rid') {
                $data_type = 'refer';
            } elseif ($data_str[0] == 'tryid') {
                $data_type = 'try';
            } elseif ($data_str[0] == 'indextry') {
                $data_type = 'indexTry';
            } elseif ($data_str[0] == 'indexproduct') {
                $data_type = 'indexProduct';
            }elseif($data_str[0]=='voteEventId'){
                $data_type = 'voteEvent';
            }elseif($data_str[0]=='wxid'){
                $data_type = 'we_share';
            } else {
                $data_type = substr(trim($data_str[0]), 0, 12);
            }
            if ($str[2] != 'rebate') {
                $this->log("WxShare: PRODUCT_KEY WRONG");
                exit();
            }
            $data = array('sharer' => $uid,'share_id' => $this->currentUser['id'] ? $this->currentUser['id'] : 0, 'created' => $created, 'data_type' => $data_type, 'data_id' => $data_str[1], 'share_type' => $type);
            $this->WxShare->save($data);
        }
    }

    public function log_share_xy_game(){
        $this->autoRender =false;
        if($this->is_weixin()&& $this->currentUser['id']){
            $share_type = $_POST['share_type'];
            if($share_type != 'timeline' && $share_type != 'appMsg'){
                $this->log("WxShare: type wrong");
                exit();
            }
            $type = $share_type == 'timeline' ? 1:0;
            $uid = $this->currentUser['id'];
            $created = date('Y-m-d H:i:s');
            $from = $_POST['from'];
            $data = array('sharer' => $uid, 'created' => $created, 'data_type' =>'xy_game' , 'data_id' => $from,'share_type'=>$type);
            $this->WxShare->save($data);
        }
    }

}