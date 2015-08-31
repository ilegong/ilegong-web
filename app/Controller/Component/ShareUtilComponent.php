<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 8/20/15
 * Time: 15:04
 */

class ShareUtilComponent extends Component{

    var $name = 'ShareUtil';

    public $components = array('Weixin', 'WeshareBuy');

    public function process_weshare_task($weshareId, $sharer_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $orderM = ClassRegistry::init('Order');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'member_id' => $weshareId,
                'type' => ORDER_TYPE_WESHARE_BUY,
                'status' => array(ORDER_STATUS_DONE, ORDER_STATUS_PAID, ORDER_STATUS_RECEIVED, ORDER_STATUS_DONE, ORDER_STATUS_RETURN_MONEY, ORDER_STATUS_RETURNING_MONEY)
            ),
            'fields' => array('id', 'creator'),
            'limit' => 500
        ));
        $saveDatas = array();
        foreach ($orders as $order) {
            if ($this->check_user_relation($sharer_id, $order['Order']['creator'])) {
                $itemData = array('user_id' => $sharer_id, 'follow_id' => $order['Order']['creator'], 'type' => 'Buy', 'created' => date('Y-m-d H:i:s'));
                $saveDatas[] = $itemData;
            }
        }
        $userRelationM->saveAll($saveDatas);
    }

    public function get_all_weshares(){
        $weshareM = ClassRegistry::init('Weshare');
        $allWeshares = $weshareM -> find('all' , array(
            'limit' => 200
        ));
        return $allWeshares;
    }

    public function check_user_is_subscribe($user_id, $follow_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('first', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return (!empty($relation) && ($relation['UserRelation']['deleted'] == DELETED_NO));
    }

    public function check_user_relation($user_id, $follow_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $relation = $userRelationM->find('all', array(
            'conditions' => array(
                'user_id' => $user_id,
                'follow_id' => $follow_id
            )
        ));
        return empty($relation);
    }

    public function delete_relation($sharer_id, $user_id) {
        $userRelationM = ClassRegistry::init('UserRelation');
        $userRelationM->updateAll(array('deleted' => DELETED_YES), array('user_id' => $sharer_id, 'follow_id' => $user_id));
    }

    public function save_relation($sharer_id, $user_id, $type='Buy') {
        $userRelationM = ClassRegistry::init('UserRelation');
        if ($this->check_user_relation($sharer_id, $user_id)) {
            $userRelationM->saveAll(array('user_id' => $sharer_id, 'follow_id' => $user_id, 'type' => $type, 'created' => date('Y-m-d H:i:s')));
        }else{
            $userRelationM->updateAll(array('deleted' => DELETED_NO), array('user_id' => $sharer_id, 'follow_id' => $user_id));
        }
    }

    public function save_rebate_log($recommend, $clicker) {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $history_log = $rebateTrackLogM->find('first', array(
            'conditions' => array(
                'sharer' => $recommend,
                'clicker' => $clicker,
                'order_id' => 0
            )
        ));
        if (!empty($history_log)) {
            return $history_log['RebateTrackLog']['id'];
        }
        $rebate_log = array('sharer' => $recommend, 'clicker' => $clicker, 'created' => date('Y-m-d H:i:s'), 'updated' => date('Y-m-d H:i:s'));
        $rebateTrackLogM->save($rebate_log);
        $rebateLogId = $rebateTrackLogM->id;
        return $rebateLogId;
    }

    public function update_rebate_log($id, $order_id) {
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateTrackLogM->updateAll(array('order_id' => $order_id, 'is_paid' => 1, 'updated' => '\'' . date('Y-m-d H:i:s') . '\''), array('id' => $id));
    }

    public function update_rebate_log_order_id($id, $order_id){
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $rebateTrackLogM->updateAll(array('order_id' => $order_id), array('id' => $id));
    }

    public function get_rebate_money($user_id){
        $rebateTrackLogM = ClassRegistry::init('RebateTrackLog');
        $orderM = ClassRegistry::init('Order');
        $rebateLogs = $rebateTrackLogM->find('all', array(
            'conditions' => array(
                'sharer' => $user_id,
                'not' => array('order_id' => 0, 'is_paid' => 0)
            )
        ));
        $rebateOrderIds = Hash::extract($rebateLogs, '{n}.RebateTrackLog.order_id');
        $orders = $orderM->find('all', array(
            'conditions' => array(
                'id' => $rebateOrderIds
            ),
            'fields' => array('id', 'total_all_price')
        ));
        $order_total_price = array_reduce($orders, 'multi_array_sum');

    }


    public function rebate_users(){

    }

    public function get_share_index_product(){
        $product = array(
            '413'=>array(
                'share_id' => 413,
                'share_name' => '澳洲芦柑',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/c436e3c5c8b_0829.jpg',
                'share_price' => '139',
                'share_user_name' => '小宝妈',
                'share_vote' => 150,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
            '363'=>array(
                'share_id' => 363,
                'share_name' => '天津茶淀玫瑰香葡萄,8小时极致新鲜',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/f16a8e0a80e_0824.jpg',
                'share_price' => '79.9',
                'share_user_name' => '刘忠立',
                'share_vote' => 53,
                'share_user_id' => 801447,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/c0a73f0653b_0822.jpg'
            ),
            '327'=>array(
                'share_id' => 327,
                'share_name' => '纯天然0添加雾岭山山楂条',
                'share_img' => '/img/share_index/shangzhatiao.jpg',
                'share_price' => '12.8',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 123,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '414'=>array(
                'share_id' => 414,
                'share_name' => '平谷后北宫村当季精品桃',
                'share_img' => '/img/share_index/datao.jpg',
                'share_price' => '80-100',
                'share_user_name' => '小宝妈',
                'share_vote' => 150,
                'share_user_id' => 811917,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_0d2b043df6670db623a2448a29af4124.jpg'
            ),
            '357'=>array(
                'share_id' => 357,
                'share_name' => '像太阳一样的猕猴桃～浦江红心猕猴桃',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/594cea5464a_0821.jpg',
                'share_price' => '77',
                'share_user_name' => '盛夏',
                'share_vote' => 95,
                'share_user_id' => 708029,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/8cff05178a6_0807.jpg'
            ),
            '385'=>array(
                'share_id' => 385,
                'share_name' => '[内蒙古土默特]大红李子，天然纯绿色，顺丰包邮',
                'share_img' => '/img/share_index/lizi.jpg',
                'share_price' => '78',
                'share_user_name' => '忠义',
                'share_vote' => 99,
                'share_user_id' => 816006,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/1ef4b665fec_0807.jpg'
            ),
            '388'=>array(
                'share_id' => 388,
                'share_name' => '泰国空运新鲜椰青',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/2d117ce8806_0826.jpg',
                'share_price' => '59',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '389'=>array(
                'share_id' => 389,
                'share_name' => '佳沛奇异果',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/5380268e014_0826.jpg',
                'share_price' => '66',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '390'=>array(
                'share_id' => 390,
                'share_name' => '佳沛金果',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/c0982711918_0826.jpg',
                'share_price' => '59',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '325'=>array(
                'share_id' => 325,
                'share_name' => '来自广西十万大山的原生态巢蜜',
                'share_img' => '/img/share_index/fengchao.jpg',
                'share_price' => '69',
                'share_user_name' => '陈玉燕',
                'share_vote' => 145,
                'share_user_id' => 807492,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0797b2ef26b_0812.jpg'
            ),
            '354'=>array(
                'share_id' => 354,
                'share_name' => '密云山谷中纯正自家产荆花蜜',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/7167382dca7_0826.jpg',
                'share_price' => '38-78',
                'share_user_name' => '樱花',
                'share_vote' => 197,
                'share_user_id' => 810684,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
            ),
            '342'=>array(
                'share_id' => 342,
                'share_name' => '中式酥皮点心之Queen——蛋黄酥',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/f1a425d856d_0818.jpg',
                'share_price' => '60',
                'share_user_name' => '甜欣',
                'share_vote' => 132,
                'share_user_id' => 813896,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/060eced7063_0807.jpg'
            ),
//            '297'=>array(
//                'share_id' => 297,
//                'share_name' => '瑞士卷——每一道甜品都是有故事的',
//                'share_img' => '/img/share_index/danggao.jpg',
//                'share_price' => '128',
//                'share_user_name' => '甜欣',
//                'share_vote' => 132,
//                'share_user_id' => 813896,
//                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_s/060eced7063_0807.jpg'
//            ),
            '326'=>array(
                'share_id' => 326,
                'share_name' => '东北五常稻花香大米五常当地米厂专供',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/965ba48b2d2_0818.jpg',
                'share_price' => '158',
                'share_user_name' => '王谷丹',
                'share_vote' => 187,
                'share_user_id' => 1388,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_928e6bec43ee9674c9abbcf7ce7eae61.jpg'
            ),
            '350'=>array(
                'share_id' => 350,
                'share_name' => '生态农庄之康陵村农户家自产小米@棒渣',
                'share_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/55d76e0b1e3_0818.jpg',
                'share_price' => '26-36',
                'share_user_name' => '樱花',
                'share_vote' => 197,
                'share_user_id' => 810684,
                'share_user_img' => 'http://51daifan-avatar.stor.sinaapp.com/wx_head_79eeee6166bd5c2af6c61fce2d5889eb.jpg'
            ),
            '330'=>array(
                'share_id' => 330,
                'share_name' => '怀柔散养老杨家黑猪肉',
                'share_img' => '/img/share_index/zhurou.jpg',
                'share_price' => '26.6-93.1',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '328'=>array(
                'share_id' => 328,
                'share_name' => '天福号山地散养有机柴鸡蛋',
                'share_img' => '/img/share_index/jidang.jpg',
                'share_price' => '24.8',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
            '329'=>array(
                'share_id' => 329,
                'share_name' => '天福号山地散养油鸡',
                'share_img' => '/img/share_index/jirou.jpg',
                'share_price' => '118',
                'share_user_name' => '朋友说小妹',
                'share_vote' => 203,
                'share_user_id' => 711503,
                'share_user_img' => 'http://51daifan-images.stor.sinaapp.com/files/201508/thumb_m/0609f46d89e_0813.jpg'
            ),
        );
        return $product;
    }
}