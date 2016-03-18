<?php

class SharePoolProduct extends AppModel
{

    public $useTable = false;

    public function __get($key)
    {
        if ($key == 'products') {
            return $this->get_all_pool_products();
        }

        return parent::__get($key);
    }

    public function get_all_pool_products()
    {
        $model = ClassRegistry::init('PoolProduct');
        $data = $model->find('all', [
            'conditions' => [
                'PoolProduct.deleted' => DELETED_NO,
                'PoolProduct.status' => 1,
            ],
            'order' => ['PoolProduct.sort ASC'],
        ]);

        return $this->rearrange_data($data);
    }

    private function rearrange_data($data)
    {
        $items = Hash::extract($data, '{n}.PoolProduct');
        usort($items, function($one, $another){
            return $one['sort'] > $another['sort'];
        });
        return $items;
    }

    /**
     * @return array
     * 获取产品池中所有产品
     */
    public function get_all_products()
    {
        return $this->products;
    }

    /**
     * @param $share_id
     * @return mixed
     * 获取产品池中产品的试吃id
     */
    public function get_product_buy_config($share_id)
    {
        return $this->product_buy_map[$share_id];
    }

    /**
     * @param $share_id
     * @return mixed
     * 获取团长从产品池中分享出去所有的分享
     */
    public function get_fork_share_ids($share_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $shares = $weshareM->find('all', array(
            'conditions' => array(
                'refer_share_id' => $share_id,
                'not' => array('type' => POOL_SHARE_TYPE)
            ),
            'fields' => array('id', 'creator', 'status', 'type'),
            'limit' => 100
        ));
        return $shares;
    }

    public function get_all_fork_shares($weshareId)
    {
        $data = $this->get_fork_share_info_with_username($weshareId);
        $ret = [];
        foreach ($data as $v) {
            $ret[] = $v['id'];
        }

        return $ret;
    }

    /**
     * @param $share_id
     * @return mixed
     * 获取团长从产品池中分享出去所有的分享(带用户名版本)
     */
    public function get_fork_share_info_with_username($share_id)
    {
        $weshareM = ClassRegistry::init('Weshare');
        $shares = $weshareM->find('all', [
            'conditions' => [
                'Weshare.refer_share_id' => $share_id,
                'not' => ['Weshare.type' => POOL_SHARE_TYPE]
            ],
            'joins' => [
                [
                    'table' => 'users',
                    'alias' => 'Users',
                    'conditions' => [
                        'Weshare.creator = Users.id',
                    ],
                ]
            ],
            'fields' => [
                'Weshare.id',
                'Weshare.creator',
                'Weshare.status',
                'Weshare.type',
                'Users.nickname',
            ],
        ]);

        $data = [];
        foreach ($shares as $v) {
            $tmp = [];
            $tmp['id'] = $v['Weshare']['id'];
            $tmp['creator'] = $v['Weshare']['creator'];
            $tmp['status'] = $v['Weshare']['status'];
            $tmp['type'] = $v['Weshare']['type'];
            $tmp['nickname'] = $v['Users']['nickname'];
            $data[] = $tmp;
        }
        return $data;
    }

    //产品和试吃的对应关系
    var $product_buy_map = [];

    /*array(
        '1411' => array(
            //'try' => 56,
            'buy' => 1463
        ),
        '1432' => array(
            'buy' => 1464
        ),
        '1433' => array(
            'buy' => 1466
        ),
        '1430' => array(
            'buy' => 1467
        ),
        '1438' => array(
            'buy' => 1468
        ),
        '1445' => array(
            'buy' => 1469
        ),
        '1447' => array(
            'buy' => 1471
        ),
//        '1448' => array(
//            'buy' => 1473
//        ),
        '1449' => array(
            'buy' => 1474
        ),
        '1450' => array(
            'buy' => 1475
        ),
//        '1703' => array(
//            'buy' => 1704
//        ),
        '1492' => array(
            'buy' => 1752
        ),
        '1783' => array(
            'buy' => 2018
        )
    );
    */
}
