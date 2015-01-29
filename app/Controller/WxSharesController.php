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
        $this->autoRender =false;
        if($this->is_weixin()&& $this->currentUser['id']){
            $share_string = urldecode($_POST['trstr']);
            $share_type = $_POST['share_type'];
            if($share_type != 'timeline' && $share_type != 'appMsg'){
                $this->log("WxShare: type wrong");
                exit();
            }
            $type = $share_type == 'timeline' ? 1:0;
            if($share_string == '0'){
                $this->log("WxShare: not login");
                exit();
            }
            $decode_string = authcode($share_string, 'DECODE', 'SHARE_TID');
            $str = explode('-',$decode_string);
            $data_str = explode('_',$str[3]);
            if($str[2] != 'rebate'){
                $this->log("WxShare: PRODUCT_KEY WRONG");
                exit();
            }
            if($data_str[0] == 'pid'){
                $data_type = 'product';
            }else{
                $this->log("WxShare: data type error");
                exit();
            }
            $uid = intval($str[0]);
            $created = intval($str[1]);
            $data = array('sharer' => $uid, 'created' => $created, 'data_type' =>$data_type , 'data_id' => $data_str[1],'share_type'=>$type);
            $this->WxShare->save($data);
        }
    }
}