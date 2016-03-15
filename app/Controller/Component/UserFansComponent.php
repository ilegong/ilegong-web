<?php

class UserFansComponent extends Component{

    public function get_fans($uid){
        $queryCond = ['conditions' => ['user_id' => $uid, 'deleted' => DELETED_NO], 'limit' => 100];
        $data = $this->process_query_data($queryCond, 'follow_id', $uid);
        return $data;
    }

    public function get_subs($uid){
        $queryCond = ['conditions' => ['follow_id' => $uid, 'deleted' => DELETED_NO], 'limit' => 100];
        $users = $this->process_query_data($queryCond, 'user_id', $uid);
        return $users;
    }

    private function process_query_data($queryCond, $extract_field, $uid){
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
        $sub_user_ids = $this->get_user_subs($uid, $user_ids);
        return ['users' => $users_data, 'level_map' => $levels_data, 'sub_user_ids' => $sub_user_ids];
    }

    private function get_user_subs($uid, $user_ids){
        $userRelationM = ClassRegistry::init('UserRelation');
        $relations = $userRelationM->find('all', [
            'conditions' => ['follow_id' => $uid, 'user_id' => $user_ids]
        ]);
        $sub_user_ids = Hash::extract($relations, '{n}.UserRelation.user_id');
        return $sub_user_ids == null ? [] : $sub_user_ids;
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