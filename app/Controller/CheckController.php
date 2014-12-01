<?php
/**
 * Created by PhpStorm.
 * User: ldy
 * Date: 14/11/28
 * Time: 下午8:28
 */
class CheckController extends AppController{
    var $name = 'Check';
    var $components  = array('Kcaptcha');
    function captcha(){
        Configure::write('debug', '0');
        $this->autoRender = false;
        $this->Kcaptcha->render();
    }
    public function message_code(){
        $this->autoRender = false;

        if ($this->request->is('post')) {
            session_start();//开启session;
            if (isset($_SESSION['captcha']) && $_SESSION['captcha'] == $_POST['keyString']) {
                $mobilephone = $_POST['phoneNumbers'];
                $verifyCode = '';
                $str = '1234567890';
                //定义用于验证的数字和字母;
                $l = strlen($str); //得到字串的长度;
                //循环随机抽取四位前面定义的字母和数字;
                for ($i = 1; $i <= 6; $i++) {
                    $num = rand(0, $l - 1);
                    //每次随机抽取一位数字;从第一个字到该字串最大长度,
                    $verifyCode .= $str[$num];
                    //将通过数字得来的字符连起来一共是四位;
                }
//                $_SESSION['messageCode'] = $verifyCode;
                $this->Session->write('messageCode', json_encode(array('code' => $verifyCode, 'time' => time())));

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://sms-api.luosimao.com/v1/send.json");

                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_HEADER, FALSE);

                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, 'api:key-fdb14217a00065ca1a47b8fcb597de0d');

                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mobilephone, 'message' => '验证码:' . $verifyCode . '【朋友说】'));

                $res = curl_exec($ch);
                curl_close($ch);
                //$res  = curl_error( $ch );
                //{"error":0,"msg":"ok"}
                echo $res;
            } else {
                $res = array('check_error' => 1);
                echo json_encode($res);
            }

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