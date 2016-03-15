<?php

class UserFansComponent extends Component{

    public function get_fans($uid){
        $queryCond = ['conditions' => ['user_id' => $uid, 'deleted' => DELETED_NO], 'limit' => 100];
        $users = $this->process_query_data($queryCond, 'follow_id');
        return $users;
    }

    public function get_subs($uid){
        $queryCond = ['conditions' => ['follow_id' => $uid, 'deleted' => DELETED_NO], 'limit' => 100];
        $users = $this->process_query_data($queryCond, 'user_id');
        return $users;
    }

    private function process_query_data($queryCond, $extract_field){
        $userRelationM = ClassRegistry::init('UserRelation');
        $userM = ClassRegistry::init('User');
        $relations = $userRelationM->find('all', $queryCond);
        $user_ids = Hash::extract($relations, '{n}.UserRelation.' . $extract_field);
        $users_data = $userM->find('all', array(
            'conditions' => array(
                'id' => $user_ids
            ),
            'fields' => array('id', 'nickname', 'image', 'avatar'),
            'order' => array('id DESC')
        ));
        $levels_data = $this->get_user_level_map($user_ids);
        return ['users' => $users_data, 'level_map' => $levels_data];
    }

    private function get_user_level_map($user_ids){
        $userLevelM = ClassRegistry::init('UserLevel');
        $levels_data = $userLevelM->find('all', [
            'conditions' => ['data_id' => $user_ids, 'deleted' => DELETED_NO]
        ]);
        $levels_data = Hash::combine($levels_data, '{n}.UserLevel.data_id', '{n}.UserLevel.data_value');
        return $levels_data;
    }
}