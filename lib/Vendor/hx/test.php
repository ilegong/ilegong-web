<?php
require_once 'common.func.php';

$app_name = 'xinyang';
$client_id = 'YXA6bLWQMCnCEeSl4oHH89FGRg';
$client_secret = 'YXA6xZH8c5_a80Ik66kM7wOu0FeTr2g';

$User = new HxUser($app_name, $client_id, $client_secret);
$data = array(
        // array('username'=>'china111','password'=>'administrator'),
        // array('username'=>'china222','password'=>'administrator'),
        // array('username'=>'china333','password'=>'administrator'),
        // array('username'=>'china555','password'=>'administrator'),
        array('username'=>'china888','password'=>'administrator')
// array('username'=>'china888','password'=>'administrator')
)
;
// $data = json_encode(array('username'=>'china999','password'=>'china999'));
$info = $User->regUserOnAuth($data);
// $info = $User->getUserInfo('china666');
// $info = $User->deleteUserByTime(1409020348390, 1409020348407);
// $info = $User->deleteUserBySort('desc', 2);
// $info = $User->addFriendToUser('china111', 'china666');
// $info = $User->deleteFriendOnUser('china111','china666');
// $info= $User->getFriendsOnUser('china111');

$Chat = new HxChat($app_name, $client_id, $client_secret);
// $info = $Chat->chatFiles(realpath('./lib/test.jpeg'));
// $info = $Chat->getMessagesByNew(2);
// $info = $Chat->getMessagesByTimes(1409013972030,1409013972050);
// $info = $Chat->getMessagesByPage(2);

$Message = new HxMessage($app_name, $client_id, $client_secret);
// $info = $Message->getUserStatus('china222');
// $info = $Message->sendMessage('users', array('admin1','china666'), 'hello china', 'china111');
// $info = $Message->getChatGroups();
// $info = $Message->getGroupInfo("140938657730924");
// $info = $Message->addChatGroup0('asd', 'dd', true, true, 'admin1', array('china111'));
// $info = $Message->deleteChatGroup("140939696825959");
// $info = $Message->getChatGroupUsers("140938657730924");
// $info = $Message->addUsersOnChatGroups("140938657730924", "china111");
// $info=$Message->deleteUsersOnChatGroups("140938657730924", "china111");
var_dump($info);





