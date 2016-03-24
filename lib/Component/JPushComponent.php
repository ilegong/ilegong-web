<?php

class JPushComponent extends Component{

    public function push(){

    }

    public function device(){

    }

    public function report(){

    }

    public function schedule(){

    }

    private function get_push_client(){
        // 初始化
        App::import('Vendor', 'JPush/JPush');
        $client = new JPush(JPUSH_APP_KEY, JPUSH_APP_SECRET);

    }

}