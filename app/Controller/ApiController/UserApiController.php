<?php

class UserApiController extends AppController
{
    public $components = array('OAuth.OAuth', 'ChatUtil', 'WeshareBuy', 'ShareUtil', 'UserFans');
    public $uses = array('User', 'UserFriend', 'UserLevel', 'UserRelation');

    public function beforeFilter()
    {
        parent::beforeFilter();
        $allow_action = array('test', 'check_mobile_available');
        $this->OAuth->allow($allow_action);
        if (array_search($this->request->params['action'], $allow_action) == false) {
            $this->currentUser = $this->OAuth->user();
        }
        $this->autoRender = false;
    }

    public function reg_hx_user()
    {
        $user_id = $this->currentUser['id'];
        $result = $this->ChatUtil->reg_hx_user($user_id);
        echo json_encode($result);
        return;
    }

    public function profile()
    {
        $user_id = $this->currentUser['id'];
        $datainfo = $this->get_user_info($user_id);
        $userInfo = $datainfo['User'];
        $user_summery = $this->WeshareBuy->get_user_share_summary($user_id);
        $rebate_money = $this->ShareUtil->get_rebate_money($user_id);
        $user_summery['rebate_money'] = $rebate_money;
        $user_level = $this->ShareUtil->get_user_level($user_id);
        $userInfo['level'] = $user_level;
        echo json_encode(array('my_profile' => array('User' => $userInfo), 'user_summery' => $user_summery));
        return;
    }

    public function my_profile()
    {
        $user_id = $this->currentUser['id'];
        $datainfo = $this->get_user_info($user_id);
        $userInfo = $datainfo['User'];
        echo json_encode($userInfo);
        return;
    }

    public function change_password()
    {
        $user_id = $this->currentUser['id'];
        $new_password = $_REQUEST['password'];
        $hash_password = Security::hash($new_password, null, true);
        $this->User->update(['password' => $hash_password], ['id' => $user_id]);
        echo json_encode(['success' => true]);
        return;
    }

    public function update_avatar()
    {
        $user_id = $this->currentUser['id'];
        $avatar_url = $_REQUEST['url'];
        $this->User->update(['image' => "'" . $avatar_url . "'", 'avatar' => "'" . $avatar_url . "'"], ['id' => $user_id]);
        echo json_encode(['success' => true]);
        return;
    }

    public function update_desc(){

    }

    /**
     * 绑定支付方式
     */
    public function bind_payment(){

    }

    /**
     * 绑定手机号码
     */
    public function bind_mobile(){
        $mobile = $_REQUEST['mobile'];
        $uid = $this->currentUser['id'];
        $this->User->update(['mobilephone' => $mobile],['id' => $uid]);
        echo json_encode(['success' => true]);
        return;
    }

    private function get_user_info($user_id)
    {
        $datainfo = $this->User->find('first', array('recursive' => -1,
            'conditions' => array('id' => $user_id),
            'fields' => array('nickname', 'image', 'sex', 'mobilephone', 'username', 'id', 'hx_password', 'description')));
        return $datainfo;
    }


    public function test()
    {
        echo 'hello world';
        return;
    }

    /**
     * @param $page
     * @param $limit
     * 接口要替换
     */
    public function load_fans($page, $limit)
    {
        $user_id = $this->currentUser['id'];
        $fans_data = $this->UserRelation->find('all', array(
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
        $users = $this->ChatUtil->get_users_info($fans_id);
        echo json_encode($users);
        return;
    }

    /**
     * @param $type 0=>粉丝 1=>关注
     * @param $uid
     * @param $page
     */
    public function get_u_list_data($type, $uid, $page)
    {
        $query = $_REQUEST['query'];
        if ($type == 0) {
            $data = $this->UserFans->get_fans($uid, $page, $query);
        } else {
            $data = $this->UserFans->get_subs($uid, $page, $query);
        }
        echo json_encode($data);
        return;
    }

    /**
     * 获取评论的数据
     */
    public function get_comment_list()
    {
        $uid = $this->currentUser['id'];
        $user_share_data = $this->WeshareBuy->prepare_user_share_info($uid);
        $my_create_share_ids = $user_share_data['my_create_share_ids'];
        $shareCommentData = $this->WeshareBuy->load_sharer_comment_data($my_create_share_ids, $uid);
        $userCommentData = $this->WeshareBuy->load_user_share_comments($uid);
        echo json_encode(array('sharer_comment_data' => $shareCommentData, 'user_comment_data' => $userCommentData));
        return;
    }

    public function delete_friend($friend_id)
    {
        $user_id = $this->currentUser['id'];
        if ($this->UserFriend->updateAll(array('deleted' => DELETED_YES), array('user_id' => $user_id, 'friend_id' => $friend_id))) {
            if ($this->ChatUtil->delete_friend($user_id, $friend_id)) {
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
                $result = $this->ChatUtil->add_friend($user_id, $friend_id);
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
        $data = $this->ChatUtil->get_users_info($friend_ids);
        echo json_encode($data);
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