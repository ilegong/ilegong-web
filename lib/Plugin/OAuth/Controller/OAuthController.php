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
 *	- response_type = code
 *	- client_id
 *	- redirect_url
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
			} catch (Exception $e){
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
	public function login () {
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
 *	- code
 *	- client_id
 *	- client_secret
 *
 * 2) refresh_token - exchange refresh_token for token
 *	- refresh_token
 *	- client_id
 *	- client_secret
 *
 * 3) password - exchange raw details for token
 *	- username
 *	- password
 *	- client_id
 *	- client_secret
 *
 */
	public function token() {
		$this->autoRender = false;
		try {
			$this->OAuth->grantAccessToken();
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

    public function register(){
        $this->autoRender = false;
        if (!isset($inputData)) {
            $inputData = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST : $_GET;
        }
        header("HTTP/1.1 " . '400 Bad Request');
        if(!$inputData['mobile'] || !$inputData['password'] || !$inputData['code'] || !$inputData['device_uuid'] || !$inputData['nickname'] ){
            echo json_encode(array('error'=>1, 'error_description'=>'input data wrong'));
            exit();
        }
        $mobile = intval($inputData['mobile']);
        $this->loadModel('MobileRegisters');
        $app_register = $this->MobileRegisters->find('first', array('conditions' => array('device_uuid' => $inputData['device_uuid'])));
        if($app_register['MobileRegisters']['message_code'] == $inputData['code']){
            $userM = ClassRegistry::init('User');
            $userM->create();
            $data = array();
            $data['User']['role_id'] = Configure::read('User.defaultroler'); // Registered defaultroler
            $data['User']['activation_key'] = md5(uniqid());
            $data['User']['nickname'] = trim($inputData['nickname']);
            $data['User']['mobilephone'] = $mobile;
            if (mb_strlen($data['User']['nickname'], 'UTF-8') < 1) {
                echo json_encode(array('error'=>2, 'error_description'=>'nickname too short'));
                exit();
            }else if ($userM->hasAny(array('User.mobilephone' => $data['User']['mobilephone']))){
                echo json_encode(array('error'=>2, 'error_description'=>'Mobile is taken by others'));
                exit();
            }else if($userM->hasAny(array('User.username' => $data['User']['mobilephone']))){
                echo json_encode(array('error'=>2, 'error_description'=>'Mobile is taken by older'));
                exit();
            } else{
                $data['User']['password'] = Security::hash($inputData['password'], null, true);
                $data['User']['uc_id'] = APP_REGISTER_MARK;
                if ($userM->save($data)) {
                    //$user_id = $userM->getLastInsertID();
                    //$token = $this->OAuth->createAccessToken($inputData['client_id'], $user_id);
                    //echo json_encode($token);
                    echo json_encode(array('error'=>0, 'error_description'=>'register success'));
                } else {
                    echo json_encode(array('error'=>3, 'error_description'=>'saving wrong'));
                    exit();
                }
            }
        }else {
            echo json_encode(array('error'=>4, 'error_description'=>'code wrong'));
            exit();
        }
    }

}
