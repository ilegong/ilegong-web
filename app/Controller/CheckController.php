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
                $this->MobileRegister->updateAll(array('picture_code'=> '\'' . $data['picture_code'] . '\''), array('device_uuid' => $inputData['device_uuid']));
            }else{
                $this->MobileRegister->save($data);
            }
        }
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
        $msg = '尊敬的用户，感谢您对朋友说的支持，短信验证码：'. $verifyCode .'，有效期为20分钟，请尽快验证。';

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
            $register_data = $this->MobileRegister->find('first', array('conditions'=>array('device_uuid' => $inputData['device_uuid'])));
            if(!empty($register_data) && $register_data['MobileRegister']['picture_code'] == $inputData['keyString']){
                $res = message_send($msg, $inputData['mobile']);
                $res = json_decode($res, true);
                $res['timelimit'] = date('H:i',time()+20*60);
                $this->MobileRegister->updateAll(array('message_code'=>$verifyCode, 'mobile' => $inputData['mobile']), array('device_uuid' => $inputData['device_uuid']));
            }else{
                $res = array('error'=> 1, 'msg'=>'not right');
            }
            echo json_encode($res);

        } else {
            $res = array('error' => 1);
            echo json_encode($res);
        }


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