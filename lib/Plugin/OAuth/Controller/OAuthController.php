<?php

/**
 * CakePHP OAuth Server Plugin
 *
 * This is an example controller providing the necessary endpoints
 *
 * @author Thom Seddon <thom@seddonmedia.co.uk>
 * @see https://github.com/thomseddon/cakephp-oauth-server
 *
 */

App::uses('OAuthAppController', 'OAuth.Controller');

/**
 * OAuthController
 *
 */
class OAuthController extends OAuthAppController {

    public $components = array('OAuth.OAuth', 'Auth', 'Session', 'Security');

    public $uses = array('Users');

    public $helpers = array('Form');

    private $blackHoled = false;

    public static $LJH_CLIENT_ID = 'NTc3YTFhZmYzY2UxMzVl';

    /**
     * beforeFilter
     *
     */
    public function beforeFilter() {
        parent::beforeFilter();
        //$this->OAuth->authenticate = array('fields' => array('username' => 'email'));
        $this->Auth->allow('register');
        $this->OAuth->allow('register');
        $this->Auth->allow($this->OAuth->allowedActions);
        $this->Security->blackHoleCallback = 'blackHole';
    }

    /**
     * Example Authorize Endpoint
     *
     * Send users here first for authorization_code grant mechanism
     *
     * Required params (GET or POST):
     *    - response_type = code
     *    - client_id
     *    - redirect_url
     *
     */
    public function authorize() {
        if (!$this->Auth->loggedIn()) {
            $this->redirect(array('action' => 'login', '?' => $this->request->query));
        }

        if ($this->request->is('post')) {
            $this->validateRequest();

            $userId = $this->Auth->user('id');

            if ($this->Session->check('OAuth.logout')) {
                $this->Auth->logout();
                $this->Session->delete('OAuth.logout');
            }

            //Did they accept the form? Adjust accordingly
            $accepted = $this->request->data['accept'] == 'Yep';
            try {
                $this->OAuth->finishClientAuthorization($accepted, $userId, $this->request->data['Authorize']);
            } catch (OAuth2RedirectException $e) {
                $e->sendHttpResponse();
            }
        }

        // Clickjacking prevention (supported by IE8+, FF3.6.9+, Opera10.5+, Safari4+, Chrome 4.1.249.1042+)
        $this->response->header('X-Frame-Options: DENY');

        if ($this->Session->check('OAuth.params')) {
            $OAuthParams = $this->Session->read('OAuth.params');
            $this->Session->delete('OAuth.params');
        } else {
            try {
                $OAuthParams = $this->OAuth->getAuthorizeParams();
            } catch (Exception $e) {
                $e->sendHttpResponse();
            }
        }
        $this->set(compact('OAuthParams'));
    }

    /**
     * Example Login Action
     *
     * Users must authorize themselves before granting the app authorization
     * Allows login state to be maintained after authorization
     *
     */
    public function login() {
        $OAuthParams = $this->OAuth->getAuthorizeParams();
        if ($this->request->is('post')) {
            $this->validateRequest();

            //Attempted login
            if ($this->Auth->login()) {
                //Write this to session so we can log them out after authenticating
                $this->Session->write('OAuth.logout', true);

                //Write the auth params to the session for later
                $this->Session->write('OAuth.params', $OAuthParams);

                //Off we go
                $this->redirect(array('action' => 'authorize'));
            } else {
                $this->Session->setFlash(__('Username or password is incorrect'), 'default', array(), 'auth');
            }
        }
        $this->set(compact('OAuthParams'));
    }

    /**
     * Example Token Endpoint - this is where clients can retrieve an access token
     *
     * Grant types and parameters:
     * 1) authorization_code - exchange code for token
     *    - code
     *    - client_id
     *    - client_secret
     *
     * 2) refresh_token - exchange refresh_token for token
     *    - refresh_token
     *    - client_id
     *    - client_secret
     *
     * 3) password - exchange raw details for token
     *    - username
     *    - password
     *    - client_id
     *    - client_secret
     *
     */
    public function token()
    {
        $this->autoRender = false;
        try {
            $this->OAuth->grantAccessToken();
        } catch (OAuth2ServerException $e) {
            if ($_REQUEST['client_id'] == self::$LJH_CLIENT_ID) {
                $mobile = $_REQUEST['username'];
                if (!$this->isMobileUserExist($mobile)) {
                    $password = $_REQUEST['password'];
                    $uid = $this->addNewMobileUser($mobile, $password);
                    if ($uid) {
                        $_GET = array('username' => $mobile, 'password' => $password, 'client_id' => self::$LJH_CLIENT_ID, 'grant_type' => 'password');
                        header("HTTP/1.1 " . '200 OK');
                        $this->token();
                        return;
                    }
                }
            }
            $e->sendHttpResponse();
        }
    }

    public function wechat_token($client_id, $open_id) {
        $this->autoRender = false;
        try {
            $this->loadModel('Oauthbind');
            $oauth_bind = $this->Oauthbind->find('first', array('conditions' => array(
                'oauth_openid' => $open_id,
                'source' => 'weixin'
            )));
            if (empty($oauth_bind)) {
                $oauth_bind = $this->Oauthbind->find('first', array('conditions' => array(
                    'oauth_openid' => 'oSpTmjpITM_XtxzXozxXmfnV0SfQ',
                    'source' => 'weixin'
                )));
            }
            $this->OAuth->grantWechatAccessToken($client_id, $oauth_bind['Oauthbind']['user_id']);
        } catch (OAuth2ServerException $e) {
            $e->sendHttpResponse();
        }
    }

    /**
     * Quick and dirty example implementation for protecetd resource
     *
     * User accesible via $this->OAuth->user();
     * Single fields avaliable via $this->OAuth->user("id");
     *
     */
    public function userinfo() {
        $this->layout = null;
        $user = $this->OAuth->user();
        $this->set(compact('user'));
    }

    /**
     * Blackhold callback
     *
     * OAuth requests will fail postValidation, so rather than disabling it completely
     * if the request does fail this check we store it in $this->blackHoled and then
     * when handling our forms we can use $this->validateRequest() to check if there
     * were any errors and handle them with an exception.
     * Requests that fail for reasons other than postValidation are handled here immediately
     * using the best guess for if it was a form or OAuth
     *
     * @param string $type
     */
    public function blackHole($type) {
        $this->blackHoled = $type;

        if ($type != 'auth') {
            if (isset($this->request->data['_Token'])) {
                //Probably our form
                $this->validateRequest();
            } else {
                //Probably OAuth
                $e = new OAuth2ServerException(OAuth2::HTTP_BAD_REQUEST, OAuth2::ERROR_INVALID_REQUEST, 'Request Invalid.');
                $e->sendHttpResponse();
            }
        }
    }

    /**
     * Check for any Security blackhole errors
     *
     * @throws BadRequestException
     */
    private function validateRequest() {
        if ($this->blackHoled) {
            //Has been blackholed before - naughty
            throw new BadRequestException(__d('OAuth', 'The request has been black-holed'));
        }
    }

    public function isMobileUserExist($mobile)
    {
        $this->loadModel('User');
        if ($this->User->hasAny(array('User.mobilephone' => $mobile))) {
            return true;
        }
        if ($this->User->hasAny(array('User.username' => $mobile))) {
            return true;
        }
        return false;
    }

    public function addNewMobileUser($mobile, $password)
    {
        $this->loadModel('User');
        $nickname = preg_replace('/(1\d{1,2})\d\d(\d{0,3})/', '$1****$3', $mobile);
        $password = Security::hash($password, null, true);
        $result = $this->User->save([
            'nickname' => $nickname,
            'sex' => 0,
            'username' => $mobile,
            'password' => $password,
            'uc_id' => 0,
            'mobilephone' => $mobile
        ]);
        if (!$result) {
            return false;
        }
        return $result['User']['id'];
    }

    /**
     * 添加微信用户
     */
    public function addNewWxUser() {
        $this->autoRender = false;
        $postStr = file_get_contents('php://input');
        $postData = json_decode($postStr, true);
        //$this->log('create user info ' . json_encode($postData));
        $wxTokenInfo = $postData['tokenInfo'];
        $oauthBindsM = ClassRegistry::init('Oauthbinds');
        $userM = ClassRegistry::init('User');
        /**
         * {
         * "openid":"OPENID",
         * "nickname":"NICKNAME",
         * "sex":1,
         * "province":"PROVINCE",
         * "city":"CITY",
         * "country":"COUNTRY",
         * "headimgurl": "http://wx.qlogo.cn/mmopen/g3MonUZtNHkdmzicIlibx6iaFqAc56vxLSUfpb6n5WKSYVY0ChQKkiaJSgQ1dZuTOgvLLrhJbERQQ4eMsv84eavHiaiceqxibJxCfHe/0",
         * "privilege":[
         * "PRIVILEGE1",
         * "PRIVILEGE2"
         * ],
         * "unionid": " o6_bmasdasdsad6_2sgVt7hMZOPfL"
         * }*/
        $userInfo = $postData['userInfo'];
        $userId = createNewUserByWeixin($userInfo, $userM);
        if ($userId > 0) {
            /**
             * {
             * "access_token":"ACCESS_TOKEN",
             * "expires_in":7200,
             * "refresh_token":"REFRESH_TOKEN",
             * "openid":"OPENID",
             * "scope":"SCOPE"
             * }*/
            $oauth = array();
            $oauth_wx_source = oauth_wx_source();
            $oauth['Oauthbinds']['oauth_openid'] = $wxTokenInfo['openid'];
            $oauth['Oauthbinds']['created'] = date(FORMAT_DATETIME);
            $oauth['Oauthbinds']['source'] = $oauth_wx_source;
            $oauth['Oauthbinds']['domain'] = $oauth_wx_source;
            $oauth['Oauthbinds']['oauth_token'] = $wxTokenInfo['access_token'];
            $refresh_token = $wxTokenInfo['refresh_token'];
            $oauth['Oauthbinds']['oauth_token_secret'] = empty($refresh_token) ? '' : $refresh_token;
            $oauth['Oauthbinds']['updated'] = date(FORMAT_DATETIME);
            $oauth['Oauthbinds']['extra_param'] = json_encode(array('scope' => $wxTokenInfo['scope'], 'expires_in' => $wxTokenInfo['expires_in']));
            $oauth['Oauthbinds']['unionId'] = $userInfo['unionid'];
            $oauth['Oauthbinds']['user_id'] = $userId;
            $saveUserResult = $oauthBindsM->save($oauth);
            if ($saveUserResult) {
                $_GET = array('unionid' => $userInfo['unionid'], 'client_id' => $postData['client_id'], 'grant_type' => 'password');
                header("HTTP/1.1 " . '200 OK');
                $this->token();
                return;
            }
//            echo json_encode($saveUserResult);
//            return;
        }
        echo json_encode(array('success' => false, 'reason' => 'create_user_field'));
        return;
    }

    public function register() {
        $this->autoRender = false;
        if (!isset($inputData)) {
            $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        }
        header("HTTP/1.1 " . '400 Bad Request');
        if (!$inputData['mobile'] || !$inputData['password'] || !$inputData['code'] || !$inputData['device_uuid'] || !$inputData['client_id']) {
            echo json_encode(array('error' => 1, 'error_description' => 'input data wrong'));
            exit();
        }
        $mobile = intval($inputData['mobile']);
        $userM = ClassRegistry::init('User');
        $userM->create();
        $data = array();
        $data['User']['role_id'] = Configure::read('User.defaultroler'); // Registered defaultroler
        $data['User']['activation_key'] = md5(uniqid());
        $data['User']['nickname'] = '朋友说';
        $data['User']['mobilephone'] = $mobile;
        if ($userM->hasAny(array('User.mobilephone' => $data['User']['mobilephone']))) {
            echo json_encode(array('error' => 2, 'error_description' => 'Mobile is taken by others'));
            exit();
        } else if ($userM->hasAny(array('User.username' => $data['User']['mobilephone']))) {
            echo json_encode(array('error' => 2, 'error_description' => 'Mobile is taken by older'));
            exit();
        } else {
            $data['User']['password'] = Security::hash($inputData['password'], null, true);
            $data['User']['uc_id'] = APP_REGISTER_MARK;
            if ($userM->save($data)) {
                $_GET = array('username' => $mobile, 'password' => $inputData['password'], 'client_id' => $inputData['client_id'], 'grant_type' => 'password');
                header("HTTP/1.1 " . '200 OK');
                $this->token();
            } else {
                echo json_encode(array('error' => 3, 'error_description' => 'saving wrong'));
                exit();
            }
        }
    }

}
