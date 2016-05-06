<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/11/28
 * Time: 下午8:28
 */
class CheckController extends AppController{
    var $components  = array('Kcaptcha');
    public function check_mobile($mobile=null){
        if(strlen($mobile) == 11 && preg_match("/[0-9]{11}/", $mobile)){
            return true;
        }
        return false;
    }

    public function captcha(array $inputData = null){
        Configure::write('debug', '0');
        $this->autoRender = false;
        $this->Kcaptcha->render();
        if (!isset($inputData)) {
            $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        }
        if(isset($inputData['type']) && $inputData['type'] == 'app'&& isset($inputData['device_uuid'])){
            $this->loadModel('MobileRegister');
            $data = array();
            $data['device_uuid'] = $inputData['device_uuid'];
            $data['picture_code'] = $this->Kcaptcha->keyString;
            if($this->MobileRegister->hasAny(array('device_uuid' => $inputData['device_uuid']))){
                $this->MobileRegister->updateAll(array('picture_code'=> '\'' . $data['picture_code'] . '\'', 'validated' => 0), array('device_uuid' => $inputData['device_uuid']));
            }else{
                $this->MobileRegister->save($data);
            }
        }
    }
    public function verify(){
        $this->autoRender = false;
        if (!isset($inputData)) {
            $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        }
        if($inputData['device_uuid'] && $inputData['keyString'] && $inputData['type'] == 'app'){
            $this->loadModel('MobileRegister');
            $code = $this->MobileRegister->find('first', array(
                'conditions' => array('device_uuid' => $inputData['device_uuid']),
                'fields' => array('picture_code')

            ));
        }else if($inputData['type'] == 'pc'&& $inputData['keyString']){
            $code = $_SESSION['captcha'];
        }
        if(!empty($inputData['keyString']) && $code['MobileRegister']['picture_code']== $inputData['keyString']){
            echo json_encode(array('success' => true));
        }else if(!empty($inputData['keyString']) &&$code == $inputData['keyString']){
            echo json_encode(array('success' => true));
        } else{
            echo json_encode(array('success' => false));
        }
    }

    private function gen_msg_verify_code() {
        $verifyCode = '';
        $str = '1234567890';
        //定义用于验证的数字和字母;
        $l = strlen($str); //得到字串的长度;
        //循环随机抽取四位前面定义的字母和数字;
        for ($i = 1; $i <= 6; $i++) {
            $num = rand(0, $l - 1);
            //每次随机抽取一位数字;从第一个字到该字串最大长度,
            $verifyCode .= $str[$num];
        }

        return $verifyCode;
    }

    public function get_message_code() {
        $this->autoRender = false;
        $mobile = $_REQUEST['mobile'];
        $verifyCode = $this->gen_msg_verify_code();
        $msg = '短信验证码：' . $verifyCode . '，有效期为20分钟，感谢您对朋友说的支持。';
        $res = message_send($msg, $mobile);
        $res = json_decode($res, true);
        $res['timelimit'] = date('H:i', time() + 20 * 60);
        $res['verify_code'] = $verifyCode;
        $res['error'] = $res['error'] == 1 ? null : true;
        $this->Session->write('messageCode', json_encode(array('code' => $verifyCode, 'time' => time())));
        $this->Session->write('current_register_phone', $mobile);
        echo json_encode($res);
    }

    public function get_mobile_code(){
        $this->autoRender = false;
        if (empty($this->currentUser['id'])) {
            echo json_encode(['error' => true, 'msg' => 'not login']);
            return;
        }
        if(empty($_REQUEST['mobile'])){
            echo json_encode(['error' => true, 'msg' => 'param error']);
            return;
        }
        $verifyCode = $this->gen_msg_verify_code();
        $msg = '短信验证码：' . $verifyCode . '，有效期为30分钟，感谢您对朋友说的支持。';
        $res = message_send($msg, $_REQUEST['mobile']);
        $res = json_decode($res, true);
        $res['timelimit'] = date('H:i', time() + 30 * 60);
        $this->Session->write('messageCode', json_encode(array('code' => $verifyCode, 'time' => time())));
        $this->Session->write('current_register_phone', $_REQUEST['mobile']);
        echo json_encode($res);
        exit();
    }

    public function message_code(array $inputData = null){
        $this->autoRender = false;
        if (!isset($inputData)) {
            $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        }

        if(!$this->check_mobile($inputData['mobile'])){
            echo json_encode(array('error' => 2));
            return;
        }
        $verifyCode = $this->gen_msg_verify_code();
        $msg = '短信验证码：' . $verifyCode . '，有效期为20分钟，感谢您对朋友说的支持。';
        if (!isset($inputData['type'])&&isset($_SESSION['captcha']) && $_SESSION['captcha'] == $inputData['keyString']) {
            unset($_SESSION['captcha']);
            $res = message_send($msg, $inputData['mobile']);
            $res = json_decode($res, true);
            $res['timelimit'] = date('H:i',time()+20*60);
            $this->Session->write('messageCode', json_encode(array('code' => $verifyCode, 'time' => time())));
            $this->Session->write('current_register_phone', $inputData['mobile']);
            echo json_encode($res);

        }else if(isset($inputData['type']) && $inputData['type'] == 'app'&&isset($inputData['device_uuid'])){
            //api接口操作
            $this->loadModel('MobileRegister');
            //log register log
            $register_data = $this->MobileRegister->find('first', array('conditions'=>array('device_uuid' => $inputData['device_uuid'])));
            if(!empty($register_data) && (empty($inputData['keyString']) || $register_data['MobileRegister']['picture_code'] == $inputData['keyString']) && $register_data['MobileRegister']['validated'] != 1){
                $back_call = message_send($msg, $inputData['mobile']);
                $back_call = json_decode($back_call, true);
                if($back_call['error'] == 0){
                    $res = array('success'=> true, 'timelimit' => date('H:i',time()+20*60));
                }else{
                    $res = array('success'=> false);
                }
                $this->MobileRegister->updateAll(array('message_code'=>$verifyCode, 'mobile' => $inputData['mobile'], 'validated' => 1), array('device_uuid' => $inputData['device_uuid']));
            }else{
                $res = array('success'=> false, 'msg'=>'not right');
            }
            echo json_encode($res);

        }else if(!isset($inputData['type'])&&$inputData['force']){
            $res = message_send($msg, $inputData['mobile']);
            $res = json_decode($res, true);
            $res['timelimit'] = date('H:i',time()+20*60);
            $this->Session->write('messageCode', json_encode(array('code' => $verifyCode, 'time' => time())));
            $this->Session->write('current_register_phone', $inputData['mobile']);
            echo json_encode($res);
        } else {
            $res = array('error' => 1);
            echo json_encode($res);
        }


    }
    public function un_code(){
        $this->autoRender = false;
        if (!isset($inputData)) {
            $inputData = $_POST;
        }
        if(!$this->check_mobile($inputData['mobile'])){
            echo json_encode(array('error' => 2));
            return;
        }
        $verifyCode = '';
        $str = '1234567890';
        //定义用于验证的数字和字母;
        $l = strlen($str); //得到字串的长度;
        //循环随机抽取四位前面定义的字母和数字;
        for ($i = 1; $i <= 6; $i++) {
            $num = rand(0, $l - 1);
            //每次随机抽取一位数字;从第一个字到该字串最大长度,
            $verifyCode .= $str[$num];
        }
        $msg = '短信验证码：'. $verifyCode .'，有效期为20分钟，感谢您对朋友说的支持。';
        $res = message_send($msg, $inputData['mobile']);
        $res = json_decode($res, true);
        $res['timelimit'] = date('H:i',time()+20*60);
        $this->Session->write('messageCode', json_encode(array('code' => $verifyCode, 'time' => time())));
        $this->Session->write('current_register_phone', $inputData['mobile']);
        echo json_encode($res);
    }


    function _checkCaptcha($model){
        if ($this->Session->check('captcha')) {
            $s_captcha = $this->Session->read('captcha');

            if (!empty($this->data[$model]['captcha']) && $this->data[$model]['captcha'] == $s_captcha) {
                return true;
            }
        }
        return false;

    }
}