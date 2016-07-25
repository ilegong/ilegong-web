<?php

/**
 * Class SpreadConf
 *
 * 团长推广的配置
 */
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
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/0e72665aaf0_1118.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400867431&idx=1&sn=329a6932fbf666c10a599be327eac7e4'
        ),
        //鲲
        806889 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/c3f462e211c_1118.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400868619&idx=1&sn=db972ca21c45da233d846ecd89940b60'
        ),
        //小宝妈
        811917 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/833f2b7b706_1118.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=400870041&idx=1&sn=c83330a75f6bb27862c3a8de4b08807a'
        ),
        //四齐
        866881 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/0aee2bc1cb8_1124.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=401036111&idx=1&sn=87ee0619ab9fcb9fa35e057dba03a403&scene=1'
        ),
        //云朵朵
        879936 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201511/thumb_m/319c148999a_1126.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=401091825&idx=1&sn=00715d72109abf15cf14a1a129f9d14f&scene=1'
        ),
        //赵静
        867250 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/8b878fee4ae_1216.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=401860042&idx=1&sn=f52c07f411c336f9d3dd08797e36c417&scene=1'
        ),
        //赵静
        848454 => array(
            'wx_pic' => 'http://51daifan-images.stor.sinaapp.com/files/201512/thumb_m/389fa44ba2f_1223.jpg',
            'wx_introduce_url' => 'http://mp.weixin.qq.com/s?__biz=MjM5MjY5ODAyOA==&mid=402114100&idx=1&sn=745eaf05b92a31034df5a75cea1438c5&scene=1'
        ),
        //后现代城
        930646 => array(
            'wx_pic' => '',
            'wx_introduce_url' => ''
        )
    );

    public function get_sharer_conf($sharer_id) {
        return $this->sharerConf[$sharer_id];
    }

}