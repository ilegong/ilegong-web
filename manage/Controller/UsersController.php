<?php
/**
 * Users Controller
 *
 * PHP version 5
 *
 * @category Controller
 * @package  SaeCMS
 * @version  1.0
 * @author   Arlon <saecms@google.com>

 * @link     http://www.saecms.net
 */
class UsersController extends AppController {

    var $name = 'Users';
   
    var  $components = array(
		'Email', 
    	'Kcaptcha',
    	//'Securimage',
	);
    
    function admin_add() {
        if (!empty($this->data)) {
            $this->data['User']['activation_key'] = md5(uniqid());
            $this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
        }
        parent::admin_add();
        
    	if (empty($this->data)) {
           $this->data['User']['role_id'] = 2; // default Role: Registered
        }
        
        $roles = $this->User->Role->find('list');
        $this->set('roles',$roles);
    }

    function admin_edit($id = null) {
    	if (!$id){
       		$id = $this->_getParamVars('id');
		}		
        if (!$id && empty($this->data)) {
            $this->redirect(array('action' => 'index'));
        }
        if (!empty($this->data)) {
        	$this->autoRender = false;
        	if(empty($this->data['User']['password'])){
        		unset($this->data['User']['password']);
        	}
        	else{
        		$this->data['User']['password'] = Security::hash($this->data['User']['password'], null, true);
        	}
            if ($this->User->save($this->data)) {
                $successinfo = array('success'=>__('Edit success',true));
            }
	        else{
	        	$successinfo = array('error'=>__('Edit error',true));
	        }
	        echo json_encode($successinfo);
        	exit;
        }
        if (empty($this->data)) {
            $this->data = $this->User->read(null, $id);
        }
        $roles = $this->User->Role->find('list',array('conditions'=>array('id >'=>100)));
        $this->set(array('roles'=>$roles,'id'=>$id));
        $this->__viewFileName = 'admin_add';
    }

    function admin_delete($id = null) {
        if (!$id) {
            $this->redirect(array('action' => 'index'));
        }
        if ($this->User->delete($id)) {
            $this->redirect(array('action' => 'index'));
        }
    }

    function admin_to_add_score(){
    }

    function admin_do_add_score(){
        $this->autoRender=false;
        $tuan_buy_ids = $_REQUEST['tuan_buy_ids'];
        $uid = $_REQUEST['user_id'];
        $score = $_REQUEST['score'];
        $score_reason = $_REQUEST['score_reason'];
        $this->loadModel('Score');
        $add_score_log = $this->Score->save(array(
            'user_id' => $uid,
            'reason' => ADD_SCORE_TUAN_LEADER,
            'data' => json_encode(array('tuan_buy_ids'=>$tuan_buy_ids)),
            'desc' => $score_reason,
            'score' => $score,
            'order_id' => empty($orderId) ? 0 : $orderId,
        ));
        if($add_score_log){
            $user = $this->User->find('first',array(
                'conditions' => array(
                    'User.id'=>$uid
                ),
                'recursive' => 1
            ));
            $old_score = $user['User']['score'];
            $this->send_score_msg($uid,$score_reason,'增加',$score);
            if($this->User->updateAll(array('User.score'=>'User.score+'.$score),array('User.score'=>$old_score,'User.id' => $uid))){
                echo json_encode(array('success'=>true,'msg'=>'更新成功'));
            }else{
                echo json_encode(array('success'=>false,'msg'=>'更新失败'));
            }
        }else{
            echo json_encode(array('success'=>false,'msg'=>'更新失败'));
        }
    }

    function send_score_msg($user_id, $intro_desc, $action, $score_change, $click_desc = null){
        try {
            $userM = ClassRegistry::init('User');
            $user = $userM->find('first',array(
                'conditions' => array(
                    'User.id'=>$user_id
                ),
                'recursive' => 1
            ));
            $totalScore = $user['User']['score'];
            $totalScore += $score_change;
            $click_url = !empty($click_url) ? $click_url : 'http://' . WX_HOST . '/scores/more_score.html';
            $oauthBindModel = ClassRegistry::init('Oauthbind');
            $user_weixin = $oauthBindModel->find('first',array(
                'conditions'=>array(
                    'user_id'=>$user_id
                )
            ));
            if ($user_weixin != false) {
                $open_id = $user_weixin['Oauthbind']['oauth_openid'];
                $post_data = array(
                    "touser" => $open_id,
                    "template_id" => 'SpyG5LYbgkJrlgKNM7bWzCaqXdoUOOkO_G14Dxk0P5Y',
                    "url" => $click_url,
                    "topcolor" => "#FF0000",
                    "data" => array(
                        "first" => array("value" => $intro_desc),
                        "FieldName" => array("value" => "有 效 期 "),
                        "Account" => array("value" => '2015年12月31日前'),
                        "change" => array("value" => $action),
                        "CreditChange" => array("value" => abs($score_change)),
                        "CreditTotal" => array("value" => $totalScore),
                        "Remark" => array("value" => $click_desc, "color" => "#FF8800")
                    )
                );
                return send_weixin_message($post_data);
            }
            return false;
        }catch(Exception $e) {
            $this->log('error to send_score_change_message:(uid='.$user_id.', action='.$action.', intro_desc='.$intro_desc.'):'.$e);
            return false;
        }
    }
}
?>