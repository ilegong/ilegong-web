<?php
/**
 * 使用手机号登录,没有密码
 * User: shichaopeng
 * Date: 1/23/15
 * Time: 00:14
 */
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class MobileAuthenticate extends BaseAuthenticate {

    public function authenticate(CakeRequest $request, CakeResponse $response) {
        $checkMobileCode = $request->data['checkMobileCode'];
        $mobilephone = $request->data['mobile'];
        if ($this->verify_msg_code($mobilephone, $checkMobileCode)) {
            $result = $this->_findUser(array('mobilephone' => $mobilephone));
            if (empty($result)) {
                createNewUserByMobile($mobilephone);
                $result = $this->_findUser(array('mobilephone' => $mobilephone));
            }
            return $result;
        }
    }


    protected function verify_msg_code($mobile, $input_code, $valid_min = 20) {
        $msgCode = CakeSession::read('messageCode');
        $recorded_mobile = CakeSession::read('current_register_phone');
        $codeLog = json_decode($msgCode, true);
        $valid = $mobile == $recorded_mobile
            && $codeLog && is_array($codeLog) && $codeLog['code'] == $input_code && (time() - $codeLog['time'] < $valid_min * 60);
        if (!$valid) {
            $ver_data = CakeSession::read('msg_ver');
            if ($ver_data) {
                $ver_data = json_decode($ver_data, true);
                $code_time_list = $ver_data[$mobile];
                if (is_array($code_time_list)) {
                    foreach ($code_time_list as $code => $valid_till) {
                        if ($code == $input_code && (time() - $valid_till) < $valid_min * 60) {
                            $valid = true;
                            break;
                        }
                    }
                }
            }
        }
        return $valid;
    }

}
