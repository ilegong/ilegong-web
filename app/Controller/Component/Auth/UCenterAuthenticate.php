<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');

class UCenterAuthenticate extends BaseAuthenticate {

    public function authenticate(CakeRequest $request, CakeResponse $response) {
        $userModel = $this->settings['userModel'];
        list($plugin, $model) = pluginSplit($userModel);
        $fields = $this->settings['fields'];

        if (empty($request->data[$model])) {
            return false;
        }
        if (empty($request->data[$model][$fields['username']]) || empty($request->data[$model][$fields['password']])) {
            return false;
        }
        App::import('Vendor', '', array('file' => 'uc_client' . DS . 'client.php'));
        App::uses('Charset', 'Lib');
        $username =  $request->data[$model][$fields['username']];
        $username = Charset::utf8_gbk($username);
        list($uid, $username, $password, $email) = $user = uc_user_login($username, $request->data[$model][$fields['password']]);
        if ($uid > 0) {
            $_model = ClassRegistry::init($model);
            $result = $_model->find('first', array(
                        'conditions' => array($model . '.id' => $uid, $model . '.username' => $username),
                        'recursive' => -1
                    ));
        }
        else{
        	return false;
        }
        $lastLoginIp = $request->clientIp();
        if (empty($result)) {
        	$_model->create();
        	$result = array();
            $result[$model] = array(
            		'id'=>$uid,
            		'username'=> $username,
            		'nickname'=> $username,
            		'email' => $email,
            		'password' => Security::hash($password, null, true),
            		'activation_key' => md5(uniqid()),
            		'status'=> 1,
            		'last_login'=> date('Y-m-d H:i:s'),
            );
            $_model->save($result[$model]);
        }
        else{
        	$result['User']['last_login']= date('Y-m-d H:i:s');
        	$_model->save($result);
        }
        $synlogin = uc_user_synlogin($uid);
        $result[$model]['session_flash'] = $synlogin;
        return $result[$model];
    }

}
