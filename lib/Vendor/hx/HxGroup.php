<?php

require_once('HxEaseServer.php');

class HxGroup extends HxEaseServer
{

    public function __construct($app_name, $client_id, $client_secret)
    {
        parent::__construct($app_name, $client_id, $client_secret);
        $this->setAuthToken();
    }

    private function setAuthToken()
    {
        $header[] = 'Authorization: Bearer ' . $this->token;
        $this->ch->createHeader($header);
    }

    public function createGroup($data)
    {
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/chatgroups', 'POST');
    }

    public function updateGroup($data, $groupId)
    {
        $this->ch->createData($data);
        return $this->ch->execute($this->url . '/chatgroups/' . $groupId, 'PUT');
    }

    public function deleteGroup($groupId)
    {
        return $this->ch->execute($this->url . '/chatgroups/' . $groupId, 'DELETE');
    }

    public function getGroupMembers($groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users';
        return $this->ch->execute($url, 'GET');

    }

    public function addMember($username, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users/' . $username;
        return $this->ch->execute($url, 'POST');
    }

    public function addMembers($data, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users';
        $this->ch->createData($data);
        return $this->ch->execute($url, 'POST');
    }

    public function removeMember($username, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users/' . $username;
        return $this->ch->execute($url, 'DELETE');
    }

    public function removeMembers($usernameList, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/users/' . implode(',', $usernameList);
        return $this->ch->execute($url, 'DELETE');
    }


    public function getGroupInfo($groupId)
    {
        return $this->getGroupsInfo(array($groupId));
    }

    public function getGroupsInfo($groupIdList)
    {
        $url = $this->url . '/chatgroups/' . implode(',', $groupIdList);
        return $this->ch->execute($url, 'GET');
    }

    public function getGroupsByUser($username)
    {
        $url = $this->url . '/users/' . $username . '/joined_chatgroups';
        return $this->ch->execute($url, 'GET');
    }

    public function getAllGroups()
    {
        $url = $this->url . '/chatgroups';
        return $this->ch->execute($url, 'GET');
    }

    public function getGroupsByPage($limit, $cursor = null)
    {
        $url = $this->url . '/chatgroups?limit=' . $limit;
        if (!empty($cursor)) {
            $url = $url . '&cursor=' . $cursor;
        }
        return $this->ch->execute($url, 'GET');
    }

    public function changeGroupOwn($data, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId;
        $this->ch->createData($data);
        return $this->ch->execute($url, 'PUT');
    }

    public function groupBlocks($groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users';
        return $this->ch->execute($url, 'GET');
    }

    public function addBlockUser($username, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users/' . $username;
        return $this->ch->execute($url, 'POST');
    }

    public function addBlockUsers($data, $groupId)
    {
        $this->ch->createData($data);
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users';
        return $this->ch->execute($url, 'POST');
    }

    public function removeBlockUser($username, $groupId)
    {
        return $this->removeBlockUsers(array($username), $groupId);
    }

    public function removeBlockUsers($usernameList, $groupId)
    {
        $url = $this->url . '/chatgroups/' . $groupId . '/blocks/users/' . implode(',', $usernameList);
        return $this->ch->execute($url, 'DELETE');

    }
}