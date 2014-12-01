<?php

class CommentsController extends AppController {

    var $name = 'Comments';

    var $components = array(
        'Email',
    );
    var $uses = array('Comment', 'User','Shichituan');
    function add() {
    	$this->autoRender = false;
    	$this->layout = 'ajax';
        if (!isset($this->data['Comment']['data_id'])) {
            $this->Session->setFlash(__('Invalid Params', true));
            $this->redirect('/');
        }
        if($this->Session->check('Auth.User.id') && !empty($this->data)) {

            if ($this->nick_should_edited($this->Session->read('Auth.User.nickname'))) {
                echo json_encode(array('error'=>'edit_nick_name'));
                return;
            }
            $this->data['Comment']['user_id'] = $this->Session->read('Auth.User.id');
            $this->data['Comment']['username'] = $this->Session->read('Auth.User.nickname');
            $this->data['Comment']['body'] = htmlspecialchars($this->data['Comment']['body']);
            $this->data['Comment']['ip'] = $this->request->clientIp(false);
            $this->data['Comment']['status'] = 1;
            $this->data['Comment']['created'] = date('Y-m-d H:i:s');
            //comment_type: 评论，补充完善，扩展阅读等等

            if ($this->Comment->save($this->data)) {
            	//$comment_id = $this->Comment->getLastInsertID();
            	$type_model = $this->data['Comment']['type'];
            	$this->loadModel($type_model);
            	$this->{$type_model}->updateAll(
            			array('comment_nums' => 'comment_nums+1'),
            			array('id'=> $this->data['Comment']['data_id'])
            	);
            	
                if ($this->data['status']) {
                	$successinfo = array('success'=> '您的评论已成功提交');
                    //$this->Session->setFlash(__('Your comment has been added successfully.', true));
                }else {
                	$successinfo = array('success'=> '您的评论已成功提交');
                    //$this->Session->setFlash(__('Your comment will appear after moderation.', true));
                }
                $successinfo['Comment'] = $this->data['Comment'];
            }
            else{
            	echo json_encode($this->{$this->modelClass}->validationErrors);
            	return;
            }
            echo json_encode($successinfo);
       }
       else{
	       	echo json_encode(array('error'=>'please_login'));
            return;
       }
       return;
    }
    
    function getlist($model_name,$id)
    {
    	$page = intval($_GET['page'])?intval($_GET['page']):1;
    	$pagesize = intval($_GET['pagesize'])?intval($_GET['pagesize']):50;
    	$this->autoRender = false;
    	$model_name = Inflector::classify($model_name);
    	$comments = $this->Comment->find('all',array(
    		 'conditions' => array('Comment.type' => $model_name,'data_id'=>$id,'status'=>1),
    		 'order' => array('Comment.created DESC'), //定义顺序的字符串或者数组
		    'limit' => $pagesize, //整型
		    'page' => $page, //整型    	
    	));
        $this->loadModel('Shichituan');
        $result = array();
        foreach ($comments as $comt){
            $item = $comt['Comment'];
            if(empty($item['username']) && !empty($item['user_id']) ){
                $data = $this->User->find('first', array('conditions' => array('id' => $item['user_id'])));
                if(isset($data['User']['id']) ){
                    $item['username'] = $data['User']['nickname'];
                }  
            }
            if( empty($item['username']) ){
                $item['username'] = '微信用户';
            }
            unset($item['ip']);
            unset($item['lft']);
            unset($item['rght']);

            if ($item['pictures']) {
                $images = array();
                $pics = mbsplit("\\|", $item['pictures']);
                foreach($pics as $pic) {
                    if($pic && strpos($pic, "http://") === 0) {
                        $images[] = $pic;
                    }
                }
                if (count($pics) > 0) {
                    $item['images'] = $images;
                }
            }

            unset($item['pictures']);

            $shichi_status=$this->Shichituan->findByUser_id($item['user_id'],array('Shichituan.status'));
            $item['shichi_status']=$shichi_status['Shichituan']['status'];
            array_push($result,array('Comment' => $item));
        }

    	echo json_encode($result);


    } 

    function __spam_protection($continue, $type, $node) {
        if (!empty($this->data) &&
            $type['Type']['comment_spam_protection'] &&
            $continue === true) {
            $this->Akismet->setCommentAuthor($this->data['Comment']['username']);
            $this->Akismet->setCommentContent($this->data['Comment']['body']);
            //$this->Akismet->setPermalink(Router::url($node['Node']['url'], true));
            if ($this->Akismet->isCommentSpam()) {
                $continue = false;
                $this->Session->setFlash(__('Sorry, the comment appears to be spam.', true));
            }
        }
        return $continue;
    }

    function __captcha($continue, $type, $node) {
        if (!empty($this->data) &&
            $type['Type']['comment_captcha'] &&
            $continue === true &&
            !$this->Recaptcha->valid($this->params['form'])) {
            $continue = false;
            $this->Session->setFlash(__('Invalid captcha entry', true));
        }        
        return $continue;
    }
	/**
	 * 用户删除自己发布的评论
	 * @param integer $id 数据编号
	 */
    function delete($id) {
        $success = 0;
        $userId = $this->Session->read('Auth.User.id');
        if ($userId) {            
            $comment = $this->Comment->find('first', array(
                'conditions' => array(
                    'Comment.id' => $id,
                    'Comment.user_id' => $userId,
                ),
            ));

            if (isset($comment['Comment']['id']) &&
                $this->Comment->delete($id)) {
                $success = 1;
            }
        }
        $this->set('success',$success);
    }

}
?>