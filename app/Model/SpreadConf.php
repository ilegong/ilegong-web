<?php

class SpreadConf extends AppModel {

    public $useTable = false;

    var $sharerConf = array(
        //测试数据
        633345 => array(
            'wx_pic' => 'https://mmbiz.qlogo.cn/mmbiz/qpxHrxLKdR0A6F8hWz04wVpntT9Jiao8XZn7as5FuHch5zFzFnvibjUGYU3J4ibxRyLicytfdd9qDQoqV1ODOp3Rjg/0',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=201694178&idx=1&sn=8dea494e02c96dc21e51931604771748#rd',
        ),
        //片片妈
        878825 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/5aa958fe129_1114.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400808364&idx=1&sn=3793771175d55f44090b64c6a9840551#rd'
        ),
        //樱花
        810684 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/0e72665aaf0_1118.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400867431&idx=1&sn=329a6932fbf666c10a599be327eac7e4'
        ),
        //鲲
        806889 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/c3f462e211c_1118.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400868619&idx=1&sn=db972ca21c45da233d846ecd89940b60'
        )
    );

    public function get_sharer_conf($sharer_id) {
        return $this->sharerConf[$sharer_id];
    }

}