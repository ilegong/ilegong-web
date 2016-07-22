<?php

class WxShareStatisticsComponent extends Component{



    public function getWeshareSummary($uid){
        $this->injectModel();


    }


    private function  injectModel(){
        $this->WxShare = ClassRegistry::init('WxShare');
        $this->Weshare = ClassRegistry::init('Weshare');
        $this->ShareTrackLog = ClassRegistry::init('ShareTrackLog');
    }

}