<?php
class ImsController extends AppController{

    public function beforeFilter(){
        parent::beforeFilter();
        $this->autoRender=false;
    }
	
    public function resume(){
        $userinfo = $this->Auth->user();

        $this->loadModel('Friend');
        $friends = $this->Friend->find('all',array(
            'recursive' => -1,
            'conditions'=>array('Friend.user_id'=>$userinfo['id']),
            'fields'=>array('User.*'),
            'joins'=>array(
                array(
                'table' => 'users',
                'type' => 'inner',
                'alias' => 'User',
                'conditions' => array('User.id=Friend.friend_id'),
                )
            )
        ));
        $user_friends = array();
        foreach ($friends as $friend){
            $user_friends[] = array(
                'u'=>$friend['User']['username'],
                's' => 1,
                'g' => 'group'
            );
        }
        
        if(!empty($userinfo)){
            echo json_encode(array('u'=>$userinfo['username'],'r'=>'connected','s'=>$this->Session->id(),'f'=> $user_friends));
        }
        else{
            echo json_encode(array('r' => 'error', 'e' => 'user not login'));
        }
        exit;
    }

    public function send($to, $message) {
        if(!$this->username)
            return array('r' => 'error', 'e' => 'no session found');

        $message = $this->_sanitize($message);
        
        $to = User::find($to);
        if(!$to)
            return array('r' => 'error', 'e' => 'no_recipient');

        if(Message::send($this->user_id, $to->user_id, $message)) {
            return array('r' => 'sent');
        } else {
            return array('r' => 'error', 'e' => 'send error');
        }
    }

    public function poll(){
        $this->autoRender=false;
        $method = $_GET['method'];
        // If output buffering hasn't been setup yet...
        if(count(ob_list_handlers()) < 2) {
            // Buffer output, but flush always after any output
            ob_start();
            ob_implicit_flush(true);

            // Set the headers such that our response doesn't get cached
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }

        switch($method) {
            case 'long':
                return $this->_longPoll();
            break;

            case 'comet':
                return $this->_comet();
            break;

            default:
            case 'short':
                return $this->_shortPoll();
            break;
        }
    }


    // === //private// {{{Default_IM::}}}**{{{_longPoll()}}}** ===
    //
    // Use the long polling technique to check for and deliver new messages.
    private function _longPoll() {
        set_time_limit(30);

        // We're going to keep a running tally of the number of times
        // we've checked for, but haven't received, messages. As that
        // number increases, the sleep duration will increase.

        $no_msg_count = 0;
        $start = time();
        do {
            $messages = Message::getMany('to', $this->user_id);

            if(!$messages) {
                $no_msg_count++;
                sleep(2.5 + min($no_msg_count * 1.5, 7.5));
            }
        } while(!$messages && time() - $start < 30);

        if($messages)
            return $this->_pollParseMessages($messages);
        else
            return array();
    }

    // === //private// {{{Default_IM::}}}**{{{_shortPoll()}}}** ===
    //
    // Use the short polling technique to check for and deliver new messages.
    private function _shortPoll() {
        $messages = Message::getMany('to', $this->user_id);

        if($messages) {
            return $this->_pollParseMessages($messages);
        } else {
            return array('r' => 'no messages');
        }
    }

    // === //private// {{{Default_IM::}}}**{{{_comet()}}}** ===
    //
    // Use the comet/streaming technique to check for and deliver new messages.
    private function _comet() {
        set_time_limit(0);

        // First, fix buffers
        echo str_repeat(chr(32), self::FIXBUFFER) . self::FIXEOL , ob_get_clean();

        $no_msg_count = 0;
        while(true) {
            $messages = Message::getMany('to', $this->user_id);

            if(!$messages) {
                $no_msg_count++;
                sleep(2.5 + min($no_msg_count * 1.5, 7.5));
                echo chr(32) , ob_get_clean();
                if(connection_aborted()) return;
            } else {
                $no_msg_count = 0;
                echo '<script type="text/javascript">parent.AjaxIM.incoming(' .
                    json_encode($this->_pollParseMessages($messages)) .
                ');</script><br/>', ob_get_clean();
                sleep(1);
            }
        }
    }

    //
    // Parse each message object and return it as an array deliverable to the client.
    //
    // ==== Parameters ====
    // * {{{$messages}}} is the array of message objects.
    private function _pollParseMessages($messages) {
        $msg_arr = array();
        foreach($messages as $msg) {
            $msg_arr[] = array('t' => $msg->type, 's' => $msg->from, 'r' => $msg->to, 'm' => $msg->message);
        }
        return $msg_arr;
    }

    // === //private// {{{Default_IM::}}}**{{{_sanitize()}}}** ===
    //
    // Sanitize messages by preventing any HTML tags from being created
    // (replaces &lt; and &gt; entities).
    private function _sanitize($str) {
        return str_replace('>', '&gt;', str_replace('<', '&lt;', str_replace('&', '&amp;', $str)));
    }
}