<?php

class UserFansComponent extends Component{

    static $PAGE_LIMIT = 50;

    public function get_fans_count_summary($uid){
        $UserRelationM = ClassRegistry::init('UserRelation');
        $summary = $UserRelationM->find('all', [
            'conditions' => [
                'user_id' => $uid,
                'deleted' => DELETED_NO
            ],
            'group' => 'is_own',
            'fields' => ['count(`id`) as `u_c`', 'is_own']
        ]);
        $result = ['total_self' => 0, 'total_comm' => 0];
        foreach ($summary as $item) {
            $i_is_own = $item['UserRelation']['is_own'];
            if ($i_is_own == 0) {
                $result['total_self'] = $item['0']['u_c'];
            }
            if ($i_is_own == 1) {
                $result['total_comm'] = $item['0']['u_c'];
            }
        }
        return $result;
    }

    public function get_fans_web($uid, $page, $limit, $is_own){

    }

    public function get_fans($uid, $page = 1, $query = null, $limit = 0, $is_own = 0){
        $limit = $limit == 0 ? self::$PAGE_LIMIT : $limit;
        $queryCond = ['conditions' => ['UserRelation.user_id' => $uid, 'UserRelation.deleted' => DELETED_NO, 'UserRelation.is_own' => $is_own], 'fields' => ['UserRelation.*']];
        if (!empty($query)) {
            $queryCond['joins'] = [[
                'table' => 'users',
                'alias' => 'User',
                'type' => 'INNER',
                'conditions' => [
                    'User.id = UserRelation.follow_id',
                ]
            ]];
            $queryCond['conditions']['User.nickname like'] = '%' . $query . '%';
        }
        $data = $this->process_query_data($queryCond, 'follow_id', $uid, $page);
        if ($page == 1) {
            $page_info = $this->get_page_info($queryCond, $limit);
            $data['page_info'] = $page_info;
        }
        return $data;
    }

    public function get_subs($uid, $page = 1, $query = null, $limit=0){
        $limit = $limit == 0 ? self::$PAGE_LIMIT : $limit;
        $queryCond = ['conditions' => ['UserRelation.follow_id' => $uid, 'UserRelation.deleted' => DELETED_NO], 'fields' => ['UserRelation.*']];
        if (!empty($query)) {
            $queryCond['joins'] = [[
                'table' => 'users',
                'alias' => 'User',
                'type' => 'INNER',
                'conditions' => [
                    'User.id = UserRelation.user_id',
                ]
            ]];
            $queryCond['conditions']['User.nickname like'] = '%' . $query . '%';
        }
        $data = $this->process_query_data($queryCond, 'user_id', $uid, $page);
        if ($page == 1) {
            $page_info = $this->get_page_info($queryCond, $limit);
            $data['page_info'] = $page_info;
        }
        return $data;
    }

    private function process_query_data($queryCond, $extract_field, $uid, $page){
        $userRelationM = ClassRegistry::init('UserRelation');
        $userM = ClassRegistry::init('User');
        $queryCond['limit'] = self::$PAGE_LIMIT;
        $queryCond['page'] = $page;
        $relations = $userRelationM->find('all', $queryCond);
        $user_ids = Hash::extract($relations, '{n}.UserRelation.' . $extract_field);
        $users_data = $userM->find('all', [
            'conditions' => [
                'id' => $user_ids
            ],
            'fields' => ['id', 'nickname', 'image', 'avatar', 'label', 'description'],
            'order' => ['id DESC']
        ]);
        $users_data = array_map('map_user_avatar2', $users_data);
        $levels_data = $this->get_user_level_map($user_ids);
        $sub_user_ids = $this->get_user_subs($uid, $user_ids);
        return ['users' => $users_data, 'level_map' => $levels_data, 'sub_user_ids' => $sub_user_ids];
    }

    private function get_user_subs($uid, $user_ids){
        $userRelationM = ClassRegistry::init('UserRelation');
        $relations = $userRelationM->find('all', [
            'conditions' => ['follow_id' => $uid, 'user_id' => $user_ids, 'deleted' => DELETED_NO]
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

    private function get_page_info($queryCond, $limit){
        $userRelationM = ClassRegistry::init('UserRelation');
        $count = $userRelationM->find('count', $queryCond);
        $page_count = ceil($count / $limit);
        return ['page_count' => $page_count, 'limit' => $limit];
    }

}