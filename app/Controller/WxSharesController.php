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
            }elseif ($data_str[0] == 'tid'){
                $data_type = 'tuan';
            }elseif($data_str[0] == 'rid'){
                $data_type = 'refer';
            } else{
                $data_type= substr(trim($data_str[0]), 0, 12);
            }
            $uid = intval($str[0]);
            $created = intval($str[1]);
            $data = array('sharer' => $uid, 'created' => $created, 'data_type' =>$data_type , 'data_id' => $data_str[1],'share_type'=>$type);
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