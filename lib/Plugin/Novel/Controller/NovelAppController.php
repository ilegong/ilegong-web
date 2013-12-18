<?php

App::uses('TopClient', 'TaobaoSDK');

class TaobaoAppController extends AppController {

    public function beforeFilter() {
        @set_time_limit(0);
        parent::beforeFilter();
        $this->loadModel('Taobao.TaobaoCate');
        $this->loadModel('Taobao.Taobaoke');
        $this->loadModel('Taobao.TaobaoPromotion');
        // 把插件中需要用到的模块都加载进来，否则在其他内容中加载模块时，没有plugin的名称，加载的appmodel
    }

}
?>