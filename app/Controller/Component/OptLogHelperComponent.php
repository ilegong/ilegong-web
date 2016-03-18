<?php

/**
 * opt log helper
 */
class OptLogHelperComponent extends Component {

    var $components = array('WeshareBuy', 'ShareUtil');

    public function __get($key)
    {
        if ($key == 'uid') {
            return $_SESSION['Auth']['User']['id'];
        }
        return parent::__get($key);
    }

    public function load_opt_log($time, $limit, $type, $new = false, $followed = false) {
        //check cache init cache
        $opt_logs = $this->load_opt_log_by_time($time, $new, $followed);
        if (!$opt_logs) return false;
        $combine_data = $this->combine_opt_log_data($opt_logs, $new, $followed);
        $opt_logs = Hash::extract($opt_logs, '{n}.OptLog');
        $opt_logs = array_map('map_opt_log_data', $opt_logs);
        usort($opt_logs, 'sort_data_by_id');
        $opt_log_data = [
            'opt_logs' => $opt_logs,
            'combine_data' => $combine_data
        ];
        if ($new) {
            $opt_log_data = $this->rearrange($opt_log_data);
        }
        return $opt_log_data;
    }

    /**
     * @param $time
     * @return array
     * load opt_log
     */
    private function load_opt_log_by_time($time, $new = false, $followed = false) {
        $optLogM = ClassRegistry::init('OptLog');
        if ($new) {
            $opt_logs = $optLogM->new_fetch_by_time_limit_type(date('Y-m-d H:i:s', $time), 10, 0, $followed);
        } else {
            $opt_logs = $optLogM->fetch_by_time_limit_type(date('Y-m-d H:i:s', $time), 10, 0);
        }
        return $opt_logs;
    }

    /**
     * @param $opt_logs
     * @return array
     */
    private function combine_opt_log_data($opt_logs, $share_info = false, $followed = false) {
        $opt_user_ids = Hash::extract($opt_logs, '{n}.OptLog.user_id');
        $opt_data_ids = Hash::extract($opt_logs, '{n}.OptLog.obj_id');
        if ($share_info) {
            $shares_info = $this->get_share_and_user_info($opt_data_ids);
        } else {
            $share_info = [];
        }
        $share_buy_user_info = $this->WeshareBuy->get_has_buy_user_map($opt_data_ids);
        $share_user_map = $share_buy_user_info['share_user_map'];
        $buy_user_ids = $share_buy_user_info['all_user_ids'];
        $opt_user_ids = array_merge($opt_user_ids, $buy_user_ids);
        $opt_user_ids = array_unique($opt_user_ids);
        $userM = ClassRegistry::init('User');
        $opt_users = $userM->find('all', array(
            'conditions' => array(
                'id' => $opt_user_ids
            ),
            'fields' => array('id', 'nickname', 'image', 'is_proxy', 'avatar')
        ));
        $userRelationM = ClassRegistry::init('UserRelation');
        $opt_users_share_info = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $opt_user_ids
            ),
            'group' => array('user_id'),
            'fields' => array(
                'count(id) as fans_count', 'user_id'
            )
        ));
        $users_level_data = $this->ShareUtil->get_users_level($opt_user_ids);
        $opt_users_share_info = Hash::combine($opt_users_share_info, '{n}.UserRelation.user_id', '{n}.0.fans_count');
        $opt_users = Hash::combine($opt_users, '{n}.User.id', '{n}.User');
        $opt_users = array_map('map_user_avatar', $opt_users);
        $combine_opt_log_data = array(
            'users' => $opt_users,
            'users_level' => $users_level_data,
            'user_fans_extra' => $opt_users_share_info,
            'share_user_buy_map' => $share_user_map,
            'share_info' => $shares_info
        );
        return $combine_opt_log_data;
    }

    private function get_share_and_user_info($shares)
    {
        $model = ClassRegistry::init('Weshare');
        $data = $model->find('all', [
            'conditions' => [
                'Weshare.id' => $shares,
            ],
            'fields' => [
                'Weshare.*',
                'User.*',
                'UserLevel.*'
            ],
            'joins' => [
                [
                    'table' => 'users',
                    'alias' => 'User',
                    'conditions' => [
                        'User.id = Weshare.creator',
                    ],
                ], [
                    'table' => 'user_levels',
                    'alias' => 'UserLevel',
                    'conditions' => [
                        'User.id = UserLevel.data_id',
                    ],
                ],
            ],
        ]);

        $level_pool = [
            0 => '分享达人',
            1 => '实习团长',
            2 => '正式团长',
            3 => '优秀团长',
            4 => '高级团长',
            5 => '资深团长',
            6 => '首席团长'
        ];

        $ret = [];
        $my_proxys = ClassRegistry::init('OptLog')->get_my_proxys();
        foreach($data as $item) {
            $share = $item['Weshare'];
            $user = $item['User'];
            $level = $item['UserLevel']['data_value'];
            $tmp = [];
            $tmp['share_id'] = $share['id'];
            $nickname = $user['nickname'];
            if (mb_strlen($nickname) > 4) {
                $tmp['proxy'] = mb_substr($nickname, 0, 4) . '...';
            } else {
                $tmp['proxy'] = $nickname;
            }
            $tmp['proxy_id'] = $user['id'];
            $tmp['current_user'] = $this->uid;
            $tmp['check_relation'] = in_array($user['id'], $my_proxys);
            $tmp['avatar'] = get_user_avatar($user) ? : "http://static.tongshijia.com/static/img/default_avatar.png";
            $tmp['level'] = "V{$level}{$level_pool[$level]}";
            $tmp['title'] = $share['title'];
            if (mb_strlen($share['description']) > 110) {
                $tmp['description'] = mb_substr($share['description'], 0, 110) . "...";
                $tmp['description_more'] = true;
            } else {
                $tmp['description'] = $share['description'];
                $tmp['description_more'] = false;
            }
            $image = explode('|', $share['images'])[0];
            $tmp['image'] = $image ? : "http://static.tongshijia.com/static/img/default_product_banner.png";
            // 1. 报名数
            $tmp['baoming'] = $this->WeshareBuy->get_share_and_all_refer_share_count($share['id'], $user['id']);
            // 2. 浏览数
            $tmp['liulan'] = $share['view_count'];

            $ret[$share['id']] = $tmp;
        }

        return $ret;
    }

    public function rearrange($data)
    {
        $ret = [];
        $users = $data['combine_data']['users'];
        foreach($data['opt_logs'] as $item) {
            // 下面是我自己(宋志刚)的combine data
            $tmp = $data['combine_data']['share_info'][$item['obj_id']];

            // 接下来组合上愣愣的数据
            $customer = $item['user_id'];
            $tmp['customer'] = $users[$customer]['nickname'];
            $tmp['time'] = $item['timestamp'];
            $tmp['readtime'] = $this->get_read_time($item['timestamp']);
            $tmp['data_url'] = $item['data_url'];

            $ret[] = $tmp;
        }

        return $ret;
    }

    private function get_read_time($t)
    {
        $now = time();
        $day = 24 * 60 * 60;
        $hour = 60 * 60;
        $minute = 60;
        $diff = $now - $t;

        if ($diff > $day) {
            $str = number_format($diff / $day, 0, '.', '') . "天前";
        } elseif ($diff > $hour) {
            $str = number_format($diff / $hour, 0, '.', '') . "小时前";
        } else {
            $min = number_format($diff / $minute, 0, '.', '');
            $str = $min ? $min . "分钟前" : "刚刚";
        }

        return $str;
    }
}
