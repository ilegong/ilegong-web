<?php

require_once('HxEaseServer.php');

class HxUser extends HxEaseServer
{

    /**
     * 开放注册模式
     * @param array $data
     */
    public function regUserOnOpen($data)
    {
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/users', 'POST');
    }

    /**
     * 授权注册模式
     * @param array $data
     */
    public function regUserOnAuth($data)
    {
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/users', 'POST');
    }

    /**
     * 获取用户信息
     * @param string username
     */
    public function getUserInfo($username)
    {
        $url = $this->url . '/users/' . $username;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    public function getUserStatus($username)
    {
        $url = $this->url . '/users/' . $username . '/status';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }

    public function addUserBlock($friend, $username)
    {
        $data = array("usernames" => array($friend));
        return $this->addUsersBlock($data, $username);
    }

    public function addUsersBlock($data, $username)
    {
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/users/' . $username . '/blocks/users', 'POST');
    }

    public function getBlockUsers($username)
    {
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($this->url . '/users/' . $username . '/blocks/users', 'GET');
    }

    public function removeBlockUser($friend, $username)
    {
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        $url = $this->url . '/users/' . $username . '/blocks/users/' . $friend;
        return $this->ch->execute($url, 'DELETE');
    }

    /**
     * 重置用户密码
     */
    public function resetUserPassword($username, $password)
    {
        $url = $this->url . '/' . $username . '/password';
        $header[] = 'Authorization: Bearer ' . $this->token;
        $data = array('newpassword' => $password);
        $this->ch->createHeader($header);
        $this->ch->createData($data);
        return $this->ch->execute($url, 'PUT');
    }

    /**
     * 删除用户
     */
    public function deleteUser($username)
    {
        $url = $this->url . '/users/' . $username;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'DELETE');
    }

    /**
     * 批量删除用户 没有制定具体删除哪些用户 可以在返回值中查看到哪些用户被删除掉了 可以通过增加查询条件来做到精确的删除
     * @package int $limit
     */
    public function deleteUserBySort($sort, $limit = 100)
    {
        $ql = 'order+by+created+' . $sort;
        $url = $this->url . '/users?ql=' . $ql . '&limit=' . $limit;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Type: application/json';
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'DELETE');
    }

    /**
     *
     * @param int(13) $start
     * @param int(13) $end
     * @param int $limit 50
     * @return array
     */
    public function deleteUserByTime($start, $end, $limit = 100)
    {
        $ql = 'created>' . $start . ' and created<' . $end;
        $url = $this->url . '/users?ql=' . url_enc($ql) . '&limit=' . $limit;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Type: application/json';
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'DELETE');
    }

    /**
     * 给一个用户添加一个好友
     */
    public function addFriendToUser($username, $friendname)
    {
        $url = $this->url . '/users/' . $username . '/contacts/users/' . $friendname;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $header[] = 'Content-Type: application/json';
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'POST');
    }

    /**
     * 删除一个用户的好友
     */
    public function deleteFriendOnUser($username, $friendname)
    {
        $url = $this->url . '/users/' . $username . '/contacts/users/' . $friendname;
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'DELETE');
    }

    /**
     * 查看一个用户的所有好友
     */
    public function getFriendsOnUser($username)
    {
        $url = $this->url . '/users/' . $username . '/contacts/users/';
        $auth = $this->getTokenOnFile();
        $header[] = 'Authorization: Bearer ' . $auth;
        $this->ch->createHeader($header);
        return $this->ch->execute($url, 'GET');
    }
}