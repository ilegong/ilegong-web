<?php

class ChatController extends AppController {

    public function chat_room(){
        $this->layout = null;
    }

    public function index() {
        $this->autoRender = false;
        $channel = new SaeChannel();
//聊天记录保存在mysql之中
        $mysql = new SaeMysql();
//uid的生成和用户列表的保存直接抄钝刀的老王大大的代码
        $uid = $_COOKIE['uid'];
        if (empty($uid)) {
            $uid = mt_rand();
            setcookie('uid', $uid, time() + 3000, '/', WX_HOST, FALSE, TRUE);
        }
//用户列表保存在kvdb之中
        $kv = new SaeKV();
        $kv->init();
        $user_list = $kv->get('ChatingUserList');

        if (empty($user_list)) {
            $user_list = array();
        }

        $act = $_REQUEST["action"];

//创建channel
        if ($act == 'create_channel') {
            $username = $_REQUEST["username"];
            $user_list[$uid] = $username;

            // channel 名称
            $channel_name = 'chatbox.' . $uid;
            // 过期时间，默认为3600秒
            $duration = 10;
            //创建一个channel，返回一个地址
            $channel_url = $channel->createChannel($channel_name, $duration);

            $kv->set('ChatingUserList', $user_list);

            exit($channel_url);
        } //发送消息
        else if ($act == 'sendmsg') {
            $msg = $_REQUEST["message"];
            $username = $_REQUEST["username"];
            $sql = "INSERT  INTO `cake_msg_list` (msg, uname, action, send_time) VALUES ('$msg', '$username', 'msg', NOW());";
            $mysql->runSql($sql);

            $msg = date('Y-m-d H:m:s') . ' <span style="color: white; background-color: #5bc0de">' . $username . '</span> 说: ' . $msg;


            // 往名为$channel_name的channel里发送一条消息
            foreach ($user_list as $uid => $uname) {
                $channel_name = 'chatbox.' . $uid;
                $send = $channel->sendMessage($channel_name, $msg);
                if ($send !== TRUE) {
                    unset($user_list[$uid]);
                }
            }

            //向小乐聊天机器人请求消息
            //比较蛋疼的是小乐机器人不识别utf-8必须先转为gbk

            $url = 'http://xiao.douqq.com/api.php?msg=' . iconv("utf-8", "gbk", $_REQUEST["message"]) . '&type=txt';
            $answer = file_get_contents($url);
            $sql = "INSERT  INTO `cake_msg_list` (msg, uname, action, send_time) VALUES ('$answer', '小乐', 'msg', NOW());";
            $mysql->runSql($sql);

            $msg = date('Y-m-d H:m:s') . ' <span style="color: white; background-color: #5bc0de">小乐</span> 说: ' . $answer;

            // 往名为$channel_name的channel里发送一条消息
            foreach ($user_list as $uid => $uname) {
                $channel_name = 'chatbox.' . $uid;
                $send = $channel->sendMessage($channel_name, $msg);
                if ($send !== TRUE) {
                    unset($user_list[$uid]);
                }
            }

            $mysql->closeDb();
        } //私聊
        else if ($act == 'sendto') {
            $msg = $_REQUEST["message"];
            $username = $_REQUEST["username"];
            $sendto = $_REQUEST["to"];

            //私聊则只发给指定用户
            foreach ($user_list as $uid => $uname) {
                if ($uname == $sendto) {
                    $msg = date('Y-m-d H:m:s') . ' <span style="color: white; background-color: #563d7c"> ' . $username . ' 悄悄对你说</span>: ' . $_REQUEST["message"];
                    $channel_name = 'chatbox.' . $uid;
                    $send = $channel->sendMessage($channel_name, $msg);
                    if ($send !== TRUE) {
                        unset($user_list[$uid]);
                    }
                } else if ($uname == $username) {
                    $msg = date('Y-m-d H:m:s') . ' <span style="color: white; background-color: #563d7c">你悄悄对 ' . $sendto . ' 说</span>: ' . $_REQUEST["message"];
                    $channel_name = 'chatbox.' . $uid;
                    $send = $channel->sendMessage($channel_name, $msg);
                    if ($send !== TRUE) {
                        unset($user_list[$uid]);
                    }
                }
            }

        } //获取当前用户列表
        else if ($act == 'getuserlist') {
            $tmp_user_list = array();
            $i = 0;
            foreach ($user_list as $uid => $uname) {
                $tmp_user_list[$i] = $uname;
                $i++;
            }
            $ret = json_encode($tmp_user_list);
            exit($ret);
        } //获取最后40条聊天记录
        else if ($act == 'getmsglist') {
            $sql = "SELECT * FROM `cake_msg_list` WHERE id > (SELECT MAX(id) FROM `cake_msg_list`) - 40 ORDER BY id;";
            $data = $mysql->getData($sql);
            if ($data == null) {
                $mysql->closeDb();
                exit('0');
            }
            $i = 0;
            foreach ($data as $line) {
                //$uname_sql = str_replace('"', '\"', $line['uname']);
                $msg_list[$i][0] = $line['send_time'];
                $msg_list[$i][1] = $line['uname'];
                $msg_list[$i][2] = $line['action'];
                $msg_list[$i][3] = $line['msg'];
                $i++;
            }

            $mysql->closeDb();
            $ret = urldecode(json_encode($msg_list));

            exit($ret);
        } else if ($act == 'connected') {
            $username = $_REQUEST["username"];
            $msg = date('Y-m-d H:m:s') . ' <span style="color: white; background-color: #5cb85c">' . $username . ' 进入聊天室</span>';

            $sql = "INSERT  INTO `cake_msg_list` (msg, uname, action, send_time) VALUES ('', '$username', 'open', NOW());";
            $mysql->runSql($sql);
            $mysql->closeDb();
            //向列表之中的所有用户发送消息
            foreach ($user_list as $uid => $uname) {
                $channel_name = 'chatbox.' . $uid;
                $send = $channel->sendMessage($channel_name, $msg);
                if ($send !== TRUE) {
                    unset($user_list[$uid]);
                }
            }
            $kv->set('ChatingUserList', $user_list);
        } else if ($act == 'disconnected') {
            unset($user_list[$uid]);
            $username = $_REQUEST["username"];
            $msg = date('Y-m-d H:m:s') . ' <span style="color: white; background-color: #d9534f">' . $username . ' 退出聊天室</span>';
            $sql = "INSERT  INTO `cake_msg_list` (msg, uname, action, send_time) VALUES ('', '$username', 'close', NOW());";
            $mysql->runSql($sql);
            $mysql->closeDb();
            //向列表之中的所有用户发送消息
            foreach ($user_list as $uid => $uname) {
                $channel_name = 'chatbox.' . $uid;
                $send = $channel->sendMessage($channel_name, $msg);
                if ($send !== TRUE) {
                    unset($user_list[$uid]);
                }
            }
            $kv->set('ChatingUserList', $user_list);
        }
        exit(0);
    }

}