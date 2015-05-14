<?php
/**
 * Created by PhpStorm.
 * User: shichaopeng
 * Date: 5/12/15
 * Time: 10:11
 */
class ReferAward extends AppModel{

    public $useTable = false;

    var $allAwards = array(
        '1' => array(
            'id' => 1,
            'name' => 'iPad Mini 3 16G',
            'price' => 2888,
            'exchange_condition' => 298,
            'limit_num' => 1,
            'image' => '/img/refer/good12.jpg',
            'url' => '',
            'order'=>1,
            'hot'=>false
        ),
        '2' => array(
            'id' => 2,
            'name' => '小米电视2 40寸',
            'price' => 1999,
            'exchange_condition' => 220,
            'limit_num' => 1,
            'image' => '/img/refer/good11.jpg',
            'url' => '',
            'order'=>2,
            'hot'=>false
        ),
        '3' => array(
            'id' => 3,
            'name' => '美的（Midea）烤箱',
            'price' => 300,
            'exchange_condition' => 32,
            'limit_num' => 5,
            'image' => '/img/refer/good10.jpg',
            'url' => '',
            'order'=>5,
            'hot'=>false
        ),
        '4' => array(
            'id' => 4,
            'name' => '美的（Midea）吸尘器',
            'price' => 188,
            'exchange_condition' => 22,
            'limit_num' => 5,
            'image' => '/img/refer/good9.jpg',
            'url' => '',
            'order'=>6,
            'hot'=>false
        ),
        '5' => array(
            'id' => 5,
            'name' => '小米16000毫安移动电源',
            'price' => 129,
            'exchange_condition' => 15,
            'limit_num' => 10,
            'image' => '/img/refer/good6.jpg',
            'url' => '',
            'order'=>7,
            'hot'=>false
        ),
        '6' => array(
            'id' => 6,
            'name' => '韩国正品Guerisson奇迹马油1瓶',
            'price' => 96,
            'exchange_condition' => 12,
            'limit_num' => 10,
            'image' => '/img/refer/good5.jpg',
            'url' => '',
            'order'=>8,
            'hot'=>false
        ),
        '7' => array(
            'id' => 7,
            'name' => '海南空运千层水果蛋糕1个  8寸/个',
            'price' => 160,
            'exchange_condition' => 16,
            'limit_num' => 20,
            'image' => '/img/refer/good7.jpg',
            'url' => '',
            'order'=>9,
            'hot'=>false
        ),
        '8' => array(
            'id' => 8,
            'name' => '北京果园散养柴鸡1只',
            'price' => 160,
            'exchange_condition' => 16,
            'limit_num' => 20,
            'image' => '/img/refer/good8.jpg',
            'url' => '',
            'order'=>10,
            'hot'=>false
        ),
        '9' => array(
            'id' => 9,
            'name' => '海南空运出口级金菠萝农场直采摘 2个',
            'price' => 40,
            'exchange_condition' => 6,
            'limit_num' => 100,
            'image' => '/img/refer/good1.jpg',
            'url' => '',
            'order'=>11,
            'hot'=>false
        ),
        '10' => array(
            'id' => 10,
            'name' => '海南空运 无添加 新鲜椰子冻1个 700g/个',
            'price' => 40,
            'exchange_condition' => 6,
            'limit_num' => 100,
            'image' => '/img/refer/good2.jpg',
            'url' => '',
            'order'=>12,
            'hot'=>false
        ),
        '12' => array(
            'id' => 12,
            'name' => '山东烟台·栖霞苹果 不打蜡 无农药 绿色健康 5斤',
            'price' => 40,
            'exchange_condition' => 6,
            'limit_num' => 100,
            'image' => '/img/refer/good4.jpg',
            'url' => '',
            'order'=>13,
            'hot'=>false
        ),
        '13' => array(
            'id' => 13,
            'name' => '顺义杨姐家散养北油鸡柴鸡蛋6个',
            'price' => 9.9,
            'exchange_condition' => 1,
            'limit_num' => 0,
            'image' => '/img/refer/good13.jpg',
            'url' => '',
            'order'=>4,
            'hot'=>true
        ),
        '14' => array(
            'id' => 14,
            'name' => '万得妙原味八连杯酸奶 100g*6杯',
            'price' => 9.9,
            'exchange_condition' => 1,
            'limit_num' => 0,
            'image' => '/img/refer/good14.jpg',
            'url' => '',
            'order'=>3,
            'hot'=>true
        ),
    );

    public function getAllAward(){
        usort($this->allAwards,'sort_award');
        return $this->allAwards;
    }

    public function getAwardById($id){
        return $this->allAwards[$id];
    }

}