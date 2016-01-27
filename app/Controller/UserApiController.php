<?php

class UserApiController extends AppController
{

    public $components = array('OAuth.OAuth', 'Session', 'HxChat');

    public $uses = array('User', 'UserFriend', 'UserLevel');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $allow_action = array('test', 'ping', 'check_mobile_available');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function reg_hx_user()
    {
        $user_id = $this->currentUser['id'];
        $result = $this->HxChat->reg_hx_user($user_id);
        echo json_encode($result);
        return;
    }

    public function profile()
    {
        $user_id = $this->currentUser['id'];
        $datainfo = $this->get_user_info($user_id);
        echo json_encode(array('my_profile' => array('User' => $datainfo['User'])));
        return;
    }

    private function get_user_info($user_id)
    {
        $userM = ClassRegistry::init('User');
        $datainfo = $userM->find('first', array('recursive' => -1,
            'conditions' => array('id' => $user_id),
            'fields' => array('nickname', 'email', 'image', 'sex', 'companies', 'bio', 'mobilephone', 'email', 'username', 'id', 'hx_password')));
        return $datainfo;
    }

    public function test()
    {

    }

    public function load_fans($page, $limit)
    {
        $user_id = $this->currentUser['id'];
        $userRelationM = ClassRegistry::init('UserRelation');
        $fans_data = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $user_id
            ),
            'limit' => $limit,
            'offset' => ($page - 1) * $limit,
            'order' => array('id DESC')
        ));
        $fans_id = Hash::extract($fans_data, '{n}.UserRelation.follow_id');
        if (empty($fans_id)) {
            echo json_encode(array());
            return;
        }
        $users = $this->HxChat->get_users_info($fans_id);
        echo json_encode($users);
        return;
    }

    public function delete_friend($friend_id)
    {
        $user_id = $this->currentUser['id'];
        if ($this->UserFriend->updateAll(array('deleted' => DELETED_YES), array('user_id' => $user_id, 'friend_id' => $friend_id))) {
            if ($this->HxChat->delete_friend($user_id, $friend_id)) {
                echo json_encode(array('statusCode' => 1, 'statusMsg' => '删除成功'));
                return;
            }
        }

        echo json_encode(array('statusCode' => -1, 'statusMsg' => '删除失败'));
        return;
    }

    public function add_friend($friend_id)
    {
        $user_id = $this->currentUser['id'];
        if (!$this->UserFriend->hasAny(array('user_id' => $user_id, 'friend_id' => $friend_id, 'deleted' => DELETED_NO))) {
            $date_now = date('Y-m-d H:i:s');
            $save_data = array();
            //互相添加好友
            $save_data[] = array('user_id' => $user_id, 'friend_id' => $friend_id, 'created' => $date_now, 'updated' => $date_now);
            $save_data[] = array('user_id' => $friend_id, 'friend_id' => $user_id, 'created' => $date_now, 'updated' => $date_now);
            $friend_data = $this->UserFriend->saveAll($save_data);
            if ($friend_data) {
                $result = $this->HxChat->add_friend($user_id, $friend_id);
                if (!$result) {
                    $this->log('add hx user friend error');
                }
                $friend_info = $this->get_user_info($friend_id);
                echo json_encode(array('statusCode' => 1, 'statusMsg' => '添加成功', 'data' => $friend_data, 'friend_info' => $friend_info['User']));
                return;
            } else {
                echo json_encode(array('statusCode' => -1, 'statusMsg' => '添加失败'));
                return;
            }
        }
        echo json_encode(array('statusCode' => 2, 'statusMsg' => '已经是好友'));
        return;
    }

    /**
     * 获取好友列表
     */
    public function get_friends()
    {
        $user_id = $this->currentUser['id'];
        $friends_data = $this->UserFriend->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'deleted' => DELETED_NO,
                'status' => 0
            ),
            'limit' => 500
        ));
        $friend_ids = Hash::extract($friends_data, '{n}.UserFriend.friend_id');
        $data = $this->HxChat->get_users_info($friend_ids);
        echo json_encode(array('friends' => $data['users'], 'levels' => $data['levels']));
        return;
    }

    public function check_mobile_available()
    {
        $this->autoRender = false;
        $mobile = $_REQUEST['mobile'];
        if ($this->User->hasAny(array('User.mobilephone' => $mobile))) {
            echo json_encode(array('statusCode' => -1, 'statusMsg' => '手机号已经被注册'));
            return;
        }
        echo json_encode(array('statusCode' => 1));
        return;
    }

    public function bind_mobile_api()
    {
        $this->autoRender = false;
        $uid = $this->currentUser['id'];
        $mobile_num = $_REQUEST['mobile'];
        $user_info = array();
        $user_info['User']['mobilephone'] = $mobile_num;
        $user_info['User']['id'] = $uid;
        $user_info['User']['uc_id'] = 5;
        if ($this->User->hasAny(array('User.mobilephone' => $mobile_num))) {
            echo json_encode(array('statusCode' => -1, 'statusMsg' => '你的手机号已注册过，无法绑定，请用手机号登录'));
            return;
        }
        //todo valid username is mobile
        if ($this->User->save($user_info)) {
            echo json_encode(array('statusCode' => 1, 'statusMsg' => '你的账号和手机号绑定成功'));
            return;
        };
        echo json_encode(array('statusCode' => -1, 'statusMsg' => '绑定失败，亲联系客服'));
        return;
    }
}