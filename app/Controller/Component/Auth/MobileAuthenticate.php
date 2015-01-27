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
        //checkMobileCode
        if(!$checkMobileCode){
            return false;
        }
        $mobilephone = $request->data['mobile'];
        $result = $this->_findUser(array('mobilephone' => $mobilephone));
        return $result;
    }

}
