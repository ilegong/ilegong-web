<?php

require_once('HxEaseServer.php');

class HxMessage extends HxEaseServer {

    /**
     * 查看用户在线状态
     * @param string $username
     */
    public function getUserStatus($username){
        $url = $this->url . '/users/' . $username . '/status';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    /**
     * 发送消息
     * @param string $type "users or chatgroups"
     * @param array $target "array('u1','u2') 用户或群组"
     * @param string $msg "消息内容"
     * @param string $from "示这个消息是谁发出来的, 可以没有这个属性, 那么就会显示是admin, 如果有的话, 则会显示是这个用户发出的"
     * @param array $ext
     */
    public function sendMessage($type, $target, $msg, $from, $ext = array()){
        $data = array('target_type'=>$type,'target'=>$target,'msg'=>array('type'=>'txt','msg'=>$msg),'from'=>$from, 'ext' => $ext);
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/messages', 'POST');
    }

    /**
     * 获取app中所有的群组
     */
    public function getChatGroups(){
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($this->url . '/chatgroups', 'GET');
    }

    /**
     * 获取app中一个群组的详情
     * @param string $group_id
     */
    public function getGroupInfo($group_id){
        $url = $this->url . '/chatgroups/' . $group_id;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    /**
     * 创建一个群组
     * @param string $groupname 群组名称, 此属性为必须的
     * @param string $desc 群组描述, 此属性为必须的
     * @param boolean $public 是否是公开群, 此属性为必须的
     * @param boolean $approval 加入公开群是否需要批准, 没有这个属性的话默认是true, 此属性为可选的
     * @param string $owner 群组的管理员, 此属性为必须的
     * @param array $members 群组成员,此属性为可选的
     */
    public function addChatGroup0($groupname, $desc, $public, $approval, $owner, $members){
        $header[] = 'Authorization: Bearer ' . $this->token;
        $data = array('groupname'=>$groupname,'desc'=>$desc,'public'=>$public,'approval'=>$approval,'owner'=>$owner,'members'=>$members);
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/chatgroups', 'POST');
    }

    /**
     * 功能同上 参数不同
     * @param unknown $data
     */
    public function addChatGroup($data){
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/chatgroups', 'POST');
    }

    /**
     * 删除群组
     * @param string $groupid '群组id'
     */
    public function deleteChatGroup($groupid){
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($this->url . '/chatgroups/' . $groupid, 'DELETE');
    }

    /**
     * 获取群组中的所有成员
     * @param string $groupid
     */
    public function getChatGroupUsers($groupid){
        $url = $this->url . '/chatgroups/' . $groupid . '/users';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    /**
     * 在群组中添加一个人
     * @param string $groupid '群组id'
     * @param string $username '用户名'
     */
    public function addUsersOnChatGroups($groupid, $username){
        $url = $this->url . '/chatgroups/' . $groupid . '/users/' . $username;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'POST');
    }

    /**
     * 在群组中减少一个人
     * @param string $groupid '群组id'
     * @param string $username '用户名'
     */
    public function deleteUsersOnChatGroups($groupid, $username){
        $url = $this->url . '/chatgroups/' . $groupid . '/users/' . $username;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'DELETE');
    }
}