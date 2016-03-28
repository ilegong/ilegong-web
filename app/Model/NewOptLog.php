<?php
class NewOptLog extends AppModel
{
    public function get_all_logs($time, $limit = 10, $type = 0, $followed = false)
    {
        $conditions = [];
        if ($type != 0) {
            // 全部类型
            $conditions['NewOptLog.data_type_tag'] = $type;
        }

        if ($time == 0) {
            $time = time();
        }

        if ($followed && $uid = $_SESSION['Auth']['User']['id']) {
            // 当用户选定只看fllowed的团长的东西时, 我们需要做一些过滤.
            // 我决定在这里给用户显示它关注的非团长信息, 都关注了,
            // 不显示不够意思
            $$proxys = ClassRegistry::init('User')->get_my_proxys($uid);
            if (!$$proxys) {
                return false;
            }
        } else {
            // 获取团长的分享
            // 这个排除策略太蠢了, 我想不出来好办法啦...
            $proxys = ClassRegistry::init('UserLevel')->find('all', [
                'conditions' => [
                    'data_value > ' => 0,
                ],
                'fields' => 'data_id',
            ]);

            $proxys = Hash::extract($proxys, '{n}.UserLevel.data_id');
        }

        $data = $this->find('all', [
            'conditions' => array_merge($conditions, [
                'NewOptLog.deleted' => DELETED_NO,
                'NewOptLog.time < ' => date('Y-m-d H:i:s', $time),
            ]),
            'fields' => [
                'NewOptLog.*',
                'Proxy.*',
                'ProxyLevel.*',
                'Customer.*',
                'Weshare.*',
            ],
            'joins' => [
                [
                    'table' => 'users',
                    'alias' => 'Proxy',
                    'conditions' => [
                        'NewOptLog.proxy_id = Proxy.id',
                        'Proxy.id' => $proxys,
                    ],
                ], [
                    'table' => 'user_levels',
                    'alias' => 'ProxyLevel',
                    'conditions' => 'Proxy.id = ProxyLevel.data_id',
                ], [
                    'table' => 'users',
                    'alias' => 'Customer',
                    'conditions' => 'NewOptLog.customer_id = Customer.id',
                ], [
                    'table' => 'weshares',
                    'alias' => 'Weshare',
                    'conditions' => 'NewOptLog.share_id = Weshare.id',
                ],
            ],
            'order' => 'time desc',
            'limit' => $limit,
        ]);

        return $data;
    }
}
