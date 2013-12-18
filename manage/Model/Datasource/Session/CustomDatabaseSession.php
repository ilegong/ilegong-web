<?php

App::uses('DatabaseSession', 'Model/Datasource/Session');

class CustomDatabaseSession extends DatabaseSession {

    public function write($id, $data) {
        $array = CakeSession::read();
        $uid = $array['Auth']['Staff']['id'];
        $username = $array['Auth']['Staff']['name'];        
        if (!$id) {
            return false;
        }
        $expires = time() + $this->_timeout;
        $record = array('id'=>$id, 'data'=>$data, 'uid'=>$uid, 'username'=>$username, 'expires'=>$expires);
        $record[$this->_model->primaryKey] = $id;
        return $this->_model->save($record);
    }
}
